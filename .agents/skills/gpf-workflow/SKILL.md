---
name: gpf-workflow
description: >-
  Procedures and runbooks for executing, testing, and debugging the GPF Final Payment
  workflow pipeline, dual-database integration (Oracle 11g / PostgreSQL 18), calculation
  engine, Base FY lookup, delay interest math, and statutory AG Tripura PDF authority generation.
---

# GPF Final Payment Workflow, Testing & Debugging Runbook

This skill provides complete testing procedures, workflow verification steps, and operational runbooks for the GPF Final Payment Portal.

---

## 1. Automated Test Suite

Run unit and feature tests across the entire GPF pipeline:

### Run All 74 Automated Tests
```powershell
php vendor/phpunit/phpunit/phpunit --testdox
```

### Targeted Workflow & Domain Tests
```powershell
# Test statutory letters suite (Input Sheet, Intimation, Corrigendum, Revalidation, Objection, Minus Balance)
php vendor/phpunit/phpunit/phpunit tests/Feature/LettersFeatureTest.php --testdox

# Test multi-criteria search and 6-tab deep docket inspector drawer
php vendor/phpunit/phpunit/phpunit tests/Feature/SearchFeatureTest.php --testdox

# Test case governance (unapproval, cancellation, signature reset, sectional receipt transfers)
php vendor/phpunit/phpunit/phpunit tests/Feature/CaseAdminFeatureTest.php --testdox

# Test MIS management reports (Productivity, Digital Signatures, Minus Balances, Cancelled)
php vendor/phpunit/phpunit/phpunit tests/Feature/MisReportsFeatureTest.php --testdox

# Test complete end-to-end workflow state transitions (Draft -> Dispatched)
php vendor/phpunit/phpunit/phpunit tests/Feature/GpfWorkflowFeatureTest.php --testdox

# Test HTTP endpoints, role authorizations, authority prints, and subscriber lookups
php vendor/phpunit/phpunit/phpunit tests/Feature/GpfControllersFeatureTest.php --testdox

# Test VLC voucher aggregation, master dropdowns, and base closing balances
php vendor/phpunit/phpunit/phpunit tests/Unit/OracleMasterBridgeTest.php --testdox

# Test interest calculation engine, progressive balance math, delayed interest, and DLIS
php vendor/phpunit/phpunit/phpunit tests/Unit/GpfCalculationEngineTest.php --testdox

# Test Indian Currency words formatter (Crore, Lakh, Thousand, Hundred, Rupees, Paise)
php vendor/phpunit/phpunit/phpunit tests/Unit/IndianCurrencyFormatterTest.php --testdox

# Test interest cutoff rules based on event types (Superannuation, Death, etc.)
php vendor/phpunit/phpunit/phpunit tests/Unit/CutoffRuleResolverTest.php --testdox

# Test PKI signature verification and digital certificate validation
php vendor/phpunit/phpunit/phpunit tests/Unit/PkiSignatureVerifierTest.php --testdox
```

---

## 2. Key Workflow Execution States

| Step | State | Role | Controller / Service | Verification Point |
| :--- | :--- | :--- | :--- | :--- |
| 1 | `DRAFT` | `deo` (`deeksha`/`kalipada`) | `InwardCaseController@store` | Demographic lookup from `VLCS.GP_APPLICATIONS` & `VLCS.STATE_DDO`. |
| 2 | `CALCULATED` | `deo` | `CalculationController@store` | Multi-year ledger, Base FY from `VLCS.GP_YEARLY_BALANCES`, monthly deposits/withdrawals from `VLCS.GP_VOUCHER_ACC_DETAILS`, Cut month suppression, Delayed interest computed with `GpfCalculationEngine`. |
| 3 | `CHECKED` | `checker` (`anjana`) | `ApprovalController@check` | AAO verifies ledger, missing credits (`VLCS.GP_MISSING_CREDIT`), and DLIS eligibility. |
| 4 | `APPROVED` | `approver` (`rkdb`) | `ApprovalController@approve` | Sr. AO sanctions settlement amount. |
| 5 | `AUTHORIZED` | `approver` (`rkdb`) | `AuthorityController@sign` | Statutory Authority letter generated & signed with PKI digital signature. |
| 6 | `HRMS_SYNCED`| System / `approver` | `DispatchController@syncHrms` | XML/JSON payload dispatched to HRMS API. |
| 7 | `DISPATCHED` | DEO / Outward | `DispatchController@store` | Speed Post tracking barcode recorded. |
| 8 | `CLOSED` | System / Admin | `GpfWorkflowService@closeCase` | Case marked closed and archived. |

