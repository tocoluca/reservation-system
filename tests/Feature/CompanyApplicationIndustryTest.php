<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompanyApplicationIndustryTest extends TestCase
{
    public function test_all_industries_can_be_submitted_and_displayed_in_receipt(): void
    {
        (require database_path('migrations/2026_02_21_063219_create_company_applications_table.php'))->up();
        (require database_path('migrations/2026_02_21_050754_create_admins_table.php'))->up();
        Mail::fake();

        $page = $this->get(route('company.application.create'))->assertOk();
        foreach (config('industries.options') as $value => $label) {
            $page->assertSee($label);
            $this->post(route('company.application.store'), [
                'company_name' => 'テスト店舗',
                'industry_type' => $value,
                'contact_person' => '山田 太郎',
                'email' => $value.'@example.com',
                'phone' => '090-1234-5678',
                'agree_terms' => '1',
            ])->assertSessionHasNoErrors()->assertRedirect();
            $application = Application::where('email', $value.'@example.com')->firstOrFail();
            $this->assertSame($value, $application->industry_type);
            $this->assertSame($label, $application->industry_label);
            $this->assertStringContainsString($label, view('emails.company_application_received', compact('application'))->render());
        }
    }

    public function test_missing_and_unknown_industries_are_rejected_and_input_is_preserved(): void
    {
        foreach (['', 'invalid', 'beauty', 'dental'] as $value) {
            $this->from(route('company.application.create'))
                ->post(route('company.application.store'), ['industry_type' => $value, 'company_name' => '入力を保持'])
                ->assertSessionHasErrors('industry_type')
                ->assertSessionHasInput('company_name', '入力を保持');
        }
    }

    public function test_legacy_industry_labels_remain_readable(): void
    {
        $this->assertSame('美容', (new Application(['industry_type' => 'beauty']))->industry_label);
        $this->assertSame('歯科', (new Application(['industry_type' => 'dental']))->industry_label);
    }
}
