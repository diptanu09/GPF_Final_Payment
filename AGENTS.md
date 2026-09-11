# GPF Final Payment Portal - Project Architecture & Institutional Memory

This document contains the complete architectural specification, database mappings, workflow rules, calculation math, legacy formats, and integration guidelines for the **GPF Final Payment Portal**. This file serves as permanent agent memory and is automatically referenced whenever this project is opened.

---

## 1. System Overview & Technology Stack

- **Backend Framework**: Laravel 12 (PHP 8.2+)
- **Frontend Framework**: Inertia.js with React 19, Tailwind CSS v4, Lucide Icons, Vite
- **Primary Database (Application Storage & Replica)**:
  - **Engine**: PostgreSQL 18
  - **Host**: `10.47.240.169:5432`
  - **Database**: `gpf_final_payment`
  - **User**: `postgres` | **Password**: `root@123`
  - **Contents**: Stores users, inward cases, calculation runs, monthly breakdowns, nominees, authorities, digital signature audits, workflow history, and PostgreSQL replica tables (`gp_accounts`, `gp_applications`, `gp_yearly_balances`, `state_ddo`, `state_treasury`, `mm_gpf_series`, `gp_missing_credit`).
- **Legacy Source Database (Master Data & Historical Records)**:
  - **Engine**: Oracle 11g Enterprise
  - **Host**: `192.168.100.247:1521` (SID: `db11g`)
  - **User / Password**: `gpffp` / `gpffp`
  - **Schemas Used**: `gpffp` and `VLCS`

---

## 2. Institutional Database Security & Exclusion Policy

### Strict Excluded Tables (DO NOT USE OR QUERY):
- **All `FP_*` prefixed tables** (`FP_AUTHORITY_TRANS`, `FP_BENE_NOMINEE`, `FP_CALCULATION`, etc.)
- **Upload & Signed Report Tables**:
  - `CORR_AUTHORITY_REPORTS_UPLOAD`
  - `CORR_SIGNED_REPORTS_UPLOAD`
  - `DLIS_AUTHORITY_REPORTS_UPLOAD`
  - `DLIS_REV_REPORTS_UPLOAD`
  - `DLIS_SIGNED_AUTH_REPORT`
  - `DLIS_SIGNED_REV_REPORT`
  - `ACNTS_SAVE`

### Allowed & Required Tables:
- **`VLCS` Master Tables**:
  - `VLCS.GP_ACCOUNTS`: Account master (Series, Account No, Balances, Closure Tag, Date of Closure, Application No, Mobile, HRMS Code).
  - `VLCS.GP_APPLICATIONS` (105,030+ records): Primary demographic master containing exact `DESIGNATION`, `APPLICANT_ADDRESS`, `DDO_CODE`, `SPOUCE_NAME`, `GUARDIAN_NAME`, `DATE_OF_BIRTH`, `DATE_OF_JOINING`.
  - `VLCS.STATE_DDO`: DDO master (`DDO_CODE`, `DDO_DESG`, `DDO_TREASURY_CODE`, `VLC_DDO`, `PHONE_NO`, `DDO_EMAIL_ID`).
  - `VLCS.STATE_TREASURY`: Treasury master (`TRES_CODE`, `TRES_NAME`, `EMAIL_ID`).
  - `VLCS.MM_GPF_SERIES`: Series master (`SERIES_ID`, `SERIES_DESCR`).
  - `VLCS.GP_MISSING_CREDIT`: Uncredited subscriptions (`SLIP_DATE`, `FIN_YEAR_CODE`, `CLEAR_TAG`).
  - `VLCS.MM_EMPLOYEE`: Employee master fallback (`EMP_CODE`, `EMP_NAME`, `EMP_DESIGNATION`, `EMP_MAIL_ADDRESS`).
