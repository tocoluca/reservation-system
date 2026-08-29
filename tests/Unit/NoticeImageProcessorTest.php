<?php

namespace Tests\Unit;

use App\Services\NoticeImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NoticeImageProcessorTest extends TestCase
{
    public function test_it_preserves_aspect_ratio_and_limits_the_long_edge_to_1600_pixels(): void
    {
        $publicRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-notice-'.bin2hex(random_bytes(6));

        try {
            $processor = new NoticeImageProcessor($publicRoot);
            $path = $processor->store(UploadedFile::fake()->image('poster.jpg', 2400, 1200), 9);
            $fullPath = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            $size = getimagesize($fullPath);

            $this->assertFileExists($fullPath);
            $this->assertSame(1600, $size[0]);
            $this->assertSame(800, $size[1]);
            $this->assertSame('image/webp', $size['mime']);
            $this->assertStringStartsWith('companies/9/notices/', $path);
        } finally {
            File::deleteDirectory($publicRoot);
        }
    }

    public function test_it_does_not_enlarge_a_small_image_and_can_delete_its_own_file(): void
    {
        $publicRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-notice-'.bin2hex(random_bytes(6));

        try {
            $processor = new NoticeImageProcessor($publicRoot);
            $path = $processor->store(UploadedFile::fake()->image('small.png', 640, 480), 9);
            $fullPath = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            $size = getimagesize($fullPath);

            $this->assertSame([640, 480], [$size[0], $size[1]]);

            $processor->delete($path, 9);
            $this->assertFileDoesNotExist($fullPath);
        } finally {
            File::deleteDirectory($publicRoot);
        }
    }
}
