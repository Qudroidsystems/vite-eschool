<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class StudentPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
           'View student',
           'Create student',
           'Update student',
           'Delete student',
           'student-edit-edit',
           'student-Delete-Delete',

           'Create student-bulk-upload',
           'Create student-bulk-uploadsave',

           'View student-results',
        ];

        foreach ($permissions as $permission) {
            $str = $permission;
            $delimiter = ' ';
            $words = explode($delimiter, $str);

            foreach ($words as $word) {
                if($word == "student")
                Permission::Create(['name' => $permission,'title'=>"Student  Management"]);
            }

            foreach ($words as $word) {
                if($word == "student-bulk")
                Permission::Create(['name' => $permission,'title'=>"Student bulk upload  Management"]);
            }

            foreach ($words as $word) {
                if($word == "student-results")
                Permission::Create(['name' => $permission,'title'=>"Student result list  Management"]);
            }
            //  Permission::Create(['name' => $permission]);
        }
    }
}
