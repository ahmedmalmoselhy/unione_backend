<?php

namespace App\Observers;

use App\Models\AuditLog;
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
        ];

        // Only create a general academic department for deferred-enrollment faculties,
        // where students need a holding department before being assigned to a specific one.
        if ($faculty->enrollment_type === 'deferred') {
            $mandatory[] = [
                'name'    => 'General',
                'name_ar' => 'القسم العام',
                'code'    => $code . '-GEN',
                'type'    => 'academic',
            ];
        }

        foreach ($mandatory as $dept) {
            $department = Department::create([
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

            AuditLog::record(
                action: 'created',
                auditableType: 'Department',
                auditableId: $department->id,
                description: "Auto-created mandatory department {$department->name} for faculty {$faculty->name}",
                newValues: ['name' => $department->name, 'code' => $department->code, 'type' => $department->type, 'faculty_id' => $faculty->id],
            );
        }
    }
}