- **`gpffp` Historical Tables**:
  - `gpffp.USER_ACCOUNTS`: Institutional user accounts (`USER_ID`, `USER_NAME`, `USER_DESG`, `EMAIL`, `USER_STATUS`).
  - `gpffp.GPF_APPLICATION`: Historical final payment applications.
  - `gpffp.LTA_APPLICATION`: Historical LTA cases.
  - `gpffp.GPF_SUBSCRIPTION`: Historical monthly ledger entries.
  - `gpffp.GPF_AMOUNT_INFO` & `gpffp.GPF_ACCOUNT_CALCULATION`: Historical calculation data.
  - `gpffp.GPF_CASES_LOG` & `gpffp.GPF_OUTWARD`: Historical audit & dispatch logs.

---

## 3. Real Institutional Users & Role-Based Access Control

The system exclusively uses institutional user accounts imported from `gpffp.USER_ACCOUNTS` (no dummy/mock users). Passwords are securely hashed with bcrypt (`secret123` / individual secure credentials):

| Username | Name / Designation | System Role | Capabilities |
| :--- | :--- | :--- | :--- |
| `dir` | Director | `admin` | Full system governance, reassignments, audits, reports |
| `jdg` | Joint Director General | `admin` | High-level approvals, overrides, reports |
| `rkdb` | R. K. Debbarma (Sr. AO) | `approver` | Final settlement approval, digital signing, HRMS dispatch |
| `anjana` | Anjana (AAO) | `checker` | Audit verification, ledger checking, returning cases |
| `deeksha` | Deeksha (DEO / Dealing Asst) | `deo` | Inward registration, calculation runs, nominee entry |
| `kalipada` | Kalipada (DEO / Dealing Asst) | `deo` | Inward registration, calculation runs, nominee entry |

---

## 4. Subscriber Inward Registration Pipeline

When registering a new docket (`/inward/create`), `OracleMasterBridge::lookupSubscriber` executes the following prioritized pipeline:

```
1. Query VLCS.GP_ACCOUNTS (where SERIES_ID = :series AND ACCOUNT_NO = :acct)
   ├── Extract: APPLICATION_NO, Balances, Closure Tag, Date of Closure, Mobile, HRMS Code
2. Query VLCS.GP_APPLICATIONS (indexed by APPLICATION_NO or SERIES_ID + ACCOUNT_NO)
   ├── Extract: DESIGNATION, APPLICANT_ADDRESS, DDO_CODE, SPOUCE_NAME, DOB, DOJ
3. Fallbacks: gpffp.GPF_APPLICATION → gpffp.LTA_APPLICATION → VLCS.MM_EMPLOYEE
4. Resolve DDO & Treasury via VLCS.STATE_DDO:
   ├── Match DDO_CODE = :d OR VLC_DDO = :d
   └── Extract canonical DDO_CODE and DDO_TREASURY_CODE (auto-fills Treasury dropdown)
5. Dual-Database Fallback:
   └── If Oracle 11g host is offline, immediately query PostgreSQL 18 replica tables.
```

---

## 5. Calculation Engine & Business Rules

1. **Base Financial Year & Opening Balance**:
   - Fetched dynamically from `VLCS.GP_ACCOUNTS` / `gp_yearly_balances` based on the latest closed FY.
   - Supports multi-year spans (e.g., Base FY 2023-2024 to Current FY 2026-2027) with progressive compounding at annual FY interest rates (default statutory: **7.10% per annum**).
2. **Cut Month & Interest Suppression Rule**:
   - Subscriptions and withdrawals occurring **after** the interest cut month (event month) are excluded from balance and earn **zero** interest.
   - For months after the cut month within the event FY, the interest computed is strictly `0.00`.
3. **Delay Interest Calculation**:
   - When payment is processed after the event FY (or after interest cut month), delayed interest is computed on the final closing balance for each delayed month:
     $$\text{Delayed Interest} = \frac{\text{Closing Balance} \times \text{Rate} \times \text{Delayed Months}}{1200}$$
   - Both `actual_interest_computed` and `delayed_interest_computed` are recorded separately and summed in `total_interest_computed`.
