<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isSiswa() && auth()->user()->student !== null;
    }

    public function rules(): array
    {
        return [
            'samples' => 'required|array|min:3|max:5',
            'samples.*.type' => 'required|in:front,left,right,challenge',
            'samples.*.image' => 'required|image|mimes:jpeg,png,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'samples.required' => 'Sampel wajah wajib diunggah.',
            'samples.min' => 'Minimal 3 sampel wajah diperlukan.',
            'samples.max' => 'Maksimal 5 sampel wajah.',
            'samples.*.image.required' => 'Setiap sampel harus berupa gambar.',
            'samples.*.image.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}