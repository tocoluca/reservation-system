<?php

namespace Tests\Unit;

use App\Models\MenuCategory;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class MenuCategoryImageTest extends TestCase
{
    public function test_known_category_uses_its_existing_fallback_image(): void
    {
        $this->assertSame('images/menu-icons/cut.jpg', MenuCategory::fallbackImagePath('カット'));
        $this->assertSame('images/menu-icons/hairset.jpg', MenuCategory::fallbackImagePath('ヘアアレンジ'));
    }

    public function test_unknown_category_uses_other_fallback_image(): void
    {
        $this->assertSame('images/menu-icons/other.jpg', MenuCategory::fallbackImagePath('オリジナル'));
    }

    public function test_company_image_takes_priority_over_fallback_image(): void
    {
        $category = new MenuCategory([
            'name' => 'カット',
            'image_path' => 'companies/10/menu-categories/custom.webp',
        ]);

        $this->assertStringEndsWith(
            '/companies/10/menu-categories/custom.webp',
            $category->display_image_url
        );
    }

    public function test_uploaded_image_can_be_cropped_to_640_square(): void
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read(dirname(__DIR__, 2).'/public/images/menu-icons/cut.jpg')
            ->orient()
            ->cover(640, 640);

        $this->assertSame(640, $image->width());
        $this->assertSame(640, $image->height());
        $this->assertNotEmpty((string) $image->toWebp(quality: 85));
    }
}
