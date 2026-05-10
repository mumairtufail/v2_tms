import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import companyAutocomplete from './company-autocomplete';

Alpine.data('companyAutocomplete', companyAutocomplete);

Livewire.start();
