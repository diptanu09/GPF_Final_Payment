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
  - **Schema**: `gpffp` (Search Path: `gpffp`)
  - **User**: `postgres` | **Password**: `root@123`
  - **Contents**: Stores users, inward cases, calculation runs, monthly breakdowns, nominees, authorities, digital signature audits, workflow history, and PostgreSQL replica tables (`vlcs_gp_accounts`, `vlcs_gp_missing_credit`, `vlcs_mm_employee`, `vlcs_mm_financial_year`, `vlcs_mm_gpf_series`, `vlcs_state_ddo`, `vlcs_state_treasury`).
- **Legacy Source Database (Master Data)**:
  - **Engine**: Oracle 11g Enterprise
  - **Host**: `192.168.100.247:1521` (SID: `db11g`)
  - **User / Password**: `gpffp` / `gpffp`
  - **Schemas Used**: `VLCS` (Master Demographic Tables only)

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
- **Deprecated Legacy GPFFP Historical Tables (Strictly Excluded)**:
  - `gpffp.GPF_APPLICATION`
  - `gpffp.LTA_APPLICATION`
  - `gpffp.GPF_SUBSCRIPTION`

### Allowed & Required Master Tables (Pure VLC Master):
All demographic profiles, yearly balances, and monthly vouchers must strictly originate from **`VLCS`** master tables (and their PostgreSQL replica counterparts in `gpffp.vlcs_*`):
- **`VLCS.GP_ACCOUNTS`**: Account master (Series, Account No, Balances, Closure Tag, Date of Closure, Application No, Mobile, HRMS Code).
- **`VLCS.GP_APPLICATIONS`** (105,030+ records): Primary demographic master containing exact `DESIGNATION`, `APPLICANT_ADDRESS`, `DDO_CODE`, `SPOUCE_NAME`, `GUARDIAN_NAME`, `DATE_OF_BIRTH`, `DATE_OF_JOINING`.
- **`VLCS.GP_YEARLY_BALANCES`**: Audited historical yearly closing balances for base financial years (`SERIES_ID`, `ACCOUNT_NO`, `FIN_YEAR_CODE`, `OP_BALANCE_WITHDRAWL`, `CL_BAL_WITHDRAWL`, `INTR_WITHDRAWL`).
- **`VLCS.GP_VOUCHER_ACC_DETAILS`** (24,000,000+ records): Individual monthly transaction vouchers (`SERIES_ID`, `ACCOUNT_NO`, `PAY_SLIP_DATE`, `SUBSCRIPTION_AMT`, `REFUND_AMT`, `OTHERS_AMT`, `WITHDRAWAL_AMT`, `VOUCHER_NO`, `ABSTRACT_NO`, `TAG`, `POSTING_TYPE`).
- **`VLCS.STATE_DDO`**: DDO master (`DDO_CODE`, `DDO_DESG`, `DDO_TREASURY_CODE`, `VLC_DDO`, `PHONE_NO`, `DDO_EMAIL_ID`).
- **`VLCS.STATE_TREASURY`**: Treasury master (`TRES_CODE`, `TRES_NAME`, `EMAIL_ID`).
- **`VLCS.MM_GPF_SERIES`**: Series master (`SERIES_ID`, `SERIES_DESCR`).
- **`VLCS.GP_MISSING_CREDIT`**: Uncredited subscriptions (`SLIP_DATE`, `FIN_YEAR_CODE`, `CLEAR_TAG`).
- **`VLCS.MM_EMPLOYEE`**: Employee master fallback (`EMP_CODE`, `EMP_NAME`, `EMP_DESIGNATION`, `EMP_MAIL_ADDRESS`).
- **`gpffp.USER_ACCOUNTS`**: Institutional user accounts for authentication and officer governance only.

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

### Strict Institutional Authentication & Layout Guidelines:
- **No Mock / Quick-Login or Dummy Data Policy**:
  - The Login and Register UI must NEVER include quick-login autofill chips, demo buttons, or pre-filled mock credentials.
  - Only registered officers with active accounts in the database can authenticate by providing their official User ID and password.
  - Form placeholders must remain generic and institutional (e.g. `"Enter registered officer username"`), never inserting dummy names or test values.
