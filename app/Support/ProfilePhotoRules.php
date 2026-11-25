<?php

namespace App\Support;

class ProfilePhotoRules
{
    public static function rules(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
            'dimensions:min_width=1,min_height=1,max_width=4096,max_height=4096',
        ];
    }
}
