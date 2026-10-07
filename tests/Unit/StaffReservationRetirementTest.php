<?php

namespace Tests\Unit;

use App\Models\Staff;
use Tests\TestCase;

class StaffReservationRetirementTest extends TestCase
{
    public function test_retirement_date_is_inclusive_for_reservations(): void
    {
        $staff = new Staff([
            'role' => 'staff',
            'is_reservable' => true,
            'retired_at' => '2026-10-10',
        ]);

        $this->assertTrue($staff->isActiveForReservation('2026-10-10 23:59:59'));
        $this->assertFalse($staff->isRetired('2026-10-10 23:59:59'));

        $this->assertFalse($staff->isActiveForReservation('2026-10-11 00:00:00'));
        $this->assertTrue($staff->isRetired('2026-10-11 00:00:00'));
    }

    public function test_staff_without_retirement_date_remains_reservable(): void
    {
        $staff = new Staff([
            'role' => 'staff',
            'is_reservable' => true,
            'retired_at' => null,
        ]);

        $this->assertTrue($staff->isActiveForReservation('2099-12-31 23:59:59'));
    }
}
