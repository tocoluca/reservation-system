<?php

namespace Tests\Unit;

use App\Services\ReservationHeroImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReservationHeroImageProcessorTest extends TestCase
{
    public function test_it_resizes_and_stores_a_tenant_scoped_webp(): void
    {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-hero-'.bin2hex(random_bytes(6));

        try {
            $processor = new ReservationHeroImageProcessor($root);
            $path = $processor->store(UploadedFile::fake()->image('hero.jpg', 2600, 1400), 21);
            $fullPath = $processor->absolutePath($path, 21);
            $size = getimagesize($fullPath);

            $this->assertNotNull($fullPath);
            $this->assertLessThanOrEqual(1920, $size[0]);
            $this->assertLessThanOrEqual(1080, $size[1]);
            $this->assertSame('image/webp', $size['mime']);
            $this->assertStringStartsWith('reservation-heroes/21/', $path);
        } finally {
            File::deleteDirectory($root);
        }
    }

    public function test_one_company_cannot_resolve_or_delete_another_company_image(): void
    {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-hero-'.bin2hex(random_bytes(6));

        try {
            $processor = new ReservationHeroImageProcessor($root);
            $path = $processor->store(UploadedFile::fake()->image('hero.png', 1000, 500), 21);
            $ownerPath = $processor->absolutePath($path, 21);

            $this->assertNull($processor->absolutePath($path, 22));
            $processor->delete($path, 22);
            $this->assertFileExists($ownerPath);
        } finally {
            File::deleteDirectory($root);
        }
    }
}
