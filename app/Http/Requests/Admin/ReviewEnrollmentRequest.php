<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $action = $this->input('action');

        $rules = [
            'action' => 'required|in:approve,reject,re_enroll,disable',
            'reason' => 'required_if:action,reject,re_enroll,disable|nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi wajib dipilih.',
            'reason.required_if' => 'Alasan wajib diisi untuk aksi ini.',
        ];
    }
}