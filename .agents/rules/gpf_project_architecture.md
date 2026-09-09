# GPF Final Payment Portal - Core System Rules & Conventions

## 1. Dual-Database Architecture & Fallbacks
- **Primary application storage**: PostgreSQL 18 (`10.47.240.169:5432/gpf_final_payment`).
- **Master data source**: Oracle 11g (`192.168.100.247:1521/db11g`).
- Never bypass `OracleMasterBridge.php` fallbacks. In automated testing and offline environments, ensure code and tests utilize the PostgreSQL replica / fallback fixtures gracefully.

## 2. Institutional Database Security & Exclusion Policy
- **STRICT PROHIBITION**: Never query or create tables starting with `FP_` (`FP_AUTHORITY_TRANS`, `FP_BENE_NOMINEE`, `FP_CALCULATION`, etc.).
- **STRICT PROHIBITION**: Never query legacy report upload tables (`CORR_AUTHORITY_REPORTS_UPLOAD`, `CORR_SIGNED_REPORTS_UPLOAD`, `DLIS_AUTHORITY_REPORTS_UPLOAD`, `DLIS_REV_REPORTS_UPLOAD`, `DLIS_SIGNED_AUTH_REPORT`, `DLIS_SIGNED_REV_REPORT`, `ACNTS_SAVE`).
- Demographic data must always be fetched from `VLCS.GP_APPLICATIONS` (105,030+ records) and mapped with `VLCS.STATE_DDO`.

## 3. Workflow State Machine Invariants
- Case workflow strictly follows: `DRAFT` ➔ `CALCULATED` ➔ `CHECKED` ➔ `APPROVED` ➔ `AUTHORIZED` ➔ `HRMS_SYNCED` ➔ `DISPATCHED` ➔ `CLOSED`.
- Role capabilities must not be relaxed:
  - `deo`: Draft registration & interest calculation.
  - `checker` (AAO): Ledger checking & audit verification.
  - `approver` (Sr. AO): Approval & DSC digital signing.
  - `admin` (Director / JDG): High-level system governance, reassignments, audits.

## 4. Institutional User Accounts
- Always use real institutional user accounts (`dir`, `jdg`, `rkdb`, `anjana`, `deeksha`, `kalipada`). Do not insert mock or generic dummy users.

## 5. Calculation Engine & Business Rules
- Base interest rate: 7.10% per annum (or applicable historical FY rate) computed on monthly progressive balances.
- DLIS is admissible only on `CaseType::DEATH_IN_SERVICE` (`pension_type_id = '2'` or `'7'`) with a maximum coverage cap of ₹60,000.
- Missing credits must be retrieved from `VLCS.GP_MISSING_CREDIT`.
