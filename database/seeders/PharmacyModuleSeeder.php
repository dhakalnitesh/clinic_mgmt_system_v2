<?php

namespace Database\Seeders;

use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\GrnItem;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\PurchaseOrderItem;
use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyModuleSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;

        // ─── SUPPLIERS ─────────────────────────────────────────────
        $suppliers = [
            ['name' => 'Nepal Pharmaceutical Distributors', 'contact_person' => 'Ram Thapa',    'phone' => '9851001001', 'email' => 'info@npd.com.np',         'address' => 'Kathmandu'],
            ['name' => 'Himalaya Drug House',               'contact_person' => 'Sita Rai',      'phone' => '9851001002', 'email' => 'info@himalayadrug.com',   'address' => 'Lalitpur'],
            ['name' => 'Kathmandu Medical Suppliers',        'contact_person' => 'Gopal Sharma',  'phone' => '9851001003', 'email' => 'info@kms.com.np',         'address' => 'Kathmandu'],
            ['name' => 'Biratnagar Pharma Traders',          'contact_person' => 'Anil Yadav',    'phone' => '9851001004', 'email' => 'info@bpt.com.np',         'address' => 'Biratnagar'],
            ['name' => 'Pokhara Health Care Logistics',      'contact_person' => 'Krishna Pandey','phone' => '9851001005', 'email' => 'info@phcl.com.np',        'address' => 'Pokhara'],
        ];

        $supplierIds = [];
        foreach ($suppliers as $s) {
            $sup = Supplier::firstOrCreate(
                ['name' => $s['name']],
                [
                    'contact_person' => $s['contact_person'],
                    'phone' => $s['phone'],
                    'email' => $s['email'],
                    'address' => $s['address'],
                    'is_active' => true,
                ]
            );
            $supplierIds[] = $sup->id;
        }

        // ─── MEDICINE CATEGORIES ───────────────────────────────────
        $catNames = ['Antibiotics', 'Cardiovascular', 'Pain Management', 'Gastrointestinal', 'Respiratory', 'CNS', 'Vitamins & Supplements', 'Dermatology', 'Hormones', 'Ophthalmology'];
        $categoryIds = [];
        foreach ($catNames as $cn) {
            $cat = MedicineCategory::firstOrCreate(['name' => $cn], ['is_active' => true]);
            $categoryIds[$cn] = $cat->id;
        }

        // ─── GENERICS ──────────────────────────────────────────────
        $genericNames = ['Amoxicillin', 'Cefixime', 'Azithromycin', 'Omeprazole', 'Paracetamol', 'Ibuprofen', 'Amlodipine', 'Metformin', 'Atorvastatin', 'Losartan', 'Salbutamol', 'Cetirizine', 'Vitamin D3', 'Iron Sulphate', 'Metronidazole'];
        $genericIds = [];
        foreach ($genericNames as $gn) {
            $gen = Generic::firstOrCreate(['name' => $gn], ['is_active' => true]);
            $genericIds[$gn] = $gen->id;
        }

        // ─── UNITS ─────────────────────────────────────────────────
        $unitNames = ['Tablet' => 'Tab', 'Capsule' => 'Cap', 'Injection' => 'Inj', 'Syrup' => 'Syr', 'Cream' => 'Crm', 'Drops' => 'Drp', 'Inhaler' => 'Inh'];
        $unitIds = [];
        foreach ($unitNames as $name => $abbr) {
            $unit = MedicineUnit::firstOrCreate(['name' => $name], ['abbreviation' => $abbr]);
            $unitIds[$name] = $unit->id;
        }

        // ─── MEDICINES ─────────────────────────────────────────────
        $medicinesData = [
            ['name' => 'Amoxicillin 500mg',         'cat' => 'Antibiotics',         'generic' => 'Amoxicillin',     'unit' => 'Capsule', 'strength' => '500mg',   'form' => 'capsule',  'pur' => 15.00, 'sale' => 28.00, 'mrp' => 30.00, 'reorder' => 50,  'reorder_qty' => 200, 'controlled' => false],
            ['name' => 'Cefixime 200mg',            'cat' => 'Antibiotics',         'generic' => 'Cefixime',       'unit' => 'Tablet',  'strength' => '200mg',   'form' => 'tablet',   'pur' => 45.00, 'sale' => 85.00, 'mrp' => 90.00, 'reorder' => 30,  'reorder_qty' => 100, 'controlled' => false],
            ['name' => 'Azithromycin 500mg',        'cat' => 'Antibiotics',         'generic' => 'Azithromycin',   'unit' => 'Tablet',  'strength' => '500mg',   'form' => 'tablet',   'pur' => 35.00, 'sale' => 65.00, 'mrp' => 70.00, 'reorder' => 40,  'reorder_qty' => 150, 'controlled' => false],
            ['name' => 'Omeprazole 20mg',           'cat' => 'Gastrointestinal',    'generic' => 'Omeprazole',     'unit' => 'Capsule', 'strength' => '20mg',    'form' => 'capsule',  'pur' => 8.00,  'sale' => 15.00, 'mrp' => 18.00, 'reorder' => 100, 'reorder_qty' => 500, 'controlled' => false],
            ['name' => 'Paracetamol 500mg',         'cat' => 'Pain Management',     'generic' => 'Paracetamol',    'unit' => 'Tablet',  'strength' => '500mg',   'form' => 'tablet',   'pur' => 3.00,  'sale' => 6.00,  'mrp' => 8.00,  'reorder' => 200, 'reorder_qty' => 1000,'controlled' => false],
            ['name' => 'Ibuprofen 400mg',           'cat' => 'Pain Management',     'generic' => 'Ibuprofen',      'unit' => 'Tablet',  'strength' => '400mg',   'form' => 'tablet',   'pur' => 6.00,  'sale' => 12.00, 'mrp' => 15.00, 'reorder' => 100, 'reorder_qty' => 300, 'controlled' => false],
            ['name' => 'Amlodipine 5mg',            'cat' => 'Cardiovascular',      'generic' => 'Amlodipine',     'unit' => 'Tablet',  'strength' => '5mg',     'form' => 'tablet',   'pur' => 10.00, 'sale' => 20.00, 'mrp' => 22.00, 'reorder' => 60,  'reorder_qty' => 200, 'controlled' => false],
            ['name' => 'Metformin 500mg',           'cat' => 'Cardiovascular',      'generic' => 'Metformin',      'unit' => 'Tablet',  'strength' => '500mg',   'form' => 'tablet',   'pur' => 9.00,  'sale' => 18.00, 'mrp' => 20.00, 'reorder' => 80,  'reorder_qty' => 300, 'controlled' => false],
            ['name' => 'Atorvastatin 10mg',         'cat' => 'Cardiovascular',      'generic' => 'Atorvastatin',   'unit' => 'Tablet',  'strength' => '10mg',    'form' => 'tablet',   'pur' => 25.00, 'sale' => 48.00, 'mrp' => 50.00, 'reorder' => 40,  'reorder_qty' => 150, 'controlled' => false],
            ['name' => 'Losartan 50mg',             'cat' => 'Cardiovascular',      'generic' => 'Losartan',       'unit' => 'Tablet',  'strength' => '50mg',    'form' => 'tablet',   'pur' => 18.00, 'sale' => 35.00, 'mrp' => 38.00, 'reorder' => 50,  'reorder_qty' => 150, 'controlled' => false],
            ['name' => 'Salbutamol Inhaler',        'cat' => 'Respiratory',         'generic' => 'Salbutamol',     'unit' => 'Inhaler', 'strength' => '100mcg',  'form' => 'inhaler',  'pur' => 120.00,'sale' => 220.00,'mrp' => 250.00,'reorder' => 15,  'reorder_qty' => 50,  'controlled' => false],
            ['name' => 'Cetirizine 10mg',           'cat' => 'Respiratory',         'generic' => 'Cetirizine',     'unit' => 'Tablet',  'strength' => '10mg',    'form' => 'tablet',   'pur' => 4.00,  'sale' => 8.00,  'mrp' => 10.00, 'reorder' => 150, 'reorder_qty' => 500, 'controlled' => false],
            ['name' => 'Vitamin D3 60K IU',         'cat' => 'Vitamins & Supplements','generic' => 'Vitamin D3',  'unit' => 'Capsule', 'strength' => '60K IU',  'form' => 'capsule',  'pur' => 25.00, 'sale' => 50.00, 'mrp' => 55.00, 'reorder' => 30,  'reorder_qty' => 100, 'controlled' => false],
            ['name' => 'Iron Supplement',           'cat' => 'Vitamins & Supplements','generic' => 'Iron Sulphate','unit' => 'Tablet',  'strength' => '200mg',   'form' => 'tablet',   'pur' => 7.00,  'sale' => 14.00, 'mrp' => 16.00, 'reorder' => 80,  'reorder_qty' => 300, 'controlled' => false],
            ['name' => 'Metronidazole 400mg',       'cat' => 'Antibiotics',         'generic' => 'Metronidazole',  'unit' => 'Tablet',  'strength' => '400mg',   'form' => 'tablet',   'pur' => 8.00,  'sale' => 16.00, 'mrp' => 18.00, 'reorder' => 60,  'reorder_qty' => 200, 'controlled' => false],
        ];

        $medicineIds = [];
        foreach ($medicinesData as $m) {
            $med = Medicine::firstOrCreate(
                ['name' => $m['name']],
                [
                'medicine_category_id'     => $categoryIds[$m['cat']],
                'generic_id'               => $genericIds[$m['generic']],
                'medicine_unit_id'         => $unitIds[$m['unit']],
                'name'                     => $m['name'],
                'strength'                 => $m['strength'],
                'form'                     => $m['form'],
                'manufacturer'             => 'Nepal Pharma Ltd.',
                'barcode'                  => 'MED' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'purchase_price'           => $m['pur'],
                'sale_price'               => $m['sale'],
                'mrp'                      => $m['mrp'],
                'reorder_level'            => $m['reorder'],
                'reorder_quantity'         => $m['reorder_qty'],
                'is_prescription_required' => in_array($m['cat'], ['Antibiotics', 'Cardiovascular']),
                'is_controlled'            => $m['controlled'],
                'is_active'                => true,
            ]);
            $medicineIds[] = $med->id;
        }

        // ─── PURCHASE ORDERS & GOODS RECEIVED NOTES ───────────────
        $poStatuses = ['received', 'received', 'received', 'sent', 'draft'];

        foreach ($medicineIds as $idx => $medId) {
            // Every 3rd medicine gets a PO + GRN
            if ($idx % 3 !== 0) continue;

            $supplierId = $supplierIds[array_rand($supplierIds)];
            $status = 'received';
            $poDate = Carbon::now()->subDays(rand(10, 60));

            $po = PurchaseOrder::create([
                'po_number'              => 'PO-' . now()->format('Ymd') . '-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'supplier_id'            => $supplierId,
                'ordered_by'             => $adminId,
                'order_date'             => $poDate,
                'expected_delivery_date' => $poDate->copy()->addDays(rand(3, 7)),
                'status'                 => $status,
                'subtotal'               => 0,
                'total_amount'           => 0,
                'notes'                  => 'Routine stock order',
            ]);

            $med = Medicine::find($medId);
            $qty = rand(50, 200);
            $lineTotal = $qty * $med->purchase_price;
            $po->update([
                'subtotal'     => $lineTotal,
                'total_amount' => $lineTotal,
            ]);

            $poItem = PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'medicine_id'       => $medId,
                'quantity_ordered'  => $qty,
                'quantity_received' => $qty,
                'unit_price'        => $med->purchase_price,
                'subtotal'          => $lineTotal,
            ]);

            // GRN
            $grn = GoodsReceivedNote::create([
                'grn_number'        => 'GRN-' . now()->format('Ymd') . '-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'purchase_order_id' => $po->id,
                'supplier_id'       => $supplierId,
                'received_date'     => $poDate->copy()->addDays(rand(1, 5)),
                'status'            => 'posted',
                'notes'             => 'All items received in good condition',
                'received_by'       => $adminId,
            ]);

            $grnItem = GrnItem::create([
                'goods_received_note_id' => $grn->id,
                'purchase_order_item_id' => $poItem->id,
                'medicine_id'            => $medId,
                'batch_number'           => 'BATCH-' . strtoupper(substr(md5(rand()), 0, 8)),
                'manufacturing_date'     => $poDate->copy()->subMonths(rand(1, 6)),
                'expiry_date'            => $poDate->copy()->addYears(rand(1, 2))->addMonths(rand(0, 6)),
                'quantity_received'      => $qty,
                'unit_price'             => $med->purchase_price,
                'sale_price'             => $med->sale_price,
                'mrp'                    => $med->mrp,
            ]);

            // Stock Batch
            StockBatch::create([
                'medicine_id'             => $medId,
                'supplier_id'             => $supplierId,
                'goods_received_note_id'  => $grn->id,
                'batch_number'            => $grnItem->batch_number,
                'manufacturing_date'      => $grnItem->manufacturing_date,
                'expiry_date'             => $grnItem->expiry_date,
                'quantity_received'       => $qty,
                'quantity_available'      => $qty,
                'quantity_sold'           => 0,
                'purchase_price'          => $med->purchase_price,
                'sale_price'              => $med->sale_price,
                'mrp'                     => $med->mrp,
                'is_active'               => true,
            ]);
        }

        $this->command->info('Pharmacy module seeded: ' . count($suppliers) . ' suppliers, ' . count($categoryIds) . ' categories, ' . count($genericIds) . ' generics, ' . count($medicinesData) . ' medicines, 5 purchase orders + GRNs + stock batches');
    }
}
