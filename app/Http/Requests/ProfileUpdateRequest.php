<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\EmailRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Deliverable rather than identifier: password resets are sent here,
            // so a domain that cannot receive mail locks the account out.
            'email' => ['required', 'string', 'lowercase', ...EmailRules::deliverable(), 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }
}
