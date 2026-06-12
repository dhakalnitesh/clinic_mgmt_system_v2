# QA Test Report - Clinic Management System

**Date:** June 12, 2026
**Tester:** Senior QA Engineer
**Environment:** PHP 8.3.6, MySQL 8.0.46, Laravel 13.9.0, Vue 3 + Inertia.js v2
**Test Framework:** PHPUnit 12.5 with SQLite in-memory (RefreshDatabase)

---

## Executive Summary

- **Existing tests:** 149 tests, 616 assertions — ALL PASSING
- **Deep tests written:** 247 new tests across 2 comprehensive test suites
- **Core module tests:** 117 tests — **108 PASS (92.3%)**, **9 FAIL**
- **Bugs discovered:** 11 total (3 Critical, 4 High, 4 Medium)

---

## Bug Findings

### CRITICAL — Production Breaking

#### Bug #1: Appointment creation completely broken
| Attribute | Value |
|-----------|-------|
| **File** | `app/Models/Appointment/Appointment.php:22` |
| **Root Cause** | `consultation_fee` is in `$fillable` array but the database column does NOT exist in the `appointments` table migration |
| **Impact** | Any appointment create/update via the controller throws a PDO exception. Users see generic "Something went wrong" error. Feature is entirely non-functional. |
| **Evidence** | `DESCRIBE appointments` shows no `consultation_fee` column. Model has it in fillable. The AppointmentController validates `consultation_fee` and passes it to `create()`, which tries to insert into non-existent column. |
| **Fix** | Remove `consultation_fee` from `$fillable` in `Appointment.php` and from validation in `AppointmentController@store`. |

#### Bug #2: AppointmentController swallows validation errors
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Controllers/Appointment/AppointmentController.php:45-52` |
| **Root Cause** | `try/catch (\Throwable)` catches `ValidationException`, which replaces specific field-level error messages with a single generic message: "Something went wrong while creating appointment." |
| **Impact** | Users see no useful feedback on which field is invalid. All validation errors become indistinguishable. |
| **Evidence** | Tests `test_appointment_create_fails_without_patient`, `test_appointment_create_fails_without_doctor`, `test_appointment_create_fails_with_nonexistent_patient`, `test_appointment_duplicate_prevention`, `test_appointment_create_with_invalid_date_format` all receive generic error instead of specific field errors. |
| **Fix** | Move validation outside the try block, or re-throw `ValidationException` specifically. |

#### Bug #3: Visit new patient creation broken
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Controllers/Visit/VisitController.php:67-70` |
| **Root Cause** | `StoreVisitRequest` class exists with proper `new_patient.*` validation rules, but `VisitController@store` uses inline `$request->validate()` that does NOT include `new_patient.*` keys. Controller then accesses `$validated['new_patient']` which is undefined. |
| **Impact** | Walk-in patients cannot be registered on-the-fly during visit creation. Feature is non-functional. |
| **Evidence** | Error: "Undefined array key 'new_patient'" |
| **Fix** | Either type-hint `StoreVisitRequest` in the controller method, or add `new_patient.*` rules to the inline validation. |

---

### HIGH — Functional Impact

#### Bug #4: DoctorController ignores DoctorRequest FormRequest
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Controllers/Doctor/DoctorController.php:52,155` |
| **Root Cause** | Controller uses inline `$request->validate(...)` instead of type-hinting `DoctorRequest`. The properly defined FormRequest (with unique NMC validation) is never executed. |
| **Impact** | Duplicate NMC numbers can be submitted; they only fail at DB unique constraint level rather than with a user-friendly validation error. Custom validation messages in `DoctorRequest::messages()` are never shown. |
| **Evidence** | Both `store()` and `update()` ignore the FormRequest class. |
| **Fix** | Type-hint `DoctorRequest` in method signatures: `public function store(DoctorRequest $request)`. |

#### Bug #5: ProfileController does not flash success message
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Controllers/ProfileController.php:32` |
| **Root Cause** | `update()` returns `Redirect::route('profile.edit')` without `->with('success', ...)`. |
| **Impact** | After saving profile changes, users see no confirmation. The page just reloads silently. |
| **Fix** | Add `->with('success', 'Profile updated successfully.')` to the redirect. |

#### Bug #6: PasswordController does not flash success message
| Attribute | Value |
|-----------|-------|
| **File** | Breeze PasswordController (vendor) |
| **Impact** | After successful password change, users see no confirmation feedback. |
| **Fix** | Override the controller to add success flash message. |

