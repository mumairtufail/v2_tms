<?php

namespace App\View\Components\V2\Company\Orders;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LocationFields extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.v2.company.orders.location-fields');
    }
}