- **Centered Form Alignment Standard**:
  - All authentication and registration screens must be precisely centered in the viewport horizontally and vertically (`min-h-screen flex flex-col justify-center items-center`, with `mx-auto` on form containers).

---

## 4. Subscriber Inward Registration Pipeline

When registering a new docket (`/inward/create`), `OracleMasterBridge::lookupSubscriber` executes the following prioritized pipeline:

```
1. Query VLCS.GP_ACCOUNTS (where SERIES_ID = :series AND ACCOUNT_NO = :acct)
   ├── Extract: APPLICATION_NO, Balances, Closure Tag, Date of Closure, Mobile, HRMS Code
2. Query VLCS.GP_APPLICATIONS (indexed by APPLICATION_NO or SERIES_ID + ACCOUNT_NO)
   ├── Extract: DESIGNATION, APPLICANT_ADDRESS, DDO_CODE, SPOUCE_NAME, DOB, DOJ
3. Fallbacks: VLCS.MM_EMPLOYEE
4. Resolve DDO & Treasury via VLCS.STATE_DDO:
   ├── Match DDO_CODE = :d OR VLC_DDO = :d
   └── Extract canonical DDO_CODE and DDO_TREASURY_CODE (auto-fills Treasury dropdown)
5. Dual-Database Fallback:
   └── If Oracle 11g host is offline, immediately query PostgreSQL 18 replica tables (gpffp.vlcs_*).
```

---

## 5. Calculation Engine & Voucher Aggregation Rules

1. **Monthly Voucher Aggregation (`VLCS.GP_VOUCHER_ACC_DETAILS`)**:
   - Query filters strictly on `(SERIES_ID = :series_num OR TO_CHAR(SERIES_ID) = :series_str)` AND `(ACCOUNT_NO = :acct_num OR TO_CHAR(ACCOUNT_NO) = :acct_str)`.
   - Filters out deleted/unposted vouchers: `(TAG IS NULL OR TAG != 'D')` and `(POSTING_TYPE IS NULL OR POSTING_TYPE != 'F')`.
   - Groups by calendar month `TO_CHAR(PAY_SLIP_DATE, 'YYYY-MM')`:
     $$\text{Total Deposit} = \sum (\text{SUBSCRIPTION\_AMT} + \text{REFUND\_AMT} + \text{OTHERS\_AMT})$$
     $$\text{Total Withdrawal} = \sum \text{WITHDRAWAL\_AMT}$$
   - Multiple voucher numbers are concatenated with `LISTAGG(VOUCHER_NO, ', ')`.
   - Dual-database fallback queries `gpffp.vlcs_gp_voucher_acc_details` via `STRING_AGG` in PostgreSQL 18.
2. **Base Financial Year & Opening Balance**:
   - Fetched dynamically from `VLCS.GP_YEARLY_BALANCES` based on the latest closed FY or user-selected base FY.
   - Supports multi-year spans (e.g., Base FY 2023-2024 to Current FY 2026-2027) with progressive compounding at annual FY interest rates (default statutory: **7.10% per annum**).
   - "Reload VLC Data" button on `/calculation/{caseId}` forces fresh re-aggregation with `refresh=1`.
3. **Cut Month & Interest Suppression Rule**:
   - Subscriptions and withdrawals occurring **after** the interest cut month (event month) are excluded from progressive balance and earn **zero** interest.
   - For months after the cut month within the event FY, the actual interest computed is strictly `0.00`.
4. **Delay Interest Calculation & Statutory 6-Month Cap (Central GPF Rule 11(4))**:
   - When payment is processed after the event FY (or after interest cut month), delayed interest is computed on the final closing balance for each delayed month:
     $$\text{Delayed Interest} = \frac{\text{Closing Balance} \times \text{Rate} \times \text{Delayed Months}}{1200}$$
   - **Statutory 6-Month Delay Cap**: Under Central GPF Rule 11(4), interest on delayed final payment is admissible for a maximum period of **6 months**.
   - **Sr. AO Delay Justification Requirement for Months 7+**: If payment is delayed for 7 months or beyond (7+), interest for months 7+ is strictly **₹0.00 (suppressed/capped)** unless an official **Delay Justification / Remarks** is recorded and approved by the Sr. Accounts Officer (`approver`) or Directorate (`admin`).
   - When approved justification is present, interest for months 7+ is unlocked and computed.
   - Delay metadata is persisted across `inward_cases` and `calculation_runs` (`delay_justification`, `delay_approved_by`, `delay_approved_at`, `delay_months_count`, `has_exceeded_delay_cap`).
   - Both `actual_interest_computed` and `delayed_interest_computed` are recorded separately and summed in `total_interest_computed`.
