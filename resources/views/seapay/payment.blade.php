<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán #{{ $session->orderId }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #EEF2FF;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(37,99,235,.35);
        }
        .header svg { width: 28px; height: 28px; flex-shrink: 0; }
        .header-title { font-size: 1.1rem; font-weight: 700; letter-spacing: .3px; }
        .header-sub   { font-size: .75rem; opacity: .8; margin-left: auto; }

        /* ── Layout ── */
        .page-body {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 16px 40px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.09);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        /* ── Amount Section ── */
        .amount-section {
            background: linear-gradient(160deg, #1e40af 0%, #2563eb 100%);
            color: #fff;
            padding: 24px 24px 20px;
            text-align: center;
        }
        .order-label  { font-size: .8rem; opacity: .75; margin-bottom: 4px; }
        .order-id     { font-size: .95rem; font-weight: 600; opacity: .9; }
        .amount-value {
            font-size: 2.25rem;
            font-weight: 800;
            margin: 8px 0 16px;
            letter-spacing: -1px;
        }

        /* Countdown */
        .countdown-box {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.15);
            border-radius: 30px;
            padding: 6px 16px;
            font-size: .85rem;
        }
        .countdown-label { opacity: .85; }
        #countdown {
            font-size: 1.15rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            min-width: 52px;
            text-align: center;
            transition: color .3s;
        }
        #countdown.urgent { color: #fca5a5; animation: pulse .8s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }

        /* ── Tabs ── */
        .tabs {
            display: flex;
            border-bottom: 2px solid #e5e7eb;
        }
        .tab {
            flex: 1;
            padding: 13px 8px;
            font-size: .88rem;
            font-weight: 600;
            color: #6b7280;
            background: none;
            border: none;
            cursor: pointer;
            position: relative;
            transition: color .2s;
        }
        .tab.active { color: #2563eb; }
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0; right: 0;
            height: 2px;
            background: #2563eb;
            border-radius: 2px 2px 0 0;
        }
        .tab:hover:not(.active) { color: #374151; }

        .tab-content          { display: none; padding: 20px 24px 8px; }
        .tab-content.active   { display: block; }

        /* ── QR Tab ── */
        .qr-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 14px;
        }
        .qr-frame {
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            display: inline-block;
            position: relative;
        }
        .qr-frame::before, .qr-frame::after,
        .qr-corner-br::before, .qr-corner-br::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            border-color: #2563eb;
            border-style: solid;
        }
        .qr-frame::before  { top: -2px; left: -2px;   border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
        .qr-frame::after   { top: -2px; right: -2px;  border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
        .qr-corner-br::before { bottom: -2px; left: -2px;  border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
        .qr-corner-br::after  { bottom: -2px; right: -2px; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }
        #qr-code img, #qr-code canvas { display: block; }

        .qr-loading {
            width: 200px; height: 200px;
            display: flex; align-items: center; justify-content: center;
            color: #9ca3af; font-size: .85rem;
        }
        .qr-hint {
            text-align: center;
            font-size: .8rem;
            color: #6b7280;
            margin-bottom: 14px;
        }

        /* ── Transfer Tab ── */
        .transfer-table { width: 100%; }
        .tr-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            gap: 8px;
        }
        .tr-row:last-child { border-bottom: none; }
        .tr-label {
            font-size: .8rem;
            color: #6b7280;
            flex-shrink: 0;
            width: 120px;
        }
        .tr-val {
            font-size: .9rem;
            font-weight: 600;
            color: #111827;
            text-align: right;
            word-break: break-all;
        }
        .tr-val.highlight { color: #1d4ed8; font-size: 1rem; }
        .copy-btn {
            flex-shrink: 0;
            padding: 3px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: .75rem;
            color: #374151;
            background: #f9fafb;
            cursor: pointer;
            transition: all .15s;
        }
        .copy-btn:hover { background: #e5e7eb; }
        .copy-btn.copied { border-color: #059669; color: #059669; background: #ecfdf5; }

        .transfer-note {
            margin-top: 12px;
            padding: 10px 12px;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            border-radius: 6px;
            font-size: .8rem;
            color: #92400e;
        }

        /* ── Order Info ── */
        .order-info { border-top: 1px solid #f3f4f6; }
        .order-toggle {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px;
            font-size: .875rem;
            font-weight: 600;
            color: #374151;
            background: none;
            border: none;
            cursor: pointer;
        }
        .order-toggle:hover { background: #f9fafb; }
        .toggle-icon { transition: transform .2s; font-size: .7rem; color: #9ca3af; }
        .toggle-icon.open { transform: rotate(180deg); }
        .order-details { padding: 0 24px 16px; }
        .order-details.hidden { display: none; }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #f9fafb;
            font-size: .85rem;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #6b7280; }
        .detail-val   { color: #111827; font-weight: 500; text-align: right; max-width: 200px; }

        /* ── Actions ── */
        .actions {
            padding: 16px 24px 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 13px 20px;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            border: none;
        }
        .btn-primary  { background: #2563eb; color: #fff; }
        .btn-primary:hover  { background: #1d4ed8; }
        .btn-outline  { background: #fff; color: #2563eb; border: 1.5px solid #2563eb; }
        .btn-outline:hover  { background: #eff6ff; }
        .btn-ghost   { background: #f3f4f6; color: #6b7280; }
        .btn-ghost:hover { background: #e5e7eb; color: #374151; }
        .btn-white   { background: rgba(255,255,255,.95); color: #111827; }
        .btn-white:hover { background: #fff; }
        .btn:disabled { opacity: .6; cursor: not-allowed; }

        /* ── Overlays ── */
        .overlay {
            position: fixed;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            text-align: center;
            padding: 32px;
            z-index: 100;
            animation: fadeIn .35s ease;
        }
        @keyframes fadeIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
        .overlay.hidden { display: none; }
        .overlay-success { background: linear-gradient(160deg, #065f46, #059669); color: #fff; }
        .overlay-failed  { background: linear-gradient(160deg, #7f1d1d, #dc2626); color: #fff; }
        .overlay-expired { background: linear-gradient(160deg, #1f2937, #4b5563); color: #fff; }

        .overlay-icon-wrap {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 8px;
        }
        .overlay-icon-wrap svg { width: 44px; height: 44px; }
        .overlay h2 { font-size: 1.5rem; font-weight: 800; }
        .overlay p  { font-size: .95rem; opacity: .85; max-width: 280px; }
        .redirect-hint {
            display: flex; align-items: center; gap: 6px;
            font-size: .82rem; opacity: .7; margin-top: 4px;
        }
        .spinner {
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to{transform:rotate(360deg)} }

        /* ── Toast ── */
        #toast {
            position: fixed;
            bottom: 28px; left: 50%;
            transform: translateX(-50%);
            background: #111827;
            color: #fff;
            padding: 9px 20px;
            border-radius: 30px;
            font-size: .85rem;
            font-weight: 500;
            z-index: 200;
            pointer-events: none;
            transition: opacity .3s;
        }
        #toast.hidden { opacity: 0; }
    </style>
</head>
<body>

<!-- Header -->
<header class="header">
    <svg viewBox="0 0 24 24" fill="none">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" fill="rgba(255,255,255,.5)"/>
        <path d="M7 10.5c0-1.38 1.12-2.5 2.5-2.5h5c1.38 0 2.5 1.12 2.5 2.5v3c0 1.38-1.12 2.5-2.5 2.5h-5C8.12 16 7 14.88 7 13.5v-3z" fill="white"/>
        <circle cx="12" cy="12" r="3" fill="rgba(37,99,235,.8)"/>
    </svg>
    <span class="header-title">SeaPay</span>
    <span class="header-sub">Thanh toán bảo mật</span>
</header>

<div class="page-body">
<div class="card">

    <!-- Amount -->
    <div class="amount-section">
        <p class="order-label">Đơn hàng</p>
        <p class="order-id">#{{ $session->orderId }}</p>
        <p class="amount-value">
            {{ number_format($session->amount, 0, ',', '.') }}
            <span style="font-size:1.3rem">{{ $session->currency === 'VND' ? '₫' : $session->currency }}</span>
        </p>
        @if($session->expiredAt)
        <div class="countdown-box">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke="rgba(255,255,255,.7)" stroke-width="2"/>
                <path d="M12 7v5l3 3" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="countdown-label">Còn lại</span>
            <span id="countdown">--:--</span>
        </div>
        @endif
    </div>

    <!-- Tabs -->
    @php
        $hasQr       = !empty($session->qrCode) || !empty($session->paymentUrl);
        $hasTransfer = !empty($session->bankInfo['bank_account']);
        $hasTabs     = $hasQr && $hasTransfer;
    @endphp

    @if($hasTabs)
    <div class="tabs">
        <button class="tab active" onclick="switchTab('qr', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="display:inline;margin-right:4px;vertical-align:-2px">
                <path d="M3 3h7v7H3zm0 11h7v7H3zm11-11h7v7h-7zm0 11h2v2h-2zm4 0h2v2h-2zm-4 4h2v2h-2zm4-2h2v2h-2zm0 4h2v2h-2z"/>
            </svg>Quét mã QR
        </button>
        <button class="tab" onclick="switchTab('transfer', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="display:inline;margin-right:4px;vertical-align:-2px">
                <path d="M4 10h3v4H4zm6.5 0h3v4h-3zM2 7l10-5 10 5v2H2zm18 8h-3v4h3zm-6.5 0h-3v4h3zM2 17h20v2H2z"/>
            </svg>Chuyển khoản
        </button>
    </div>
    @endif

    <!-- QR Tab -->
    @if($hasQr)
    <div id="tab-qr" class="tab-content active">
        <div class="qr-wrapper">
            <div class="qr-frame">
                <div class="qr-corner-br"></div>
                <div id="qr-code">
                    <div class="qr-loading">Đang tạo QR...</div>
                </div>
            </div>
        </div>
        <p class="qr-hint">Mở ứng dụng ngân hàng hoặc SeaPay để quét</p>

        <div style="display:flex;gap:8px;flex-direction:column;margin-bottom:12px">
            @if($session->deeplink)
            <a href="{{ $session->deeplink }}" class="btn btn-outline" style="font-size:.85rem;padding:10px">
                Mở ứng dụng SeaPay
            </a>
            @endif
            @if(!empty($session->bankInfo['transfer_content']))
            <button onclick="copyText('{{ addslashes($session->bankInfo['transfer_content']) }}', this)" class="btn btn-ghost" style="font-size:.82rem;padding:10px">
                Sao chép nội dung chuyển khoản
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Transfer Tab -->
    @if($hasTransfer)
    <div id="tab-transfer" class="tab-content {{ !$hasTabs ? 'active' : '' }}">
        <div class="transfer-table">
            @if(!empty($session->bankInfo['bank_name']))
            <div class="tr-row">
                <span class="tr-label">Ngân hàng</span>
                <span class="tr-val">{{ $session->bankInfo['bank_name'] }}</span>
            </div>
            @endif
            <div class="tr-row">
                <span class="tr-label">Số tài khoản</span>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="tr-val">{{ $session->bankInfo['bank_account'] }}</span>
                    <button class="copy-btn" onclick="copyText('{{ $session->bankInfo['bank_account'] }}', this)">Sao chép</button>
                </div>
            </div>
            @if(!empty($session->bankInfo['bank_account_name']))
            <div class="tr-row">
                <span class="tr-label">Chủ tài khoản</span>
                <span class="tr-val">{{ strtoupper($session->bankInfo['bank_account_name']) }}</span>
            </div>
            @endif
            <div class="tr-row">
                <span class="tr-label">Số tiền</span>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="tr-val highlight">{{ number_format($session->amount, 0, ',', '.') }} ₫</span>
                    <button class="copy-btn" onclick="copyText('{{ (int)$session->amount }}', this)">Sao chép</button>
                </div>
            </div>
            @if(!empty($session->bankInfo['transfer_content']))
            <div class="tr-row">
                <span class="tr-label">Nội dung CK</span>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="tr-val" style="color:#d97706;font-size:.82rem">{{ $session->bankInfo['transfer_content'] }}</span>
                    <button class="copy-btn" onclick="copyText('{{ addslashes($session->bankInfo['transfer_content']) }}', this)">Sao chép</button>
                </div>
            </div>
            @endif
        </div>
        <p class="transfer-note" style="margin-bottom:12px">
            ⚠️ Nhập <strong>đúng nội dung</strong> để hệ thống tự xác nhận thanh toán
        </p>
    </div>
    @endif

    <!-- Order details (collapsible) -->
    <div class="order-info">
        <button class="order-toggle" onclick="toggleDetails(this)">
            <span>Chi tiết đơn hàng</span>
            <span class="toggle-icon">▼</span>
        </button>
        <div class="order-details hidden">
            @if($session->description)
            <div class="detail-row">
                <span class="detail-label">Mô tả</span>
                <span class="detail-val">{{ $session->description }}</span>
            </div>
            @endif
            @if($session->customerName)
            <div class="detail-row">
                <span class="detail-label">Khách hàng</span>
                <span class="detail-val">{{ $session->customerName }}</span>
            </div>
            @endif
            @foreach($session->items as $item)
            <div class="detail-row">
                <span class="detail-label">{{ $item['name'] ?? 'Sản phẩm' }}</span>
                <span class="detail-val">
                    {{ $item['quantity'] ?? 1 }} x {{ number_format($item['price'] ?? 0, 0, ',', '.') }} ₫
                </span>
            </div>
            @endforeach
            <div class="detail-row">
                <span class="detail-label">Mã giao dịch</span>
                <span class="detail-val" style="font-size:.78rem;color:#9ca3af">{{ $session->transactionId }}</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <button id="btn-confirm" onclick="confirmPaid()" class="btn btn-primary">
            Tôi đã thanh toán
        </button>
        @if($session->cancelUrl)
        <a href="{{ $session->cancelUrl }}" class="btn btn-ghost" style="font-size:.85rem;padding:11px">
            Huỷ thanh toán
        </a>
        @endif
    </div>

</div><!-- .card -->
</div><!-- .page-body -->

<!-- ── Overlay: Success ── -->
<div id="overlay-success" class="overlay overlay-success hidden">
    <div class="overlay-icon-wrap">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M5 13l4 4L19 7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <h2>Thanh toán thành công!</h2>
    <p>Đơn hàng <strong>#{{ $session->orderId }}</strong> đã được xác nhận.</p>
    <p class="redirect-hint"><span class="spinner"></span> Đang chuyển hướng...</p>
</div>

<!-- ── Overlay: Failed ── -->
<div id="overlay-failed" class="overlay overlay-failed hidden">
    <div class="overlay-icon-wrap">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M6 6l12 12M6 18L18 6" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
    </div>
    <h2>Thanh toán thất bại</h2>
    <p>Giao dịch không thể hoàn tất. Vui lòng thử lại.</p>
    @if($session->cancelUrl)
    <a href="{{ $session->cancelUrl }}" class="btn btn-white" style="margin-top:8px;max-width:200px">Quay lại</a>
    @endif
</div>

<!-- ── Overlay: Expired ── -->
<div id="overlay-expired" class="overlay overlay-expired hidden">
    <div class="overlay-icon-wrap">
        <svg viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="white" stroke-width="2"/>
            <path d="M12 7v5l3 3" stroke="white" stroke-width="2" stroke-linecap="round"/>
            <path d="M4.5 4.5l15 15" stroke="rgba(255,255,255,.4)" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </div>
    <h2>Phiên thanh toán hết hạn</h2>
    <p>Đơn hàng <strong>#{{ $session->orderId }}</strong> đã hết thời gian chờ.</p>
    @if($session->cancelUrl)
    <a href="{{ $session->cancelUrl }}" class="btn btn-white" style="margin-top:8px;max-width:200px">Quay lại</a>
    @endif
</div>

<!-- Toast -->
<div id="toast" class="hidden">Đã sao chép!</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function () {
    /* ── Config ── */
    const QR_DATA      = @json($session->qrCode ?? $session->paymentUrl ?? '');
    const EXPIRED_AT   = @json($session->expiredAt ?? '');
    const STATUS_URL   = "{{ route('seapay.payment.status', ['token' => $session->token]) }}";
    const POLL_MS      = 5000;
    const URGENT_SECS  = 60;

    let pollTimer, countdownTimer;

    /* ── QR Code ── */
    if (QR_DATA) {
        const el = document.getElementById('qr-code');
        el.innerHTML = '';
        try {
            new QRCode(el, {
                text:           QR_DATA,
                width:          200,
                height:         200,
                colorDark:      '#111827',
                colorLight:     '#ffffff',
                correctLevel:   QRCode.CorrectLevel.M,
            });
        } catch (e) {
            el.innerHTML = '<p style="color:#9ca3af;font-size:.8rem;padding:20px">Không thể tạo QR</p>';
        }
    } else {
        const qrTab = document.getElementById('tab-qr');
        if (qrTab) qrTab.style.display = 'none';
    }

    /* ── Countdown ── */
    const cdEl = document.getElementById('countdown');
    if (cdEl && EXPIRED_AT) {
        const deadline = new Date(EXPIRED_AT).getTime();

        function tick() {
            const diff = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
            const m    = String(Math.floor(diff / 60)).padStart(2, '0');
            const s    = String(diff % 60).padStart(2, '0');
            cdEl.textContent = m + ':' + s;

            if (diff <= URGENT_SECS) {
                cdEl.classList.add('urgent');
            }
            if (diff === 0) {
                clearInterval(countdownTimer);
                stopPolling();
                showExpired();
            }
        }
        tick();
        countdownTimer = setInterval(tick, 1000);
    }

    /* ── Status polling ── */
    function startPolling() {
        pollTimer = setInterval(fetchStatus, POLL_MS);
    }

    function stopPolling() {
        clearInterval(pollTimer);
    }

    async function fetchStatus() {
        try {
            const res  = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (data.is_expired) {
                stopPolling();
                clearInterval(countdownTimer);
                showExpired();
            } else if (data.is_paid) {
                stopPolling();
                clearInterval(countdownTimer);
                showSuccess(data.return_url);
            } else if (data.is_failed) {
                stopPolling();
                clearInterval(countdownTimer);
                showFailed();
            }
        } catch (_) { /* network blip — keep polling */ }
    }

    startPolling();

    /* ── Manual confirm ── */
    window.confirmPaid = function () {
        const btn = document.getElementById('btn-confirm');
        btn.disabled    = true;
        btn.textContent = 'Đang kiểm tra...';
        fetchStatus().finally(() => {
            btn.disabled    = false;
            btn.textContent = 'Tôi đã thanh toán';
        });
    };

    /* ── Overlays ── */
    function showSuccess(returnUrl) {
        document.getElementById('overlay-success').classList.remove('hidden');
        setTimeout(() => {
            if (returnUrl) window.location.href = returnUrl;
        }, 3000);
    }

    function showFailed() {
        document.getElementById('overlay-failed').classList.remove('hidden');
    }

    function showExpired() {
        document.getElementById('overlay-expired').classList.remove('hidden');
    }

    window.showSuccess = showSuccess;
    window.showFailed  = showFailed;
    window.showExpired = showExpired;

    /* ── Tabs ── */
    window.switchTab = function (name, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
        const panel = document.getElementById('tab-' + name);
        if (panel) panel.classList.add('active');
        btn.classList.add('active');
    };

    /* ── Order details toggle ── */
    window.toggleDetails = function (btn) {
        const icon    = btn.querySelector('.toggle-icon');
        const details = btn.nextElementSibling;
        details.classList.toggle('hidden');
        icon.classList.toggle('open');
    };

    /* ── Copy to clipboard ── */
    window.copyText = function (text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Đã sao chép!');
            if (btn) {
                const orig = btn.textContent;
                btn.textContent = '✓ Đã sao chép';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = orig;
                    btn.classList.remove('copied');
                }, 2000);
            }
        }).catch(() => {
            // Fallback for older browsers
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity  = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast('Đã sao chép!');
        });
    };

    let toastTimer;
    function showToast(msg) {
        const el = document.getElementById('toast');
        el.textContent = msg;
        el.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => el.classList.add('hidden'), 2200);
    }
})();
</script>
</body>
</html>