4. **Deposit-Linked Insurance Scheme (DLIS)**:
   - Admissible on `CaseType::DEATH_IN_SERVICE` (`pension_type_id = '2'` or `'7'`).
   - Maximum statutory coverage: **₹60,000**.
   - Cites Govt. of Tripura Finance Dept. O.M. No. `F.12(7)/FIN(G)/75` dated 18-02-76 and Debit Head `2235-60-104`.
5. **Nominee / Beneficiary Matrix**:
   - Multiple nominees supported with exact `beneficiary_code`, `relationship`, `share_percentage`, and `allocated_amount`.
   - Odd paisa remainder is automatically reconciled to ensure the sum equals the exact net payable balance.
6. **Missing Credits**:
   - Retrieved from `VLCS.GP_MISSING_CREDIT` for uncredited deduction adjustments.

---

## 6. Statutory AG Tripura Authority PDF Formats

### A. Final Payment Authority Letter (`pdf/authority_letter.blade.php`)
- **Bilingual Official Header**:
  - `महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला`
  - `OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA`
  - CAG Logo (`public/images/cag_logo.png`) & Ashok Stambh (`public/images/ashok_stambh.png`) with offline base64 fallback.
- **Memo Numbering**: `No. {SECTION} / FP / [{Revised /}] {PENSION_TYPE} / {OPENING_FIN_YEAR} / {REGISTRATION_NO} /` (e.g. `No. Fund Section I / FP / FAM / 2023-2024 / 202616135951 /`).
- **Statutory Legal Rules**: Rule 31/32/33 of Central GPF Rules 1960 / Rule 28 of AIS GPF Rules 1955; Debit Head `8009-01-101` (State) and `8009-01-104` (AIS).
- **5-Column Working Breakdown Table**:
  1. `O.B. as at the beginning of the year`
  2. `Subscription / Refund during the year`
  3. `Withdrawal / Advance`
  4. `Interest (Actual + Delayed)`
  5. `Closing balance`
- **Currency in Words**: Formatted using `IndianCurrencyFormatter::toWords()` (*Crore, Lakh, Thousand, Hundred, Rupees, Paise*).
- **Copy Forwarded (Endorsements)**: Formatted to (1) Treasury Officer, (2) DDO, (3) Subscriber/Nominee with address, mobile, and dynamic verification QR code.
- **DSC Verification Stamp**: PKCS#7 hardware USB token signature seal and SHA-256 verification hash.

### B. DLIS Sanction Order (`pdf/dlis_letter.blade.php`)
- Dedicated Sanction Order for Death-in-service cases citing *Finance Dept. O.M. No. F.12(7)/FIN(G)/75* and Debit Head *2235-60-104*.
- Accessible via print route `/authority/{id}/print-dlis`.

### C. LTA Authority Order (`pdf/lta_authority_letter.blade.php`)
- Dedicated Lifetime Arrears Authority Order citing `lta_to_whom` claimant.

---

## 7. GPF Final Payment Workflow States

```
[1] DRAFT 
     │ (DEO registers docket & inputs subscriber profile)
     ▼
[2] CALCULATED 
     │ (Interest calculated & multi-year monthly ledger verified)
     ▼
[3] CHECKED 
     │ (AAO audits ledger, verifies interest & checks missing credits)
     ▼
[4] APPROVED 
     │ (Sr. AO sanctions final settlement amount)
     ▼
[5] AUTHORIZED 
     │ (Authority letter generated & digitally signed with DSC)
     ▼
[6] HRMS_SYNCED 
     │ (Authority synced to Tripura HRMS API)
     ▼
[7] DISPATCHED 
     │ (Physical dispatch recorded with Speed Post barcode)
     ▼
[8] CLOSED
```

---

## 8. Directorate Security Governance & User Access Control

### A. Two-Tier Officer Registration & Approval Pipeline
1. **Public Registration (`/register`)**:
   - Any staff member can register their institutional account by supplying their full name, official username, email address, requested role, designation, section, and mobile number.
   - **Default Flow (No Admin Token)**: The account is registered with `approval_status = 'pending'` and `is_active = false`. Login attempts are blocked until an Administrator reviews and approves the account.
   - **Fast-Track Flow (With Admin Security Token)**: If the user provides a valid 1-time `admin_token` (e.g. `ADM-REG-XXXXXX`), the system automatically sets `approval_status = 'approved'`, `is_active = true`, sets the assigned role, and immediately grants access.

