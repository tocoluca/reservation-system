<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Staff extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'company_id',
        'staff_code',
        'name',
        'password',
        'role',
        'is_reservable',
        'priority_order',
        'nomination_fee',
        'image_path',
        'comment',
        'retired_at',
        'force_password_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_reservable' => 'boolean',
        'force_password_change' => 'boolean',
        'priority_order' => 'integer',
        'nomination_fee' => 'integer',
        'retired_at' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_staff');
    }

    public function canDashboard(string $permissionKey): bool
    {
        return CompanyDashboardPermission::can(
            (int) $this->company_id,
            (string) $this->role,
            $permissionKey
        );
    }

    public function isMaster(): bool
    {
        return $this->role === 'master';
    }

    public function isStoreOperator(): bool
    {
        return $this->role === 'store_operator';
    }

    public function isAreaLeader(): bool
    {
        return $this->role === 'area_leader';
    }

    public function isChief(): bool
    {
        return $this->role === 'chief';
    }

    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    public function isStaffRole(): bool
    {
        return $this->role === 'staff';
    }

    public function isRetired(?string $dateTime = null): bool
    {
        if (empty($this->retired_at)) {
            return false;
        }

        $target = $dateTime
            ? Carbon::parse($dateTime)
            : now();

        return $target->gte(Carbon::parse($this->retired_at)->startOfDay());
    }

    public function isActiveForReservation(?string $dateTime = null): bool
    {
        return $this->role !== 'store_operator'
            && $this->is_reservable
            && !$this->isRetired($dateTime);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'master' => 'マスター',
            'store_operator' => '店舗運営',
            'chief' => 'チーフ',
            'area_leader' => '統括リーダー',
            'leader' => 'リーダー',
            'staff' => 'スタッフ',
            default => (string) $this->role,
        };
    }

    public static function roleOptions(): array
    {
        return [
            'staff' => 'スタッフ',
            'leader' => 'リーダー',
            'area_leader' => '統括リーダー',
            'store_operator' => '店舗運営',
            'chief' => 'チーフ',
            'master' => 'マスター',
        ];
    }
}
