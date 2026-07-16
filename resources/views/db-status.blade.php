<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DB Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        }
        h1, h2 {
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 12px;
            vertical-align: top;
        }
        th {
            width: 220px;
            background: #f8fafc;
        }
        .note {
            padding: 12px 14px;
            border-radius: 10px;
            background: #eff6ff;
            color: #1e3a8a;
            margin-bottom: 10px;
        }
        code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 6px;
        }
        ul {
            margin: 0;
            padding-right: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>تشخيص الطلب الحالي</h1>
            <p>هذه الصفحة لا تتصل بقاعدة البيانات نهائيا، وتعرض فقط معلومات الطلب والخادم الحالي حتى تعرف إن المشكلة من المشروع أو من مكان آخر.</p>
        </div>

        <div class="card">
            <h2>ملاحظات</h2>
            @foreach ($notes as $note)
                <div class="note">{{ $note }}</div>
            @endforeach
        </div>

        <div class="card">
            <h2>معلومات الطلب</h2>
            <table>
                <tbody>
                @foreach ($requestInfo as $label => $value)
                    <tr>
                        <th>{{ $label }}</th>
                        <td><code>{{ $value ?: '-' }}</code></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>معلومات الخادم</h2>
            <table>
                <tbody>
                @foreach ($serverInfo as $label => $value)
                    <tr>
                        <th>{{ $label }}</th>
                        <td><code>{{ $value ?: '-' }}</code></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>كيف تعرف إذا المشكلة من خارج المشروع؟</h2>
            <ul>
                <li>إذا فتحت هذه الصفحة وكانت سريعة دائما، فهذا يعني أن بطء 504 ليس من هذا المسار.</li>
                <li>إذا كانت اتصالات MySQL عالية بينما هذه الصفحة لا تستخدم MySQL، فالضغط غالبا من مشروع آخر أو خدمة خارج Laravel.</li>
                <li>لرؤية عدد الـ threads الحقيقي افحص MySQL مباشرة مثل <code>SHOW PROCESSLIST</code> أو <code>SHOW STATUS LIKE 'Threads_%'</code> من السيرفر نفسه.</li>
            </ul>
        </div>
    </div>
</body>
</html>
