<?php

namespace Database\Seeders;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\CalculationMonthlyBreakdown;
use App\Models\CalculationRun;
use App\Models\CaseNominee;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\User;
use App\Models\WorkflowHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleCaseSeeder extends Seeder
{
    public function run(): void
    {
        $deo = User::where('username', 'deeksha')->orWhere('role', 'deo')->first() ?? User::first();
        $da = User::where('username', 'kalipada')->orWhere('role', 'deo')->first() ?? $deo;
        $aao = User::where('username', 'anjana')->orWhere('role', 'checker')->first() ?? $deo;
        $srao = User::where('username', 'rkdb')->orWhere('role', 'approver')->first() ?? $deo;

        // -------------------------------------------------------------
        // Case 1: Draft / Inward Registered (Superannuation)
        // -------------------------------------------------------------
        $case1 = InwardCase::create([
            'registration_no' => '20240110001',
            'diary_number' => 'INW/2024/1042',
            'diary_date' => Carbon::now()->subDays(5),
            'series_code' => '01',
            'series_name' => 'EDN - Education Department',
            'account_no' => '10001',
            'subscriber_name_cache' => 'Sri Debabrata Roy',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Executive Engineer',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'section' => 'Fund Section I',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => Carbon::now()->subMonths(1)->endOfMonth(),
            'last_fund_deduction' => Carbon::now()->subMonths(2),
            'debit_during_year' => 0.00,
            'personal_address' => 'Quarter No. B-12, Kunjaban Colony, Agartala, West Tripura',
            'mobile_no' => '9436123456',
            'employee_code' => 'EMP-TR-8812',
            'beneficiary_code' => 'BEN-77123',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $deo->id,
            'assigned_user_id' => $da->id,
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $case1->id,
            'performed_by' => $deo->id,
            'from_status' => null,
            'to_status' => CaseWorkflowStatus::DRAFT,
            'action_type' => 'INWARD_REGISTER',
            'remarks' => 'Inward registered via physical application received from DDO.',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // -------------------------------------------------------------
        // Case 2: Calculation Done (Death in Service with Nominees)
        // -------------------------------------------------------------
        $case2 = InwardCase::create([
            'registration_no' => '20240110002',
            'diary_number' => 'INW/2024/1038',
            'diary_date' => Carbon::now()->subDays(12),
            'series_code' => '01',
            'series_name' => 'EDN - Education Department',
            'account_no' => '10002',
            'subscriber_name_cache' => 'Late Pradip Sen',
            'name_title' => 'Late',
            'designation_title' => 'Mr',
            'designation' => 'Assistant Teacher',
            'case_type' => CaseType::DEATH_IN_SERVICE,
            'pension_type_id' => '2',
            'section' => 'Fund Section II',
            'ddo_code' => '1002',
            'treasury_code' => '02',
            'event_date' => Carbon::now()->subMonths(4)->day(15),
            'last_fund_deduction' => Carbon::now()->subMonths(5),
            'debit_during_year' => 0.00,
            'personal_address' => 'Vill & PO: Radhakishorepur, Udaipur, Gomati Tripura',
            'mobile_no' => '9862987654',
            'employee_code' => 'EMP-TR-5541',
            'spouse_name' => 'Smt Gita Sen',
            'spouse_relation' => 'Wife',
            'current_status' => CaseWorkflowStatus::CALCULATED,
            'created_by' => $deo->id,
            'assigned_user_id' => $aao->id,
            'calculated_at' => Carbon::now()->subDays(2),
        ]);

        $run2 = CalculationRun::create([
            'inward_case_id' => $case2->id,
            'computed_by' => $da->id,
            'opening_balance_amount' => 450000.00,
            'opening_fin_year' => '2023-2024',
            'total_subscriptions' => 60000.00,
            'total_refunds' => 0.00,
            'total_withdrawals' => 0.00,
            'total_interest_computed' => 38740.00,
            'dlis_amount' => 10000.00,
            'final_closing_balance' => 558740.00,
        ]);

        CaseNominee::create([
            'inward_case_id' => $case2->id,
            'nominee_name' => 'Smt Gita Sen',
            'relationship' => 'Spouse',
            'share_percentage' => 60.00,
            'allocated_amount' => 335244.00,
            'is_minor' => false,
            'bank_account_no' => '30291827364',
            'bank_ifsc' => 'SBIN0001234',
            'bank_name' => 'State Bank of India, Udaipur',
        ]);

        CaseNominee::create([
            'inward_case_id' => $case2->id,
            'nominee_name' => 'Master Rahul Sen',
            'relationship' => 'Son',
            'share_percentage' => 40.00,
            'allocated_amount' => 223496.00,
            'is_minor' => true,
            'guardian_name' => 'Smt Gita Sen',
            'bank_account_no' => '30291827364',
            'bank_ifsc' => 'SBIN0001234',
            'bank_name' => 'State Bank of India, Udaipur',
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $case2->id,
            'performed_by' => $deo->id,
            'from_status' => null,
            'to_status' => CaseWorkflowStatus::DRAFT,
            'action_type' => 'INWARD_REGISTER',
            'remarks' => 'Inward claim registered for deceased subscriber.',
            'created_at' => Carbon::now()->subDays(12),
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $case2->id,
            'performed_by' => $da->id,
            'from_status' => CaseWorkflowStatus::DRAFT,
            'to_status' => CaseWorkflowStatus::CALCULATED,
            'action_type' => 'CALCULATION_SUBMIT',
            'remarks' => 'Final calculation generated with ₹10,000 DLIS benefit. Forwarded to AAO.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // -------------------------------------------------------------
        // Case 3: Checked by AAO / Ready for Sr. AO Sanction
        // -------------------------------------------------------------
        $case3 = InwardCase::create([
            'registration_no' => '20240210003',
            'diary_number' => 'INW/2024/0982',
            'diary_date' => Carbon::now()->subDays(20),
            'series_code' => '02',
            'series_name' => 'MED - Health & Family Welfare',
            'account_no' => '10003',
            'subscriber_name_cache' => 'Dr. Bikramjit Das',
            'name_title' => 'Dr',
            'designation_title' => 'Dr',
            'designation' => 'Senior Medical Officer',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'section' => 'Fund Section I',
            'ddo_code' => '1003',
            'treasury_code' => '01',
            'event_date' => Carbon::now()->subMonths(2)->endOfMonth(),
            'last_fund_deduction' => Carbon::now()->subMonths(3),
            'debit_during_year' => 0.00,
            'personal_address' => 'Hospital Road, Dhaleswar, Agartala',
            'mobile_no' => '9436456789',
            'employee_code' => 'EMP-MED-1092',
            'beneficiary_code' => 'BEN-99341',
            'current_status' => CaseWorkflowStatus::CHECKED,
            'created_by' => $deo->id,
            'assigned_user_id' => $srao->id,
            'calculated_at' => Carbon::now()->subDays(10),
            'checked_at' => Carbon::now()->subDays(1),
        ]);

        $run3 = CalculationRun::create([
            'inward_case_id' => $case3->id,
            'computed_by' => $da->id,
            'opening_balance_amount' => 780000.00,
            'opening_fin_year' => '2023-2024',
            'total_subscriptions' => 120000.00,
            'total_refunds' => 0.00,
            'total_withdrawals' => 0.00,
            'total_interest_computed' => 69450.00,
            'dlis_amount' => 0.00,
            'final_closing_balance' => 969450.00,
        ]);

        CaseNominee::create([
            'inward_case_id' => $case3->id,
            'nominee_name' => 'Dr. Bikramjit Das',
            'relationship' => 'Self',
            'share_percentage' => 100.00,
            'allocated_amount' => 969450.00,
            'is_minor' => false,
            'bank_account_no' => '10928374650',
            'bank_ifsc' => 'SBIN0000012',
            'bank_name' => 'State Bank of India, Agartala Branch',
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $case3->id,
            'performed_by' => $deo->id,
            'from_status' => null,
            'to_status' => CaseWorkflowStatus::DRAFT,
            'action_type' => 'INWARD_REGISTER',
            'remarks' => 'Inward registered.',
            'created_at' => Carbon::now()->subDays(20),
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $case3->id,
            'performed_by' => $da->id,
            'from_status' => CaseWorkflowStatus::DRAFT,
            'to_status' => CaseWorkflowStatus::CALCULATED,
            'action_type' => 'CALCULATION_SUBMIT',
            'remarks' => 'Calculations and ledger slips audited.',
            'created_at' => Carbon::now()->subDays(10),
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $case3->id,
            'performed_by' => $aao->id,
            'from_status' => CaseWorkflowStatus::CALCULATED,
            'to_status' => CaseWorkflowStatus::CHECKED,
            'action_type' => 'AAO_VERIFY',
            'remarks' => 'Ledger figures, interest rates, and nominal share audited & approved for sanction.',
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // -------------------------------------------------------------
        // Case 4: Digitally Authorized & Signed (Ready for Dispatch)
        // -------------------------------------------------------------
        $case4 = InwardCase::create([
            'registration_no' => '20240110004',
            'diary_number' => 'INW/2024/0871',
            'diary_date' => Carbon::now()->subDays(35),
            'series_code' => '01',
            'series_name' => 'EDN - Education Department',
            'account_no' => '10004',
            'subscriber_name_cache' => 'Shri Anjan Deb',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Superintendent',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'section' => 'Fund Section I',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => Carbon::now()->subMonths(3)->endOfMonth(),
            'last_fund_deduction' => Carbon::now()->subMonths(4),
            'debit_during_year' => 0.00,
            'personal_address' => 'Ramnagar Road No. 4, Agartala, West Tripura',
            'mobile_no' => '9436789012',
            'employee_code' => 'EMP-EDN-4421',
            'beneficiary_code' => 'BEN-33921',
            'current_status' => CaseWorkflowStatus::AUTHORIZED,
            'created_by' => $deo->id,
            'assigned_user_id' => $srao->id,
            'calculated_at' => Carbon::now()->subDays(25),
            'checked_at' => Carbon::now()->subDays(20),
            'approved_at' => Carbon::now()->subDays(15),
            'authorized_at' => Carbon::now()->subDays(14),
        ]);

        $run4 = CalculationRun::create([
            'inward_case_id' => $case4->id,
            'computed_by' => $da->id,
            'opening_balance_amount' => 520000.00,
            'opening_fin_year' => '2023-2024',
            'total_subscriptions' => 45000.00,
            'total_refunds' => 0.00,
            'total_withdrawals' => 0.00,
            'total_interest_computed' => 42150.00,
            'dlis_amount' => 0.00,
            'final_closing_balance' => 607150.00,
        ]);

        CaseNominee::create([
            'inward_case_id' => $case4->id,
            'nominee_name' => 'Shri Anjan Deb',
            'relationship' => 'Self',
            'share_percentage' => 100.00,
            'allocated_amount' => 607150.00,
            'is_minor' => false,
            'bank_account_no' => '20394857610',
            'bank_ifsc' => 'SBIN0000012',
            'bank_name' => 'State Bank of India, Agartala Main',
        ]);

        $sig4 = DigitalSignature::create([
            'signatory_user_id' => $srao->id,
            'signatory_name' => 'Senior Accounts Officer (Sr. AO)',
            'signatory_role' => 'Senior Accounts Officer',
            'certificate_serial' => 'DSC-CAG-TR-8899201948',
            'certificate_issuer' => 'NIC Certifying Authority (NIC-CA)',
            'signed_hash' => hash('sha256', 'AG/TRIPURA/GPF-FP/2024/10004-AUTHENTICATED'),
            'signed_at' => Carbon::now()->subDays(14),
            'certificate_valid_to' => Carbon::now()->addYears(2),
        ]);

        $auth4 = Authority::create([
            'inward_case_id' => $case4->id,
            'calculation_run_id' => $run4->id,
            'authority_number' => 'AG/TRIPURA/GPF-FP/2024/011004',
            'authority_type' => 'FP',
            'authority_date' => Carbon::now()->subDays(15),
            'gross_amount' => 607150.00,
            'deductions_amount' => 0.00,
            'net_amount' => 607150.00,
            'dlis_amount' => 0.00,
            'digital_signature_id' => $sig4->id,
            'is_signed' => true,
            'signed_at' => Carbon::now()->subDays(14),
            'verification_hash' => hash('sha256', 'AG/TRIPURA/GPF-FP/2024/011004'),
        ]);

        $sig4->update(['authority_id' => $auth4->id]);

        // -------------------------------------------------------------
        // Case 5: Fully Settled & Dispatched (Speed Post Barcode)
        // -------------------------------------------------------------
        $case5 = InwardCase::create([
            'registration_no' => '20240310005',
            'diary_number' => 'INW/2024/0710',
            'diary_date' => Carbon::now()->subDays(55),
            'series_code' => '03',
            'series_name' => 'POL - Tripura Police',
            'account_no' => '10005',
            'subscriber_name_cache' => 'Shri Samarjit Tripura',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Deputy Superintendent of Police',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'section' => 'Fund Section III',
            'ddo_code' => '1004',
            'treasury_code' => '03',
            'event_date' => Carbon::now()->subMonths(4)->endOfMonth(),
            'last_fund_deduction' => Carbon::now()->subMonths(5),
            'debit_during_year' => 0.00,
            'personal_address' => 'Police Officers Quarter, A.D. Nagar, Agartala',
            'mobile_no' => '9436998877',
            'employee_code' => 'EMP-POL-9011',
            'beneficiary_code' => 'BEN-44119',
            'current_status' => CaseWorkflowStatus::DISPATCHED,
            'created_by' => $deo->id,
            'assigned_user_id' => $srao->id,
            'calculated_at' => Carbon::now()->subDays(45),
            'checked_at' => Carbon::now()->subDays(40),
            'approved_at' => Carbon::now()->subDays(35),
            'authorized_at' => Carbon::now()->subDays(34),
            'hrms_uploaded_at' => Carbon::now()->subDays(30),
            'dispatched_at' => Carbon::now()->subDays(28),
        ]);

        $run5 = CalculationRun::create([
            'inward_case_id' => $case5->id,
            'computed_by' => $da->id,
            'opening_balance_amount' => 1120000.00,
            'opening_fin_year' => '2023-2024',
            'total_subscriptions' => 180000.00,
            'total_refunds' => 0.00,
            'total_withdrawals' => 0.00,
            'total_interest_computed' => 102450.00,
            'dlis_amount' => 0.00,
            'final_closing_balance' => 1402450.00,
        ]);

        CaseNominee::create([
            'inward_case_id' => $case5->id,
            'nominee_name' => 'Shri Samarjit Tripura',
            'relationship' => 'Self',
            'share_percentage' => 100.00,
            'allocated_amount' => 1402450.00,
            'is_minor' => false,
            'bank_account_no' => '99482019482',
            'bank_ifsc' => 'SBIN0000012',
            'bank_name' => 'State Bank of India, Agartala Main',
        ]);

        $sig5 = DigitalSignature::create([
            'signatory_user_id' => $srao->id,
            'signatory_name' => 'Senior Accounts Officer (Sr. AO)',
            'signatory_role' => 'Senior Accounts Officer',
            'certificate_serial' => 'DSC-CAG-TR-8899201948',
            'certificate_issuer' => 'NIC Certifying Authority (NIC-CA)',
            'signed_hash' => hash('sha256', 'AG/TRIPURA/GPF-FP/2024/10005-AUTHENTICATED'),
            'signed_at' => Carbon::now()->subDays(34),
            'certificate_valid_to' => Carbon::now()->addYears(2),
        ]);

        $auth5 = Authority::create([
            'inward_case_id' => $case5->id,
            'calculation_run_id' => $run5->id,
            'authority_number' => 'AG/TRIPURA/GPF-FP/2024/031005',
            'authority_type' => 'FP',
            'authority_date' => Carbon::now()->subDays(35),
            'gross_amount' => 1402450.00,
            'deductions_amount' => 0.00,
            'net_amount' => 1402450.00,
            'dlis_amount' => 0.00,
            'digital_signature_id' => $sig5->id,
            'is_signed' => true,
            'signed_at' => Carbon::now()->subDays(34),
            'verification_hash' => hash('sha256', 'AG/TRIPURA/GPF-FP/2024/031005'),
            'is_uploaded_hrms' => true,
            'hrms_uploaded_at' => Carbon::now()->subDays(30),
            'is_dispatched' => true,
            'dispatched_at' => Carbon::now()->subDays(28),
            'dispatch_barcode' => 'TR987654321IN',
        ]);

        $sig5->update(['authority_id' => $auth5->id]);
    }
}
