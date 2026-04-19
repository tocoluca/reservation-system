@extends('layouts.app')

@section('title', 'プライバシーポリシー')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%); padding: 32px 16px;">
    <div style="max-width: 960px; margin: 0 auto;">
		<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <button
        type="button"
        onclick="window.close()"
        style="
            border: none;
            background: #111827;
            color: #fff;
            padding: 10px 18px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(17, 24, 39, 0.15);
        "
    >
        閉じる
    </button>
		</div>

        <div style="
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        ">
            <div style="
                padding: 40px 28px 24px;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                border-bottom: 1px solid #e5e7eb;
            ">
                <div style="
                    display: inline-block;
                    font-size: 12px;
                    font-weight: 700;
                    letter-spacing: .08em;
                    color: #475569;
                    background: #ecfeff;
                    padding: 6px 10px;
                    border-radius: 9999px;
                    margin-bottom: 14px;
                ">
                    PRIVACY POLICY
                </div>
                <h1 style="font-size: 2rem; line-height: 1.4; margin: 0; color: #0f172a;">プライバシーポリシー</h1>
                <p style="margin: 12px 0 0; color: #475569; font-size: 0.98rem; line-height: 1.9;">
                    個人情報および各種利用情報の取扱いについてご案内します。
                </p>
            </div>

            <div style="
                padding: 32px 28px 40px;
                color: #1f2937;
                line-height: 1.95;
                font-size: 0.98rem;
            ">
                <p style="margin-top: 0;">
                    [運営者名]（以下「当社」といいます。）は、当社が提供する予約システムおよび関連サービス（以下「本サービス」といいます。）において取得する利用者および利用者の顧客等に関する情報について、以下のとおり取り扱います。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">1. 取得する情報</h2>
                <p style="margin-bottom: 8px;">当社は、以下の情報を取得することがあります。</p>
                <ul style="padding-left: 1.4em; margin: 0;">
                    <li>利用者の氏名、名称、店舗名、住所、電話番号、メールアドレス</li>
                    <li>契約情報、請求情報、支払状況</li>
                    <li>ログイン情報、アカウント情報</li>
                    <li>予約情報、顧客情報、スタッフ情報、設定情報</li>
                    <li>IPアドレス、Cookie、アクセスログ、利用環境情報</li>
                    <li>お問い合わせ内容</li>
                    <li>外部サービス連携に伴い取得する情報</li>
                    <li>その他本サービスの提供・運営に必要な情報</li>
                </ul>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">2. 利用目的</h2>
                <p style="margin-bottom: 8px;">取得した情報は、以下の目的で利用します。</p>
                <ul style="padding-left: 1.4em; margin: 0;">
                    <li>本サービスの提供、運営、保守、改善</li>
                    <li>本人確認、認証、アカウント管理</li>
                    <li>予約受付、通知、顧客管理等の機能提供</li>
                    <li>料金請求、決済、契約管理</li>
                    <li>問い合わせ対応、サポート対応</li>
                    <li>障害対応、不正利用防止、セキュリティ確保</li>
                    <li>利用状況の分析、機能改善、新サービス開発</li>
                    <li>重要なお知らせ、規約変更、サービス変更等の通知</li>
                    <li>法令または行政機関の要請への対応</li>
                    <li>上記に付随する目的</li>
                </ul>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">3. 第三者提供</h2>
                <p style="margin-bottom: 8px;">当社は、以下の場合を除き、取得した個人情報を第三者に提供しません。</p>
                <ul style="padding-left: 1.4em; margin: 0;">
                    <li>本人の同意がある場合</li>
                    <li>法令に基づく場合</li>
                    <li>人の生命、身体または財産の保護に必要で本人同意の取得が困難な場合</li>
                    <li>業務委託先に業務遂行上必要な範囲で提供する場合</li>
                    <li>合併、事業譲渡その他の事由により事業承継が行われる場合</li>
                </ul>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">4. 委託</h2>
                <p>
                    当社は、サーバー運用、決済、メール配信、外部システム連携、保守等のため、取得情報の取扱いを第三者に委託する場合があります。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">5. Cookie等の利用</h2>
                <p>
                    当社は、利便性向上、利用状況分析、障害対応、不正防止等のため、Cookie、アクセス解析ツールその他類似技術を利用することがあります。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">6. 情報の管理</h2>
                <p>
                    当社は、取得した情報の漏えい、滅失、毀損の防止その他安全管理のため、合理的な範囲で必要かつ適切な措置を講じるよう努めます。<br>
                    ただし、当社は情報の完全な安全性、消失防止、漏えい防止を保証するものではありません。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">7. データ消失等について</h2>
                <p>
                    当社は、システム障害、外部サービス障害、不正アクセス、通信障害、天災、保守、仕様変更、サービス終了その他いかなる理由によっても、登録情報、予約情報、顧客情報その他のデータが消失、破損、改ざん、漏えいまたは閲覧不能となった場合について責任を負いません。<br>
                    利用者は、必要な情報について自己の責任でバックアップを行うものとします。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">8. 利用者が登録する第三者情報について</h2>
                <p>
                    利用者が本サービス上に顧客情報その他第三者情報を登録する場合、利用者の責任において必要な同意取得、告知その他法令対応を行うものとします。<br>
                    当社は、利用者による第三者情報の登録・利用に起因して生じた紛争等について責任を負いません。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">9. 保有個人情報の開示等</h2>
                <p>
                    当社は、法令に基づき、本人から自己の個人情報について開示、訂正、追加、削除、利用停止等の請求を受けた場合、合理的な範囲で対応します。<br>
                    ただし、法令により当社が応じる義務を負わない場合はこの限りではありません。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">10. ポリシーの変更</h2>
                <p>
                    当社は、必要に応じて本ポリシーを変更することができます。<br>
                    変更後の内容は、当社ウェブサイトまたは本サービス上に掲載した時点から効力を生じます。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">11. お問い合わせ窓口</h2>
                <ul style="padding-left: 1.4em; margin: 0;">
                    <li>事業者名：tocoluca</li>
                    <li>メールアドレス：master@tocoluca.com</li>
                    <li>住所：神奈川県藤沢市遠藤638-4</li>
                </ul>

                <div style="
                    margin-top: 36px;
                    padding-top: 20px;
                    border-top: 1px solid #e5e7eb;
                    color: #475569;
                ">
                    <p style="margin: 0 0 6px;">制定日：2026年4月18日</p>
                    <p style="margin: 0;">事業者名：tocoluca</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection