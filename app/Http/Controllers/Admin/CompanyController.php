<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\Staff;
use App\Mail\MasterCreatedMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CompanyController extends Controller
{
    public function create()
    {
        return view('admin.company_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'industry_type' => 'required|string|max:255',
            'staff_code' => 'required|string|max:255|unique:staff,staff_code',
            'staff_name' => 'required|string|max:255',
            'staff_password' => ['required', Password::min(8)],
            'email' => 'required|email:rfc,dns|max:255',
        ], [
            'name.required' => '企業名を入力してください。',
            'industry_type.required' => '業種を選択してください。',
            'staff_code.required' => '担当者コードを入力してください。',
            'staff_code.unique' => '担当者コードはすでに使われています。',
            'staff_name.required' => '担当者名を入力してください。',
            'staff_password.required' => '初期パスワードを入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
        ]);

        $code = $this->generateCompanyCode();

        DB::transaction(function () use ($request, $code) {
            $company = Company::create([
                'company_code' => $code,
                'name' => $request->name,
                'email' => $request->email,
                'industry_type' => $request->industry_type,
                'theme_color' => '#3b82f6',
                'is_active' => true,
                'line_login_enabled' => false,
                'slot_minutes' => 30,
                'max_simultaneous_reservations' => 1,
                'menu_time_priority_flag' => true,
                'reservation_month_limit' => 3,
                'reservation_open_days' => 0,
                'reservation_close_hours' => 1,
                'regular_holidays' => [],
                'holiday_is_closed' => false,
                'open_patterns' => [],
            ]);

            Staff::create([
                'company_id' => $company->id,
                'staff_code' => $request->staff_code,
                'name' => $request->staff_name,
                'password' => Hash::make($request->staff_password),
                'role' => 'master',
                'is_reservable' => false,
                'priority_order' => 0,
                'force_password_change' => true,
            ]);

            Mail::to($request->email)->send(
                new MasterCreatedMail(
                    $company,
                    $request->staff_code,
                    $request->staff_name,
                    $request->staff_password
                )
            );
        });

        return redirect()
            ->route('admin.company.index')
            ->with('success', '企業とマスター担当者を登録しました。');
    }

    private function generateCompanyCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Company::where('company_code', $code)->exists());

        return $code;
    }

    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('company_code', 'like', "%{$keyword}%")
                    ->orWhere('industry_type', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'uninitialized' => $query->where('is_initialized', false),
                'billing_attention' => $query->where(function ($q) {
                    $q->where(function ($billingQuery) {
                        $billingQuery->whereNull('billing_starts_at')
                            ->orWhere('billing_starts_at', '<=', now());
                    })
                        ->where(function ($billingQuery) {
                            $billingQuery->where('is_billing_active', false)
                                ->orWhereIn('subscription_status', ['past_due', 'unpaid', 'incomplete', 'incomplete_expired']);
                        });
                }),
                'billing_campaign' => $query->whereNotNull('billing_starts_at')->where('billing_starts_at', '>', now()),
                'line_enabled' => $query->where('line_login_enabled', true),
                default => null,
            };
        }

        $companies = $query
            ->withCount(['staff', 'reservations', 'customers'])
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => Company::count(),
            'active' => Company::where('is_active', true)->count(),
            'inactive' => Company::where('is_active', false)->count(),
            'uninitialized' => Company::where('is_initialized', false)->count(),
            'billing_attention' => Company::where(function ($q) {
                $q->where(function ($billingQuery) {
                    $billingQuery->whereNull('billing_starts_at')
                        ->orWhere('billing_starts_at', '<=', now());
                })
                    ->where(function ($billingQuery) {
                        $billingQuery->where('is_billing_active', false)
                            ->orWhereIn('subscription_status', ['past_due', 'unpaid', 'incomplete', 'incomplete_expired']);
                    });
            })->count(),
            'billing_campaign' => Company::whereNotNull('billing_starts_at')->where('billing_starts_at', '>', now())->count(),
        ];

        return view('admin.company_index', compact('companies', 'summary'));
    }

    public function updateBillingStartCampaign(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'billing_starts_at' => ['required', 'date'],
        ], [
            'company_id.required' => '企業を選択してください。',
            'billing_starts_at.required' => '請求開始日を入力してください。',
            'billing_starts_at.date' => '請求開始日の形式が正しくありません。',
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $billingStartsAt = Carbon::parse($validated['billing_starts_at'])->startOfDay();

        $company->billing_starts_at = $billingStartsAt;
        $company->save();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $company->name . ' の請求開始日を ' . $billingStartsAt->format('Y/m/d') . ' に設定しました。');
    }

    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('admin.company_edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate($this->rules($company->id), $this->messages());

        $this->validateOpenPatterns($validated['open_patterns'] ?? []);

        $company->update($this->buildCompanyData($request, $validated, false));

        return redirect()
            ->route('admin.company.edit', $company->id)
            ->with('success', '企業情報を更新しました。');
    }

    public function bulkEdit(Request $request)
    {
        $ids = collect($request->company_ids ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $companies = Company::whereIn('id', $ids)->orderBy('name')->get();

        if ($companies->isEmpty()) {
            return redirect()
                ->route('admin.company.index')
                ->with('error', '一括編集する企業を選択してください。');
        }

        return view('admin.company_bulk_edit', compact('companies'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'required|integer|exists:companies,id',

            'apply_theme_color' => 'nullable|boolean',
            'theme_color' => 'nullable|string|max:20',

            'apply_is_active' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',

            'apply_is_initialized' => 'nullable|boolean',
            'is_initialized' => 'nullable|boolean',

            'apply_line_login_enabled' => 'nullable|boolean',
            'line_login_enabled' => 'nullable|boolean',

            'apply_line_channel_id' => 'nullable|boolean',
            'line_channel_id' => 'nullable|string|max:255',

            'apply_line_channel_secret' => 'nullable|boolean',
            'line_channel_secret' => 'nullable|string|max:255',

            'apply_slot_minutes' => 'nullable|boolean',
            'slot_minutes' => 'nullable|integer|min:5|max:120',

            'apply_max_simultaneous_reservations' => 'nullable|boolean',
            'max_simultaneous_reservations' => 'nullable|integer|min:1|max:10',

            'apply_menu_time_priority_flag' => 'nullable|boolean',
            'menu_time_priority_flag' => 'nullable|boolean',

            'apply_reservation_month_limit' => 'nullable|boolean',
            'reservation_month_limit' => 'nullable|integer|min:1|max:12',

            'apply_reservation_open_days' => 'nullable|boolean',
            'reservation_open_days' => 'nullable|integer|min:0|max:60',

            'apply_reservation_close_hours' => 'nullable|boolean',
            'reservation_close_hours' => 'nullable|integer|min:0|max:72',

            'apply_holiday_is_closed' => 'nullable|boolean',
            'holiday_is_closed' => 'nullable|boolean',

            'apply_regular_holidays' => 'nullable|boolean',
            'regular_holidays' => 'nullable|array',

            'apply_grace_until' => 'nullable|boolean',
            'grace_until' => 'nullable|date',

            'apply_subscription_status' => 'nullable|boolean',
            'subscription_status' => 'nullable|in:incomplete,incomplete_expired,trialing,active,past_due,canceled,unpaid',

            'apply_is_billing_active' => 'nullable|boolean',
            'is_billing_active' => 'nullable|boolean',

            'apply_stripe_customer_id' => 'nullable|boolean',
            'stripe_customer_id' => 'nullable|string|max:255',

            'apply_stripe_subscription_id' => 'nullable|boolean',
            'stripe_subscription_id' => 'nullable|string|max:255',
        ], $this->messages());

        $updateData = [];

        $fields = [
            'theme_color',
            'is_active',
            'is_initialized',
            'line_login_enabled',
            'line_channel_id',
            'line_channel_secret',
            'slot_minutes',
            'max_simultaneous_reservations',
            'menu_time_priority_flag',
            'reservation_month_limit',
            'reservation_open_days',
            'reservation_close_hours',
            'holiday_is_closed',
            'grace_until',
            'subscription_status',
            'is_billing_active',
            'stripe_customer_id',
            'stripe_subscription_id',
        ];

        foreach ($fields as $field) {
            if ($request->boolean('apply_' . $field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        if ($request->boolean('apply_regular_holidays')) {
            $updateData['regular_holidays'] = $request->input('regular_holidays', []);
        }

        if (
            array_key_exists('line_login_enabled', $updateData) &&
            (int) $updateData['line_login_enabled'] !== 1
        ) {
            if ($request->boolean('apply_line_channel_id')) {
                $updateData['line_channel_id'] = null;
            }
            if ($request->boolean('apply_line_channel_secret')) {
                $updateData['line_channel_secret'] = null;
            }
        }

        if (empty($updateData)) {
            return back()
                ->with('error', '一括更新する項目を選択してください。')
                ->withInput();
        }

        Company::whereIn('id', $request->company_ids)->update($updateData);

        return redirect()
            ->route('admin.company.index')
            ->with('success', '選択した企業を一括更新しました。');
    }

    public function toggle($id)
    {
        $company = Company::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        return back()->with('success', '状態を更新しました。');
    }

    private function rules($ignoreId = null)
    {
        $companyCodeRule = 'required|string|max:8|unique:companies,company_code';
        if ($ignoreId) {
            $companyCodeRule .= ',' . $ignoreId;
        }

        return [
            'name' => 'required|string|max:255',
            'company_code' => $companyCodeRule,
            'industry_type' => 'required|string|max:255',
            'email' => 'nullable|email:rfc,dns|max:255',
            'homepage' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'theme_color' => 'required|string|max:20',

            'is_active' => 'nullable|boolean',
            'is_initialized' => 'nullable|boolean',

            'line_login_enabled' => 'nullable|boolean',
            'line_channel_id' => 'nullable|string|max:255',
            'line_channel_secret' => 'nullable|string|max:255',
            'line_channel_access_token' => 'nullable|string|max:5000',
            'line_official_account_id' => 'nullable|string|max:255',

            'slot_minutes' => 'required|integer|min:5|max:120',
            'max_simultaneous_reservations' => 'required|integer|min:1|max:10',
            'menu_time_priority_flag' => 'nullable|boolean',

            'reservation_month_limit' => 'nullable|integer|min:1|max:12',
            'reservation_open_days' => 'nullable|integer|min:0|max:60',
            'reservation_close_hours' => 'nullable|integer|min:0|max:72',

            'regular_holidays' => 'nullable|array',
            'holiday_is_closed' => 'nullable|boolean',
            'open_patterns' => 'nullable|array',

            'grace_until' => 'nullable|date',
            'subscription_status' => 'nullable|in:incomplete,incomplete_expired,trialing,active,past_due,canceled,unpaid',
            'is_billing_active' => 'nullable|boolean',
            'stripe_customer_id' => 'nullable|string|max:255',
            'stripe_subscription_id' => 'nullable|string|max:255',
        ];
    }

    private function messages()
    {
        return [
            'name.required' => '企業名を入力してください。',
            'company_code.required' => '企業コードを入力してください。',
            'company_code.unique' => '企業コードが重複しています。',
            'industry_type.required' => '業種を入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'homepage.url' => 'ホームページURLの形式が正しくありません。',
            'slot_minutes.required' => '時間刻みを入力してください。',
            'slot_minutes.integer' => '時間刻みは数値で入力してください。',
            'max_simultaneous_reservations.required' => '同時予約数を入力してください。',
            'max_simultaneous_reservations.integer' => '同時予約数は数値で入力してください。',
            'reservation_month_limit.integer' => '予約可能期間は数値で入力してください。',
            'reservation_open_days.integer' => '予約受付開始日は数値で入力してください。',
            'reservation_close_hours.integer' => '予約締切時間は数値で入力してください。',
        ];
    }

    private function validateOpenPatterns(array $patterns)
    {
        $dayNames = ['日', '月', '火', '水', '木', '金', '土'];

        foreach ($patterns as $weekday => $dayPatterns) {
            foreach ($dayPatterns as $index => $pattern) {
                $open = $pattern['open'] ?? null;
                $close = $pattern['close'] ?? null;

                if (!empty($open) && !empty($close) && $open >= $close) {
                    $dayLabel = $dayNames[$weekday] ?? $weekday;
                    $slotNumber = $index + 1;

                    abort(
                        back()
                            ->withErrors([
                                "open_patterns.$weekday.$index.open" =>
                                    "{$dayLabel}曜日 {$slotNumber}枠目：開始時間は終了時間より前にしてください。"
                            ])
                            ->withInput()
                    );
                }
            }
        }
    }

    private function buildCompanyData(Request $request, array $validated, bool $isBulk = false)
    {
        $patterns = collect($request->open_patterns ?? [])
            ->map(function ($day) {
                return collect($day)
                    ->filter(fn ($p) => !empty($p['open']) && !empty($p['close']))
                    ->values()
                    ->toArray();
            })
            ->toArray();

        $lineLoginEnabled = (int) ($request->input('line_login_enabled', 0)) === 1;

        return [
            'name' => $validated['name'],
            'company_code' => $validated['company_code'],
            'industry_type' => $validated['industry_type'],
            'email' => $validated['email'] ?? null,
            'homepage' => $validated['homepage'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'theme_color' => $validated['theme_color'] ?? '#3b82f6',

            'is_active' => $request->boolean('is_active'),
            'is_initialized' => $request->boolean('is_initialized'),

            'line_login_enabled' => $lineLoginEnabled,
            'line_channel_id' => $lineLoginEnabled ? ($validated['line_channel_id'] ?? null) : null,
            'line_channel_secret' => $lineLoginEnabled ? ($validated['line_channel_secret'] ?? null) : null,
            'line_channel_access_token' => $validated['line_channel_access_token'] ?? null,
            'line_official_account_id' => $validated['line_official_account_id'] ?? null,

            'slot_minutes' => $validated['slot_minutes'],
            'max_simultaneous_reservations' => $validated['max_simultaneous_reservations'],
            'menu_time_priority_flag' => $request->boolean('menu_time_priority_flag'),

            'reservation_month_limit' => $validated['reservation_month_limit'] ?? 3,
            'reservation_open_days' => $validated['reservation_open_days'] ?? 0,
            'reservation_close_hours' => $validated['reservation_close_hours'] ?? 1,

            'regular_holidays' => $validated['regular_holidays'] ?? [],
            'holiday_is_closed' => $request->boolean('holiday_is_closed'),
            'open_patterns' => $patterns,

            'grace_until' => $validated['grace_until'] ?? null,
            'subscription_status' => $validated['subscription_status'] ?? null,
            'is_billing_active' => $request->boolean('is_billing_active'),
            'stripe_customer_id' => $validated['stripe_customer_id'] ?? null,
            'stripe_subscription_id' => $validated['stripe_subscription_id'] ?? null,
        ];
    }
}
