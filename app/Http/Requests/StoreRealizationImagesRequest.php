<?php

namespace App\Http\Requests;

use App\Services\RealizationImageStorage;
use Illuminate\Foundation\Http\FormRequest;

class StoreRealizationImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-posts') === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'images' => [
                'required',
                'array',
                'min:1',
                'max:'.RealizationImageStorage::MAX_FILES_PER_UPLOAD,
            ],
            'images.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'extensions:jpg,jpeg,png,webp',
                'max:'.(RealizationImageStorage::MAX_UPLOAD_SIZE_MB * 1024),
                'dimensions:min_width=320,min_height=180,max_width=6000,max_height=6000',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'images.required' => 'Wybierz co najmniej jedno zdjęcie.',
            'images.array' => 'Lista zdjęć ma nieprawidłowy format.',
            'images.min' => 'Wybierz co najmniej jedno zdjęcie.',
            'images.max' => 'Jednorazowo możesz przesłać maksymalnie '.RealizationImageStorage::MAX_FILES_PER_UPLOAD.' zdjęć.',
            'images.*.required' => 'Nie udało się odczytać jednego ze zdjęć.',
            'images.*.image' => 'Jeden z plików nie jest prawidłowym obrazem.',
            'images.*.mimes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'images.*.mimetypes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'images.*.extensions' => 'Dozwolone są rozszerzenia JPG, PNG i WebP.',
            'images.*.max' => 'Każde zdjęcie może mieć maksymalnie '.RealizationImageStorage::MAX_UPLOAD_SIZE_MB.' MB.',
            'images.*.dimensions' => 'Każde zdjęcie musi mieć od 320×180 do 6000×6000 px.',
        ];
    }
}
