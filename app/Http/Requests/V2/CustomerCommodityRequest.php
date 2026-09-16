<?php

namespace App\Http\Requests\V2;

use App\Models\CustomerCommodity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerCommodityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::baseRules();
    }

    /** Shared with CSV import so both paths validate the same way. */
    public static function baseRules(): array
    {
        $measure = ['nullable', 'numeric', 'min:0', 'max:999999'];

        return [
            'description' => ['required', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(array_keys(CustomerCommodity::TYPES))],
            'measurement_unit' => ['required', Rule::in(array_keys(CustomerCommodity::UNITS))],
            'volume' => $measure,
            'weight' => $measure,
            'linear_feet' => $measure,
            'length' => $measure,
            'width' => $measure,
            'height' => $measure,
            'freight_class' => ['nullable', Rule::in(CustomerCommodity::FREIGHT_CLASSES)],
            'nmfc' => ['nullable', 'string', 'max:30'],
            'sku' => ['nullable', 'string', 'max:60'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $blankToNull = [];
        foreach (['type', 'volume', 'weight', 'linear_feet', 'length', 'width', 'height', 'freight_class', 'nmfc', 'sku'] as $field) {
            if ($this->has($field) && trim((string) $this->input($field)) === '') {
                $blankToNull[$field] = null;
            }
        }

        $this->merge($blankToNull);
    }
}
