@extends('layouts.app')

@section('title', '特定商取引法に基づく表記')

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
                    background: #fef3c7;
                    padding: 6px 10px;
                    border-radius: 9999px;
                    margin-bottom: 14px;
                ">
                    LEGAL NOTICE
                </div>
                <h1 style="font-size: 2rem; line-height: 1.4; margin: 0; color: #0f172a;">特定商取引法に基づく表記</h1>
                <p style="margin: 12px 0 0; color: #475569; font-size: 0.98rem; line-height: 1.9;">
                    本サービスのお申込みに関する重要事項を記載しています。
                </p>
            </div>

            <div style="padding: 32px 28px 40px;">
                <div style="
                    overflow: hidden;
                    border: 1px solid #e5e7eb;
                    border-radius: 18px;
                    background: #fff;
                ">
                    <table style="width: 100%; border-collapse: collapse; color: #1f2937; font-size: 0.97rem; line-height: 1.8;">
                        <tbody>
                            <tr>
                                <th style="width: 32%; text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">販売事業者</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">tocoluca</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">運営統括責任者</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">住友 宏和</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">所在地</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">神奈川県藤沢市遠藤638-4</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">電話番号</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
                                    090-2766-9391<br>
                                    ※お問い合わせは原則としてメールにてお願いいたします。
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">メールアドレス</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">master@tocoluca.com</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">販売URL</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">https://reserve.tocoluca.com/apply</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">販売価格</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">各プラン紹介ページまたは申込ページに記載のとおり</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">商品代金以外の必要料金</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">インターネット接続料金、通信料金、振込手数料その他利用環境整備に要する費用は利用者の負担となります。</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">お支払い方法</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">クレジットカード、その他当社が定める方法</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">お支払い時期</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">申込時または当社が別途定める課金日</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">サービスの提供時期</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">当社所定の審査・設定完了後、利用可能となります。開始時期は申込内容、審査状況、設定状況等により異なる場合があります。</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">返品・キャンセルについて</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">サービスの性質上、契約成立後の返品はできません。また、支払済み料金については、法令上返金が必要な場合を除き返金いたしません。</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">中途解約について</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">利用者は当社所定の方法により解約手続を行うことができます。ただし、すでに発生した利用料金その他の債務は消滅せず、日割返金も行いません。</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">表現およびサービスに関する注意書き</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">本サービスの表示内容、機能、効果、集客成果、売上向上その他については個人差・店舗差があり、必ずしも一定の成果を保証するものではありません。</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #0f172a;">動作環境</th>
                                <td style="padding: 16px; border-bottom: 1px solid #e5e7eb;">推奨ブラウザ、推奨OS、インターネット接続環境等が必要です。詳細は当社が別途案内するところによります。</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; vertical-align: top; padding: 16px; background: #f8fafc; color: #0f172a;">サービス停止・終了について</th>
                                <td style="padding: 16px;">当社は、事業上、技術上、保守上その他の都合により、本サービスの全部または一部を停止、変更または終了する場合があります。</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection