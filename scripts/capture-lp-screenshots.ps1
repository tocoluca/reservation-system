$ErrorActionPreference = 'Stop'

$ChromePath = 'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
$Port = 9225
$BaseUrl = 'https://demo.tocoluca.com'
$Root = (Resolve-Path '.').Path
$OutDir = Join-Path $Root 'public\lp-screenshots'
$ProfileDir = Join-Path $Root ('storage\app\lp-screenshot-chrome-profile-' + [guid]::NewGuid().ToString())

New-Item -ItemType Directory -Force $OutDir | Out-Null
New-Item -ItemType Directory -Force $ProfileDir | Out-Null

$Shots = @(
    @{
        Name = 'company-dashboard'
        Url = "$BaseUrl/company/dashboard"
        Width = 1440
        Height = 1000
        Mobile = $false
        WaitSeconds = 3
    },
    @{
        Name = 'reservation-calendar'
        Url = "$BaseUrl/company/reserve"
        Width = 1440
        Height = 1000
        Mobile = $false
        WaitSeconds = 3
    },
    @{
        Name = 'reservation-list'
        Url = "$BaseUrl/company/reservations"
        Width = 1440
        Height = 1000
        Mobile = $false
        WaitSeconds = 3
    },
    @{
        Name = 'customers'
        Url = "$BaseUrl/company/customers"
        Width = 1440
        Height = 1000
        Mobile = $false
        WaitSeconds = 3
    },
    @{
        Name = 'public-reservation-mobile'
        Url = "$BaseUrl/r/DEMO"
        Width = 430
        Height = 1100
        Mobile = $true
        WaitSeconds = 4
    }
)

function Wait-ForChrome {
    $deadline = (Get-Date).AddSeconds(20)
    while ((Get-Date) -lt $deadline) {
        try {
            return Invoke-RestMethod -UseBasicParsing "http://127.0.0.1:$Port/json/version"
        } catch {
            Start-Sleep -Milliseconds 250
        }
    }
    throw 'Chrome remote debugging endpoint did not become ready.'
}

function New-CdpClient {
    param([string]$WsUrl)

    $Socket = [System.Net.WebSockets.ClientWebSocket]::new()
    $Socket.ConnectAsync([Uri]$WsUrl, [Threading.CancellationToken]::None).GetAwaiter().GetResult()
    $script:CdpNextId = 1

    return [pscustomobject]@{
        Socket = $Socket
    }
}

function Receive-CdpMessage {
    param($Client)

    $Buffer = New-Object byte[] 1048576
    $Segment = [ArraySegment[byte]]::new($Buffer)
    $Builder = [System.Text.StringBuilder]::new()

    do {
        $Result = $Client.Socket.ReceiveAsync($Segment, [Threading.CancellationToken]::None).GetAwaiter().GetResult()
        if ($Result.Count -gt 0) {
            [void]$Builder.Append([System.Text.Encoding]::UTF8.GetString($Buffer, 0, $Result.Count))
        }
    } while (-not $Result.EndOfMessage)

    return $Builder.ToString() | ConvertFrom-Json
}

function Send-Cdp {
    param(
        $Client,
        [string]$Method,
        [hashtable]$Params = @{}
    )

    $Id = $script:CdpNextId
    $script:CdpNextId = $script:CdpNextId + 1

    $Payload = @{
        id = $Id
        method = $Method
        params = $Params
    } | ConvertTo-Json -Depth 20 -Compress

    $Bytes = [System.Text.Encoding]::UTF8.GetBytes($Payload)
    $Segment = [ArraySegment[byte]]::new($Bytes)
    $Client.Socket.SendAsync($Segment, [System.Net.WebSockets.WebSocketMessageType]::Text, $true, [Threading.CancellationToken]::None).GetAwaiter().GetResult()

    while ($true) {
        $Message = Receive-CdpMessage $Client
        if ($Message.id -eq $Id) {
            if ($Message.error) {
                throw "$Method failed: $($Message.error.message)"
            }
            return $Message.result
        }
    }
}

function Set-Viewport {
    param($Client, [int]$Width, [int]$Height, [bool]$Mobile)

    Send-Cdp $Client 'Emulation.setDeviceMetricsOverride' @{
        width = $Width
        height = $Height
        deviceScaleFactor = 1
        mobile = $Mobile
    } | Out-Null
}

function Navigate-To {
    param($Client, [string]$Url, [int]$WaitSeconds = 2)

    Send-Cdp $Client 'Page.navigate' @{ url = $Url } | Out-Null
    Start-Sleep -Seconds $WaitSeconds
}

function Invoke-Js {
    param($Client, [string]$Expression)

    return Send-Cdp $Client 'Runtime.evaluate' @{
        expression = $Expression
        awaitPromise = $true
        returnByValue = $true
    }
}

function Save-Screenshot {
    param($Client, [string]$Path)

    $Result = Send-Cdp $Client 'Page.captureScreenshot' @{
        format = 'png'
        fromSurface = $true
    }

    [IO.File]::WriteAllBytes($Path, [Convert]::FromBase64String($Result.data))
}

$Chrome = Start-Process -FilePath $ChromePath -PassThru -WindowStyle Hidden -ArgumentList @(
    '--headless=new',
    "--remote-debugging-port=$Port",
    "--user-data-dir=$ProfileDir",
    '--disable-gpu',
    '--disable-crash-reporter',
    '--disable-crashpad',
    '--no-first-run',
    '--hide-scrollbars',
    '--window-size=1440,1000',
    'about:blank'
)

try {
    Wait-ForChrome | Out-Null
    $Target = Invoke-RestMethod -UseBasicParsing -Method Put "http://127.0.0.1:$Port/json/new?$([uri]::EscapeDataString("$BaseUrl/company/login"))"
    $Client = New-CdpClient $Target.webSocketDebuggerUrl

    Send-Cdp $Client 'Page.enable' | Out-Null
    Send-Cdp $Client 'Runtime.enable' | Out-Null
    Send-Cdp $Client 'Network.enable' | Out-Null

    Set-Viewport $Client 1440 1000 $false
    Navigate-To $Client "$BaseUrl/company/login" 3

    Invoke-Js $Client @'
(() => {
  document.querySelector('#company_code').value = 'DEMO';
  document.querySelector('#staff_code').value = 'MASTER01';
  document.querySelector('#password').value = '12345678';
  document.querySelector('form').submit();
  return true;
})()
'@ | Out-Null

    Start-Sleep -Seconds 4

    $CurrentUrl = (Invoke-Js $Client 'location.href').result.value
    if ($CurrentUrl -notlike '*/company/dashboard*') {
        throw "Login did not reach dashboard. Current URL: $CurrentUrl"
    }

    foreach ($Shot in $Shots) {
        Set-Viewport $Client $Shot.Width $Shot.Height $Shot.Mobile
        Navigate-To $Client $Shot.Url $Shot.WaitSeconds
        $Path = Join-Path $OutDir ($Shot.Name + '.png')
        Save-Screenshot $Client $Path
        Write-Output "saved public/lp-screenshots/$($Shot.Name).png"
    }

    $Client.Socket.Dispose()
} finally {
    if ($Chrome -and -not $Chrome.HasExited) {
        Stop-Process -Id $Chrome.Id -Force
    }
}