5. **Deposit-Linked Insurance Scheme (DLIS)**:
   - Admissible on `CaseType::DEATH_IN_SERVICE` (`pension_type_id = '2'` or `'7'`).
   - Maximum statutory coverage: **₹60,000**.
   - Cites Govt. of Tripura Finance Dept. O.M. No. `F.12(7)/FIN(G)/75` dated 18-02-76 and Debit Head `2235-60-104`.
6. **Nominee / Beneficiary Matrix**:
   - Multiple nominees supported with exact `beneficiary_code`, `relationship`, `share_percentage`, and `allocated_amount`.
   - Odd paisa remainder is automatically reconciled to ensure the sum equals the exact net payable balance.
7. **Missing Credits**:
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

### D. Input Sheet Official Report (`pdf/input_sheet.blade.php`)
- Standard 3-signature verification ledger (DEO, AAO, Sr. AO) displaying subscriber demographics, base financial year closing balances, monthly deposit/withdrawal ledgers, interest rate slabs, and delayed interest audit notes.
- Accessible via print route `/letters/{caseId}/input-sheet`.

### E. Annexure 5.24 Intimation to Subscriber on Authorization (`pdf/intimation_letter.blade.php`)
- Official intimation letter dispatched to subscriber/claimant upon authority issuance.
- Contains statutory instructions to submit bill through DDO to Treasury, contact details, dynamic verification QR code, and list of uncredited/missing credits requiring separate reconciliation.
- Accessible via print route `/letters/{caseId}/intimation`.

### F. Corrigendum Amendment Order (`pdf/corrigendum_letter.blade.php`)
- Official amendment order citing original Authority No. and date, detailing statutory rectifications with formal endorsements to Treasury Officer, DDO, and claimant.
- Accessible via print route `/letters/{caseId}/corrigendum`.

### G. Revalidation Order (`pdf/revalidation_letter.blade.php`)
- Formal revalidation order issued to the Treasury Officer extending validity of an uncashed GPF final payment authority.
- Accessible via print route `/letters/{caseId}/revalidation`.

### H. Objection / Defect Return Memo (`pdf/objection_letter.blade.php`)
- Official memo returning defective GPF final payment applications to DDOs citing specific statutory checklist defects (non-matching signature, incomplete service book, missing nomination, unverified advances, etc.).
- Accessible via print route `/letters/{caseId}/objection`.

### I. Rule 11(7) Minus Balance Notice & Recovery Record (`pdf/minus_balance_letter.blade.php`)
- Statutory notice issued to the Head of Office / DDO under Rule 11(7) of Central GPF Rules when progressive calculation yields a negative closing balance due to excess past withdrawals.
- Demands immediate recovery through Treasury challan to Major Head `8009` (Principal) and `0049` (Interest).
- Accessible via print route `/letters/{caseId}/minus-balance`.

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

Auxiliary States:
- [9] MINUS_BALANCE (Calculation yields negative net balance; notice issued to DDO under Rule 11(7))
- [10] CANCELLED (Case formally cancelled by Directorate or Sr. AO with audit justification)
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

### E. Global Multi-Criteria Docket Search & Deep Inspector (`/search`)
- **Multi-Parameter Search**: Instant searching by Registration No, Employee Code, Beneficiary Code, GPF Account No (with Series ID dropdown), Subscriber Name, Workflow Status, Pension Type, and Inward Date Range.
- **Deep Docket Inspector Drawer (`/search/{id}/inspect`)**: Complete 6-tab inspection drawer rendering all docket details without leaving the search view:
  1. *Overview*: Status badge, registration info, timestamps, assigned officer.
  2. *Subscriber & Service*: Full demographic details, designation, DDO, Treasury, event dates.
  3. *Financial Ledger*: Base FY, opening balance, monthly deposit/withdrawal records, interest breakdown, net payable.
  4. *Nominee Matrix*: Beneficiary codes, relationship, share percentages, allocated amounts.
  5. *Authority & Dispatch*: Authority number, date, gross/net amounts, DSC status, dispatch barcode.
  6. *Audit Trail & Letters*: Complete immutable workflow history log and 1-click statutory letter generation links.

