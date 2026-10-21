<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates changing the password of the signed-in user.
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Errors go to a separate bag so they show next to the password form only.
     *
     * @var string
     */
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['current_password' => 'Aktuelles Passwort', 'password' => 'Neues Passwort'];
    }
}