---

## 3. Database Health & Fallback Validation

### Checking Dual-Database Connections
- **Oracle 11g Master (VLC Data)**: `192.168.100.247:1521/db11g` (Schema: `VLCS` only) handled via `OracleMasterBridge.php`.
- **PostgreSQL 18 Primary Storage & Replica**: `10.47.240.169:5432/gpf_final_payment` (Schema: `gpffp`).

### Monthly Voucher Aggregation Query
```sql
SELECT 
    TO_CHAR(PAY_SLIP_DATE, 'YYYY-MM') AS CAL_MONTH,
    MAX(TO_CHAR(PAY_SLIP_DATE, 'YYYY-MM-DD')) AS PAY_SLIP_DATE,
    SUM(NVL(SUBSCRIPTION_AMT, 0) + NVL(REFUND_AMT, 0) + NVL(OTHERS_AMT, 0)) AS TOTAL_DEPOSIT,
    SUM(NVL(SUBSCRIPTION_AMT, 0)) AS TOTAL_SUBSCRIPTION,
    SUM(NVL(REFUND_AMT, 0)) AS TOTAL_REFUND,
    SUM(NVL(OTHERS_AMT, 0)) AS TOTAL_OTHERS,
    SUM(NVL(WITHDRAWAL_AMT, 0)) AS TOTAL_WITHDRAWAL,
    LISTAGG(VOUCHER_NO, ', ') WITHIN GROUP (ORDER BY VOUCHER_NO) AS VOUCHERS,
    LISTAGG(ABSTRACT_NO, ', ') WITHIN GROUP (ORDER BY ABSTRACT_NO) AS ABSTRACTS,
    COUNT(*) AS VOUCHER_COUNT
FROM VLCS.GP_VOUCHER_ACC_DETAILS
WHERE (SERIES_ID = :s_num OR TO_CHAR(SERIES_ID) = :s_str)
  AND (ACCOUNT_NO = :a_num OR TO_CHAR(ACCOUNT_NO) = :a_str)
  AND (TAG IS NULL OR TAG != 'D')
  AND (POSTING_TYPE IS NULL OR POSTING_TYPE != 'F')
GROUP BY TO_CHAR(PAY_SLIP_DATE, 'YYYY-MM')
ORDER BY CAL_MONTH ASC
```

### Offline / Fallback Testing
When Oracle 11g host is unreachable or when running automated CI tests, `OracleMasterBridge` automatically queries the PostgreSQL replica tables (`vlcs_gp_accounts`, `vlcs_gp_applications`, `vlcs_gp_yearly_balances`, `vlcs_gp_voucher_acc_details`, `vlcs_gp_missing_credit`, etc.).

---

## 4. Statutory PDF Authority & Letter Printing Routes

- **Final Payment Authority Letter**: `/authority/{id}/print`
- **Deposit-Linked Insurance Scheme (DLIS) Order**: `/authority/{id}/print-dlis`
- **Lifetime Arrears (LTA) Authority Letter**: `/authority/{id}/print-lta`
- **Official Input Sheet Report (3-Signature Ledger)**: `/letters/{caseId}/input-sheet`
- **Annexure 5.24 Intimation to Subscriber**: `/letters/{caseId}/intimation`
- **Corrigendum Amendment Order**: `/letters/{caseId}/corrigendum`
- **Revalidation Order to Treasury Officer**: `/letters/{caseId}/revalidation`
- **Objection / Defect Return Memo to DDO**: `/letters/{caseId}/objection`
- **Rule 11(7) Minus Balance Recovery Notice**: `/letters/{caseId}/minus-balance`

---

## 5. Delay Interest Calculation & Statutory 6-Month Cap (Rule 11(4))

### Statutory Rule & Policy
Under Rule 11(4) of Central GPF Rules 1960:
1. Interest on delayed final payment is admissible for a maximum duration of **6 months**.
2. If payment is delayed for **7 months or beyond (7+)**, delayed interest for months 7+ is strictly **₹0.00 (suppressed/capped)** unless an official **Delay Justification / Remarks** is recorded and approved by the Sr. Accounts Officer (`approver`) or Directorate (`admin`).
3. When formal justification is entered, interest is unlocked and computed for all authorized extended delay months.

