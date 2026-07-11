import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import companyAutocomplete from './company-autocomplete';
import { getContactBook, normalizedKey } from './contact-book';

Alpine.data('companyAutocomplete', companyAutocomplete);

// Exposed for the inline orderForm() script in the order form blade,
// which can't import from the Vite bundle.
window.__getContactBook = () => getContactBook(window.__contactBookUrl);
window.__contactBookKey = normalizedKey;

Livewire.start();
