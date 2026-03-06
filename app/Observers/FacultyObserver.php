<?php

namespace App\Observers;

use App\Models\Department;
use App\Models\Faculty;

class FacultyObserver
{
    public function created(Faculty $faculty): void
    {
        $code = strtoupper($faculty->code);

        $mandatory = [
            [
                'name'    => 'Students Care',
                'name_ar' => 'رعاية الطلاب',
                'code'    => $code . '-SC',
                'type'    => 'managerial',
            ],
            [
                'name'    => 'Students Affairs',
                'name_ar' => 'شؤون الطلاب',
                'code'    => $code . '-SA',
                'type'    => 'managerial',
            ],
            [
                'name'    => 'Legal',
                'name_ar' => 'الشؤون القانونية',
                'code'    => $code . '-LGL',
                'type'    => 'managerial',
            ],
            [
                'name'    => 'General',
                'name_ar' => 'القسم العام',
                'code'    => $code . '-GEN',
                'type'    => 'academic',
            ],
        ];

        foreach ($mandatory as $dept) {
            Department::create([
                'faculty_id'     => $faculty->id,
                'name'           => $dept['name'],
                'name_ar'        => $dept['name_ar'],
                'code'           => $dept['code'],
                'type'           => $dept['type'],
                'is_preparatory' => false,
                'head_id'        => null,
                'is_active'      => true,
                'is_mandatory'   => true,
            ]);
        }
    }
}
