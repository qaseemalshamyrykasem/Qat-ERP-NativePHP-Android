<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'username'  => 'required|string|max:50|unique:users,username',
            'password'  => 'required|string|min:6',
            'full_name' => 'required|string|max:100',
            'email'     => 'nullable|email|max:100',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:admin,manager,agent,accountant',
            'agent_id'  => 'nullable|integer|exists:agents,id',
            'status'    => 'nullable|boolean',
        ];
    }
}