### F. Case Governance & Unapproval Console (`/admin/cases`)
- **Restricted to Approvers and Directorate Admins** (`rkdb`, `dir`, `jdg`).
- **Audit Actions**:
  - *Unapprove Case*: Reverts `APPROVED` / `LTA_APPROVED` dockets back to `CALCULATED` so Dealing Assistants can correct vouchers or interest calculations.
  - *Cancel Case*: Formally cancels dockets with mandatory justification recorded in `cancelled_remarks` and workflow history.
  - *Reset PKI Digital Signature*: Clears digital signature and resets `AUTHORIZED` case back to `APPROVED` for re-signing in case of endorsement revisions.
  - *Draft Deletion*: Allows purging orphaned `DRAFT` dockets before calculation runs are initiated.

### G. Sectional Receipt Docket Transfer (`/inward/{id}/transfer`)
- Allows reassigning dockets between Dealing Assistants (`deo`) with mandatory transfer remarks logged in `transfer_remarks` and `workflow_histories`.

### H. Comprehensive Management Information System (MIS) Reports (`/reports/*`)
1. **Settled Cases Register** (`/reports/settled`): Summary of authorized and dispatched settlements with financial totals.
2. **Pending Cases Register** (`/reports/pending`): Action-item queue for dockets in `DRAFT`, `CALCULATED`, and `CHECKED` states.
3. **Staff Productivity Register** (`/reports/productivity`): Officer-wise matrix counting inward registrations, calculations, audits, approvals, and digital signatures.
4. **PKI Digital Signature Audit Log** (`/reports/digital-signatures`): Log of hardware USB token DSC events, serial numbers, timestamps, and signatories.
5. **Minus Balance Recovery Dashboard** (`/reports/minus-balance`): Tracks cases with negative balances under Rule 11(7), recording recovery amounts and settlement dates.
6. **Cancelled Cases Register** (`/reports/cancelled`): Archive of all cancelled dockets and reasons.

### I. Native Residual GPF Payment Roadmap
- Residual Payment processing (post-closure interest, late adjustments, and residual claims) will be developed natively inside this project within the GPF Final Payment Portal rather than redirecting to an external application.

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
- `app/Http/Controllers/InwardCaseController.php` — Docket registration, subscriber lookup API (`/inward/lookup`), and docket transfer (`/inward/{id}/transfer`).
- `app/Http/Controllers/CalculationController.php` — Calculation runs, Base FY lookup, and live breakdown sheets.
- `app/Http/Controllers/NomineeController.php` — Nominee distribution & beneficiary codes.
- `app/Http/Controllers/ApprovalController.php` — AAO verification and Sr. AO approval.
- `app/Http/Controllers/AuthorityController.php` — Authority generation, PDF preview, DLIS print, and DSC digital signing.
- `app/Http/Controllers/DispatchController.php` — HRMS dispatch and postal outward tracking.
- `app/Http/Controllers/LettersController.php` — Statutory letters suite (Input Sheet, Intimation, Corrigendum, Revalidation, Objection, Minus Balance).
- `app/Http/Controllers/SearchController.php` — Multi-criteria search and 6-tab deep docket inspection drawer API.
- `app/Http/Controllers/CaseAdminController.php` — Case governance (unapprove, cancel, signature reset, draft delete).
- `app/Http/Controllers/ReportController.php` — MIS reports (Settled, Pending, Staff Productivity, Digital Signatures, Minus Balances, Cancelled).
- `resources/views/pdf/authority_letter.blade.php` — Official statutory AG Tripura Authority Letter template.
- `resources/views/pdf/dlis_letter.blade.php` — Official DLIS Sanction Order template.
- `resources/views/pdf/lta_authority_letter.blade.php` — Official LTA Authority Order template.
- `resources/views/pdf/input_sheet.blade.php` — Official Input Sheet Report template.
- `resources/views/pdf/intimation_letter.blade.php` — Official Annexure 5.24 Intimation Letter template.
- `resources/views/pdf/corrigendum_letter.blade.php` — Official Corrigendum Amendment Order template.
- `resources/views/pdf/revalidation_letter.blade.php` — Official Revalidation Order template.
- `resources/views/pdf/objection_letter.blade.php` — Official Objection / Defect Return Memo template.
- `resources/views/pdf/minus_balance_letter.blade.php` — Official Rule 11(7) Minus Balance Notice template.
- `resources/js/Pages/Admin/Users/Index.jsx` — Admin User Governance & Security Token Console.
- `resources/js/Pages/Admin/CaseAdmin/Index.jsx` — Case Governance & Signature Reset Console.
- `resources/js/Pages/Profile/Show.jsx` — Officer Profile, Security & Activity Dashboard.
- `resources/js/Pages/Letters/Index.jsx` — Statutory Letters & Notices Action Hub.
- `resources/js/Pages/Search/Index.jsx` — Multi-Criteria Search & Deep Docket Inspector.
- `resources/js/Pages/Reports/` — MIS Reports suite (`SettledCases.jsx`, `PendingCases.jsx`, `UserProductivity.jsx`, `DigitalSignatures.jsx`, `MinusBalanceCases.jsx`, `CancelledCases.jsx`).
- `resources/js/Pages/` — Inertia React UI components (`Authority/Show.jsx`, `Calculation/CalculationSheet.jsx`, `Inward/Create.jsx`, etc.).

