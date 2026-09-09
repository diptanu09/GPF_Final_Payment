# GPF Final Payment Portal - Project Architecture & Institutional Memory

This document contains the complete architectural specification, database mappings, workflow rules, and integration guidelines for the **GPF Final Payment Portal**. This file serves as permanent agent memory and must be referenced across all future iterations.

---

## 1. System Overview & Technology Stack

- **Backend Framework**: Laravel 12 (PHP 8.2+)
- **Frontend Framework**: Inertia.js with React 19, Tailwind CSS v4, Lucide Icons, Vite
- **Primary Database (Application Storage)**:
  - **Engine**: PostgreSQL 18
  - **Host**: `10.47.240.169:5432`
  - **Database**: `gpf_final_payment`
  - **User**: `postgres` | **Password**: `root@123`
  - **Contents**: Stores users, inward cases, calculation runs, monthly breakdowns, nominees, authorities, digital signature audits, workflow history, and PostgreSQL replica tables.
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

## 3. User Accounts & Role-Based Access Control

The system uses institutional user accounts imported from `gpffp.USER_ACCOUNTS` (no dummy/mock users):

| Username | Name / Designation | System Role | Capabilities |
| :--- | :--- | :--- | :--- |
| `dir` | Director | `admin` | Full system governance, reassignments, audits, reports |
| `jdg` | Joint Director General | `admin` | High-level approvals, overrides, reports |
| `rkdb` | R. K. Debbarma (Sr. AO) | `approver` | Final settlement approval, digital signing, HRMS dispatch |
| `anjana` | Anjana (AAO) | `checker` | Audit verification, ledger checking, returning cases |
| `deeksha` | Deeksha (DEO / Dealing Asst) | `deo` | Inward registration, calculation runs, nominee entry |
| `kalipada` | Kalipada (DEO / Dealing Asst) | `deo` | Inward registration, calculation runs, nominee entry |

---

## 4. Subscriber Lookup & Inward Pipeline

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

## 5. GPF Final Payment Workflow States

```
[1] DRAFT 
     │ (DEO registers docket & inputs subscriber profile)
     ▼
[2] CALCULATED 
     │ (Interest calculated & monthly ledger verified)
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

## 6. Calculation Engine & Business Rules

1. **Rate of Interest**: 7.10% per annum (or applicable historical FY rate) computed on monthly progressive balances.
2. **Deposit-Linked Insurance Scheme (DLIS)**:
   - Admissible on `CaseType::DEATH_IN_SERVICE` (`pension_type_id = '2'` or `'7'`).
   - Max insurance coverage: **₹60,000**.
   - Requires minimum 3 years continuous service and average balance thresholds.
3. **Interest Cutoff Rules**:
   - Superannuation / Resignation: End of event month.
   - Death in Service: Cutoff resolved per death grace rules.
   - LTA: Date of LTA / cessation.
4. **Missing Credits**: Retrieved from `VLCS.GP_MISSING_CREDIT` for uncredited deduction adjustments.

---

## 7. Key File Sitemap

- `app/Services/Integration/OracleMasterBridge.php` — Oracle 11g OCI8 & PostgreSQL 18 dual-database bridge.
- `app/Services/Calculation/GpfInterestCalculator.php` — Progressive interest calculation engine.
- `app/Services/Calculation/CutoffRuleResolver.php` — Interest cutoff date resolver.
- `app/Services/Workflow/GpfWorkflowService.php` — Workflow state transitions and audit logging.
- `app/Http/Controllers/InwardCaseController.php` — Docket registration & subscriber lookup API (`/inward/lookup`).
- `app/Http/Controllers/CalculationController.php` — Calculation runs and monthly breakdown sheets.
- `app/Http/Controllers/ApprovalController.php` — AAO verification and Sr. AO approval.
- `app/Http/Controllers/AuthorityController.php` — Authority generation, PDF preview, and DSC digital signing.
- `app/Http/Controllers/DispatchController.php` — HRMS dispatch and postal outward tracking.
- `resources/js/Pages/` — Inertia React UI components.

---

## 8. Common Commands

```powershell
# Run backend dev server:
php artisan serve --host=127.0.0.1 --port=8000

# Build frontend assets:
npm run build

# Run automated tests:
php artisan test
```
