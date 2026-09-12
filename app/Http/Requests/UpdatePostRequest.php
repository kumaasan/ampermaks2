<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends StorePostRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $post = $this->route('post');
        abort_unless($post instanceof Post, 404);

        return [
            ...parent::rules(),
            'slug' => [
                'required',
                'string',
                'max:190',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('posts', 'slug')->ignore($post->getKey()),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'slug.required' => 'Podaj adres artykułu.',
            'slug.max' => 'Adres artykułu może mieć maksymalnie 190 znaków.',
            'slug.regex' => 'Adres może zawierać małe litery, cyfry i pojedyncze myślniki.',
            'slug.unique' => 'Ten adres artykułu jest już zajęty.',
        ];
    }
}
