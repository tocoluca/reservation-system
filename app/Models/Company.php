<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'company_code',
        'name',
        'email',
        'password',
        'theme_color',
        'logo_path',

        // 予約設定
        'slot_minutes',
        'max_simultaneous_reservations',
        'open_patterns',
        'regular_holidays',
        'holiday_is_closed',
        'reservation_month_limit',
        'reservation_open_days',
        'reservation_close_hours',

        // Stripe / Billing
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'stripe_subscription_id',
        'stripe_price_id',
        'subscription_status',
        'plan_code',
        'trial_ends_at',
        'current_period_end',
        'subscribed_at',
        'canceled_at',
        'is_billing_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'open_patterns' => 'array',
        'regular_holidays' => 'array',
        'holiday_is_closed' => 'boolean',
        'is_billing_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
        'subscribed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function businessCalendars(): HasMany
    {
        return $this->hasMany(CompanyBusinessCalendar::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

	public function isSubscribed(): bool
	{
	    return $this->subscription_status === 'active'
	        && (bool) $this->is_billing_active;
	}

	public function isOnTrial(): bool
	{
	    return $this->subscription_status === 'trialing'
	        && !is_null($this->trial_ends_at)
	        && $this->trial_ends_at->isFuture();
	}

	public function isSubscriptionAvailable(): bool
	{
	    return $this->isSubscribed() || $this->isOnTrial();
	}

    public function isCanceled(): bool
    {
        return in_array($this->subscription_status, ['canceled', 'incomplete_expired', 'unpaid'], true);
    }

    public function planLabel(): string
    {
        return match ($this->plan_code) {
            'standard' => 'スタンダード',
            'platinum' => 'プラチナ',
            default => '未契約',
        };
    }
	public function inquiries()
	{
	    return $this->hasMany(\App\Models\Inquiry::class);
	}
}