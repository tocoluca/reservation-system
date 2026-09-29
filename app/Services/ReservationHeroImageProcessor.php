<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;

class ReservationHeroImageProcessor
{
    public function __construct(private readonly ?string $storageRoot = null) {}

    public function store(UploadedFile $file, int $companyId): string
    {
        $relativeDirectory = "reservation-heroes/{$companyId}";
        $directory = $this->root().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('画像の保存先を作成できませんでした。');
        }

        $filename = Str::uuid().'.webp';

        (new ImageManager(new Driver))
            ->read($file)
            ->orient()
            ->scaleDown(width: 1920, height: 1080)
            ->toWebp(quality: 78)
            ->save($directory.DIRECTORY_SEPARATOR.$filename);

        return $relativeDirectory.'/'.$filename;
    }

    public function absolutePath(?string $path, int $companyId): ?string
    {
        if (! $path || ! str_starts_with($path, "reservation-heroes/{$companyId}/")) {
            return null;
        }

        $root = $this->root();
        $fullPath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
        $realRoot = realpath($root);
        $realPath = realpath($fullPath);

        if (! $realRoot || ! $realPath || ! str_starts_with($realPath, $realRoot.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return is_file($realPath) ? $realPath : null;
    }

    public function delete(?string $path, int $companyId): void
    {
        $fullPath = $this->absolutePath($path, $companyId);

        if ($fullPath) {
            @unlink($fullPath);
        }
    }

    private function root(): string
    {
        return rtrim($this->storageRoot ?? storage_path('app/private'), '/\\');
    }
}