---

## 12. Modern UI/UX Architecture, Layout Standards & Interaction Design

### A. Compact Ergonomic High-Density Layout Standards
- **Scale & Density**: Designed specifically for high-efficiency government accounting and audit workflows. Avoids oversized buttons and excessive whitespace.
- **Max-Width & Centering**: Main page containers utilize `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5`.
- **Top Navigation Bar (`h-14` / 56px)**:
  - **Dynamic Scroll Transparency**: At the top of the page (`scrollY === 0`), the header is **100% transparent** (`bg-transparent border-transparent`) showing only floating icons, breadcrumbs, search, theme toggles, audio controls, and profile dropdowns.
  - **Scroll Transition**: As soon as the user scrolls (`scrollY > 8` or inner scroll container moves), the topbar smoothly transitions into a frosted glassmorphic bar (`bg-white/85 dark:bg-slate-900/85 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 shadow-xs`).
- **Pinned / Fixed Left Sidebar (`fixed top-14 bottom-0 left-0 z-30`)**:
  - Pinned directly below the topbar and anchored to the bottom.
  - **Does NOT scroll with the page**: Main content scrolls independently while the sidebar remains permanently accessible.
  - Features its own independent scrollbar (`overflow-y-auto thin-scrollbar`).
  - Smooth expansion/collapse width transition (`w-56` when expanded, `w-16` when collapsed).
  - Main content offset matches exactly: `md:ml-56` or `md:ml-16` with `pt-14`.
- **Sidebar Collapse Control**:
  - Uses semantic panel toggle icons: `PanelLeftClose` (when expanded) and `PanelLeftOpen` (when collapsed) from `lucide-react`. Never uses a misleading modal-close `"X"`.

### B. Comprehensive Light & Dark Theme Parity
- **Full Theme Spectrum**: Supports `Light`, `Dark`, and `System` OS preferences managed by `ThemeContext.jsx` with persistent `localStorage` key `'gpf_theme'`.
- **High-Contrast Typography & Surfaces**:
  - Light mode: Deep contrast text (`text-slate-900`, `text-slate-800`, `text-slate-600`), pure white cards (`bg-white`), crisp borders (`border-slate-200/90`), subtle inner shadows (`shadow-xs`).
  - Dark mode: Crisp text (`text-white`, `text-slate-200`, `text-slate-400`), deep midnight panels (`bg-slate-900/80`, `bg-slate-950`), subtle glowing borders (`border-slate-800/80`).
- **Tailwind CSS v4 Dark Variant**: Configured with `@custom-variant dark (&:where(.dark, .dark *));` in `resources/css/app.css` to ensure full reactive styling across all Inertia React components.

