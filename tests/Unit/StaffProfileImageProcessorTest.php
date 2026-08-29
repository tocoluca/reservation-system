<?php

namespace Tests\Unit;

use App\Services\StaffProfileImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StaffProfileImageProcessorTest extends TestCase
{
    public function test_it_crops_and_stores_a_640_square_webp(): void
    {
        $publicRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-staff-profile-'.bin2hex(random_bytes(6));

        try {
            $processor = new StaffProfileImageProcessor($publicRoot);
            $path = $processor->store(
                UploadedFile::fake()->image('profile.jpg', 1200, 800),
                21
            );

            $fullPath = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            $size = getimagesize($fullPath);

            $this->assertFileExists($fullPath);
            $this->assertSame(640, $size[0]);
            $this->assertSame(640, $size[1]);
            $this->assertSame('image/webp', $size['mime']);
            $this->assertStringStartsWith('companies/21/staff/', $path);
        } finally {
            File::deleteDirectory($publicRoot);
        }
    }
}
