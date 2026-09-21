<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Staff;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class CompanySupportManualTest extends TestCase
{
    public function test_manual_and_faq_render_current_screen_links_without_old_images(): void
    {
        $company = new Company;
        $company->id = 1;
        $company->name = 'テスト店舗';
        $company->company_code = 'TEST';

        $staff = new Staff;
        $staff->id = 1;
        $staff->name = '担当者';
        $staff->role = 'master';
        $staff->setRelation('company', $company);

        $this->actingAs($staff, 'company');

        $html = view('company.support.index', [
            'inquiries' => new LengthAwarePaginator([], 0, 10),
            'errors' => new ViewErrorBag,
        ])->render();

        $this->assertStringContainsString('営業日・営業時間管理', $html);
        $this->assertStringContainsString('カテゴリー内のメニューを担当者にまとめて設定できますか', $html);
        $this->assertStringContainsString(route('company.calendar.index'), $html);
        $this->assertStringContainsString(route('company.reserve'), $html);
        $this->assertStringNotContainsString('images/manual/support-', $html);
    }
}