### Architecture & Fields
- **Database Schema**:
  - `gpffp.inward_cases`: `delay_justification` (text), `delay_approved_by` (bigint -> users.id), `delay_approved_at` (timestamp).
  - `gpffp.calculation_runs`: `delay_justification`, `delay_approved_by`, `delay_months_count` (int), `has_exceeded_delay_cap` (bool).
- **Engine Logic (`GpfCalculationEngine.php`)**:
  - During delayed period processing, tracks sequential delay month index (`$delayMonthCount`).
  - If `$delayMonthCount > 6` and `empty($delayJustification)`, sets `$delayInt = '0.0000'` (strictly ₹0.00).
  - If `$delayJustification` is non-empty, calculates standard monthly delayed interest: `round((progressive * rate) / 1200, 2)`.
- **UI Interaction (`CalculationSheet.jsx`)**:
  - Automatically detects delay months count (`delay_rows.length > 6`).
  - If uncapped without justification: displays amber warning banner and strikethrough `₹ 0.00 (Capped: Rule 11(4))` badge on rows 7+.
  - When officer enters justification: displays emerald unlocked banner, authorizer badge, and recalculates total payable in real-time.

---

## 6. Development & Build Commands

```powershell
# Run backend dev server:
php artisan serve --host=127.0.0.1 --port=8000

# Build frontend assets:
npm.cmd run build

# Run automated tests:
php vendor/phpunit/phpunit/phpunit --testdox
```

---

## 6. Remote Docker Deployment & Maintenance (`10.47.240.169`)

### 1-Click Remote Deployment
To deploy code updates, run migrations, and refresh caches on the remote Docker host `10.47.240.169`:

```powershell
# Run the automated deployment script:
.\deploy.bat

# Or directly in PowerShell:
powershell -ExecutionPolicy Bypass -File .\scripts\deploy.ps1 -CommitMsg "Your update message"
```

### Rebuilding the Docker Container on Remote Server
When `Dockerfile`, PHP extensions (`oci8`, `pdo_oci`), or system dependencies change:
```powershell
# Rebuild container without cache:
docker compose build --no-cache
docker compose up -d --force-recreate
```

### Remote Health Check & Status Endpoint
Check container status, PHP version, loaded extensions, and live Oracle connection:
```powershell
Invoke-RestMethod -Uri "http://10.47.240.169/api/v1/system/status" -Headers @{ "X-Deploy-Token" = "GPF_DEPLOY_SECRET_TOKEN_2026" }
```
Expected output includes:
- `database`: `connected (pgsql)`
- `oracle`: `{ connected: true, status: "Connected to Oracle 11g (VLCS)", oci8_loaded: true, pdo_oci_loaded: true }`
- `extensions`: `{ oci8: true, pdo_oci: true, pgsql: true, pdo_pgsql: true }`

### Troubleshooting Missing Closing Balances / 3-Year Fallback in Docker
If the Calculation page in Docker shows only 3 financial years (`2024-2025`, `2023-2024`, `2022-2023`) and Opening Balance 0:
1. Check `/api/v1/system/status` to verify `oci8_loaded` is `true` and `oracle.connected` is `true`.
2. If `oci8` is false, ensure Docker was built using **Oracle Instant Client 19c (19.24 LTS)** with `$PHPIZE_DEPS`. (Oracle 21c is NOT compatible with Oracle 11g).
3. If `oracle.status` says `unreachable`, check network route from Docker host `10.47.240.169` to Oracle host `192.168.100.247:1521` or increase `ORACLE_PROBE_TIMEOUT=3.0` in `.env`.
4. Ensure `/usr/lib/oracle/current/network/admin/sqlnet.ora` contains:
   `SQLNET.ALLOWED_LOGON_VERSION_CLIENT=8`
   `SQLNET.ALLOWED_LOGON_VERSION_SERVER=8`

### Browser Access Notes
- **Direct IP**: `http://10.47.240.169` or `https://10.47.240.169`
- **Domain Name**: `http://gpffp.local` or `https://gpffp.local` (ensure `10.47.240.169 gpffp.local` in `C:\Windows\System32\drivers\etc\hosts`).
- **Edge/Chrome "Can't reach this page" or Untrusted SSL**:
  - In Chrome / Edge on the error page, type `thisisunsafe` to bypass self-signed certificate warnings.
  - Or install `docker/ssl/server.crt` into Windows "Trusted Root Certification Authorities".

