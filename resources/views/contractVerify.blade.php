<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من العقد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet" integrity="sha384-iT7ZROZg/OdzFFMCMpMKj9OoicfrhdYtmwyeUP8yPY+0dz4nUK2wQOKy/Z62m7z/" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f8fafc;
        }
        .verification-card {
            max-width: 720px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .verification-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            padding: 32px;
        }
        .badge-status {
            font-size: 0.85rem;
        }
        .contract-section {
            padding: 24px 32px;
        }
        .field-label {
            color: #64748b;
            font-size: 0.9rem;
        }
        .field-value {
            color: #0f172a;
            font-weight: 600;
            font-size: 1.05rem;
        }
        .barcode-wrapper {
            text-align: center;
            padding-bottom: 24px;
        }
        .secondary-info {
            background-color: #f1f5f9;
            padding: 16px 24px;
            font-size: 0.95rem;
            color: #334155;
        }
        a.contract-link {
            color: #1d4ed8;
            text-decoration: none;
        }
        a.contract-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="verification-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h1 class="h4 mb-2">التحقق من عقد بيع مركبة</h1>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-light text-dark badge-status">رقم العقد: {{ $contract->id }}</span>
                        <span class="badge bg-light text-dark badge-status">تاريخ العقد: {{ $contract->created ?? $contract->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">مركز: {{ $config->company_name ?? 'غير محدد' }}</div>
                    <div class="small opacity-75">{{ $config->second_title_ar ?? '' }}</div>
                </div>
            </div>
        </div>
        <div class="contract-section">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="field-label mb-1">الطرف الأول (البائع)</div>
                    <div class="field-value">{{ $contract->name_seller }}</div>
                    <div class="text-muted small mt-1">{{ $contract->phone_seller }} — {{ $contract->address_seller }}</div>
                    <div class="text-muted small">رقم الهوية: {{ $contract->seller_id_number }}</div>
                </div>
                <div class="col-md-6">
                    <div class="field-label mb-1">الطرف الثاني (المشتري)</div>
                    <div class="field-value">{{ $contract->name_buyer }}</div>
                    <div class="text-muted small mt-1">{{ $contract->phone_buyer }} — {{ $contract->address_buyer }}</div>
                    <div class="text-muted small">رقم الهوية: {{ $contract->buyer_id_number }}</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="field-label mb-1">نوع المركبة</div>
                    <div class="field-value">{{ $contract->car_name }}</div>
                    <div class="text-muted small mt-1">الموديل: {{ $contract->modal }} / اللون: {{ $contract->color }} / الرقم: {{ $contract->no }}</div>
                </div>
                <div class="col-md-6">
                    <div class="field-label mb-1">الشاصي</div>
                    <div class="field-value">{{ $contract->vin }}</div>
                    <div class="text-muted small mt-1">السعر: {{ number_format($contract->car_price, 2) }} $ / المدفوع: {{ number_format($contract->car_paid, 2) }} $</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="barcode-wrapper">
                <img id="verification-qr" alt="QR" style="display:none;width:160px;height:160px;margin:0 auto;background:#fff;padding:8px;border-radius:10px;" />
                <div class="mt-3 field-label">رمز التحقق: {{ $contract->verification_token }}</div>
                <a class="contract-link d-inline-block mt-2" href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
            </div>
        </div>

        <div class="secondary-info">
            <div>هذه الصفحة تؤكد صحة العقد عند مسحه من خلال الباركود. المعلومات المعروضة للاطلاع فقط، وأي تفاصيل إضافية تحتاج إلى مراجعة المعرض مباشرة.</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const verificationUrl = @json($verificationUrl ?? '');
            const qrImg = document.getElementById('verification-qr');
            const fallbackRender = () => {
                if (verificationUrl && qrImg) {
                    qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(verificationUrl);
                    qrImg.style.display = 'block';
                }
            };

            if (typeof QRCode !== 'undefined' && verificationUrl && qrImg && typeof QRCode.toDataURL === 'function') {
                QRCode.toDataURL(
                    verificationUrl,
                    {
                        width: 160,
                        margin: 1,
                        colorDark: "#000000",
                        colorLight: "#ffffff"
                    },
                    function (error, url) {
                        if (error) {
                            console.error(error);
                            fallbackRender();
                        } else {
                            qrImg.src = url;
                            qrImg.style.display = 'block';
                        }
                    }
                );
            } else if (qrImg) {
                fallbackRender();
            }
        });
    </script>
</body>
</html>

