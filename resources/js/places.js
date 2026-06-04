const DEFAULT_HEADERS = {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
};

function createSessionToken() {
    return window.crypto?.randomUUID ? window.crypto.randomUUID() : `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function logToConsole(enabled, message, context = {}) {
    if (!enabled) {
        return;
    }

    console.debug(`[PlacesAPI] ${message}`, context);
}

async function parseJson(response) {
    const text = await response.text();

    if (!text) {
        return null;
    }

    try {
        return JSON.parse(text);
    } catch {
        return { raw: text };
    }
}

export const PlacesAPI = {
    sessionToken: null,
    debug: false,
    autocompleteEndpoint: '/api/google/places/autocomplete',
    detailsEndpointBase: '/api/google/places',

    init({ debug = false } = {}) {
        this.debug = debug;
        this.refreshToken();
    },

    refreshToken() {
        this.sessionToken = createSessionToken();
        logToConsole(this.debug, 'session token refreshed', { sessionToken: this.sessionToken });
    },

    async autocomplete(input) {
        const query = String(input || '').trim();

        if (query.length < 3) {
            return [];
        }

        logToConsole(this.debug, 'autocomplete request queued', { query, sessionToken: this.sessionToken });

        const response = await fetch(this.autocompleteEndpoint, {
            method: 'POST',
            headers: DEFAULT_HEADERS,
            credentials: 'same-origin',
            body: JSON.stringify({
                input: query,
                sessionToken: this.sessionToken,
            }),
        });

        const payload = await parseJson(response);

        logToConsole(this.debug, 'autocomplete response received', {
            status: response.status,
            ok: response.ok,
            payload,
        });

        if (!response.ok || !payload?.success) {
            const message = payload?.message || 'Autocomplete request failed.';
            throw new Error(message);
        }

        return Array.isArray(payload.data) ? payload.data : [];
    },

    async getDetails(placeId) {
        const id = String(placeId || '').trim();

        if (!id) {
            return null;
        }

        logToConsole(this.debug, 'details request queued', { placeId: id, sessionToken: this.sessionToken });

        const response = await fetch(`${this.detailsEndpointBase}/${encodeURIComponent(id)}?sessionToken=${encodeURIComponent(this.sessionToken || '')}`, {
            method: 'GET',
            headers: DEFAULT_HEADERS,
            credentials: 'same-origin',
        });

        const payload = await parseJson(response);

        logToConsole(this.debug, 'details response received', {
            status: response.status,
            ok: response.ok,
            payload,
        });

        if (!response.ok || !payload?.success) {
            const message = payload?.message || 'Place details request failed.';
            throw new Error(message);
        }

        this.refreshToken();

        return {
            place: payload.data,
            parsed: payload.parsed,
        };
    },
};
