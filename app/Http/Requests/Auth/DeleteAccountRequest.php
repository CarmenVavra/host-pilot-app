<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates deleting the signed-in user's account: the password must be confirmed.
 */
class DeleteAccountRequest extends FormRequest
{
    /**
     * Errors go to a separate bag so they show next to the delete form only.
     *
     * @var string
     */
    protected $errorBag = 'deleteAccount';

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
            'delete_password' => ['required', 'current_password'],
        ];
    }

    public function attributes(): array
    {
        return ['delete_password' => 'Passwort'];
    }
}
