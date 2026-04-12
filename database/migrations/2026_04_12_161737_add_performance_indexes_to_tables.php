<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add strategic database indexes for frequently queried columns
     * to improve performance of transcripts, analytics, and common lookups.
     * Only adds indexes that don't already exist in original migrations.
     */
    public function up(): void
    {
        // Students - enrollment and lookup queries
        Schema::table('students', function (Blueprint $table) {
            $table->index('student_number');
            $table->index('faculty_id');
            $table->index('department_id');
            $table->index(['faculty_id', 'department_id']); // Composite
            $table->index('enrollment_status');
            $table->index('academic_year');
        });

        // Professors - lookup queries
        Schema::table('professors', function (Blueprint $table) {
            $table->index('staff_number');
            $table->index('department_id');
        });

        // Employees - lookup queries
        Schema::table('employees', function (Blueprint $table) {
            $table->index('staff_number');
        });

        // Courses - catalog queries
        Schema::table('courses', function (Blueprint $table) {
            $table->index('code');
            $table->index('is_active');
            $table->index('level');
        });

        // Sections - enrollment and scheduling
        Schema::table('sections', function (Blueprint $table) {
            // Note: ['course_id', 'academic_year', 'semester'] already exists
            $table->index('professor_id');
            $table->index('academic_term_id');
            $table->index(['course_id', 'academic_term_id']); // Composite for term queries
            $table->index('is_active');
        });

        // Enrollments - student queries
        Schema::table('enrollments', function (Blueprint $table) {
            // Note: unique(['student_id', 'section_id']) already exists
            $table->index('academic_term_id');
            $table->index('status');
            $table->index(['student_id', 'academic_term_id']); // Composite
            $table->index(['section_id', 'status']); // Composite
        });

        // Grades - transcript queries
        Schema::table('grades', function (Blueprint $table) {
            $table->index('enrollment_id');
        });

        // Announcements - visibility queries
        Schema::table('announcements', function (Blueprint $table) {
            // Note: ['visibility', 'target_id'] and 'published_at' already exist
            $table->index('expires_at');
            $table->index('type');
        });

        // Notifications - user inbox
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('read_at');
            $table->index(['user_id', 'read_at']); // Composite
            $table->index(['user_id', 'deleted_at']); // Composite
        });

        // Attendance sessions - professor queries
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->index('section_id');
            $table->index('created_by');
            $table->index('date');
            $table->index(['section_id', 'date']); // Composite
        });

        // Attendance records - student queries
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->index('enrollment_id');
            $table->index('session_id');
            $table->index('status');
        });

        // Course ratings - analytics
        Schema::table('course_ratings', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('course_id');
            $table->index(['course_id', 'rating']); // Composite for analytics
        });

        // Webhooks - delivery queries
        Schema::table('webhooks', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('owner_id');
            $table->index(['owner_id', 'is_active']); // Composite
        });

        // Webhook deliveries - monitoring
        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->index('webhook_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['webhook_id', 'status']); // Composite
            $table->index(['status', 'created_at']); // Composite for cleanup
        });

        // Audit logs - filtering queries
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('action');
            $table->index('entity_type');
            $table->index('entity_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']); // Composite
            $table->index(['entity_type', 'entity_id']); // Composite
        });

        // Student term GPAs - transcript queries
        Schema::table('student_term_gpas', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('academic_term_id');
            $table->index(['student_id', 'academic_term_id']); // Composite
        });

        // Student department history - audit queries
        Schema::table('student_department_history', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('switched_by');
            $table->index(['student_id', 'switched_at']); // Composite
        });

        // Role user - authorization queries
        Schema::table('role_user', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('role_id');
            $table->index(['user_id', 'role_id']); // Composite
            $table->index('scope_type');
            $table->index('scope_id');
            $table->index(['scope_type', 'scope_id']); // Composite
        });

        // Group project members - membership queries
        Schema::table('group_project_members', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('group_project_id');
            $table->index(['group_project_id', 'student_id']); // Composite
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['student_number']);
            $table->dropIndex(['faculty_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['faculty_id', 'department_id']);
            $table->dropIndex(['enrollment_status']);
            $table->dropIndex(['academic_year']);
        });

        Schema::table('professors', function (Blueprint $table) {
            $table->dropIndex(['staff_number']);
            $table->dropIndex(['department_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['staff_number']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['level']);
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropIndex(['professor_id']);
            $table->dropIndex(['academic_term_id']);
            $table->dropIndex(['course_id', 'academic_term_id']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['academic_term_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['student_id', 'academic_term_id']);
            $table->dropIndex(['section_id', 'status']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['enrollment_id']);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['type']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['user_id', 'deleted_at']);
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['date']);
            $table->dropIndex(['section_id', 'date']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex(['enrollment_id']);
            $table->dropIndex(['session_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('course_ratings', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['course_id']);
            $table->dropIndex(['course_id', 'rating']);
        });

        Schema::table('webhooks', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['owner_id']);
            $table->dropIndex(['owner_id', 'is_active']);
        });

        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->dropIndex(['webhook_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['webhook_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['action']);
            $table->dropIndex(['entity_type']);
            $table->dropIndex(['entity_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['entity_type', 'entity_id']);
        });

        Schema::table('student_term_gpas', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['academic_term_id']);
            $table->dropIndex(['student_id', 'academic_term_id']);
        });

        Schema::table('student_department_history', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['switched_by']);
            $table->dropIndex(['student_id', 'switched_at']);
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['user_id', 'role_id']);
            $table->dropIndex(['scope_type']);
            $table->dropIndex(['scope_id']);
            $table->dropIndex(['scope_type', 'scope_id']);
        });

        Schema::table('group_project_members', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['group_project_id']);
            $table->dropIndex(['group_project_id', 'student_id']);
        });
    }
};
