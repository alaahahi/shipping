<?php

return [
    'show_brokerage' => env('VITE_SHOW_BROKERAGE', false),

    // أرقام الهاتف على عقد السيارة — مفصولة بفاصلة في .env (COMPANY_PHONES)
    'phones' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('COMPANY_PHONES', '07701575738,07707588987,07718456595'))
    ))),
];

