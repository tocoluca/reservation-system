<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Support\PlanCatalog;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LightPlanTest extends TestCase
{
    public function test_plan_prices_are_tax_included_and_light_has_no_campaign_or_trial(): void
    {
        $light = PlanCatalog::get('light');
        $standard = PlanCatalog::get('standard');
        $platinum = PlanCatalog::get('platinum');

        $this->assertSame(1980, $light['amount']);
        $this->assertSame(5980, $standard['amount']);
        $this->assertSame(7980, $platinum['amount']);

        $this->assertTrue($light['tax_included']);
        $this->assertTrue($standard['tax_included']);
        $this->assertTrue($platinum['tax_included']);

        $this->assertSame(1, $light['max_active_staff']);
        $this->assertSame(0, $light['trial_days']);
        $this->assertFalse($light['campaign_eligible']);
    }

    public function test_light_plan_allows_core_reservations_and_denies_advanced_features(): void
    {
        $company = new Company(['plan_code' => 'light']);

        foreach ([
            'reservation_page',
            'menus',
            'multiple_menu_selection',
            'reservation_calendar',
            'reservation_management',
            'business_calendar',
            'reservation_window',
            'past_time_block',
            'double_booking_prevention',
            'customer_basic_information',
            'reservation_hero',
        ] as $feature) {
            $this->assertTrue($company->hasFeature($feature), $feature);
        }

        foreach ([
            'staff_management',
            'advanced_customer_management',
            'shifts',
            'vacations',
            'auto_assignment',
            'notices',
            'style_posts',
            'reviews',
            'analytics',
            'line_login',
            'line_notifications',
            'customer_login',
            'reservation_change_notifications',
            'advanced_theme',
        ] as $feature) {
            $this->assertFalse($company->hasFeature($feature), $feature);
        }

        $this->assertSame('ライト', $company->planLabel());
    }

    public function test_existing_plans_keep_their_features(): void
    {
        $standard = new Company(['plan_code' => 'standard']);
        $platinum = new Company(['plan_code' => 'platinum']);

        $this->assertTrue($standard->hasFeature('staff_management'));
        $this->assertTrue($standard->hasFeature('analytics'));
        $this->assertFalse($standard->hasFeature('line_login'));
        $this->assertTrue($platinum->hasFeature('staff_management'));
        $this->assertTrue($platinum->hasFeature('line_login'));
        $this->assertTrue($platinum->hasFeature('line_notifications'));
    }

    public function test_stripe_price_ids_resolve_to_all_plan_codes(): void
    {
        config()->set('plans.plans.light.price_id', 'price_light');
        config()->set('plans.plans.standard.price_id', 'price_standard');
        config()->set('plans.plans.platinum.price_id', 'price_platinum');

        $this->assertSame('light', PlanCatalog::codeForPriceId('price_light'));
        $this->assertSame('standard', PlanCatalog::codeForPriceId('price_standard'));
        $this->assertSame('platinum', PlanCatalog::codeForPriceId('price_platinum'));
        $this->assertNull(PlanCatalog::codeForPriceId('price_unknown'));
    }

    public function test_duplicate_stripe_price_id_is_not_mapped_to_the_wrong_plan(): void
    {
        config()->set('plans.plans.light.price_id', 'price_shared');
        config()->set('plans.plans.standard.price_id', 'price_shared');

        $this->assertNull(PlanCatalog::codeForPriceId('price_shared'));
    }

    public function test_restricted_company_routes_have_server_side_plan_middleware(): void
    {
        $expectations = [
            'company.staff.index' => 'company.plan.feature:staff_management',
            'company.customers' => 'company.plan.feature:advanced_customer_management',
            'company.staff-shifts' => 'company.plan.feature:shifts',
            'company.vacation.index' => 'company.plan.feature:vacations',
            'company.notices.index' => 'company.plan.feature:notices',
            'company.reviews.index' => 'company.plan.feature:reviews',
            'company.style-posts.index' => 'company.plan.feature:style_posts',
            'company.reservation_change_notices.index' => 'company.plan.feature:reservation_change_notifications',
            'company.theme' => 'company.plan.feature:advanced_theme',
        ];

        foreach ($expectations as $routeName => $middleware) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, $routeName);
            $this->assertContains($middleware, $route->gatherMiddleware(), $routeName);
        }
    }
}
