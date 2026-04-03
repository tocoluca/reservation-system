<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Company extends Model
{
    use Billable;

    protected $fillable = [
        'company_code',
        'name',
        'industry_type',
        'logo_path',
        'email',
        'phone',
        'address',
        'slot_minutes',
        'theme_color',
        'max_simultaneous_reservations',
        // 'open_time',
        // 'close_time',
        'regular_holidays',
        'holiday_is_closed',
        'line_login_enabled',
        'line_channel_id',
        'line_channel_secret',
        'menu_time_priority_flag',
        'reservation_month_limit',
        'reservation_open_days',
        'reservation_close_hours',
        'revisit_reminder_days',
        'web_cancel_deadline_hours',
        'is_initialized',
        'open_patterns',

        // Stripe / 契約管理
        'is_active',
        'grace_until',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'subscription_status',
        'subscription_started_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'open_patterns' => 'array',
        'regular_holidays' => 'array',
        'holiday_is_closed' => 'boolean',
        'menu_time_priority_flag' => 'boolean',
        'is_initialized' => 'boolean',
        'is_active' => 'boolean',
        'grace_until' => 'datetime',
        'subscription_started_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function isSubscriptionAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (in_array($this->subscription_status, ['active', 'trialing'], true)) {
            return true;
        }

        if ($this->grace_until && now()->lt($this->grace_until)) {
            return true;
        }

        if ($this->subscription_ends_at && now()->lt($this->subscription_ends_at)) {
            return true;
        }

        return false;
    }

    public function getSubscriptionStatusLabelAttribute(): string
    {
        return match ($this->subscription_status) {
            'trialing' => 'お試し期間中',
            'active' => '契約中',
            'past_due' => 'お支払い確認中',
            'unpaid' => '未払い',
            'canceled' => '解約済み',
            default => '未契約',
        };
    }

	public function reviews()
	{
	    return $this->hasMany(\App\Models\Review::class);
	}

}