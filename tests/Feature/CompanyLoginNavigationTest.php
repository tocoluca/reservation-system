<?php

namespace Tests\Feature;

use App\Models\Staff;
use Tests\TestCase;

class CompanyLoginNavigationTest extends TestCase
{
    public function test_returning_to_login_keeps_company_authentication_and_session(): void
    {
        $staff = new Staff;
        $staff->id = 1;

        $this->actingAs($staff, 'company')
            ->withSession(['navigation_marker' => 'preserved'])
            ->get(route('company.login'))
            ->assertRedirect(route('company.dashboard'))
            ->assertSessionHas('navigation_marker', 'preserved');

        $this->assertAuthenticatedAs($staff, 'company');
    }

    public function test_guests_can_still_open_the_company_login_form(): void
    {
        $this->get(route('company.login'))
            ->assertOk()
            ->assertViewIs('company.login');

        $this->assertGuest('company');
    }
}
