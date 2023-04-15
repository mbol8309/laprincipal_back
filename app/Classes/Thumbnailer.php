<?php

namespace App\Classes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Str;

class Thumbnailer
{

    public static function createImageThumbnail(UploadedFile $path)
    {
        $thumbnail = Image::make($path)->resize(100, null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('png');

        if (!Storage::disk('public')->exists('thumbnails')) {
            Storage::disk('public')->makeDirectory('thumbnails');
        }
        $thumbnailPath = 'thumbnails/' . $path->getFilename() . '.png';

        $publicPath = public_path();

        // Get the relative path to the file within the storage directory
        $storagePath = Storage::url($thumbnailPath);

        // Generate the full path to the file
        $filePath = $publicPath . $storagePath;

        $thumbnail->save($filePath);
        return asset($storagePath);
    }

    public static function createPdfThumbnail(UploadedFile $path)
    {
        $pdf = new Pdf($path);
        $pdf->setPage(1);
        $pdf->setOutputFormat('png');
        $pdf->setCompressionQuality(70);
        $pdf->width(100);

        if (!Storage::disk('public')->exists('thumbnails')) {
            Storage::disk('public')->makeDirectory('thumbnails');
        }
        $thumbnailPath = 'thumbnails/' . $path->getFilename() . '.png';
        $publicPath = public_path();
        // Get the relative path to the file within the storage directory
        $storagePath = Storage::url($thumbnailPath);
        // Generate the full path to the file
        $filePath = $publicPath . $storagePath;
        $pdf->saveImage($filePath);

        return asset(Storage::url($thumbnailPath));
    }

    public static function createTextThumbnail(UploadedFile $path)
    {
        // Read the first 50 characters of the file
        $content = file_get_contents($path);
        $text = substr($content, 0, 100);

        // Set font properties
        $fontSize = 8;
        $fontFile = Storage::path('fonts/droid_sans.ttf'); // Path to font file

        // Get bounding box of text
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[3] - $bbox[5];

        // Create an image from the text
        $imageWidth = 100;
        $imageHeight = 100;
        $image = imagecreatetruecolor($imageWidth, $imageHeight);
        $background = imagecolorallocate($image, 255, 255, 255);
        $textcolor = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $background);
        imagettftext($image, $fontSize, 0, 10, 20, $textcolor, $fontFile, $text);

        // Add gradient effect to the text
        $alphaStart = 80; // Start opacity of gradient
        $alphaEnd = 0; // End opacity of gradient
        $gradientHeight = 20;
        for ($y = 0; $y < $gradientHeight; $y++) {
            $alpha = $alphaStart - $y * $alphaStart / $gradientHeight;
            $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
            imageline($image, 0, $imageHeight - $gradientHeight + $y, $imageWidth, $imageHeight - $gradientHeight + $y, $color);
        }

        // borders
        $color = imagecolorallocatealpha($image, 80, 80, 80, 0);
        imageline($image, 0, 0, $imageWidth-1, 0, $color);
        imageline($image, $imageWidth-1, 0, $imageWidth-1, $imageHeight-1, $color);
        imageline($image, 0, $imageHeight-1, $imageWidth-1, $imageHeight-1, $color);
        imageline($image, 0, 0, 0, $imageHeight-1, $color);

        // Save the image to a PNG file
        $thumbnailPath = 'thumbnails/' . Str::random(40) . '.png';
        $thumbnailFilePath = Storage::disk('public')->path($thumbnailPath);
        imagepng($image, $thumbnailFilePath);
        imagedestroy($image);

        return asset(Storage::url($thumbnailPath));
    }
}
