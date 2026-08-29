<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class NoticeImageProcessor
{
    public function __construct(private readonly ?string $publicRoot = null) {}

    public function store(UploadedFile $file, int $companyId): string
    {
        $relativeDirectory = "companies/{$companyId}/notices";
        $root = rtrim($this->publicRoot ?? public_path(), '/\\');
        $directory = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid().'.webp';

        (new ImageManager(new Driver))
            ->read($file)
            ->orient()
            ->scaleDown(width: 1600, height: 1600)
            ->toWebp(quality: 85)
            ->save($directory.DIRECTORY_SEPARATOR.$filename);

        return $relativeDirectory.'/'.$filename;
    }

    public function delete(?string $path, int $companyId): void
    {
        $prefix = "companies/{$companyId}/notices/";

        if (! $path || ! str_starts_with(str_replace('\\', '/', $path), $prefix)) {
            return;
        }

        $root = rtrim($this->publicRoot ?? public_path(), '/\\');
        $fullPath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
