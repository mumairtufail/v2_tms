{{--
    Renders UTC timestamps in the viewer's own timezone.

    The app stores and renders in UTC (config/app.php), so a dispatcher in
    Vancouver was reading every activity log eight hours ahead of what actually
    happened. Rather than convert server-side — which would mean picking one
    timezone for a whole company, and would wrongly shift the order stop times
    that are already stored as local wall-clock strings — the server emits a real
    ISO-8601 instant and the browser formats it.

    Two ways in:
      <x-local-time :value="$log->created_at" format="short" />   markup
      window.localTime(iso, 'short')                             Alpine / JS

    Anything carrying [data-local-time] is upgraded on load and again whenever
    nodes are added, so Alpine-rendered panels (the log detail drawer, the order
    activity slide-over) are covered without each one having to opt in.
--}}
<script>
(() => {
    const FORMATS = {
        // Month, day, year + time. The default for anything that needs to be unambiguous.
        datetime:           { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' },
        // Same, with seconds — for a single record's detail view where ordering matters.
        'datetime-seconds': { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit' },
        // No year: dense tables where almost everything is recent.
        short:              { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' },
        date:               { month: 'short', day: 'numeric', year: 'numeric' },
        time:               { hour: 'numeric', minute: '2-digit' },
    };

    const cache = new Map();

    function formatter(name) {
        if (!cache.has(name)) {
            // Intl.DateTimeFormat is the expensive part, so one instance per format.
            // Undefined locale means "whatever the browser is set to", which is the point.
            cache.set(name, new Intl.DateTimeFormat(undefined, FORMATS[name] || FORMATS.datetime));
        }
        return cache.get(name);
    }

    /**
     * iso -> a string in the viewer's timezone. Returns '' for empty input and
     * echoes the input back unchanged if it is not a date we can parse, so a bad
     * value shows as itself rather than "Invalid Date".
     */
    function localTime(iso, format = 'datetime') {
        if (!iso) return '';
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return String(iso);
        return formatter(format).format(date);
    }

    window.localTime = localTime;

    function upgrade(root) {
        const nodes = root.querySelectorAll ? root.querySelectorAll('[data-local-time]') : [];
        nodes.forEach(node => {
            // datetime="..." holds the instant; the element's text is the UTC
            // fallback that was rendered server-side, replaced here.
            const iso = node.getAttribute('datetime') || node.dataset.iso;
            if (!iso || node.dataset.localised === iso) return;

            const text = localTime(iso, node.dataset.localTime || 'datetime');
            if (!text) return;

            node.textContent = text;
            // Guard against re-running on the same value when the observer fires
            // for an unrelated change inside this element.
            node.dataset.localised = iso;

            if (!node.title) {
                node.title = new Intl.DateTimeFormat(undefined, {
                    dateStyle: 'full', timeStyle: 'long',
                }).format(new Date(iso));
            }
        });
    }

    window.upgradeLocalTimes = upgrade;

    const run = () => upgrade(document);

    // Covers both load orders: this partial sits in the scripts stack, so the
    // table above it already exists, but a deferred bundle may not have run yet.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }

    new MutationObserver(mutations => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType === 1) upgrade(node);
            }
        }
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
</script>
