<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\DrugInteraction;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\SalesItem;
use App\Models\Pharmacy\StockBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DispenseService
{
    /**
     * Process a complete sale: create sale record, deduct stock, create items.
     */
    public function processSale(array $data): Sales
    {
        return DB::transaction(function () use ($data) {
            $sale = Sales::create([
                'invoice_number'   => $this->generateInvoiceNumber(),
                'patient_id'       => $data['patient_id'] ?? null,
                'prescription_id'  => $data['prescription_id'] ?? null,
                'sale_date'        => $data['sale_date'],
                'sale_type'        => $data['sale_type'],
                'cashier_id'       => $data['cashier_id'],
                'pharmacist_id'    => $data['pharmacist_id'] ?? null,
                'discount_type'    => $data['discount_type'],
                'discount_value'   => $data['discount_value'],
                'payment_mode'     => $data['payment_mode'],
                'payment_reference'=> $data['payment_reference'] ?? null,
                'paid_amount'      => $data['paid_amount'],
                'notes'            => $data['notes'] ?? null,
                'status'           => 'completed',
            ]);

            foreach ($data['items'] as $itemData) {
                $batches = $this->selectBatches($itemData['medicine_id'], $itemData['quantity']);

                $remaining = $itemData['quantity'];
                foreach ($batches as $batch) {
                    $take = min($remaining, $batch->quantity_available);
                    if ($take <= 0) continue;

                    $gross    = $take * $itemData['unit_price'];
                    $discount = $gross * ($itemData['discount_percent'] / 100);
                    $taxable  = $gross - $discount;
                    $tax      = $taxable * ($itemData['tax_percent'] / 100);
                    $subtotal = round($taxable + $tax, 2);

                    SalesItem::create([
                        'sale_id'              => $sale->id,
                        'medicine_id'          => $itemData['medicine_id'],
                        'stock_batch_id'       => $batch->id,
                        'prescription_item_id' => $itemData['prescription_item_id'] ?? null,
                        'quantity'             => $take,
                        'unit_price'           => $itemData['unit_price'],
                        'discount_percent'     => $itemData['discount_percent'],
                        'tax_percent'          => $itemData['tax_percent'],
                        'subtotal'             => $subtotal,
                    ]);

                    $batch->deduct($take);
                    $remaining -= $take;
                }

                if ($remaining > 0) {
                    throw new \RuntimeException("Insufficient stock for medicine #{$itemData['medicine_id']}. Short by {$remaining} units.");
                }
            }

            $sale->recalculateTotals();

            $change = $data['paid_amount'] - $sale->total_amount;
            if ($change > 0) {
                $sale->update(['change_amount' => round($change, 2)]);
            }

            if ($data['prescription_id'] ?? null) {
                $this->updatePrescriptionStatus($data['prescription_id']);
            }

            return $sale;
        });
    }

    /**
     * Check drug interactions between selected medicines.
     */
    public function checkInteractions(array $medicineIds): Collection
    {
        $genericIds = Medicine::whereIn('id', $medicineIds)
            ->with('generic')
            ->get()
            ->pluck('generic_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return DrugInteraction::checkInteractions($genericIds);
    }

    /**
     * Select best batches (FEFO) for a medicine and quantity.
     */
    public function selectBatches(int $medicineId, int $quantity): Collection
    {
        $batches = StockBatch::fefo()
            ->where('medicine_id', $medicineId)
            ->get();

        $totalAvailable = $batches->sum('quantity_available');

        if ($totalAvailable < $quantity) {
            throw new \RuntimeException("Insufficient stock. Available: {$totalAvailable}, Requested: {$quantity}.");
        }

        return $batches;
    }

    /**
     * Generate a unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd');
        $last = Sales::whereDate('created_at', today())
            ->orderByDesc('id')
            ->value('invoice_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update prescription status when fully dispensed.
     */
    private function updatePrescriptionStatus(int $prescriptionId): void
    {
        $prescription = \App\Models\Pharmacy\Prescription::with('items')->find($prescriptionId);
        if (!$prescription) return;

        $allDispensed = $prescription->items->every(fn ($item) => $item->status === 'dispensed');

        if ($allDispensed) {
            $prescription->update([
                'status'       => 'dispensed',
                'dispensed_by' => auth()->id(),
                'dispensed_at' => now(),
            ]);
        } else {
            $prescription->update(['status' => 'partial']);
        }
    }
}