### C. 3D WebGL Settlement Nexus & Low-Power Optimization
- **Three.js Visualizations**:
  - `ThreeDashboardGlobe.jsx`: Interactive settlement particle globe and orbital trajectories representing treasury and HRMS dispatches.
  - `ThreeAuthNexus.jsx`: Quantum mesh geometry background for administrative portal authentication.
- **Resource & Battery Efficiency**:
  - Automatically pauses WebGL animation loops when the tab is hidden or when the component scrolls out of the viewport (`IntersectionObserver` + `visibilitychange`).
  - Fully reactive to theme changes (switching between radiant sapphire lights in dark mode and warm gold/navy highlights in light mode).

### D. Audio Feedback System (`AudioFeedbackService.js`)
- **Zero-Dependency Web Audio API**: Synthesizes custom micro-haptic frequencies directly in the browser (no external mp3 files required).
- **Sound Events**:
  - `click` (520Hz subtle soft tap),
  - `navigate` (600Hz-800Hz ascending chirp),
  - `success` (587Hz-880Hz harmonized major chime),
  - `error` (220Hz dual discordant alert tone),
  - `toggle` (440Hz-550Hz state switch).
- **Officer Preference**: Topbar speaker toggle button (`Volume2` / `VolumeX`) allows officers to mute or unmute audio feedback at any time, persisted in `localStorage`.

### E. Omnipresent Command Palette (`Ctrl+K` / `Cmd+K`)
- Accessible anywhere in the application. Provides instant fuzzy search across:
  - All navigation routes (Dashboard, Inward, Calculation, Letters, MIS Reports, Governance),
  - Statutory letter printing shortcuts,
  - Role-specific actions (AAO Audit Queue, Sr. AO Sanction Hub, Digital Signatures).

---

## 13. Common Commands

```powershell
# Run backend dev server:
php artisan serve --host=127.0.0.1 --port=8000

# Build frontend assets:
npm.cmd run build

# Run automated test suite:
php vendor/phpunit/phpunit/phpunit --testdox
```

---

## 14. Docker Architecture, Oracle 19c Client & Container Operations

### A. Container Architecture & Dependencies
- **Base Image**: `php:8.3-fpm-bookworm` (Debian 12 Bookworm).
- **Oracle Instant Client Version**: **Oracle Instant Client 19c (19.24 LTS)**.
  - *Critical Rule*: Do NOT use Oracle 21c Instant Client. Oracle 21c rejects connections to Oracle 11g Enterprise with `ORA-28040: No matching authentication protocol`.
- **Legacy Authentication Configuration (`sqlnet.ora`)**:
  - Located at `/usr/lib/oracle/current/network/admin/sqlnet.ora` (`ENV TNS_ADMIN=/usr/lib/oracle/current/network/admin`).
  - Required parameters:
    ```text
    SQLNET.ALLOWED_LOGON_VERSION_CLIENT=8
    SQLNET.ALLOWED_LOGON_VERSION_SERVER=8
    ```
- **PHP Compilation Dependencies**:
  - Requires `$PHPIZE_DEPS` and `build-essential` in `apt-get` to compile `oci8-3.4.0` via PECL and `pdo_oci` via `docker-php-ext-install`.
  - Both `oci8` and `pdo_oci` are validated during image build:
    `RUN php -m | grep -q oci8 && php -m | grep -q pdo_oci`.
- **Virtual NAT & Socket Resilience**:
  - Set `ORACLE_PROBE_TIMEOUT=3.0` (or higher) to prevent false-negative connection dropouts when container traffic traverses Docker bridge / WSL2 virtual NAT.

### B. Common Docker Operations
```powershell
# Build and launch production container:
docker compose up -d --build

# View container logs:
docker compose logs -f app

# Clear and rebuild cache inside container:
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache

# Check live remote container diagnostics:
Invoke-RestMethod -Uri "http://10.47.240.169/api/v1/system/status" -Headers @{ "X-Deploy-Token" = "GPF_DEPLOY_SECRET_TOKEN_2026" }
```

---

## 15. Front-End Viewport, 3D WebGL Backgrounds & Dark-Theme Hover Standards

