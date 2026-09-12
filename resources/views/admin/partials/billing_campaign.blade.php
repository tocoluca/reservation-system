            @if($billingStartCampaignEnabled ?? true)
            <div class="bg-white p-6 rounded-xl shadow mb-8 border border-blue-100">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
                    <div class="xl:w-1/2">
                        <h3 class="text-lg md:text-xl font-bold">キャンペーン請求開始日</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-6">
                            キャンペーン対象企業の請求開始日を設定します。設定日までは契約管理の制限対象から外れます。
                        </p>

                        <form method="POST"
                              action="{{ route('admin.company.billing-start-campaign') }}"
                              class="mt-5 grid grid-cols-1 lg:grid-cols-[1fr_180px_auto] gap-3"
                              onsubmit="return confirm('選択した企業の請求開始日を設定しますか？');">
                            @csrf
                            <select aria-label="請求開始日を設定する企業" required name="company_id" class="border border-gray-300 rounded-lg p-3 bg-white">
                                <option value="">企業を選択</option>
                                @foreach($companyOptions as $company)
                                    <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>
                                        {{ $company->name }}（{{ $company->company_code }}）
                                    </option>
                                @endforeach
                            </select>

                            <input type="date"
                                   aria-label="請求開始日" required name="billing_starts_at"
                                   value="{{ old('billing_starts_at') }}"
                                   class="border border-gray-300 rounded-lg p-3">

                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold">
                                設定する
                            </button>
                        </form>
                    </div>

                    <div class="xl:w-1/2 rounded-xl bg-blue-50 border border-blue-100 p-5">
                        <div class="text-sm font-bold text-blue-800 mb-3">設定中の企業</div>
                        <div class="space-y-3">
                            @forelse($billingCampaignCompanies as $company)
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg bg-white border border-blue-100 px-4 py-3">
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $company->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $company->company_code }}</div>
                                    </div>
                                    <div class="text-sm font-bold text-blue-700">
                                        {{ optional($company->billing_starts_at)->format('Y/m/d') }} 開始
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-blue-700">現在、請求開始日が設定されている企業はありません。</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- 企業管理ミニダッシュボード -->
            @endif