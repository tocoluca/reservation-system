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
            'store_operator' => '店舗運営',
            'area_leader' => 'エリアリーダー',
            'leader' => 'リーダー',
            'staff' => 'スタッフ',
        ];
    }

    public static function permissionLabels(): array
    {
        return [
            'dashboard.manage' => 'ダッシュボード管理',
            'dashboard.sales' => '売上ダッシュボード',

            'card.reserve' => '予約管理',
            'card.customers' => '顧客管理',
            'card.month_shift' => '勤務管理',
            'card.month_shift_view' => 'スタッフ別シフト表',
            'card.business_calendar' => '営業日管理',
            'card.support' => 'よくあるご質問・お問い合わせ',
            'card.style' => '最新スタイル投稿',
            'card.reviews' => '口コミ管理',
            'card.vacation' => '休暇管理',
            'card.my_profile' => 'マイプロフィール',
            'card.company_info' => '企業情報編集',
            'card.staff' => '担当者管理',
            'card.logo' => 'ロゴ設定',
            'card.menu_category_tag' => 'カテゴリー・タグ管理',
            'card.menu' => 'メニュー管理',
            'card.menu_staff' => 'メニュー対応スタッフ設定',
            'card.shift_patterns' => 'シフトパターン',
            'card.default_shift' => '基本シフト',
            'card.notices' => 'お知らせ情報管理',
            'card.billing' => '契約管理',
            'card.theme' => 'テーマ設定',
            'card.reservation_change_notices' => '予約変更連絡管理',

        ];
    }

    public static function defaultPermissions(string $role): array
    {
        $all = array_fill_keys(array_keys(static::permissionLabels()), false);

        $defaults = match ($role) {
            'master' => [
                'dashboard.manage' => true,
                'dashboard.sales' => true,
                'card.reserve' => true,
                'card.business_calendar' => true,
                'card.staff' => true,
                'card.menu_category_tag' => true,
                'card.menu' => true,
                'card.menu_staff' => true,
                'card.shift_patterns' => true,
                'card.default_shift' => true,
                'card.month_shift' => true,
                'card.month_shift_view' => true,
                'card.customers' => true,
                'card.style' => true,
                'card.reviews' => true,
                'card.notices' => true,
                'card.support' => true,
                'card.vacation' => true,
                'card.theme' => true,
                'card.company_info' => true,
                'card.logo' => true,
                'card.billing' => true,
                'card.my_profile' => true,
                'card.reservation_change_notices' => true,
            ],

            'store_operator' => [
                'dashboard.sales' => true,
                'card.reserve' => true,
                'card.business_calendar' => true,
                'card.staff' => true,
                'card.menu_category_tag' => true,
                'card.menu' => true,
                'card.menu_staff' => false,
                'card.shift_patterns' => false,
                'card.default_shift' => false,
                'card.month_shift' => false,
                'card.month_shift_view' => false,
                'card.customers' => true,
                'card.style' => true,
                'card.reviews' => true,
                'card.notices' => true,
                'card.support' => true,
                'card.vacation' => false,
                'card.theme' => true,
                'card.company_info' => true,
                'card.logo' => true,
                'card.billing' => false,
                'card.my_profile' => false,
                'card.reservation_change_notices' => true,
            ],

            'area_leader' => [
                'dashboard.sales' => true,
                'card.reserve' => true,
                'card.business_calendar' => true,
                'card.staff' => true,
                'card.menu_category_tag' => true,
                'card.menu' => true,
                'card.menu_staff' => true,
                'card.shift_patterns' => true,
                'card.default_shift' => true,
                'card.month_shift' => true,
                'card.month_shift_view' => true,
                'card.customers' => true,
                'card.style' => true,
                'card.reviews' => true,
                'card.notices' => true,
                'card.support' => true,
                'card.vacation' => true,
                'card.my_profile' => true,
                'card.reservation_change_notices' => true,
            ],

            'leader' => [
                'dashboard.sales' => true,
                'card.reserve' => true,
                'card.business_calendar' => true,
                'card.staff' => true,
                'card.menu_category_tag' => true,
                'card.menu' => true,
                'card.menu_staff' => true,
                'card.shift_patterns' => true,
                'card.default_shift' => true,
                'card.month_shift' => true,
                'card.month_shift_view' => true,
                'card.customers' => true,
                'card.style' => true,
                'card.reviews' => true,
                'card.notices' => true,
                'card.support' => true,
                'card.vacation' => true,
                'card.my_profile' => true,
                'card.reservation_change_notices' => true,
            ],

            'staff' => [
                'card.reserve' => true,
                'card.vacation' => true,
                'card.month_shift_view' => true,
                'card.my_profile' => true,
                'card.style' => true,
                'card.support' => true,
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
        // 旧データ救済
        if ($role === 'member') {
            $role = 'staff';
        }

        $defaults = static::defaultPermissions($role);

        $rows = static::query()
            ->where('company_id', $companyId)
            ->whereIn('role', array_unique([$role, $role === 'staff' ? 'member' : $role]))
            ->get(['permission_key', 'is_enabled']);

        foreach ($rows as $row) {
            $defaults[$row->permission_key] = (bool) $row->is_enabled;
        }

        if ($role === 'master') {
            $defaults['dashboard.manage'] = true;
        }

        if ($role === 'store_operator') {
            foreach ([
                'card.menu_staff',
                'card.shift_patterns',
                'card.default_shift',
                'card.vacation',
                'card.my_profile',
            ] as $permissionKey) {
                $defaults[$permissionKey] = false;
            }
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
