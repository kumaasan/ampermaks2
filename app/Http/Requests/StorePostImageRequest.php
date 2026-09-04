<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:8192',
                'dimensions:min_width=320,min_height=180,max_width=6000,max_height=6000',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.required' => 'Wybierz obraz do przesłania.',
            'image.image' => 'Plik nie jest prawidłowym obrazem.',
            'image.mimes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'image.mimetypes' => 'Dozwolone są pliki JPG, PNG i WebP.',
            'image.max' => 'Obraz może mieć maksymalnie 8 MB.',
            'image.dimensions' => 'Obraz musi mieć od 320×180 do 6000×6000 px.',
        ];
    }
}
