<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statutory GPF Calculation & Rules Configuration
    |--------------------------------------------------------------------------
    */

    'dlis' => [
        'max_amount' => 10000,
        'average_months' => 36,
        'eligible_pension_types' => [2, 7], // Death cases
    ],

    'interest' => [
        'decimal_precision' => 4,
        'capitalization_month' => 3, // March (Financial Year End)
        'default_post_demise_grace_months' => 6,
    ],

    'authority' => [
        'office_name_hi' => 'महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला',
        'office_name_en' => 'OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA',
        'address' => 'PO: Kunjaban, Agartala - 799006',
        'cag_portal_url' => 'https://gpfagartala.agtripura.gov.in/GpfAgartala/',
    ],

    'roles' => [
        'super_admin' => 'Super Admin',
        'approver' => 'Senior Accounts Officer (Sr. AO)',
        'checker' => 'Assistant Accounts Officer (AAO)',
        'dealing_assistant' => 'Dealing Assistant (DA)',
        'deo' => 'Data Entry Operator (DEO)',
    ],
];
