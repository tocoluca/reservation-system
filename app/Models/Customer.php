<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'visit_count',
        'last_visit',
        'memo',
        'photo',

        // LINE連携
        'line_user_id',
        'line_name',
        'line_picture_url',
        'line_linked_at',
        'line_notifications_enabled',
        'line_review_opt_in',
        'line_friend_flag',
        'last_line_sent_at',
    ];

    protected $casts = [
        'last_visit' => 'datetime',
        'line_linked_at' => 'datetime',
        'last_line_sent_at' => 'datetime',
        'line_notifications_enabled' => 'boolean',
        'line_review_opt_in' => 'boolean',
        'line_friend_flag' => 'boolean',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function photos()
    {
        return $this->hasMany(CustomerPhoto::class);
    }

    public function followupMailLogs()
    {
        return $this->hasMany(CustomerFollowupMailLog::class);
    }

    public function latestRevisitReminderLog()
    {
        return $this->hasOne(CustomerFollowupMailLog::class)
            ->where('mail_type', 'revisit_reminder')
            ->latestOfMany('sent_at');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function canReceiveMailOrLine(): bool
    {
        $company = $this->company ?? Company::find($this->company_id);
        $hasEmail = !empty($this->email)
            && ($company?->sendsCustomerEmail() ?? true);
        $hasLine = !empty($this->line_user_id)
            && ($company?->sendsCustomerLine() ?? false)
            && (bool) ($this->line_notifications_enabled ?? true)
            && (bool) ($this->line_friend_flag ?? false);

        return $hasEmail || $hasLine;
    }

    public function isRevisitReminderTarget(): bool
    {
        if (!$this->canReceiveMailOrLine() || empty($this->last_visit)) {
            return false;
        }

        $company = $this->company ?? Company::find($this->company_id);
        $reminderDays = (int) ($company->revisit_reminder_days ?? 45);
        $targetDate = Carbon::now()->subDays($reminderDays)->startOfDay();

        if ($this->last_visit->gt($targetDate)) {
            return false;
        }

        $hasFutureReservation = $this->reservations()
            ->where('status', 'reserved')
            ->where('start_at', '>', now())
            ->exists();

        if ($hasFutureReservation) {
            return false;
        }

        $sentRecently = $this->followupMailLogs()
            ->where('mail_type', 'revisit_reminder')
            ->where('sent_at', '>=', now()->subDays(30))
            ->exists();

        if ($sentRecently) {
            return false;
        }

        return true;
    }

    public function getRevisitReminderStatusAttribute(): string
    {
        if (!$this->canReceiveMailOrLine()) {
            return '送信先未登録';
        }

        if (empty($this->last_visit)) {
            return '来店日未登録';
        }

        $company = $this->company ?? Company::find($this->company_id);
        $reminderDays = (int) ($company->revisit_reminder_days ?? 45);
        $targetDate = Carbon::now()->subDays($reminderDays)->startOfDay();

        if ($this->last_visit->gt($targetDate)) {
            return '対象外';
        }

        $hasFutureReservation = $this->reservations()
            ->where('status', 'reserved')
            ->where('start_at', '>', now())
            ->exists();

        if ($hasFutureReservation) {
            return '予約済み';
        }

        $sentRecently = $this->followupMailLogs()
            ->where('mail_type', 'revisit_reminder')
            ->where('sent_at', '>=', now()->subDays(30))
            ->exists();

        if ($sentRecently) {
            return '送信済み';
        }

        return '対象';
    }
}