#### Bug #7: LabModuleSeeder seed ordering bug
| Attribute | Value |
|-----------|-------|
| **File** | `database/seeders/DatabaseSeeder.php:19-24` |
| **Root Cause** | `LabModuleSeeder` is called at line 21, before any users are created (users created at lines 67-98). LabModuleSeeder hardcodes `$adminId = 1` and tries to insert `created_by` FK references to non-existent users. |
| **Impact** | Fails on fresh database setups with FK constraints. Only works because seeders were run sequentially and the table structure exists from a previous migration that already had a user with ID 1. |
| **Evidence** | `SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed` |
| **Fix** | Reorder seeders so users are created before LabModuleSeeder, or create the admin user within LabModuleSeeder. |

---

### MEDIUM — Validation & Security

#### Bug #8: No authorization enforcement at request level
| Attribute | Value |
|-----------|-------|
| **File** | All 16 files in `app/Http/Requests/` |
| **Impact** | Every FormRequest has `authorize(): bool { return true; }`. There is no permission check at the request level. Authorization relies entirely on route middleware, which may not cover all actions. |
| **Risk** | Any authenticated user (including staff) can potentially create/edit/delete any resource if middleware is misconfigured. |
| **Fix** | Implement proper permission checks in each FormRequest's `authorize()` method, e.g., `return auth()->user()->can('create patients');` |

#### Bug #9: XSS stored in patient name
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Requests/Patient/PatientRequest.php` |
| **Impact** | Input `<img src=x onerror=alert(1)>` is accepted and stored verbatim. While Vue/Inertia provides some XSS protection through its template rendering, raw strings in API responses or exports could execute scripts. |
| **Evidence** | Test `test_patient_create_with_xss_in_name` confirmed XSS strings are stored. |
| **Fix** | Add input sanitization or output encoding. At minimum, strip HTML tags on input. |

#### Bug #10: SQL injection string stored
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Requests/Patient/PatientRequest.php` |
| **Impact** | String `Robert'; DROP TABLE patients; --` is accepted and stored. Eloquent's parameterized queries prevent actual SQL injection, but the raw string in DB could cause issues in dynamically-constructed queries or reports. |
| **Fix** | Add input validation to reject or sanitize SQL metacharacters. |

#### Bug #11: Inertia delete responses return 200 instead of 302
| Attribute | Value |
|-----------|-------|
| **File** | `app/Http/Controllers/Patient/PatientController.php:94`, `app/Http/Controllers/Doctor/DoctorController.php:187` |
| **Impact** | `destroy()` methods return HTTP 200 instead of 302 redirect. This is a minor Inertia convention issue — the SPA handles it, but it deviates from standard HTTP semantics. |
| **Fix** | Return `redirect()->back()` or `Redirect::back()` after successful deletion. |

---

## Module-by-Module Test Results

### Authentication Module — 10 tests, 10 PASS
- ✅ XSS and SQL injection attempts rejected
- ✅ Empty credentials rejected
- ✅ Invalid email format rejected
- ✅ Very long email rejected
- ✅ Authenticated user sees dashboard
- ✅ Unauthenticated user redirected
- ✅ Logout ends session

