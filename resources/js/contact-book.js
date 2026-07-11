// Tenant contact book: fetched once per page, filtered client-side per keystroke.
// The server caches the full list (Cache::rememberForever, busted by
// ContactBookEntryObserver), so this endpoint is cheap.

let _promise = null;
let _url = null;

export function getContactBook(url) {
    // One request per page load, shared by every autocomplete instance and
    // the save-to-book dialog. One tenant per page, so first URL wins.
    if (!_promise) {
        _url = url || window.__contactBookUrl;
        if (!_url) return Promise.resolve([]);
        _promise = fetch(_url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((r) => (r.ok ? r.json() : { entries: [] }))
            .then((d) => d.entries || [])
            .catch(() => []); // never block the form on a failed fetch
    }
    return _promise;
}

// Mirrors ContactBookEntry::normalizedKey() in PHP — keep in sync.
export function normalizedKey(name, address1, city, state) {
    const n = (v) => String(v || '').trim().replace(/\s+/g, ' ').toLowerCase();
    return [n(name), n(address1), n(city), n(state)].join('|');
}

export function filterContactBook(entries, query, limit = 8) {
    const q = String(query || '').trim().toLowerCase();
    if (q.length < 2) return [];
    return entries
        .filter((e) =>
            `${e.company_name} ${e.address_1} ${e.city} ${e.state}`.toLowerCase().includes(q)
        )
        .sort((a, b) => {
            const ap = String(a.company_name || '').toLowerCase().startsWith(q) ? 0 : 1;
            const bp = String(b.company_name || '').toLowerCase().startsWith(q) ? 0 : 1;
            return ap - bp || String(a.company_name || '').localeCompare(String(b.company_name || ''));
        })
        .slice(0, limit);
}