---

## 7. Authentication UI/UX & Security Design Standards

- **Viewport Scrollability & Centering Invariant**:
  - All authentication pages (`/login`, `/register`, `/forgot-password`, `/reset-password`, `/forgot-username`) must use `overflow-x-hidden overflow-y-auto` on the outermost container. Never use `overflow-hidden`.
  - Centering must use `my-auto` with vertical padding (`py-8 sm:py-12`), avoiding raw `justify-center` alone on scroll containers so taller cards are never clipped into negative scroll space at the top.
  - Do not apply `select-none` on outer authentication wrappers. Inputs and text must remain selectable.
  - Card containers (`InteractiveTiltCard.jsx`) must explicitly specify `w-full`.
- **Full-Viewport Ambient 3D WebGL Backgrounds (`ThreeAuthNexus.jsx`)**:
  - The 3D canvas must never be bound inside a small or fixed-width box (`max-w-2xl` or hardcoded `height={600}`).
  - Must always span the entire viewport (`fixed inset-0 pointer-events-none w-full h-full overflow-hidden`) with a comfortable FOV (45°) and `camera.position.z >= 5.2` to eliminate edge clipping on rotation.
  - Wrap WebGL initialization and animation in `try ... catch` and add null checks in cleanup (`if (renderer)`, `if (observer)`).
- **Strict Prohibition of Mock / Quick Login & Dummy Placeholders**:
  - Do NOT provide quick-login profile chips, autofill shortcuts, or mock user buttons.
  - Only registered officers with existing records in the database may log in by manually typing their user ID and password.
  - Placeholders must remain neutral and institutional (`Enter registered officer username`, `Enter institutional password`).
- **Micro-Animations & Interactive Dynamics**:
  - Interactive canvas constellation background (`AuthBackground.jsx`).
  - 3D perspective tilt container with cursor spotlight border glow (`InteractiveTiltCard.jsx`).
  - Reactive SVG Security Hologram Avatar (`SecurityShieldAvatar.jsx`) responding to field focus (`idle`, `username`, `password`, `peek`, `token`, `processing`, `error`).

---

## 8. Layout, Navigation & Theme Design Standards

### A. Compact Ergonomic Layout Scale
- **High-Density Scaling**: High-efficiency departmental information density (`max-w-7xl`, `h-14` topbar, compact cards and metric widgets).
- **Pinned Left Sidebar (`fixed top-14 bottom-0 left-0 z-30`)**:
  - Anchored permanently to the left viewport; never scrolls with the page.
  - Dedicated independent scrollbar (`overflow-y-auto thin-scrollbar`).
  - Uses `PanelLeftClose` (when expanded) and `PanelLeftOpen` (when collapsed) for toggling width between `w-56` and `w-16`. Never uses a modal-close `"X"`.
  - Main container content offset: `md:ml-56` (or `md:ml-16`) and `pt-14`.
- **Top Navigation Bar Dynamic Transparency**:
  - At scroll position 0 (`scrollY === 0`): 100% transparent (`bg-transparent border-transparent`) rendering only floating breadcrumbs, search, theme toggles, audio controls, and profile dropdowns.
  - On scroll (`scrollY > 8`): Smoothly fades in frosted glassmorphic background (`bg-white/85 dark:bg-slate-900/85 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 shadow-xs`).
- **Full Light & Dark Theme Parity & Matrix Hover Standards**:
  - When light-mode hover backgrounds (e.g., `hover:bg-emerald-100/70`) are used on cards, they **must always** have explicit dark-mode counterparts (`dark:hover:bg-emerald-900/40 dark:hover:border-emerald-400/60 dark:hover:shadow-none`) to prevent light-theme washouts on dark cards.
  - Use `shadow-2xs dark:shadow-none` on tinted matrices (e.g. Dashboard Aging Breakdown) to avoid murky shadow smudges.
  - High-contrast text hierarchy: `text-slate-900 dark:text-white` for primary headers, `dark:text-*-100 font-mono` for counts, and `dark:text-*-300` for badges.
  - Tailwind CSS v4 custom variant: `@custom-variant dark (&:where(.dark, .dark *));`.
- **Audio Feedback**:
  - Web Audio API zero-dependency haptic feedback with header mute toggle (`Volume2`/`VolumeX`).
- **Omnipresent Command Palette**:
  - `Ctrl+K` modal providing instant search for routes, statutory letter actions, and role consoles.




