# VIVA — Key Decisions & Explanations

## Billing/Payments vs Due Management — Why Both?

### Billing/Payments (`/billing/invoices`, `/billing/payments`)
**Purpose:** Record-keeping of every financial transaction.  
When a patient makes a payment, it is recorded here with:
- Receipt number (`bill-{patient_id}-{seq}`)
- Amount, method, date
- Link to the specific invoice

This is the **source of truth** for all money received. The `Payment` model stores individual transactions, and each payment auto-generates a printable receipt.

**Files:**
- `BillingController.php` — create invoices, record payments, print receipts
- `Invoices.vue` — full invoice list with create/pay/print actions
- `Payments.vue` — all payments across all invoices
- `PatientPaymentHistory.vue` — per-patient payment summary

### Due Management (`/dues`)
**Purpose:** Operational tool for tracking & collecting outstanding balances.  
Shows only invoices with `pending` or `partial` status. It answers the question: "Who still owes money and how much?"

**Why separate?** The billing index shows *all* invoices (paid, partial, pending). Due management filters to just unpaid ones so staff can focus on follow-ups without noise. Same underlying data, different view.

**File:** `DueController.php` — queries only `WHERE status IN ('pending', 'partial')`

### Why both exist:
| Aspect | Billing/Payments | Due Management |
|--------|-----------------|----------------|
| Shows | All invoices (paid/pending/partial) | Only unpaid (pending/partial) |
| Actions | Create invoice, record payment, print | Quick-pay, create new invoice |
| Purpose | Full audit trail | Debt collection focus |
| Receipts | Yes (print on payment) | No (links to billing) |

Think of it like a bank account statement (billing = all transactions) vs a reminder list of unpaid bills (dues = what's outstanding). Both use the same `invoices` + `payments` tables — just different filters.

---

## Patient → Payment Relationship

Payments are **not** directly linked to patients. The chain is:

`Patient → hasMany → Invoice → hasMany → Payment`

The `Patient` model uses `hasManyThrough(Payment::class, Invoice::class)` to reach payments via invoices.

---

## Payment Receipt Numbers

Format: `bill-{patient_id_padded_3}-{sequence_per_patient_padded_3}`

Example: `bill-003-005` means patient #3's 5th payment across all invoices.

---

## Why Print Pages Use `window.close()` for Back

Print pages (PrintInvoice, PrintInvoiceList, Lab Print, Prescription Print) are opened in new tabs via `target="_blank"`. `window.history.back()` fails because new tabs have no history. `window.close()` closes the tab, which is the correct behavior.

The payment receipt page is different — it's opened via server redirect (same tab), so it uses `window.history.back()` to return to the invoices page.

---

## Invoice Edit (`PATCH /billing/invoices/{invoice}`)

**What:** Allows editing pending invoices — change patient, items, discount, tax, due date, notes.

**Why:** Staff make mistakes and need to correct invoices without deleting and recreating them.

**Restrictions:**
- Cannot edit cancelled invoices
- Items are replaced entirely (delete old items, insert new ones)
- Paid/partial status invoices CAN be edited (items/prices can change before full payment)

**UI:** Edit button (pen icon) on each row opens the edit modal pre-filled with existing data.

---

## Invoice Cancel/Void (`PATCH /billing/invoices/{invoice}/cancel`)

**What:** Marks an invoice as `cancelled` instead of deleting it. Preserves the audit trail.

**Why:** If an invoice was created by mistake or services were not rendered, staff can void it rather than delete. The cancelled record stays in the database for reconciliation.

**Restrictions:**
- Cannot cancel a fully paid invoice — must issue a refund first
- Already-cancelled invoices cannot be cancelled again
- Cancelled invoices are excluded from revenue stats

**UI:** Cancel button (ban icon) on each row with confirmation dialog.

---

## Tax/VAT Support

**What:** Added `tax_percent` and `tax_amount` columns to the `invoices` table.

**Calculation:** `tax_amount = subtotal × tax_percent / 100`, `total = subtotal + tax_amount - discount`

**Why:** Clinics may charge VAT on certain services (e.g., cosmetic procedures, medical equipment). Previously invoices had no way to account for tax.

**UI:** Tax field (percentage) in create/edit invoice modals. Displayed on print invoice.

---

## Due Dates & Aging

**What:** Added `due_date` column to the `invoices` table. Dues page now shows aging buckets.

**Aging Logic:**
- If `due_date` is set: overdue when `due_date < today`
- If `due_date` is NOT set: falls back to `created_at < 30 days ago` (legacy heuristic)
- Buckets: 0-30 days, 31-60 days, 61-90 days, 90+ days

**UI:** 
- Due date column in both Invoices and Dues tables
- Overdue dates shown in red
- Aging bucket stats cards on Dues page

**Overdue scope:** `Invoice::overdue()` — active (pending/partial) invoices where due_date is past, or (if no due_date) created more than 30 days ago.

---

## Billing Stats Dashboard

**What:** 6 stats cards at the top of the Invoices page.

| Card | Source |
|------|--------|
| Today's Revenue | Sum of all invoice totals created today |
| Collected Today | Sum of all payments recorded today |
| Pending | Count of pending invoices |
| Overdue | Count of overdue invoices (via `overdue()` scope) |
| Outstanding | Sum of all due amounts across active invoices |
| Cancelled | Count of cancelled invoices |

**Why:** Gives staff immediate visibility into daily financial performance without running separate reports.

---

## Refund Processing

**What:** Refunds reduce the `paid_amount` on an invoice and create a negative payment record.

**Flow:**
1. Staff clicks refund icon on invoice with payments
2. Enters refund amount (must be ≤ total paid)
3. System creates a Payment record with `amount = -refund_amount` and `payment_method = 'refund'`
4. `refunded_amount` on invoice is incremented
5. `paid_amount` is reduced; if it reaches 0, status reverts to `pending`

**Why:** Patients may overpay, cancel services, or request money back. Refunds must be tracked for accounting.

**Restrictions:**
- Refund amount cannot exceed total paid amount
- Cannot refund an invoice with zero payments

---

## Invoice Delete

**What:** Only invoices with zero payments can be permanently deleted.

**Why:** If an invoice has payments, deleting it would orphan payment records. Cancelling is preferred. Delete is available only for draft/empty invoices.

---

## Key Statuses

| Status | Meaning |
|--------|---------|
| `pending` | Created, no payments |
| `partial` | Partial payment received |
| `paid` | Fully paid (paid_amount ≥ total) |
| `cancelled` | Voided — no longer valid |

The Invoice model defines constants for all statuses:
- `Invoice::STATUS_PENDING`
- `Invoice::STATUS_PARTIAL`
- `Invoice::STATUS_PAID`
- `Invoice::STATUS_CANCELLED`
