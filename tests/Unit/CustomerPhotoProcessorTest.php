<?php

namespace Tests\Unit;

use App\Services\CustomerPhotoProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomerPhotoProcessorTest extends TestCase
{
    public function test_it_preserves_aspect_ratio_and_stores_a_webp_within_1600_pixels(): void
    {
        $publicRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-customer-photo-'.bin2hex(random_bytes(6));

        try {
            $processor = new CustomerPhotoProcessor($publicRoot);
            $path = $processor->store(
                UploadedFile::fake()->image('customer.jpg', 2400, 1200),
                15
            );

            $fullPath = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            $size = getimagesize($fullPath);

            $this->assertFileExists($fullPath);
            $this->assertSame(1600, $size[0]);
            $this->assertSame(800, $size[1]);
            $this->assertSame('image/webp', $size['mime']);
            $this->assertStringStartsWith('companies/15/customer/', $path);
        } finally {
            File::deleteDirectory($publicRoot);
        }
    }
}
