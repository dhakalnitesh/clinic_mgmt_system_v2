<?php

namespace Database\Seeders;

use App\Models\Consultation\Consultation;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabOrderItem;
use App\Models\Laboratory\LabResult;
use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestParameter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LabOrderSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;
        $consultations = Consultation::whereNotNull('patient_id')->whereNotNull('doctor_id')->get();

        if ($consultations->isEmpty()) {
            $this->command->warn('No consultations found. Skipping lab orders.');
            return;
        }

        $tests = LabTest::pluck('id', 'code');

        $prescriptions = [
            ['tests' => ['CBC', 'BSF'], 'status' => 'completed'],
            ['tests' => ['CHOL', 'TG'], 'status' => 'completed'],
            ['tests' => ['CR', 'ALT', 'AST'], 'status' => 'processing'],
            ['tests' => ['UR', 'CBC'], 'status' => 'completed'],
            ['tests' => ['BSR', 'UA'], 'status' => 'completed'],
            ['tests' => ['HB', 'ESR'], 'status' => 'completed'],
            ['tests' => ['DEN', 'PLT'], 'status' => 'completed'],
            ['tests' => ['TBIL', 'ALT', 'AST'], 'status' => 'ordered'],
            ['tests' => ['TYPH', 'CBC'], 'status' => 'processing'],
            ['tests' => ['BSF', 'CHOL'], 'status' => 'completed'],
        ];

        $orderCount = 0;
        $resultCount = 0;

        foreach ($prescriptions as $i => $p) {
            $consultation = $consultations->skip($i)->first() ?? $consultations->random();
            if (!$consultation) continue;

            $orderDate = $consultation->consulted_at ? Carbon::parse($consultation->consulted_at) : Carbon::now()->subDays(count($prescriptions) - $i);

            $order = LabOrder::create([
                'consultation_id' => $consultation->id,
                'patient_id'      => $consultation->patient_id,
                'doctor_id'       => $consultation->doctor_id,
                'order_number'    => 'LAB-' . $orderDate->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . chr(rand(65, 90)),
                'status'          => $p['status'],
                'notes'           => 'Routine lab investigation',
                'created_by'      => $adminId,
                'created_at'      => $orderDate,
                'updated_at'      => $orderDate,
            ]);
            $orderCount++;

            foreach ($p['tests'] as $testCode) {
                $testId = $tests[$testCode] ?? null;
                if (!$testId) continue;

                $item = LabOrderItem::create([
                    'lab_order_id' => $order->id,
                    'lab_test_id'  => $testId,
                    'status'       => $p['status'] === 'completed' ? 'completed' : ($p['status'] === 'processing' ? 'processing' : 'ordered'),
                    'created_at'   => $orderDate,
                    'updated_at'   => $orderDate,
                ]);

                if ($p['status'] === 'completed') {
                    $params = LabTestParameter::where('lab_test_id', $testId)->where('is_active', true)->get();
                    foreach ($params as $param) {
                        LabResult::create([
                            'lab_order_item_id'     => $item->id,
                            'lab_test_parameter_id' => $param->id,
                            'result_value'          => $this->fakeResultValue($param),
                            'remarks'               => rand(0, 5) === 0 ? 'Borderline - clinical correlation advised' : null,
                            'created_at'            => $orderDate,
                            'updated_at'            => $orderDate,
                        ]);
                        $resultCount++;
                    }
                }
            }
        }

        $this->command->info("Lab orders seeded: {$orderCount} orders with {$resultCount} results");
    }

    private function fakeResultValue($param): string
    {
        $name = strtolower($param->name);
        $unit = strtolower($param->unit ?? '');

        if (str_contains($name, 'hemoglobin')) return number_format(rand(110, 160) / 10, 1);
        if (str_contains($name, 'rbc') && !str_contains($unit, 'hpf')) return number_format(rand(45, 55) / 10, 1);
        if (str_contains($name, 'wbc') && !str_contains($unit, 'hpf')) return (string) rand(5000, 9000);
        if (str_contains($name, 'leukocyte')) return (string) rand(5000, 9000);
        if (str_contains($name, 'platelet')) return (string) rand(200000, 350000);
        if (str_contains($name, 'neutrophil')) return (string) rand(50, 70);
        if (str_contains($name, 'lymphocyte')) return (string) rand(20, 35);
        if (str_contains($name, 'monocyte')) return (string) rand(3, 8);
        if (str_contains($name, 'eosinophil')) return (string) rand(1, 4);
        if (str_contains($name, 'basophil')) return '0';
        if (str_contains($name, 'esr')) return (string) rand(2, 12);
        if (str_contains($name, 'sugar') || str_contains($name, 'glucose')) return (string) rand(85, 140);
        if (str_contains($name, 'creatinine')) return number_format(rand(7, 11) / 10, 1);
        if (str_contains($name, 'uric acid')) return number_format(rand(35, 65) / 10, 1);
        if (str_contains($name, 'cholesterol')) return (string) rand(150, 220);
        if (str_contains($name, 'triglyceride')) return (string) rand(100, 180);
        if (str_contains($name, 'alt') || str_contains($name, 'sgpt')) return (string) rand(15, 35);
        if (str_contains($name, 'ast') || str_contains($name, 'sgot')) return (string) rand(15, 35);
        if (str_contains($name, 'bilirubin')) return number_format(rand(3, 10) / 10, 1);
        if (str_contains($name, 'ns1') || str_contains($name, 'igg') || str_contains($name, 'igm')) return 'Negative';
        if (str_contains($name, 'color')) return 'Yellow';
        if (str_contains($name, 'appearance')) return 'Clear';
        if (str_contains($name, 'specific gravity')) return '1.015';
        if (str_contains($name, 'ph')) return '6.0';
        if (str_contains($name, 'protein') || str_contains($name, 'ketone')) return 'Negative';
        if ($unit === '/hpf' && str_contains($name, 'rbc')) { $v = rand(0, 3); return $v === 3 ? '2-4' : (string) $v; }
        if ($unit === '/hpf' && str_contains($name, 'wbc')) { $v = rand(1, 8); return $v > 5 ? '6-8' : (string) $v; }
        if (str_contains($name, 'typhi') || str_contains($name, 'paratyphi')) {
            $titers = ['< 1:40', '1:40', '1:80', '1:160'];
            return $titers[array_rand($titers)];
        }
        if (str_contains($name, 'hematocrit')) return (string) rand(40, 48);
        if (str_contains($name, 'mcv')) return (string) rand(82, 95);
        if (str_contains($name, 'mch')) return (string) rand(28, 32);
        if (str_contains($name, 'mchc')) return (string) rand(33, 35);
        if (str_contains($name, 'rdw')) return number_format(rand(120, 140) / 10, 1);
        if (str_contains($name, 'hdl')) return (string) rand(35, 55);
        if (str_contains($name, 'ldl')) return (string) rand(70, 130);
        if (str_contains($name, 'band')) return (string) rand(0, 5);
        if (str_contains($name, 'meta')) return 'Absent';

        return (string) rand(1, 100);
    }
}
