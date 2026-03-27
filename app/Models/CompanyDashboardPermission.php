<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDashboardPermission extends Model
{
    protected $fillable = [
        'company_id',
        'role',
        'permission_key',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function roleLabels(): array
    {
        return [
            'master' => 'マスター',
            'area_leader' => 'エリアリーダー',
            'leader' => 'リーダー',
            'member' => 'スタッフ',
        ];
    }

    public static function permissionLabels(): array
    {
        return [
            'dashboard.manage'        => 'ダッシュボード管理',
            'dashboard.sales'         => '売上ダッシュボード',

            'card.reserve'            => '予約カレンダー',
            'card.business_calendar'  => '営業日カレンダー',
            'card.staff'              => '担当者管理',
            'card.menu_category_tag'  => 'カテゴリー・タグ管理',
            'card.menu'               => 'メニュー管理',
            'card.menu_staff'         => 'メニュー対応スタッフ設定',
            'card.shift_patterns'     => 'シフトパターン',
            'card.default_shift'      => '基本シフト',
            'card.month_shift'        => '月シフト',
            'card.customers'          => '顧客管理',
            'card.notices'            => 'お知らせ情報管理',
            'card.vacation'           => '休暇管理',
            'card.theme'              => 'テーマ設定',
            'card.company_info'       => '企業情報編集',
            'card.logo'               => 'ロゴ設定',
            'card.billing'            => '契約管理',
            'card.my_profile'         => 'マイプロフィール',
        ];
    }

    public static function defaultPermissions(string $role): array
    {
        $all = array_fill_keys(array_keys(static::permissionLabels()), false);

        $defaults = match ($role) {
            'master' => [
                'dashboard.manage'        => true,
                'dashboard.sales'         => true,
                'card.reserve'            => true,
                'card.business_calendar'  => true,
                'card.staff'              => true,
                'card.menu_category_tag'  => true,
                'card.menu'               => true,
                'card.menu_staff'         => true,
                'card.shift_patterns'     => true,
                'card.default_shift'      => true,
                'card.month_shift'        => true,
                'card.customers'          => true,
                'card.notices'            => true,
                'card.vacation'           => true,
                'card.theme'              => true,
                'card.company_info'       => true,
                'card.logo'               => true,
                'card.billing'            => true,
                'card.my_profile'         => true,
            ],
            'area_leader' => [
                'dashboard.sales'         => true,
                'card.reserve'            => true,
                'card.business_calendar'  => true,
                'card.staff'              => true,
                'card.menu_category_tag'  => true,
                'card.menu'               => true,
                'card.menu_staff'         => true,
                'card.shift_patterns'     => true,
                'card.default_shift'      => true,
                'card.month_shift'        => true,
                'card.customers'          => true,
                'card.notices'            => true,
                'card.vacation'           => true,
                'card.my_profile'         => true,
            ],
            'leader' => [
                'dashboard.sales'         => true,
                'card.reserve'            => true,
                'card.business_calendar'  => true,
                'card.staff'              => true,
                'card.menu_category_tag'  => true,
                'card.menu'               => true,
                'card.menu_staff'         => true,
                'card.shift_patterns'     => true,
                'card.default_shift'      => true,
                'card.month_shift'        => true,
                'card.customers'          => true,
                'card.notices'            => true,
                'card.vacation'           => true,
                'card.my_profile'         => true,
            ],
            'member' => [
                'card.reserve'            => true,
                'card.vacation'           => true,
                'card.my_profile'         => true,
            ],
            default => [],
        };

        return array_merge($all, $defaults);
    }

    public static function seedForCompany(int $companyId): void
    {
        foreach (static::roleLabels() as $role => $label) {
            $permissions = static::defaultPermissions($role);

            foreach ($permissions as $permissionKey => $isEnabled) {
                static::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'role' => $role,
                        'permission_key' => $permissionKey,
                    ],
                    [
                        'is_enabled' => $isEnabled,
                    ]
                );
            }
        }
    }

    public static function resolveForCompanyRole(int $companyId, string $role): array
    {
        $defaults = static::defaultPermissions($role);

        $rows = static::query()
            ->where('company_id', $companyId)
            ->where('role', $role)
            ->get(['permission_key', 'is_enabled']);

        foreach ($rows as $row) {
            $defaults[$row->permission_key] = (bool) $row->is_enabled;
        }

        if ($role === 'master') {
            $defaults['dashboard.manage'] = true;
        }

        return $defaults;
    }

    public static function resolveAllForCompany(int $companyId): array
    {
        $result = [];

        foreach (static::roleLabels() as $role => $label) {
            $result[$role] = [
                'role' => $role,
                'role_label' => $label,
                'permissions' => static::resolveForCompanyRole($companyId, $role),
            ];
        }

        return $result;
    }

    public static function can(int $companyId, string $role, string $permissionKey): bool
    {
        $permissions = static::resolveForCompanyRole($companyId, $role);
        return (bool) ($permissions[$permissionKey] ?? false);
    }
}