### A. Ambient 3D WebGL Backgrounds & Full Viewport Geometry (`ThreeAuthNexus.jsx`)
- **Full-Viewport Canvas Coverage**:
  - Ambient 3D canvas backgrounds (`ThreeAuthNexus`) must NEVER be constrained by fixed-width containers (`max-w-2xl`, etc.) or hardcoded pixel heights (`height={600}`).
  - Always mount the 3D nexus in a full-viewport container (`fixed inset-0 pointer-events-none w-full h-full overflow-hidden`).
  - Constraining rotating 3D geometry inside a smaller bounding box causes visible, flat rectangular clipping along the left, right, top, and bottom edges.
- **Camera Frustum & Aspect Ratio Resiliency**:
  - Use a comfortable camera FOV (e.g., 45°) with `camera.position.z >= 5.2` to give rotating wireframes (e.g. Torus Knot) ample breathing room.
  - Automatically recalculate `camera.aspect = newW / newH` and update projection matrices in window resize listeners.
- **Fail-Safe Scene Graph & Null Guards**:
  - The Three.js scene (`const scene = new THREE.Scene()`) must always be declared and available before geometry/particle groups are initialized.
  - Wrap WebGL initialization and animation loops in defensive `try ... catch` blocks.
  - Cleanup functions must check for object existence (`if (renderer)`, `if (observer)`) so unmounting and Vite hot-reloads never trigger unhandled exceptions.

### B. Authentication Screen Viewport & Scrolling Invariants (`AuthBackground.jsx`, `Login.jsx`)
- **Smooth Vertical Scrollability**:
  - Use `overflow-x-hidden overflow-y-auto font-sans` on the outermost auth wrapper. Never use `overflow-hidden`.
  - On smaller laptop viewports (e.g. 768p or 1080p with Windows 125%/150% display scaling), forms exceeding available height must scroll cleanly without clipping the top emblem or bottom submit/action buttons.
- **Flexbox Centering without Negative Scroll Clipping**:
  - Avoid rigid `justify-center` alone on scroll containers. In CSS flexbox, `justify-center` pushes overflow into negative scroll coordinates where users cannot scroll up to see it.
  - Use responsive `my-auto` centering with adequate vertical padding (`py-8 sm:py-12`) so the card centers vertically when extra space exists, but aligns from top and scrolls naturally when height is constrained.
- **No Global Text Selection Blocking**:
  - Do NOT apply `select-none` on the outer authentication layout. Inputs, labels, error notices, and security text must remain selectable for password managers and clipboard operations.
- **Card Flex Expansion**:
  - Tilt cards (`InteractiveTiltCard.jsx`) and form containers must explicitly include `w-full` to prevent flexbox shrink-to-fit sizing anomalies.

### C. Dark Theme Hover & Shadow Parity Standards for Tinted & Matrix Cards
- **Strict Hover Parity Requirement**:
  - When light-mode hover backgrounds (e.g., `hover:bg-emerald-100/70`, `hover:bg-blue-100/70`) are applied to tinted cards, they **MUST ALWAYS** have an explicit dark-mode counterpart (`dark:hover:bg-emerald-900/40`, `dark:hover:border-emerald-400/60`, `dark:hover:shadow-none`).
  - *Bug Prevention*: Omitting `dark:hover:bg-*` causes Tailwind to fall back to the light-mode pale/white hover background, creating a glaring white wash and broken shadow flash over dark cards when hovered with the mouse.
- **Dark Mode Elevation & Shadow Sanitation**:
  - Avoid murky dark smudges by using `dark:shadow-none` on small tinted matrix cards (such as the Aging Breakdown matrix on `Dashboard.jsx`).
  - Rely on subtle, glowing perimeters (`dark:border-*-500/30` brightening to `dark:hover:border-*-400/60`) for clean dark elevation.
- **High-Contrast Text Hierarchy on Dark Tinted Surfaces**:
  - Labels: `text-[11px] font-semibold text-*-800 dark:text-*-300`
  - Numbers/Counts: `text-xl font-extrabold text-*-700 dark:text-*-100 font-mono tracking-tight`
  - Subtitles: `text-[10px] text-*-700/80 dark:text-*-400 font-medium`




