@extends('layouts.app')

@section('title', '利用規約')

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
                    background: #eef2ff;
                    padding: 6px 10px;
                    border-radius: 9999px;
                    margin-bottom: 14px;
                ">
                    TERMS OF SERVICE
                </div>
                <h1 style="font-size: 2rem; line-height: 1.4; margin: 0; color: #0f172a;">利用規約</h1>
                <p style="margin: 12px 0 0; color: #475569; font-size: 0.98rem; line-height: 1.9;">
                    本サービスをご利用いただく前に、以下の内容をご確認ください。
                </p>
            </div>

            <div style="
                padding: 32px 28px 40px;
                color: #1f2937;
                line-height: 1.95;
                font-size: 0.98rem;
            ">
                <p style="margin-top: 0;">
                    本利用規約（以下「本規約」といいます。）は、[運営者名]（以下「当社」といいます。）が提供する予約システムおよびこれに付随する一切のサービス（以下「本サービス」といいます。）の利用条件を定めるものです。利用者は、本サービスの申込みまたは利用をもって、本規約に同意したものとみなします。
                </p>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第1条（適用）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>本規約は、本サービスの利用に関する当社と利用者との間の一切の関係に適用されます。</li>
                    <li>当社が本サービス上または当社ウェブサイト上で掲載するルール、注意事項等は、本規約の一部を構成するものとします。</li>
                    <li>本規約と個別の案内等の内容が異なる場合は、当社が別途定める場合を除き、本規約が優先して適用されます。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第2条（本サービスの内容）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>本サービスは、予約受付、顧客管理、スタッフ管理、営業時間設定、通知機能、外部連携機能、その他当社が提供する予約関連サービスを含みます。</li>
                    <li>当社は、利用者への事前通知なく、本サービスの全部または一部の内容、仕様、名称、機能、画面構成、URL等を追加、変更、停止または終了することができます。</li>
                    <li>利用者は、本サービスが永続的に提供されるものではなく、当社の都合、事業上の判断、システム上の事情、外部環境の変化その他の理由により、全部または一部が終了する場合があることをあらかじめ承諾するものとします。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第3条（利用申込み・契約成立）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>利用者は、当社所定の方法により本サービスの利用を申し込むものとします。</li>
                    <li>利用契約は、当社が申込みを承諾した時点または当社が利用開始を認めた時点で成立するものとします。</li>
                    <li>当社は、申込者に以下の事由があると判断した場合、申込みを承諾しないことができます。
                        <ol style="padding-left: 1.4em; margin-top: 8px;">
                            <li>申込内容に虚偽、誤記または記載漏れがあった場合</li>
                            <li>過去に本規約違反等があった場合</li>
                            <li>料金の支払能力に不安がある場合</li>
                            <li>反社会的勢力等に関与していると当社が判断した場合</li>
                            <li>その他当社が不適当と判断した場合</li>
                        </ol>
                    </li>
                    <li>当社は、承諾拒否の理由を開示する義務を負いません。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第4条（料金・支払）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>利用者は、当社が別途定める利用料金、初期費用、オプション料金その他の費用を、当社所定の方法で支払うものとします。</li>
                    <li>利用料金は、利用の有無、予約件数、売上、集客結果その他にかかわらず発生します。</li>
                    <li>月途中の契約開始、解約、停止等があっても、当社が別途定める場合を除き、日割計算は行いません。</li>
                    <li>支払済みの料金は、法令上返金が必要な場合を除き、理由のいかんを問わず返金しません。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第5条（アカウント管理）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>利用者は、ID、パスワードその他の認証情報を自己の責任で適切に管理するものとします。</li>
                    <li>認証情報の管理不十分、漏えい、第三者使用等によって生じた損害について、当社は一切責任を負いません。</li>
                    <li>利用者のアカウントにより行われた行為は、当該利用者自身によるものとみなします。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第6条（禁止事項）</h2>
                <p style="margin-bottom: 8px;">利用者は、以下の行為をしてはなりません。</p>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>法令または公序良俗に違反する行為</li>
                    <li>虚偽情報の登録</li>
                    <li>第三者の権利または利益を侵害する行為</li>
                    <li>本サービスの運営を妨害する行為</li>
                    <li>不正アクセス、過度な負荷を与える行為</li>
                    <li>本サービスの不具合や仕様を悪用する行為</li>
                    <li>本サービスの複製、改変、解析、再販売、再許諾</li>
                    <li>その他当社が不適切と判断する行為</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第7条（データ管理）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>利用者は、本サービス上に登録、保存、送信するデータについて、自己の責任で管理し、必要に応じて自らバックアップを行うものとします。</li>
                    <li>当社は、利用者データの保存、保全、バックアップ、復旧を保証しません。</li>
                    <li>システム障害、通信障害、外部サービス障害、操作ミス、不正アクセス、天災、仕様変更、サービス終了その他いかなる理由によっても、データの消失、破損、改ざん、漏えい、閲覧不能等が生じた場合、当社は一切責任を負いません。</li>
                    <li>契約終了後またはサービス終了後、当社は利用者データを保持する義務を負わず、任意に削除できるものとします。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第8条（サービス停止・終了）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>当社は、システム保守、障害対応、仕様変更、外部サービスとの連携事情、事業上の判断その他の理由により、利用者への事前通知なく、本サービスの全部または一部を停止、中断または終了することができます。</li>
                    <li>本サービスの停止、中断、変更または終了により利用者または第三者に損害が生じた場合であっても、当社は一切責任を負いません。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第9条（保証の否認）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>当社は、本サービスについて、完全性、正確性、有用性、継続性、無停止稼働、特定目的適合性、売上向上、集客効果、予約増加その他一切を保証しません。</li>
                    <li>当社は、本サービスが利用者の期待する成果を生むことを保証しません。</li>
                    <li>当社は、本サービスに不具合、エラー、誤表示、通知漏れ、外部連携不全等が存在しないことを保証しません。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第10条（免責）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>当社は、本サービスの利用または利用不能に関連して利用者または第三者に生じた一切の損害について、法令上認められる範囲で責任を負いません。</li>
                    <li>前項には、売上減少、逸失利益、予約機会の喪失、信用毀損、顧客対応費用、返金対応、データ消失、復旧費用、営業損害、間接損害、特別損害、結果損害を含みます。</li>
                    <li>利用者が本サービスを正常に利用していた場合であっても、不具合、誤作動、仕様上の問題、通知不達、データ消失等について、当社は責任を負いません。</li>
                    <li>当社が何らかの理由で責任を負う場合であっても、その上限は、当該利用者が直近1か月に当社へ支払った利用料金の総額とします。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第11条（利用停止・解除）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>当社は、利用者が本規約に違反した場合、料金支払を怠った場合、または当社が利用継続を不適当と判断した場合、事前通知なく本サービスの利用停止、契約解除、データ削除その他必要な措置を行うことができます。</li>
                    <li>前項の場合でも、当社は既に受領した料金を返金しません。</li>
                    <li>当社は、本条に基づく措置により生じた損害について責任を負いません。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第12条（規約変更）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>当社は、必要に応じて本規約を変更することができます。</li>
                    <li>変更後の規約は、当社ウェブサイトまたは本サービス上に掲載した時点から効力を生じるものとします。</li>
                    <li>利用者が変更後も本サービスを利用した場合、変更後の規約に同意したものとみなします。</li>
                </ol>

                <h2 style="font-size: 1.15rem; margin-top: 32px; margin-bottom: 12px; color: #0f172a;">第13条（準拠法・管轄）</h2>
                <ol style="padding-left: 1.4em; margin: 0;">
                    <li>本規約は日本法に準拠します。</li>
                    <li>本サービスに関連して生じた紛争については、当社所在地を管轄する裁判所を第一審の専属的合意管轄裁判所とします。</li>
                </ol>

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