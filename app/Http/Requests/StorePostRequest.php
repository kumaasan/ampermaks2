<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'excerpt' => ['nullable', 'string', 'max:320'],
            'content' => ['required', 'array'],
            'content.type' => ['required', 'string', Rule::in(['doc'])],
            'content.content' => ['required', 'array', 'min:1'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Podaj tytuł artykułu.',
            'title.max' => 'Tytuł może mieć maksymalnie 160 znaków.',
            'excerpt.max' => 'Opis może mieć maksymalnie 320 znaków.',
            'content.required' => 'Dodaj treść artykułu.',
            'content.content.required' => 'Dodaj treść artykułu.',
            'content.content.min' => 'Dodaj treść artykułu.',
            'status.in' => 'Nieprawidłowy status artykułu.',
        ];
    }
}
