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

### Run All 47 Automated Tests
```powershell
php vendor/phpunit/phpunit/phpunit --testdox
```

### Targeted Workflow & Domain Tests
```powershell
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

## 4. Statutory PDF Authority Printing Routes

- **Final Payment Authority Letter**: `/authority/{id}/print`
- **Deposit-Linked Insurance Scheme (DLIS) Order**: `/authority/{id}/print-dlis`

---

## 5. Development & Build Commands

```powershell
# Run backend dev server:
php artisan serve --host=127.0.0.1 --port=8000

# Build frontend assets:
npm.cmd run build

# Run automated tests:
php vendor/phpunit/phpunit/phpunit --testdox
```
