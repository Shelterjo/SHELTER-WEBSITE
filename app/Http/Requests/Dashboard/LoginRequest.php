<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'password' => ['required', 'string', 'max:200'],
        ];
    }

    public function throttleKey(): string
    {
        return 'login:'.hash('sha256', mb_strtolower(trim((string) $this->input('email'))).'|'.$this->ip());
    }
}
