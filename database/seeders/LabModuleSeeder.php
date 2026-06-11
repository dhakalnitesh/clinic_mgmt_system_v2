<?php

namespace Database\Seeders;

use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestCategory;
use App\Models\Laboratory\LabTestParameter;
use Illuminate\Database\Seeder;

class LabModuleSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;

        // ─── LAB TEST CATEGORIES ──────────────────────────────────
        $categories = [
            ['name' => 'Hematology',     'code' => 'HEMA', 'description' => 'Blood cell analysis'],
            ['name' => 'Biochemistry',   'code' => 'BIO',  'description' => 'Chemical analysis of blood/fluids'],
            ['name' => 'Microbiology',   'code' => 'MICRO','description' => 'Infectious disease testing'],
            ['name' => 'Serology',       'code' => 'SERO', 'description' => 'Antibody/antigen testing'],
            ['name' => 'Urinalysis',     'code' => 'URIN', 'description' => 'Urine analysis'],
            ['name' => 'Coagulation',    'code' => 'COAG', 'description' => 'Blood clotting tests'],
        ];

        $categoryIds = [];
        foreach ($categories as $c) {
            $cat = LabTestCategory::create([
                'name' => $c['name'],
                'code' => $c['code'],
                'description' => $c['description'],
                'is_active' => true,
                'created_by' => $adminId,
            ]);
            $categoryIds[$c['code']] = $cat->id;
        }

        // ─── LAB TESTS ────────────────────────────────────────────
        $tests = [
            // Hematology
            ['name' => 'Complete Blood Count',          'code' => 'CBC',  'cat' => 'HEMA', 'price' => 350],
            ['name' => 'Hemoglobin',                     'code' => 'HB',   'cat' => 'HEMA', 'price' => 150],
            ['name' => 'Total Leukocyte Count',          'code' => 'TLC',  'cat' => 'HEMA', 'price' => 200],
            ['name' => 'Platelet Count',                 'code' => 'PLT',  'cat' => 'HEMA', 'price' => 200],
            ['name' => 'Erythrocyte Sedimentation Rate', 'code' => 'ESR',  'cat' => 'HEMA', 'price' => 150],
            // Biochemistry
            ['name' => 'Blood Sugar Fasting',            'code' => 'BSF',  'cat' => 'BIO',  'price' => 120],
            ['name' => 'Blood Sugar Random',             'code' => 'BSR',  'cat' => 'BIO',  'price' => 120],
            ['name' => 'Serum Creatinine',               'code' => 'CR',   'cat' => 'BIO',  'price' => 200],
            ['name' => 'Serum Uric Acid',                'code' => 'UA',   'cat' => 'BIO',  'price' => 250],
            ['name' => 'Total Cholesterol',              'code' => 'CHOL', 'cat' => 'BIO',  'price' => 300],
            ['name' => 'Triglycerides',                  'code' => 'TG',   'cat' => 'BIO',  'price' => 300],
            ['name' => 'ALT (SGPT)',                     'code' => 'ALT',  'cat' => 'BIO',  'price' => 250],
            ['name' => 'AST (SGOT)',                     'code' => 'AST',  'cat' => 'BIO',  'price' => 250],
            ['name' => 'Total Bilirubin',                'code' => 'TBIL', 'cat' => 'BIO',  'price' => 200],
            // Urinalysis
            ['name' => 'Urine Routine',                  'code' => 'UR',   'cat' => 'URIN', 'price' => 150],
            ['name' => 'Urine Culture & Sensitivity',    'code' => 'UCS',  'cat' => 'URIN', 'price' => 400],
            // Serology
            ['name' => 'Typhoid (Widal Test)',           'code' => 'TYPH', 'cat' => 'SERO', 'price' => 300],
            ['name' => 'Dengue NS1 Antigen',             'code' => 'DEN',  'cat' => 'SERO', 'price' => 600],
            ['name' => 'HBsAg (Hepatitis B)',            'code' => 'HBS',  'cat' => 'SERO', 'price' => 350],
            ['name' => 'HIV Serology',                   'code' => 'HIV',  'cat' => 'SERO', 'price' => 400],
        ];

        $testIds = [];
        foreach ($tests as $t) {
            $test = LabTest::create([
                'name' => $t['name'],
                'code' => $t['code'],
                'lab_test_category_id' => $categoryIds[$t['cat']],
                'price' => $t['price'],
                'is_active' => true,
                'created_by' => $adminId,
            ]);
            $testIds[$t['code']] = $test->id;
        }

        // ─── LAB TEST PARAMETERS ─────────────────────────────────
        $params = [
            'CBC' => [
                ['name' => 'Hemoglobin',          'unit' => 'g/dL',    'range' => '13.0 - 17.0',     'order' => 1],
                ['name' => 'RBC Count',           'unit' => 'M/μL',   'range' => '4.5 - 5.5',       'order' => 2],
                ['name' => 'Hematocrit',          'unit' => '%',      'range' => '40 - 50',         'order' => 3],
                ['name' => 'MCV',                 'unit' => 'fL',     'range' => '80 - 100',        'order' => 4],
                ['name' => 'MCH',                 'unit' => 'pg',     'range' => '27 - 32',         'order' => 5],
                ['name' => 'MCHC',                'unit' => 'g/dL',   'range' => '32 - 36',         'order' => 6],
                ['name' => 'RDW',                 'unit' => '%',      'range' => '11.5 - 14.5',     'order' => 7],
                ['name' => 'Total WBC Count',     'unit' => '/μL',    'range' => '4000 - 11000',   'order' => 8],
                ['name' => 'Neutrophils',         'unit' => '%',      'range' => '40 - 80',        'order' => 9],
                ['name' => 'Lymphocytes',         'unit' => '%',      'range' => '20 - 40',        'order' => 10],
                ['name' => 'Monocytes',           'unit' => '%',      'range' => '2 - 10',         'order' => 11],
                ['name' => 'Eosinophils',         'unit' => '%',      'range' => '1 - 6',          'order' => 12],
                ['name' => 'Basophils',           'unit' => '%',      'range' => '0 - 1',          'order' => 13],
                ['name' => 'Platelet Count',      'unit' => '/μL',    'range' => '150000 - 450000','order' => 14],
            ],
            'HB' => [
                ['name' => 'Hemoglobin',          'unit' => 'g/dL',   'range' => '13.0 - 17.0',     'order' => 1],
            ],
            'TLC' => [
                ['name' => 'Total WBC Count',     'unit' => '/μL',    'range' => '4000 - 11000',   'order' => 1],
            ],
            'PLT' => [
                ['name' => 'Platelet Count',      'unit' => '/μL',    'range' => '150000 - 450000','order' => 1],
            ],
            'ESR' => [
                ['name' => 'ESR',                 'unit' => 'mm/hr',  'range' => '0 - 15',          'order' => 1],
            ],
            'BSF' => [
                ['name' => 'Blood Sugar Fasting', 'unit' => 'mg/dL',  'range' => '70 - 110',        'order' => 1],
            ],
            'BSR' => [
                ['name' => 'Blood Sugar Random',  'unit' => 'mg/dL',  'range' => '< 140',           'order' => 1],
            ],
            'CR' => [
                ['name' => 'Creatinine',          'unit' => 'mg/dL',  'range' => '0.6 - 1.2',       'order' => 1],
            ],
            'UA' => [
                ['name' => 'Uric Acid',           'unit' => 'mg/dL',  'range' => '3.4 - 7.0',       'order' => 1],
            ],
            'CHOL' => [
                ['name' => 'Total Cholesterol',   'unit' => 'mg/dL',  'range' => '< 200',           'order' => 1],
                ['name' => 'HDL Cholesterol',    'unit' => 'mg/dL',  'range' => '> 40',            'order' => 2],
                ['name' => 'LDL Cholesterol',    'unit' => 'mg/dL',  'range' => '< 100',           'order' => 3],
            ],
            'TG' => [
                ['name' => 'Triglycerides',       'unit' => 'mg/dL',  'range' => '< 150',           'order' => 1],
            ],
            'ALT' => [
                ['name' => 'ALT (SGPT)',          'unit' => 'U/L',    'range' => '10 - 40',         'order' => 1],
            ],
            'AST' => [
                ['name' => 'AST (SGOT)',          'unit' => 'U/L',    'range' => '10 - 40',         'order' => 1],
            ],
            'TBIL' => [
                ['name' => 'Total Bilirubin',     'unit' => 'mg/dL',  'range' => '0.1 - 1.2',       'order' => 1],
                ['name' => 'Direct Bilirubin',    'unit' => 'mg/dL',  'range' => '0.0 - 0.3',       'order' => 2],
                ['name' => 'Indirect Bilirubin',  'unit' => 'mg/dL',  'range' => '0.1 - 0.9',       'order' => 3],
            ],
            'UR' => [
                ['name' => 'Color',               'unit' => '',       'range' => 'Yellow',          'order' => 1],
                ['name' => 'Appearance',           'unit' => '',       'range' => 'Clear',           'order' => 2],
                ['name' => 'Specific Gravity',     'unit' => '',       'range' => '1.005 - 1.030',  'order' => 3],
                ['name' => 'pH',                   'unit' => '',       'range' => '4.5 - 8.0',       'order' => 4],
                ['name' => 'Protein',              'unit' => 'mg/dL',  'range' => 'Negative',        'order' => 5],
                ['name' => 'Glucose',              'unit' => 'mg/dL',  'range' => 'Negative',        'order' => 6],
                ['name' => 'Ketones',              'unit' => 'mg/dL',  'range' => 'Negative',        'order' => 7],
                ['name' => 'RBC',                  'unit' => '/HPF',   'range' => '0 - 2',           'order' => 8],
                ['name' => 'WBC',                  'unit' => '/HPF',   'range' => '0 - 5',           'order' => 9],
            ],
            'TYPH' => [
                ['name' => 'S. Typhi O',          'unit' => 'Titer',  'range' => '< 1:80',          'order' => 1],
                ['name' => 'S. Typhi H',          'unit' => 'Titer',  'range' => '< 1:80',          'order' => 2],
                ['name' => 'S. Paratyphi AH',     'unit' => 'Titer',  'range' => '< 1:80',          'order' => 3],
                ['name' => 'S. Paratyphi BH',     'unit' => 'Titer',  'range' => '< 1:80',          'order' => 4],
            ],
            'DEN' => [
                ['name' => 'NS1 Antigen',          'unit' => '',       'range' => 'Negative',        'order' => 1],
                ['name' => 'IgG Antibody',         'unit' => '',       'range' => 'Negative',        'order' => 2],
                ['name' => 'IgM Antibody',         'unit' => '',       'range' => 'Negative',        'order' => 3],
            ],
        ];

        foreach ($params as $testCode => $testParams) {
            $testId = $testIds[$testCode] ?? null;
            if (!$testId) continue;
            foreach ($testParams as $p) {
                LabTestParameter::create([
                    'lab_test_id' => $testId,
                    'name' => $p['name'],
                    'unit' => $p['unit'],
                    'reference_range' => $p['range'],
                    'display_order' => $p['order'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Lab module seeded: ' . count($categories) . ' categories, ' . count($tests) . ' tests, ' . LabTestParameter::count() . ' parameters');
    }
}
