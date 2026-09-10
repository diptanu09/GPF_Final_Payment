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

### Run All 33 Automated Tests
```powershell
php vendor/phpunit/phpunit/phpunit --testdox
```

### Targeted Workflow & Domain Tests
```powershell
# Test complete end-to-end workflow state transitions (Draft -> Dispatched)
php vendor/phpunit/phpunit/phpunit tests/Feature/GpfWorkflowFeatureTest.php --testdox

# Test HTTP endpoints, role authorizations, authority prints, and subscriber lookups
php vendor/phpunit/phpunit/phpunit tests/Feature/GpfControllersFeatureTest.php --testdox

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
| 2 | `CALCULATED` | `deo` | `CalculationController@calculate` | Multi-year ledger, Base FY from VLC, Cut month suppression, Delayed interest computed with `GpfCalculationEngine`. |
| 3 | `CHECKED` | `checker` (`anjana`) | `ApprovalController@check` | AAO verifies ledger, missing credits, and DLIS eligibility. |
| 4 | `APPROVED` | `approver` (`rkdb`) | `ApprovalController@approve` | Sr. AO sanctions settlement amount. |
| 5 | `AUTHORIZED` | `approver` (`rkdb`) | `AuthorityController@sign` | Statutory Authority letter generated & signed with PKI digital signature. |
| 6 | `HRMS_SYNCED`| System / `approver` | `DispatchController@syncHrms` | XML/JSON payload dispatched to HRMS API. |
| 7 | `DISPATCHED` | DEO / Outward | `DispatchController@store` | Speed Post tracking barcode recorded. |
| 8 | `CLOSED` | System / Admin | `GpfWorkflowService@closeCase` | Case marked closed and archived. |

---

## 3. Database Health & Fallback Validation

### Checking Dual-Database Connections
- **Oracle 11g Master**: `192.168.100.247:1521/db11g` (Schemas: `gpffp`, `VLCS`) handled via `OracleMasterBridge.php`.
- **PostgreSQL 18 Primary Storage**: `10.47.240.169:5432/gpf_final_payment`.

### Offline / Fallback Testing
When Oracle 11g host is unreachable or when running automated CI tests, `OracleMasterBridge::lookupSubscriber` automatically queries the PostgreSQL replica tables (`gp_accounts`, `gp_applications`, `gp_yearly_balances`, etc.).

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
