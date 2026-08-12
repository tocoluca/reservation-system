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
        'industry_type',
        'email',
        'password',
        'theme_color',
        'logo_path',
        'address',
        'phone',
        'homepage',
        'is_active',
        'is_initialized',
        'slot_minutes',

        // 予約設定
        'slot_minutes',
        'max_simultaneous_reservations',
        'open_patterns',
        'regular_holidays',
        'holiday_is_closed',
        'reservation_month_limit',
        'reservation_open_days',
        'reservation_close_hours',
        'web_cancel_deadline_hours',
        'web_cancel_deadline_type',
        'reservation_auto_status_mode',
        'reservation_auto_status_hours',

        // Stripe / Billing
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_price_id',
        'subscription_status',
        'plan_code',
        'trial_ends_at',
        'current_period_end',
        'subscribed_at',
        'canceled_at',
        'grace_until',
        'billing_starts_at',
        'is_billing_active',

        // サロン情報
        'salon_message',
        'business_hours_text',
        'parking_info',
        'payment_methods',
        'access_info',
        'salon_note',

        // LINE Login / Messaging
        'line_login_enabled',
        'line_channel_id',
        'line_channel_secret',
        'line_channel_access_token',
        'line_official_account_id',
        'customer_notification_channel',
        'review_enabled',
        'menu_time_priority_flag',
        'prefer_less_capable_staff_for_menu_assignment',

        // 口コミ・再来店設定
        'review_enabled',
        'revisit_reminder_days',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'line_channel_secret',
        'line_channel_access_token',
    ];

    protected $casts = [
        'open_patterns' => 'array',
        'regular_holidays' => 'array',
        'holiday_is_closed' => 'boolean',
        'is_billing_active' => 'boolean',
        'line_login_enabled' => 'boolean',
        'review_enabled' => 'boolean',
        'menu_time_priority_flag' => 'boolean',
        'prefer_less_capable_staff_for_menu_assignment' => 'boolean',
        'trial_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
        'subscribed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'grace_until' => 'datetime',
        'billing_starts_at' => 'datetime',
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

    public function inquiries()
    {
        return $this->hasMany(\App\Models\Inquiry::class);
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

    public function isInGracePeriod(): bool
    {
        return $this->subscription_status === 'past_due'
            && !is_null($this->grace_until)
            && $this->grace_until->isFuture();
    }

    public function isInBillingStartCampaign(): bool
    {
        return !is_null($this->billing_starts_at)
            && $this->billing_starts_at->isFuture();
    }

    public function isSubscriptionAvailable(): bool
    {
        return $this->isSubscribed()
            || $this->isOnTrial()
            || $this->isInGracePeriod()
            || $this->isInBillingStartCampaign();
    }

    public function shouldBeLockedForBilling(): bool
    {
        return !$this->isSubscriptionAvailable();
    }

    public function isCanceled(): bool
    {
        return in_array($this->subscription_status, ['canceled', 'incomplete_expired', 'unpaid'], true);
    }

    public function customerNotificationChannel(): string
    {
        if ($this->plan_code !== 'platinum') {
            return 'email';
        }

        return in_array($this->customer_notification_channel, ['both', 'email', 'line'], true)
            ? $this->customer_notification_channel
            : 'both';
    }

    public function sendsCustomerEmail(): bool
    {
        return in_array($this->customerNotificationChannel(), ['both', 'email'], true);
    }

    public function sendsCustomerLine(): bool
    {
        return $this->plan_code === 'platinum'
            && in_array($this->customerNotificationChannel(), ['both', 'line'], true);
    }

    public function planLabel(): string
    {
        return match ($this->plan_code) {
            'standard' => 'スタンダード',
            'platinum' => 'プラチナ',
            default => '未契約',
        };
    }

    public function getSubscriptionStatusLabelAttribute(): string
    {
        if ($this->isInBillingStartCampaign()) {
            return '請求開始前';
        }

        return match ($this->subscription_status) {
            'active' => '有効',
            'trialing' => 'トライアル中',
            'past_due' => $this->isInGracePeriod()
                ? '支払い失敗（猶予中）'
                : '支払い失敗（停止）',
            'canceled' => '解約済み',
            'incomplete' => '未完了',
            'incomplete_expired' => '期限切れ',
            'unpaid' => '未払い',
            default => '未契約',
        };
    }

    public function getUsageStartedAtAttribute()
    {
        return $this->subscribed_at ?: $this->created_at;
    }

    public function getUsageStartedSourceLabelAttribute(): string
    {
        return $this->subscribed_at ? '契約開始' : '登録日';
    }
}
