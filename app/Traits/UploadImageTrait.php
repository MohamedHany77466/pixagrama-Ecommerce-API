<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

trait UploadImageTrait
{
    /*
    |--------------------------------------------------------------------------
    | Upload Product Image + Thumbnail
    |--------------------------------------------------------------------------
    */

    public function uploadProductImage($file)
    {
        $fileName =
            Str::uuid() .
            '.webp';

        /*
        |--------------------------------------------------------------------------
        | MAIN IMAGE
        |--------------------------------------------------------------------------
        */

        $mainImage = Image::read($file)
            ->scale(width: 1200);

        Storage::disk('public')->put(
            'products/main/' . $fileName,
            (string) $mainImage->toWebp(85)
        );

        /*
        |--------------------------------------------------------------------------
        | THUMBNAIL
        |--------------------------------------------------------------------------
        */

        $thumbnail = Image::read($file)
            ->cover(300, 300);

        Storage::disk('public')->put(
            'products/thumbnails/' . $fileName,
            (string) $thumbnail->toWebp(80)
        );

        return [

            'image' =>
                'products/main/' . $fileName,

            'thumbnail' =>
                'products/thumbnails/' . $fileName,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Image
    |--------------------------------------------------------------------------
    */

    public function deleteImage($path)
    {
        if (
            $path &&
            Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }
}