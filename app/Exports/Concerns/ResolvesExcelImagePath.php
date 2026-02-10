<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesExcelImagePath
{
    protected function localPhotoPathForExcel(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $fullPath = $disk->path($path);
        $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($ext !== 'webp') {
            return $fullPath;
        }

        return $this->convertWebpToPngTemp($fullPath);
    }

    private function convertWebpToPngTemp(string $webpPath): ?string
    {
        $tmpPng = sys_get_temp_dir() . '/excel_img_' . md5($webpPath) . '.png';

        if (is_file($tmpPng)) {
            return $tmpPng;
        }

        if ($this->convertWebpViaImagick($webpPath, $tmpPng) || $this->convertWebpViaGd($webpPath, $tmpPng)) {
            return is_file($tmpPng) ? $tmpPng : null;
        }

        return null;
    }

    private function convertWebpViaImagick(string $webpPath, string $tmpPng): bool
    {
        if (! class_exists(\Imagick::class)) {
            return false;
        }

        try {
            $img = new \Imagick($webpPath);
            $img->setImageFormat('png');
            $img->setImageCompressionQuality(85);
            $img->writeImage($tmpPng);
            $img->clear();
            $img->destroy();
        } catch (\Throwable) {
            return false;
        }

        return is_file($tmpPng);
    }

    private function convertWebpViaGd(string $webpPath, string $tmpPng): bool
    {
        if (! function_exists('imagecreatefromwebp') || ! function_exists('imagepng')) {
            return false;
        }

        try {
            $img = @imagecreatefromwebp($webpPath);
            if ($img === false) {
                return false;
            }

            $written = @imagepng($img, $tmpPng, 8);
            imagedestroy($img);
        } catch (\Throwable) {
            return false;
        }

        return $written === true && is_file($tmpPng);
    }
}
