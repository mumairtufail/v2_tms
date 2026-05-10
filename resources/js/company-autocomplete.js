import { PlacesAPI } from './places';

export default function companyAutocomplete() {
    return {
        query: '',
        results: [],
        googleResults: [],
        isLoading: false,
        isGoogleLoading: false,
        showDropdown: false,
        mode: 'local', // 'local' | 'google'
        debug: ['localhost', '127.0.0.1'].includes(window.location.hostname) || window.localStorage.getItem('googlePlacesDebug') === '1',

        init() {
            PlacesAPI.init({ debug: this.debug });

            this.$watch('query', (value) => {
                if (value.length < 2) {
                    this.results = [];
                    this.googleResults = [];
                    this.showDropdown = false;
                    this.mode = 'local';
                }
            });
        },

        async search() {
            if (this.query.length < 2) return;

            this.mode = 'local';
            this.googleResults = [];
            this.isLoading = true;
            this.showDropdown = true;

            try {
                const response = await fetch(`/api/customers/search?q=${encodeURIComponent(this.query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Search failed');

                const data = await response.json();
                this.results = Array.isArray(data) ? data : [];
            } catch (error) {
                console.error('[companyAutocomplete] local search error:', error);
                this.results = [];
            } finally {
                this.isLoading = false;
            }
        },

        async searchGoogle() {
            if (this.query.length < 3) return;

            this.mode = 'google';
            this.isGoogleLoading = true;

            try {
                const suggestions = await PlacesAPI.autocomplete(this.query);
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

        selectLocal(customer) {
            this.query = customer.name;
            this.showDropdown = false;

            this.$el.dispatchEvent(new CustomEvent('place-selected', {
                detail: {
                    company_name: customer.name,
                    address_1: customer.address || '',
                    address_2: '',
                    city: customer.city || '',
                    state: customer.state || '',
                    zip: customer.postal_code || '',
                    country: customer.country || 'US',
                    lat: null,
                    lng: null,
                    contact_name: customer.contact_name || '',
                    phone: customer.contact_phone || '',
                    email: customer.customer_email || '',
                },
                bubbles: true,
            }));
        },

        async select(result) {
            this.query = result.mainText;
            this.googleResults = [];
            this.showDropdown = true;
            this.isGoogleLoading = true;

            try {
                const placeDetails = await PlacesAPI.getDetails(result.placeId);
                if (placeDetails?.parsed) {
                    this.$el.dispatchEvent(new CustomEvent('place-selected', {
                        detail: placeDetails.parsed,
                        bubbles: true,
                    }));
                }
            } catch (error) {
                console.error('[PlacesAPI] selection error:', error);
            } finally {
                this.isGoogleLoading = false;
                this.showDropdown = false;
            }
        },

        close() {
            this.showDropdown = false;
        },
    };
}
