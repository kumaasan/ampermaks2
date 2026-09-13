<?php

namespace App\Http\Requests;

use App\Services\RealizationImageStorage;
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
        $imageRules = [
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'mimetypes:image/jpeg,image/png,image/webp',
            'extensions:jpg,jpeg,png,webp',
            'max:'.(RealizationImageStorage::MAX_UPLOAD_SIZE_MB * 1024),
            'dimensions:min_width=320,min_height=180,max_width=6000,max_height=6000',
        ];

        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:300'],
            'images' => [
                'required_without:image',
                'array',
                'min:1',
                'max:'.RealizationImageStorage::MAX_FILES_PER_UPLOAD,
            ],
            'images.*' => $imageRules,
            // Zachowuje zgodność z formularzem/API sprzed wprowadzenia galerii.
            'image' => ['required_without:images', ...$imageRules],
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
            'images.required_without' => 'Wybierz co najmniej jedno zdjęcie realizacji.',
            'images.array' => 'Lista zdjęć ma nieprawidłowy format.',
            'images.min' => 'Wybierz co najmniej jedno zdjęcie realizacji.',
            'images.max' => 'Jednorazowo możesz przesłać maksymalnie '.RealizationImageStorage::MAX_FILES_PER_UPLOAD.' zdjęć.',
            'images.*.image' => 'Jeden z plików nie jest prawidłowym obrazem.',
            'images.*.mimes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'images.*.mimetypes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'images.*.extensions' => 'Dozwolone są rozszerzenia JPG, PNG i WebP.',
            'images.*.max' => 'Każde zdjęcie może mieć maksymalnie '.RealizationImageStorage::MAX_UPLOAD_SIZE_MB.' MB.',
            'images.*.dimensions' => 'Każde zdjęcie musi mieć od 320×180 do 6000×6000 px.',
            'image.required_without' => 'Wybierz zdjęcie realizacji.',
            'image.image' => 'Plik nie jest prawidłowym obrazem.',
            'image.mimes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'image.mimetypes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'image.extensions' => 'Dozwolone są rozszerzenia JPG, PNG i WebP.',
            'image.max' => 'Zdjęcie może mieć maksymalnie '.RealizationImageStorage::MAX_UPLOAD_SIZE_MB.' MB.',
            'image.dimensions' => 'Zdjęcie musi mieć od 320×180 do 6000×6000 px.',
        ];
    }
}
