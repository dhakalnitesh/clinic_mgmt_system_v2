<?php

namespace Database\Seeders;

use App\Models\Doctor\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NepalAddressSeeder::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'view patients', 'create patients', 'edit patients', 'delete patients',
            'view doctors', 'create doctors', 'edit doctors', 'delete doctors',
            'view appointments', 'create appointments', 'edit appointments', 'delete appointments',
            'view visits', 'create visits', 'edit visits', 'delete visits',
            'view consultations', 'create consultations', 'edit consultations',
            'view invoices', 'create invoices', 'edit invoices',
            'view payments', 'create payments',
            'view reports',
            'view users', 'create users', 'edit users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $doctor = Role::firstOrCreate(['name' => 'doctor']);
        $doctor->givePermissionTo([
            'view dashboard',
            'view patients', 'view appointments',
            'view visits', 'create visits', 'edit visits',
            'view consultations', 'create consultations', 'edit consultations',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->givePermissionTo([
            'view dashboard',
            'view patients', 'create patients', 'edit patients',
            'view appointments', 'create appointments',
            'view visits', 'create visits',
            'view invoices', 'create invoices',
            'view payments', 'create payments',
        ]);

        // ─── USERS ───────────────────────────────────────────────
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');

        $staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('Staff@123'),
            'email_verified_at' => now(),
        ]);
        $staffUser->assignRole('staff');

        // ─── DOCTORS ─────────────────────────────────────────────
        $doctorsData = [
            ['name' => 'Dr. Ram Sharma',       'nmc_number' => 'NMC-001', 'specialization' => 'General Physician',  'phone' => '9841234567', 'consultation_fee' => 500],
            ['name' => 'Dr. Sita Devi',         'nmc_number' => 'NMC-002', 'specialization' => 'Gynecologist',       'phone' => '9841234568', 'consultation_fee' => 800],
            ['name' => 'Dr. Rajesh Hamal',      'nmc_number' => 'NMC-003', 'specialization' => 'Cardiologist',       'phone' => '9841234569', 'consultation_fee' => 1200],
            ['name' => 'Dr. Anju Pradhan',      'nmc_number' => 'NMC-004', 'specialization' => 'Pediatrician',       'phone' => '9841234570', 'consultation_fee' => 600],
            ['name' => 'Dr. Krishna Thapa',     'nmc_number' => 'NMC-005', 'specialization' => 'Orthopedic Surgeon', 'phone' => '9841234571', 'consultation_fee' => 1000],
        ];

        $doctorUser = User::create([
            'name' => 'Dr. Ram Sharma',
            'email' => 'doctor@gmail.com',
            'password' => Hash::make('Doctor@123'),
            'email_verified_at' => now(),
        ]);
        $doctorUser->assignRole('doctor');

        $doctorIds = [];
        foreach ($doctorsData as $i => $d) {
            $doc = Doctor::create([
                'user_id'          => $i === 0 ? $doctorUser->id : null,
                'name'             => $d['name'],
                'nmc_number'       => $d['nmc_number'],
                'specialization'   => $d['specialization'],
                'phone'            => $d['phone'],
                'consultation_fee' => $d['consultation_fee'],
                'address1'         => 'Kathmandu, Nepal',
            ]);
            $doctorIds[] = $doc->id;
        }

        // Doctor schedules
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        foreach ($doctorIds as $did) {
            foreach ($days as $day) {
                DB::table('doctor_schedules')->insert([
                    'doctor_id'  => $did,
                    'day'        => $day,
                    'start_time' => '09:00',
                    'end_time'   => '17:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ─── PATIENTS ────────────────────────────────────────────
        $patientsData = [
            ['name' => 'Hari Bahadur KC',      'phone' => '9841000001', 'age' => 45, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 3, 'district_id' => 30, 'municipal_id' => 1,  'address1' => 'Baneshwor'],
            ['name' => 'Gita Gurung',          'phone' => '9841000002', 'age' => 32, 'gender' => 'Female', 'citizenship_type' => 'nepali', 'province_id' => 3, 'district_id' => 28, 'municipal_id' => 11, 'address1' => 'Patan'],
            ['name' => 'Surya Man Tamang',     'phone' => '9841000003', 'age' => 28, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 1, 'district_id' => 14, 'municipal_id' => 21, 'address1' => 'Dharan-8'],
            ['name' => 'Radha Acharya',        'phone' => '9841000004', 'age' => 55, 'gender' => 'Female', 'citizenship_type' => 'nepali', 'province_id' => 5, 'district_id' => 54, 'municipal_id' => 28, 'address1' => 'Butwal-10'],
            ['name' => 'Mohan Pandey',         'phone' => '9841000005', 'age' => 60, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 4, 'district_id' => 40, 'municipal_id' => 32, 'address1' => 'Pokhara-5'],
            ['name' => 'Shanti Rai',           'phone' => '9841000006', 'age' => 27, 'gender' => 'Female', 'citizenship_type' => 'nepali', 'province_id' => 1, 'district_id' => 13, 'municipal_id' => 19, 'address1' => 'Biratnagar-3'],
            ['name' => 'Bishnu Adhikari',      'phone' => '9841000007', 'age' => 38, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 3, 'district_id' => 35, 'municipal_id' => 33, 'address1' => 'Bharatpur-2'],
            ['name' => 'Laxmi Shrestha',       'phone' => '9841000008', 'age' => 50, 'gender' => 'Female', 'citizenship_type' => 'nepali', 'province_id' => 3, 'district_id' => 30, 'municipal_id' => 1,  'address1' => 'New Baneshwor'],
            ['name' => 'Kiran Bhattarai',      'phone' => '9841000009', 'age' => 22, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 5, 'district_id' => 57, 'municipal_id' => 39, 'address1' => 'Nepalgunj-6'],
            ['name' => 'Sunita Magar',         'phone' => '9841000010', 'age' => 35, 'gender' => 'Female', 'citizenship_type' => 'foreign', 'province_id' => 7, 'district_id' => 77, 'municipal_id' => 41, 'address1' => 'Dhangadhi-4'],
            ['name' => 'Prakash Oli',          'phone' => '9841000011', 'age' => 48, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 6, 'district_id' => 68, 'municipal_id' => 40, 'address1' => 'Birendranagar'],
            ['name' => 'Deepa Karki',          'phone' => '9841000012', 'age' => 29, 'gender' => 'Female', 'citizenship_type' => 'nepali', 'province_id' => 2, 'district_id' => 22, 'municipal_id' => 48, 'address1' => 'Birgunj'],
            ['name' => 'Rameshwor Yadav',      'phone' => '9841000013', 'age' => 42, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 2, 'district_id' => 17, 'municipal_id' => 45, 'address1' => 'Janakpur'],
            ['name' => 'Anita Thapa',          'phone' => '9841000014', 'age' => 31, 'gender' => 'Female', 'citizenship_type' => 'nepali', 'province_id' => 4, 'district_id' => 40, 'municipal_id' => 32, 'address1' => 'Pokhara-12'],
            ['name' => 'Gopal Bahadur Shah',   'phone' => '9841000015', 'age' => 65, 'gender' => 'Male',   'citizenship_type' => 'nepali', 'province_id' => 3, 'district_id' => 30, 'municipal_id' => 6,  'address1' => 'Gokarneshwar'],
        ];

        $patientIds = [];
        foreach ($patientsData as $p) {
            $pat = \App\Models\Patient\Patient::create($p);
            $patientIds[] = $pat->id;
        }

        // ─── APPOINTMENTS ────────────────────────────────────────
        $statuses = ['waiting', 'visited', 'waiting', 'visited', 'visited'];
        $appointmentIds = [];
        $usedAppointments = [];
        for ($i = 0; $i < 20; $i++) {
            do {
                $pid = $patientIds[array_rand($patientIds)];
                $did = $doctorIds[array_rand($doctorIds)];
                $date = Carbon::now()->subDays(rand(0, 14))->format('Y-m-d');
                $key = "{$pid}-{$did}-{$date}";
            } while (isset($usedAppointments[$key]));
            $usedAppointments[$key] = true;

            $time = sprintf('%02d:%02d', rand(9, 16), rand(0, 3) * 15);
            $stat = $statuses[array_rand($statuses)];

            $id = DB::table('appointments')->insertGetId([
                'patient_id'       => $pid,
                'doctor_id'        => $did,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'status'           => $stat,
                'reasons'          => 'Regular checkup and consultation',
                'created_at'       => now()->subDays(rand(0, 20)),
                'updated_at'       => now(),
            ]);
            $appointmentIds[] = $id;
        }

        // ─── VISITS ──────────────────────────────────────────────
        $visitStatuses = ['completed', 'completed', 'completed', 'waiting', 'in_consultation'];
        $visitIds = [];
        for ($i = 0; $i < 25; $i++) {
            $pid = $patientIds[array_rand($patientIds)];
            $did = $doctorIds[array_rand($doctorIds)];
            $date = Carbon::now()->subDays(rand(0, 30));

            $id = DB::table('visits')->insertGetId([
                'patient_id'      => $pid,
                'doctor_id'       => $did,
                'chief_complaint' => $this->fakeComplaint(),
                'visit_type'      => rand(0, 3) > 0 ? 'walk_in' : 'follow_up',
                'status'          => $visitStatuses[array_rand($visitStatuses)],
                'visited_at'      => $date,
                'created_at'      => $date,
                'updated_at'      => $date,
            ]);
            $visitIds[] = $id;
        }

        // ─── CONSULTATIONS ──────────────────────────────────────
        $complaints = [
            'Fever and cough for 3 days',
            'Lower back pain since 2 weeks',
            'Headache and dizziness',
            'Stomach ache and indigestion',
            'Skin rash on arms and legs',
            'Joint pain in knees',
            'Sore throat and difficulty swallowing',
            'Chest pain during exercise',
            'Eye redness and itching',
            'Ear pain and reduced hearing',
        ];
        $diagnoses = [
            'Upper respiratory tract infection',
            'Muscle strain - conservative management',
            'Tension headache - stress related',
            'Acute gastritis - prescribe antacids',
            'Contact dermatitis - allergic reaction',
            'Osteoarthritis - both knees',
            'Acute pharyngitis - bacterial',
            'Angina - referred to cardiology',
            'Allergic conjunctivitis',
            'Otitis media - prescribe antibiotics',
        ];

        foreach ($visitIds as $vid) {
            $visit = DB::table('visits')->where('id', $vid)->first();
            if (!$visit) continue;
            if (rand(0, 2) > 0) continue; // ~66% get a consultation

            $ci = array_rand($complaints);

            DB::table('consultations')->insert([
                'visit_id'            => $vid,
                'patient_id'          => $visit->patient_id,
                'doctor_id'           => $visit->doctor_id,
                'chief_complaint'     => $complaints[$ci],
                'diagnosis'           => $diagnoses[$ci],
                'consultation_status' => 'completed',
                'consulted_at'        => $visit->visited_at,
                'notes'               => 'Patient advised rest and prescribed medications',
                'created_at'          => $visit->created_at,
                'updated_at'          => $visit->created_at,
            ]);
        }

        // ─── FOLLOW-UPS ──────────────────────────────────────────
        $followStatuses = ['pending', 'completed', 'cancelled'];
        for ($i = 0; $i < 10; $i++) {
            $pid = $patientIds[array_rand($patientIds)];
            $did = $doctorIds[array_rand($doctorIds)];
            $status = $followStatuses[array_rand($followStatuses)];
            $date = Carbon::now()->addDays(rand(3, 30));

            DB::table('follow_ups')->insert([
                'patient_id'    => $pid,
                'doctor_id'     => $did,
                'follow_up_date' => $date,
                'notes'         => 'Follow-up for medication review',
                'status'        => $status,
                'completed_at'  => $status === 'completed' ? Carbon::now() : null,
                'created_by'    => $adminUser->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // ─── INVOICES & PAYMENTS ────────────────────────────────
        $services = [
            ['description' => 'Consultation Fee',        'qty' => 1, 'price' => 500],
            ['description' => 'Blood Test - CBC',         'qty' => 1, 'price' => 350],
            ['description' => 'X-Ray Chest',              'qty' => 1, 'price' => 800],
            ['description' => 'ECG',                      'qty' => 1, 'price' => 600],
            ['description' => 'Medicine - Amoxicillin',   'qty' => 2, 'price' => 120],
            ['description' => 'Medicine - Paracetamol',   'qty' => 1, 'price' => 50],
            ['description' => 'Ultrasound Abdomen',       'qty' => 1, 'price' => 1500],
            ['description' => 'Urine Test',               'qty' => 1, 'price' => 200],
            ['description' => 'Bandage & Dressing',       'qty' => 1, 'price' => 150],
            ['description' => 'Vitamin B12 Injection',   'qty' => 1, 'price' => 250],
        ];

        for ($i = 0; $i < 15; $i++) {
            $pid = $patientIds[array_rand($patientIds)];
            $numServices = rand(1, 4);
            $selected = array_rand($services, $numServices);
            if (!is_array($selected)) $selected = [$selected];

            $subtotal = 0;
            $items = [];
            foreach ($selected as $si) {
                $s = $services[$si];
                $lineTotal = $s['qty'] * $s['price'];
                $subtotal += $lineTotal;
                $items[] = [
                    'description' => $s['description'],
                    'quantity'    => $s['qty'],
                    'unit_price'  => $s['price'],
                    'total_price' => $lineTotal,
                ];
            }

            $discount = rand(0, 2) > 0 ? 0 : rand(50, 200);
            $total = max(0, $subtotal - $discount);
            $invStatus = ['pending', 'paid', 'paid', 'partial'][array_rand([0, 1, 1, 2])];

            $invNum = 'INV-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . chr(rand(65, 90));

            $invId = DB::table('invoices')->insertGetId([
                'invoice_number' => $invNum,
                'patient_id'     => $pid,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'status'         => $invStatus,
                'created_by'     => $adminUser->id,
                'created_at'     => now()->subDays(rand(0, 20)),
                'updated_at'     => now(),
            ]);

            foreach ($items as $item) {
                DB::table('invoice_items')->insert(array_merge($item, [
                    'invoice_id' => $invId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // Payments
            if ($invStatus !== 'pending') {
                $paidAmt = $invStatus === 'paid' ? $total : round($total * 0.5, 2);
                DB::table('payments')->insert([
                    'invoice_id'    => $invId,
                    'amount'        => $paidAmt,
                    'payment_method' => ['cash', 'card', 'online'][array_rand([0, 0, 1])],
                    'payment_date'  => now()->subDays(rand(0, 5)),
                    'created_by'    => $adminUser->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                if ($invStatus === 'paid') {
                    DB::table('invoices')->where('id', $invId)->update(['paid_amount' => $total]);
                } else {
                    DB::table('invoices')->where('id', $invId)->update(['paid_amount' => $paidAmt]);
                }
            }
        }
    }

    private function fakeComplaint(): string
    {
        $list = [
            'Fever with chills since 2 days',
            'Persistent cough with phlegm',
            'Severe headache and nausea',
            'Lower abdominal pain',
            'Difficulty breathing',
            'Skin infection on right arm',
            'Sudden chest pain',
            'Painful urination',
            'Swollen ankle after fall',
            'General weakness and fatigue',
            'Blurred vision in left eye',
            'Numbness in right hand',
            'Recurring back pain',
            'High blood pressure reading at home',
            'Diarrhea and vomiting since yesterday',
        ];
        return $list[array_rand($list)];
    }
}
