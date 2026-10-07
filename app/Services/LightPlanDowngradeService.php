<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LightPlanDowngradeService
{
    public function __construct(
        private readonly ReservationHeroImageProcessor $heroImageProcessor,
    ) {}

    /**
     * Apply values that must be saved atomically with the plan change.
     */
    public function applyCompanyDefaults(Company $company): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        $columns = array_flip(Schema::getColumnListing('companies'));
        $defaults = [
            'theme_color' => Company::DEFAULT_THEME_COLOR,
            'logo_path' => null,
            'reservation_hero_image_path' => null,
            'staff_setup_confirmed_at' => null,
            'max_simultaneous_reservations' => 1,
            'review_enabled' => false,
            'revisit_reminder_days' => 45,
            'prefer_less_capable_staff_for_menu_assignment' => false,
            'line_login_enabled' => false,
            'customer_notification_channel' => 'email',
        ];

        foreach ($defaults as $attribute => $value) {
            if (isset($columns[$attribute])) {
                $company->setAttribute($attribute, $value);
            }
        }
    }

    /**
     * Remove settings that are unavailable on Light and would otherwise become
     * active again after a later plan upgrade.
     *
     * @param  array<string, mixed>  $previous
     */
    public function resetRelatedSettings(Company $company, array $previous): void
    {
        $staffIds = Schema::hasTable('staff')
            ? DB::table('staff')->where('company_id', $company->id)->pluck('id')
            : collect();

        DB::transaction(function () use ($company, $staffIds): void {
            if (Schema::hasTable('shift_review_requirements')) {
                DB::table('shift_review_requirements')
                    ->where('company_id', $company->id)
                    ->delete();
            }

            if ($staffIds->isNotEmpty()) {
                foreach (['staff_default_shifts', 'staff_shifts', 'menu_staff'] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->whereIn('staff_id', $staffIds)->delete();
                    }
                }

                // Past vacation records are history. Only remove current/future
                // entries that could unexpectedly become effective after upgrade.
                if (Schema::hasTable('vacations') && Schema::hasColumn('vacations', 'end_at')) {
                    DB::table('vacations')
                        ->whereIn('staff_id', $staffIds)
                        ->where('end_at', '>=', today()->startOfDay())
                        ->delete();
                }

                if (Schema::hasColumn('staff', 'nomination_fee')) {
                    DB::table('staff')
                        ->whereIn('id', $staffIds)
                        ->update(['nomination_fee' => 0]);
                }
            }

            if (Schema::hasTable('shift_patterns')) {
                DB::table('shift_patterns')
                    ->where('company_id', $company->id)
                    ->delete();
            }
        });

        $this->heroImageProcessor->delete(
            $previous['reservation_hero_image_path'] ?? null,
            (int) $company->id
        );

        $this->deleteLogo($previous['logo_path'] ?? null, (int) $company->id);
    }

    private function deleteLogo(?string $path, int $companyId): void
    {
        $normalizedPath = str_replace('\\', '/', (string) $path);
        $expectedPrefix = "companies/{$companyId}/logos/";

        if (! str_starts_with($normalizedPath, $expectedPrefix)) {
            return;
        }

        $logoDirectory = public_path("companies/{$companyId}/logos");
        $fullPath = public_path($normalizedPath);
        $realDirectory = realpath($logoDirectory);
        $realPath = realpath($fullPath);

        if (! $realDirectory || ! $realPath) {
            return;
        }

        if (str_starts_with($realPath, $realDirectory.DIRECTORY_SEPARATOR) && is_file($realPath)) {
            @unlink($realPath);
        }
    }
}
