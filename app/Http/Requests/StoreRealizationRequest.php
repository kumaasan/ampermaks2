<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRealizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-posts') === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:300'],
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'extensions:jpg,jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=320,min_height=180,max_width=6000,max_height=6000',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Podaj tytuł realizacji.',
            'title.max' => 'Tytuł może mieć maksymalnie 160 znaków.',
            'description.required' => 'Dodaj krótki opis realizacji.',
            'description.max' => 'Opis może mieć maksymalnie 300 znaków.',
            'image.required' => 'Wybierz zdjęcie realizacji.',
            'image.image' => 'Plik nie jest prawidłowym obrazem.',
            'image.mimes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'image.mimetypes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'image.extensions' => 'Dozwolone są rozszerzenia JPG, PNG i WebP.',
            'image.max' => 'Zdjęcie może mieć maksymalnie 8 MB.',
            'image.dimensions' => 'Zdjęcie musi mieć od 320×180 do 6000×6000 px.',
        ];
    }
}