### B. Admin User Governance Console (`/admin/users`)
- **Restricted to Directorate Admins** (`dir`, `jdg`).
- **Live Pending Badge**: Displays glowing alert notification for unapproved staff in the navigation bar.
- **Queue Actions**: 1-click **Approve & Activate**, **Reject** with audit notes, or modify requested role and branch before approval.
- **Account Governance**: Search and filter all registered officers, edit designations/sections/roles/status, and execute direct administrative password resets.

### C. Admin Security Token Generator (`/admin/tokens/generate`)
- Generates cryptographically secure, 1-time authorization tokens (`ADM-REG-...`, `ADM-RST-...`, `ADM-SEC-...`).
- Supports expiry durations (1 to 30 days), designated role locking, and optional email locking.
- Tokens can be copied with 1-click or revoked immediately.

### D. Officer Profile & Security (`/profile`)
- Officers can view their official profile badge, role capabilities, database state, and update their email, designation, section, and phone number.
- Secure password change module (`/profile/password`) requiring current password verification.
- Real-time audit performance metrics (dockets created, audits checked, settlements approved, DSC signatures).

---

## 9. Key File Sitemap

- `app/Services/Format/IndianCurrencyFormatter.php` — Converts numeric figures to Indian English words and formatted INR strings.
- `app/Services/Integration/OracleMasterBridge.php` — Oracle 11g OCI8 & PostgreSQL 18 dual-database bridge.
- `app/Services/Calculation/GpfCalculationEngine.php` — Multi-year progressive compounding, cut month suppression, delay interest math, and DLIS logic.
- `app/Services/Calculation/CutoffRuleResolver.php` — Interest cutoff date resolver.
- `app/Services/Workflow/GpfWorkflowService.php` — Workflow state transitions and audit logging.
- `app/Services/DigitalSignature/PkiSignatureVerifier.php` — PKI SHA-256 digital signature verification.
- `app/Http/Controllers/AuthController.php` — Login, registration with Admin Security Tokens, password reset, and username recovery.
- `app/Http/Controllers/AdminUserController.php` — Directorate user governance, approval queue, role editing, password resets, and token issuance.
- `app/Http/Controllers/ProfileController.php` — Officer profile management, password updates, and audit metric counters.
- `app/Http/Controllers/InwardCaseController.php` — Docket registration & subscriber lookup API (`/inward/lookup`).
- `app/Http/Controllers/CalculationController.php` — Calculation runs, Base FY lookup, and live breakdown sheets.
- `app/Http/Controllers/NomineeController.php` — Nominee distribution & beneficiary codes.
- `app/Http/Controllers/ApprovalController.php` — AAO verification and Sr. AO approval.
- `app/Http/Controllers/AuthorityController.php` — Authority generation, PDF preview, DLIS print, and DSC digital signing.
- `app/Http/Controllers/DispatchController.php` — HRMS dispatch and postal outward tracking.
- `resources/views/pdf/authority_letter.blade.php` — Official statutory AG Tripura Authority Letter template.
- `resources/views/pdf/dlis_letter.blade.php` — Official DLIS Sanction Order template.
- `resources/views/pdf/lta_authority_letter.blade.php` — Official LTA Authority Order template.
- `resources/js/Pages/Admin/Users/Index.jsx` — Admin User Governance & Security Token Console.
- `resources/js/Pages/Profile/Show.jsx` — Officer Profile, Security & Activity Dashboard.
- `resources/js/Pages/` — Inertia React UI components (`Authority/Show.jsx`, `Calculation/CalculationSheet.jsx`, `Inward/Create.jsx`, etc.).

---

## 10. Common Commands

```powershell
# Run backend dev server:
php artisan serve --host=127.0.0.1 --port=8000

# Build frontend assets:
npm.cmd run build

# Run automated test suite:
php vendor/phpunit/phpunit/phpunit --testdox
```
