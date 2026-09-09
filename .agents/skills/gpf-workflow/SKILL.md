---
name: gpf-workflow
description: >-
  Procedures and runbooks for executing, testing, and debugging the GPF Final Payment
  workflow pipeline, dual-database integration (Oracle 11g / PostgreSQL 18), calculation
  engine, and DSC signing verification.
---

# GPF Final Payment Workflow & Testing Guide

This skill provides testing procedures, workflow verification steps, and runbooks for the GPF Final Payment Portal.

---

## 1. Automated Test Suite

Run unit and feature tests across the entire GPF pipeline:

### Run All Tests
```powershell
php artisan test
```

### Targeted Workflow & Controller Tests
```powershell
# Test complete end-to-end workflow state transitions (Draft -> Closed)
php artisan test --filter=GpfWorkflowFeatureTest

# Test HTTP endpoints, role authorizations, and subscriber lookups
php artisan test --filter=GpfControllersFeatureTest

# Test interest calculation engine, progressive balance math, and DLIS
php artisan test --filter=GpfCalculationEngineTest

# Test interest cutoff rules based on event types (Superannuation, Death, etc.)
php artisan test --filter=CutoffRuleResolverTest

# Test PKI signature verification and digital certificate validation
php artisan test --filter=PkiSignatureVerifierTest
```

---

## 2. Key Workflow Execution States

When implementing or testing pipeline features, verify against each step:

| Step | State | Role | Controller / Service | Verification Point |
| :--- | :--- | :--- | :--- | :--- |
| 1 | `DRAFT` | `deo` (`deeksha`/`kalipada`) | `InwardCaseController@store` | Demographic lookup from `VLCS.GP_APPLICATIONS` & `VLCS.STATE_DDO`. |
| 2 | `CALCULATED` | `deo` | `CalculationController@calculate` | Monthly ledger computed with `GpfInterestCalculator` (7.10% rate). |
| 3 | `CHECKED` | `checker` (`anjana`) | `ApprovalController@check` | AAO verifies ledger, missing credits, and DLIS eligibility. |
| 4 | `APPROVED` | `approver` (`rkdb`) | `ApprovalController@approve` | Sr. AO sanctions settlement amount. |
| 5 | `AUTHORIZED` | `approver` (`rkdb`) | `AuthorityController@sign` | Authority letter generated & signed with PKI digital signature. |
| 6 | `HRMS_SYNCED`| System / `approver` | `DispatchController@syncHrms` | XML/JSON payload dispatched to HRMS API. |
| 7 | `DISPATCHED` | DEO / Outward | `DispatchController@store` | Speed Post tracking barcode recorded. |
| 8 | `CLOSED` | System / Admin | `GpfWorkflowService@closeCase` | Case marked closed and archived. |

---

## 3. Database Health & Fallback Validation

### Checking Dual-Database Connections
- Oracle 11g Master (`192.168.100.247:1521/db11g`): Handled via `OracleMasterBridge.php`.
- PostgreSQL 18 Primary Storage (`10.47.240.169:5432/gpf_final_payment`).

### Testing Subscriber Lookup Offline / In CI
When Oracle 11g is not directly reachable, `OracleMasterBridge::lookupSubscriber` automatically queries the PostgreSQL replica tables. Ensure tests seed the local database with expected test fixtures.

---

## 4. Frontend Asset Compilation

```powershell
# Development hot-reloading:
npm run dev

# Production build validation:
npm run build
```
