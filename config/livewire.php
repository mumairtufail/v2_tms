<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto-inject Frontend Assets
    |--------------------------------------------------------------------------
    |
    | Must stay disabled: resources/js/app.js bundles Livewire's ESM build and
    | calls Livewire.start() itself. If injection is on, Livewire appends a
    | second copy of livewire.js to every page, and the duplicate Livewire +
    | Alpine instances break bindings (e.g. wire:submit) on morphed content.
    |
    */

    'inject_assets' => false,

];
