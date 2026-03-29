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
    ];

    protected $casts = [
        'last_visit' => 'datetime',
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

    public function isRevisitReminderTarget(): bool
    {
        if (empty($this->email) || empty($this->last_visit)) {
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
        if (empty($this->email)) {
            return 'メール未登録';
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}