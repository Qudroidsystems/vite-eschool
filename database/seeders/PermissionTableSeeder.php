<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
        //    'Super role',
           'View user',
           'Create user',
           'Update user',
           'Delete user',
           'View role',
           'Create role',
           'Update role',
           'Delete role',
           'Add user-role',
           'Update user-role',
           'Remove user-role',
           'View permission',
           'Create permission',
           'Update permission',
           'Delete permission',
           'dashboard',
           'View session',
           'Create session',
           'Update session',
           'Delete session',
           'View term',
           'Create term',
           'Update term',
           'Delete term',
           'View schoolhouse',
           'Create schoolhouse',
           'Update schoolhouse',
           'Delete schoolhouse',
        //    'usermanagement-link',
        //    'staffmanagement-link',
        //    'basicsettingsmanagement-link',
        //    'apps-link',

        //    'class_operation-list',
        //    'class_operation-Create',
        //    'class_operation-edit',
        //    'class_operation-Delete',

        //    'myclass-list',
        //    'mysubject-list',

        //    'class_teacher-list',
        //    'class_teacher-Create',
        //    'class_teacher-edit',
        //    'class_teacher-Delete',

        //    'myresultroom-list',
        //    'myresultroom-creat',
        //    'myresultroom-edit',
        //    'myresultroom-Delete',

        //    'parent-list',
        //    'parent-Create',
        //    'parent-edit',
        //    'parent-Delete',

        //    'school_arm-list',
        //    'school_arm-Create',
        //    'school_arm-edit',
        //    'school_arm-Delete',

        //    'school_class-list',
        //    'school_class-Create',
        //    'school_class-edit',
        //    'school_class-Delete',

          

        

        //    'staff-list',
        //    'staff-Create',
        //    'staff-edit',
        //    'staff-Delete',

        //    'student-list',
        //    'student-Create',
        //    'student-edit',
        //    'student-Delete',
        //    'student_edit-edit',
        //    'student_Delete-Delete',

        //    'student_bulk-upload',
        //    'student_bulk-uploadsave',

        //    'studentresults-list',

        //    'subject_class-list',
        //    'subject_class-assign',
        //    'subject_class-edit',
        //    'subject_class-Delete',

        //    'subject_operation-list',
        //    'subject_operation-Create',
        //    'subject_operation-edit',
        //    'subject_operation-Delete',

        //    'subject-list',
        //    'subject-Create',
        //    'subject-edit',
        //    'subject-Delete',

        //    'subject_teacher-list',
        //    'subject_teacher-assign',
        //    'subject_teacher-edit',
        //    'subject_teacher-Delete',

        //    'View_student-list',

        //    'academic_operations-list',

        //    'student_picture-upload',

   

        //    'classcategory-list',
        //    'classcategory-Create',
        //    'classcategory-edit',
        //    'classcategory-Delete',

        //    'studenthouse-Create',
        //    'classsettings-Create',
        //    'classsettings-Delete',

        ];

        foreach ($permissions as $permission) {
            $str = $permission;
            $delimiter = ' ';
            $words = explode($delimiter, $str);

            foreach ($words as $word) {
                if($word == "user")
                Permission::Create(['name' => $permission,'title'=>"User Management"]);

                if($word == "role" || $word == "user-role")
                Permission::Create(['name' => $permission,'title'=>"Role Management"]);

                if($word == "permission")
                Permission::Create(['name' => $permission,'title'=>"Permission Management"]);

                if($word == "dashboard")
                Permission::Create(['name' => $permission,'title'=>"Dashboard Management"]);

                // if($word == "class_operation")
                // Permission::Create(['name' => $permission,'title'=>"Class Operation Management"]);

                // if($word == "myclass")
                // Permission::Create(['name' => $permission,'title'=>"User Class Management"]);

                // if($word == "mysubject")
                // Permission::Create(['name' => $permission,'title'=>"User Subject Management"]);

                // if($word == "class_teacher")
                // Permission::Create(['name' => $permission,'title'=>"Class Teacher Management"]);

                // if($word == "myresultroom")
                // Permission::Create(['name' => $permission,'title'=>"User Result Room Management"]);

                // if($word == "parent")
                // Permission::Create(['name' => $permission,'title'=>"Parent Management"]);

                // if($word == "school_arm")
                // Permission::Create(['name' => $permission,'title'=>"School Arm Management"]);

                // if($word == "school_class")
                // Permission::Create(['name' => $permission,'title'=>"School ClassManagement"]);

                if($word == "session")
                Permission::Create(['name' => $permission,'title'=>"School Session Management"]);

                if($word == "term")
                Permission::Create(['name' => $permission,'title'=>"School Term Management "]);

                // if($word == "student")
                // Permission::Create(['name' => $permission,'title'=>"Student Management"]);

                // if($word == "studentresults")
                // Permission::Create(['name' => $permission,'title'=>"Student Results Management"]);

                // if($word == "student_bulk")
                // Permission::Create(['name' => $permission,'title'=>"Student Management"]);

                // if($word == "subject_class")
                // Permission::Create(['name' => $permission,'title'=>"Subject Class Management"]);

                // if($word == "subject_operation")
                // Permission::Create(['name' => $permission,'title'=>"Subject Operations Management"]);

                // if($word == "subject")
                // Permission::Create(['name' => $permission,'title'=>"Subject Management"]);

                // if($word == "subject_teacher")
                // Permission::Create(['name' => $permission,'title'=>"Subject Teacher Management"]);

                // if($word == "View_student")
                // Permission::Create(['name' => $permission,'title'=>"View Student Management "]);

                // if($word == "academic_operations")
                // Permission::Create(['name' => $permission,'title'=>"Basic Settings Management Link"]);

                // if($word == "student_picture")
                // Permission::Create(['name' => $permission,'title'=>"Student Picture Management"]);

                if($word == "schoolhouse")
                Permission::Create(['name' => $permission,'title'=>"School House Management"]);

                // if($word == "classcategory")
                // Permission::Create(['name' => $permission,'title'=>"Class Category Management"]);

                // if($word == "studenthouse")
                // Permission::Create(['name' => $permission,'title'=>"Student house Management"]);

                // if($word == "classsettings")
                // Permission::Create(['name' => $permission,'title'=>"Class Settings Management "]);

            }
            //  Permission::Create(['name' => $permission]);
        }
    }
}
