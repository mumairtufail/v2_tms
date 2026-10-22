{{--
    Formats times that are rendered in the browser (Alpine panels such as the
    activity log drawer and the order activity slide-over) on the system clock,
    the same one the server uses: config('app.display_timezone'), Mountain Time.

    Server-rendered times go through <x-local-time> / App\Support\DisplayTime
    and need nothing from here. Converting to each viewer's own browser zone
    was dropped: two people at the same company saw different times for one
    record, and the screens disagreed with emails and PDFs.

      window.localTime(iso, 'short')        a UTC instant -> "Oct 23, 2:15 PM MDT"
      window.displayTimeZone                { name, label, abbr } for headings

    Anything with [data-local-tz] gets the current abbreviation (MST / MDT).
--}}
<script>
(() => {
    const ZONE = @json(\App\Support\DisplayTime::tz());
    const LABEL = @json(\App\Support\DisplayTime::label());
    const ABBR = @json(\App\Support\DisplayTime::abbreviation());

    const FORMATS = {
        datetime:           { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' },
        'datetime-seconds': { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit' },
        short:              { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' },
        date:               { month: 'short', day: 'numeric', year: 'numeric' },
        time:               { hour: 'numeric', minute: '2-digit' },
    };
    const WITH_CLOCK = ['datetime', 'datetime-seconds', 'short', 'time'];

    const cache = new Map();
    function formatter(name, zone) {
        const key = name + '|' + zone;
        if (!cache.has(key)) {
            let options = { ...(FORMATS[name] || FORMATS.datetime) };
            try {
                cache.set(key, new Intl.DateTimeFormat('en-US', { ...options, timeZone: zone }));
            } catch (e) {
                // Unknown zone in a very old browser: UTC is better than throwing.
                cache.set(key, new Intl.DateTimeFormat('en-US', { ...options, timeZone: 'UTC' }));
            }
        }
        return cache.get(key);
    }

    // MST or MDT for that particular instant, since it changes with daylight saving
    function abbrFor(date, zone) {
        try {
            const part = new Intl.DateTimeFormat('en-US', { timeZone: zone, timeZoneName: 'short' })
                .formatToParts(date).find(p => p.type === 'timeZoneName');
            return part ? part.value : '';
        } catch (e) { return ''; }
    }

    /**
     * A UTC instant -> text on the system clock. Returns '' for empty input and
     * echoes the input back if it cannot be parsed, so a bad value shows as
     * itself rather than "Invalid Date".
     */
    function localTime(iso, format = 'datetime') {
        if (!iso) return '';
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return String(iso);
        const text = formatter(format, ZONE).format(date);
        return WITH_CLOCK.includes(format) ? text + ' ' + abbrFor(date, ZONE) : text;
    }

    window.localTime = localTime;
    window.displayTimeZone = { name: ZONE, label: LABEL, abbr: ABBR };

    function upgrade(root) {
        if (!root.querySelectorAll) return;
        root.querySelectorAll('[data-local-tz]').forEach(node => { node.textContent = ABBR; });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => upgrade(document));
    } else {
        upgrade(document);
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
