import { PlacesAPI } from './places';

export default function companyAutocomplete(config = {}) {
    return {
        query: config.initialQuery || '',
        prefix: config.prefix || '',
        stopIndex: config.stopIndex !== undefined ? config.stopIndex : null,
        results: [],
        googleResults: [],
        isLoading: false,
        isGoogleLoading: false,
        showDropdown: false,
        lastSearched: '', // last query actually sent to Google (cost dedupe)
        mode: 'google', // 'local' | 'google'
        debug: ['localhost', '127.0.0.1'].includes(window.location.hostname) || window.localStorage.getItem('googlePlacesDebug') === '1',

        init() {
            PlacesAPI.init({ debug: this.debug });

            this.$watch('query', (value) => {
                if (value.length < 3) {
                    this.googleResults = [];
                    this.showDropdown = false;
                }
            });
        },

        async search() {
            // Re-route normal search to directly perform Google search.
            if (this.query.length < 3) return;
            this.mode = 'google';
            this.searchGoogle();
        },

        async searchGoogle() {
            const q = this.query.trim();
            if (q.length < 3) return;

            // Cost dedupe: skip re-billing Google if the query hasn't changed
            // since the last request (re-focus, add-then-delete a char, etc.).
            if (q === this.lastSearched) {
                this.showDropdown = true;
                return;
            }

            this.mode = 'google';
            this.lastSearched = q;
            this.isGoogleLoading = true;
            this.showDropdown = true;

            try {
                const suggestions = await PlacesAPI.autocomplete(q);
                this.googleResults = suggestions.map((s) => ({
                    placeId: s.placeId,
                    mainText: s.mainText,
                    secondaryText: s.secondaryText || '',
                }));
            } catch (error) {
                console.error('[PlacesAPI] search error:', error);
                this.googleResults = [];
            } finally {
                this.isGoogleLoading = false;
            }
        },

        async select(result) {
            console.log('[company-autocomplete] selecting:', result.mainText, 'for leg:', this.stopIndex);
            this.query = result.mainText;
            this.googleResults = [];
            this.showDropdown = false;
            this.isGoogleLoading = true;

            try {
                const placeDetails = await PlacesAPI.getDetails(result.placeId);
                console.log('[company-autocomplete] details received:', placeDetails);
                
                if (placeDetails?.parsed) {
                    const p = placeDetails.parsed;
                    const idx = this.stopIndex;
                    const pfx = this.prefix;

                    // 1. DOM-Based Failsafe Sync:
                    // We target elements by a deterministic ID (e.g., field-shipper-city-0)
                    // and dispatch an 'input' event. This mimics a user typing and 
                    // guarantees Alpine's x-model picks it up.
                    const fieldMap = {
                        address_1:    p.address_1   || '',
                        address_2:    p.address_2   || '',
                        city:         p.city        || '',
                        state:        p.state       || '',
                        zip:          p.zip         || '',
                        country:      p.country     || '',
                        lat:          p.lat ?? '',
                        lng:          p.lng ?? '',
                    };

                    Object.entries(fieldMap).forEach(([field, value]) => {
                        const el = document.getElementById(`field-${pfx}-${field}-${idx}`);
                        if (el) {
                            el.value = value;
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    // 2. State Mutation:
                    // Also dispatch the global event for any other logic that depends on it
                    window.dispatchEvent(new CustomEvent('google-place-selected', {
                        detail: {
                            ...p,
                            targetPrefix: this.prefix,
                            targetIndex: this.stopIndex
                        }
                    }));
                    
                    // Update local query to match company name
                    this.query = p.company_name || result.mainText;
                    
                    console.log('[company-autocomplete] DOM and State sync triggered');
                }
            } catch (error) {
                console.error('[PlacesAPI] selection error:', error);
            } finally {
                this.isGoogleLoading = false;
            }
        },

        close() {
            this.showDropdown = false;
        },
    };
}
