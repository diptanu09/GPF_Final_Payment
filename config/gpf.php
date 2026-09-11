<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statutory GPF Calculation & Business Rules Configuration
    |--------------------------------------------------------------------------
    | All statutory rates, thresholds, and institutional defaults are defined
    | here and can be overridden in .env without modifying source code.
    */

    'dlis' => [
        'max_amount' => (float) env('GPF_DLIS_MAX_AMOUNT', 60000.00),
        'average_months' => (int) env('GPF_DLIS_AVG_MONTHS', 36),
        'eligible_pension_types' => [2, 7], // 2: Death-in-service, 7: Family Pension
        'debit_head' => env('GPF_DLIS_DEBIT_HEAD', '2235-60-104'),
        'om_reference' => env('GPF_DLIS_OM_REF', 'F.12(7)/FIN(G)/75 dated 18-02-76'),
    ],

    'interest' => [
        'default_rate' => (float) env('GPF_DEFAULT_INTEREST_RATE', 7.10),
        'decimal_precision' => (int) env('GPF_DECIMAL_PRECISION', 4),
        'capitalization_month' => 3, // March (Financial Year End)
        'default_post_demise_grace_months' => (int) env('GPF_POST_DEMISE_GRACE_MONTHS', 6),
    ],

    'authority' => [
        'office_name_hi' => env('GPF_OFFICE_NAME_HI', 'महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला'),
        'office_name_en' => env('GPF_OFFICE_NAME_EN', 'OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA'),
        'office_address' => env('GPF_OFFICE_ADDRESS', 'PO: Kunjaban, Agartala, West Tripura - 799006'),
        'cag_portal_url' => env('GPF_CAG_PORTAL_URL', 'https://gpfagartala.agtripura.gov.in/GpfAgartala/'),
        'state_debit_head' => env('GPF_STATE_DEBIT_HEAD', '8009-01-101 (State GPF)'),
        'ais_debit_head' => env('GPF_AIS_DEBIT_HEAD', '8009-01-104 (All India Services GPF)'),
    ],

    'roles' => [
        'admin' => 'Administrator / Director',
        'approver' => 'Senior Accounts Officer (Sr. AO)',
        'checker' => 'Assistant Accounts Officer (AAO)',
        'deo' => 'Data Entry Operator / Dealing Assistant',
        'dispatch' => 'Outward Dispatch Section',
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary System Administrator Bootstrap Configuration
    |--------------------------------------------------------------------------
    */
    'default_admin' => [
        'name' => env('GPF_ADMIN_NAME', 'Administrator / Director'),
        'username' => env('GPF_ADMIN_USERNAME', 'dir'),
        'email' => env('GPF_ADMIN_EMAIL', 'dir@tripura.gov.in'),
        'password' => env('GPF_ADMIN_PASSWORD', 'dir'),
        'role' => 'admin',
        'designation' => env('GPF_ADMIN_DESG', 'Director (Fund)'),
        'section' => env('GPF_ADMIN_SECTION', 'Directorate Office'),
        'phone_number' => env('GPF_ADMIN_PHONE', '0381-2351234'),
    ],
];