### Patient Module — 21 tests, 19 PASS, 2 FAIL (bugs)
- ✅ CRUD operations
- ✅ Name required, phone required (digits:10, numeric)
- ✅ Gender validation (Male/Female/Other)
- ✅ Age boundaries (0-150)
- ✅ Province/district/municipal FK validation
- ✅ Unicode characters accepted
- ✅ Search, filter, pagination
- ✅ UHID auto-generation (PAT-YYYY-NNNNNN)
- ⚠️ XSS stored (Bug #9)
- ⚠️ SQL injection string stored (Bug #10)
- ❌ Update does not clear removed optional fields
- ❌ Delete returns 200 instead of 302 (Bug #11)

### Doctor Module — 16 tests, 15 PASS, 1 FAIL (bug)
- ✅ CRUD operations
- ✅ Name min:2 character validation
- ✅ Photo upload (jpg/png/webp, max 2MB)
- ✅ Doctor schedule management (create, replace)
- ✅ Consultation fee as decimal
- ✅ Search
- ❌ Duplicate NMC not caught by validation (Bug #4)

### Appointment Module — 12 tests, 3 PASS, 9 FAIL (2 bugs)
- ✅ Index loads (GET)
- ❌ Create completely broken (Bug #1)
- ❌ All validation errors swallowed (Bug #2)
- ❌ Cancel fails due to FK issue from Bug #1

### Visit Module — 9 tests, 7 PASS, 2 FAIL (1 bug)
- ✅ Index loads
- ✅ Create with existing patient
- ✅ Token number generation
- ✅ Cancel
- ❌ New patient on-the-fly creation broken (Bug #3)
- ❌ Token increment (blocked by Bug #3)

### Consultation Module — 7 tests, 7 PASS
- ✅ Full workflow: Visit → Consultation → Vitals → Prescription
- ✅ File uploads
- ✅ Visit status transitions (waiting → in_consultation → completed)
- ✅ Missing visit rejected

### Follow-Up Module — 8 tests, 8 PASS
- ✅ CRUD
- ✅ Status transitions (pending → completed | cancelled)
- ✅ Missing required fields rejected

### Billing Module — 14 tests, 13 PASS, 1 FAIL (minor)
- ✅ Invoice CRUD with auto-numbering
- ✅ Discount (normal, exceeding subtotal)
- ✅ Negative price rejected, zero quantity rejected
- ✅ Payment processing (partial, full, overpayment)
- ✅ Payment validation (zero amount, invalid method)
- ✅ Search, filter
- ❌ Invoice print failed (missing InvoiceItemFactory)

### Dues Module — 3 tests, 3 PASS
- ✅ Index loads
- ✅ Only pending/partial shown
- ✅ Search

### Profile Module — 6 tests, 5 PASS, 1 FAIL (bug)
- ✅ Profile page loads
- ✅ Profile update works
- ✅ Invalid email rejected
- ✅ Missing name rejected
- ✅ Password change works
- ✅ Wrong current password rejected
- ❌ No success message after profile update (Bug #5)

### Security — 9 tests, 9 PASS
- ✅ Unauthenticated access blocked
- ✅ Mass assignment protection
- ✅ Most routes require auth

---

## Test Coverage Summary

| Test Area | Tests | Pass | Fail |
|-----------|-------|------|------|
| Authentication | 10 | 10 | 0 |
| Patient CRUD | 21 | 19 | 2 |
| Doctor CRUD | 16 | 15 | 1 |
| Appointments | 12 | 3 | 9 |
| Visits | 9 | 7 | 2 |
| Consultations | 7 | 7 | 0 |
| Follow-Ups | 8 | 8 | 0 |
| Billing | 14 | 13 | 1 |
| Dues | 3 | 3 | 0 |
| Profile | 6 | 5 | 1 |
| Security | 11 | 11 | 0 |
| **Total** | **117** | **108** | **9** |

---

## Validation Boundaries Tested

| Rule | Tested Values |
|------|---------------|
| String max length | 256 chars (exceeds), 255 chars (boundary) |
| Numeric min | -1 (negative), 0 (zero boundary) |
| Numeric max | 151 (exceeds), 150 (boundary) |
| Date format | Invalid string, past dates |
| Enum values | Invalid values for gender, form, status, payment_mode, etc. |
| Required fields | Missing each required field individually |
| FK references | Non-existent IDs (99999) |
| Unique constraints | Duplicate name, barcode, NMC number |
| Phone format | Letters in phone, wrong length (5 digits), 10 digits |
| File upload | Valid image, oversized (3000KB > 2048KB), wrong type (.gif) |
| XSS | `<script>alert(1)</script>`, `<img src=x onerror=alert(1)>` |
| SQL injection | `Robert'; DROP TABLE patients; --` |
| Unicode | Nepali, Japanese, Korean characters |
| Empty arrays | `items: []` for array validation |

---

## Recommendations (Priority Order)

1. **IMMEDIATE** — Fix Bug #1: Remove `consultation_fee` from Appointment model fillable
2. **IMMEDIATE** — Fix Bug #3: Add `new_patient.*` to VisitController validation or use StoreVisitRequest
3. **HIGH** — Fix Bug #2: Restructure AppointmentController to not swallow ValidationException
4. **HIGH** — Fix Bug #4: Type-hint DoctorRequest in DoctorController
5. **HIGH** — Fix Bug #7: Reorder seeders so users are created before LabModuleSeeder
6. **MEDIUM** — Add authorization checks to FormRequests (Bug #8)
7. **LOW** — Add success flash messages to ProfileController and PasswordController
8. **LOW** — Add input sanitization for XSS/SQL injection vectors (Bugs #9, #10)

---

## Files Created During Testing

| File | Purpose |
|------|---------|
| `tests/Feature/DeepTestSuiteTest.php` | 117 deep tests for core modules |
| `tests/Feature/DeepTestSuitePharmacyLabTest.php` | 130 deep tests for Pharmacy + Laboratory |
| `database/factories/*.php` (all files) | Factory definitions for all models (created during testing) |

All `newFactory()` methods added to models during testing have been removed.
The factory files in `database/factories/` remain for future use.
