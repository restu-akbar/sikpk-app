<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanProdiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name'          => 'Teknik Sipil',
                'study_programs' => [
                    ['name' => 'Teknik Konstruksi Sipil',                'degree_level' => 'D3'],
                    ['name' => 'Teknik Konstruksi Gedung',               'degree_level' => 'D3'],
                    ['name' => 'Teknik Perancangan Jalan dan Jembatan',  'degree_level' => 'D4'],
                    ['name' => 'Teknik Perawatan dan Perbaikan Gedung',  'degree_level' => 'D4'],
                    ['name' => 'Rekayasa Infrastruktur',                 'degree_level' => 'S2'],
                ],
            ],
            [
                'name'          => 'Teknik Mesin',
                'study_programs' => [
                    ['name' => 'Teknik Mesin',                                   'degree_level' => 'D3'],
                    ['name' => 'Teknik Aeronautika',                             'degree_level' => 'D3'],
                    ['name' => 'Teknik Perancangan dan Konstruksi Mesin',        'degree_level' => 'D4'],
                    ['name' => 'Proses Manufaktur',                              'degree_level' => 'D4'],
                ],
            ],
            [
                'name'          => 'Teknik Refrigerasi dan Tata Udara',
                'study_programs' => [
                    ['name' => 'Teknik Pendingin dan Tata Udara',  'degree_level' => 'D3'],
                    ['name' => 'Teknik Pendingin dan Tata Udara',  'degree_level' => 'D4'],
                ],
            ],
            [
                'name'          => 'Teknik Konversi Energi',
                'study_programs' => [
                    ['name' => 'Teknik Konversi Energi',               'degree_level' => 'D3'],
                    ['name' => 'Teknologi Pembangkit Tenaga Listrik',   'degree_level' => 'D4'],
                    ['name' => 'Teknik Konservasi Energi',              'degree_level' => 'D4'],
                ],
            ],
            [
                'name'          => 'Teknik Elektro',
                'study_programs' => [
                    ['name' => 'Teknik Elektronika',       'degree_level' => 'D3'],
                    ['name' => 'Teknik Listrik',            'degree_level' => 'D3'],
                    ['name' => 'Teknik Telekomunikasi',     'degree_level' => 'D3'],
                    ['name' => 'Teknik Elektronika',       'degree_level' => 'D4'],
                    ['name' => 'Teknik Telekomunikasi',     'degree_level' => 'D4'],
                    ['name' => 'Teknik Otomasi Industri',  'degree_level' => 'D4'],
                ],
            ],
            [
                'name'          => 'Teknik Kimia',
                'study_programs' => [
                    ['name' => 'Teknik Kimia',                  'degree_level' => 'D3'],
                    ['name' => 'Analis Kimia',                  'degree_level' => 'D3'],
                    ['name' => 'Teknik Kimia Produksi Bersih',  'degree_level' => 'D4'],
                ],
            ],
            [
                'name'          => 'Teknik Komputer dan Informatika',
                'study_programs' => [
                    ['name' => 'Teknik Informatika',  'degree_level' => 'D3'],
                    ['name' => 'Teknik Informatika',  'degree_level' => 'D4'],
                ],
            ],
            [
                'name'          => 'Akuntansi',
                'study_programs' => [
                    ['name' => 'Akuntansi',                              'degree_level' => 'D3'],
                    ['name' => 'Keuangan dan Perbankan',                 'degree_level' => 'D3'],
                    ['name' => 'Akuntansi Manajemen Pemerintahan',       'degree_level' => 'D4'],
                    ['name' => 'Akuntansi',                              'degree_level' => 'D4'],
                    ['name' => 'Keuangan Syariah',                       'degree_level' => 'D4'],
                    ['name' => 'Keuangan dan Perbankan Syariah',         'degree_level' => 'S2'],
                ],
            ],
            [
                'name'          => 'Administrasi Niaga',
                'study_programs' => [
                    ['name' => 'Administrasi Bisnis',                        'degree_level' => 'D3'],
                    ['name' => 'Manajemen Pemasaran',                        'degree_level' => 'D3'],
                    ['name' => 'Usaha Perjalanan Wisata',                    'degree_level' => 'D3'],
                    ['name' => 'Manajemen Aset',                             'degree_level' => 'D4'],
                    ['name' => 'Administrasi Bisnis',                        'degree_level' => 'D4'],
                    ['name' => 'Manajemen Pemasaran',                        'degree_level' => 'D4'],
                    ['name' => 'Destinasi Pariwisata',                       'degree_level' => 'D4'],
                    ['name' => 'Pemasaran, Inovasi, dan Teknologi',          'degree_level' => 'S2'],
                ],
            ],
            [
                'name'          => 'Bahasa Inggris',
                'study_programs' => [
                    ['name' => 'Bahasa Inggris',                                         'degree_level' => 'D3'],
                    ['name' => 'Bahasa Inggris untuk Komunikasi Bisnis dan Profesional', 'degree_level' => 'D4'],
                ],
            ],
        ];

        foreach ($data as $dept) {
            $studyPrograms = $dept['study_programs'];
            unset($dept['study_programs']);

            $deptId = DB::table('departments')->insertGetId([
                'name'       => $dept['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($studyPrograms as $program) {
                DB::table('study_programs')->insert([
                    'department_id' => $deptId,
                    'name'          => $program['name'],
                    'degree_level'  => $program['degree_level'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
