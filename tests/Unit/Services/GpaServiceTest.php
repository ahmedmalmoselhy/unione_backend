<?php

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use App\Services\GpaService;

beforeEach(function () {
    $this->gpaService = app(GpaService::class);
    
    // Create test data
    $this->faculty = Faculty::factory()->create();
    $this->department = Department::factory()->create(['faculty_id' => $this->faculty->id]);
    $this->student = Student::factory()->create([
        'faculty_id' => $this->faculty->id,
        'department_id' => $this->department->id,
    ]);
    
    $this->term = AcademicTerm::factory()->create([
        'is_active' => true,
    ]);
});

it('calculates cumulative GPA correctly', function () {
    $course1 = Course::factory()->create(['credit_hours' => 3]);
    $course2 = Course::factory()->create(['credit_hours' => 4]);
    
    $section1 = Section::factory()->create([
        'course_id' => $course1->id,
        'academic_term_id' => $this->term->id,
    ]);
    
    $section2 = Section::factory()->create([
        'course_id' => $course2->id,
        'academic_term_id' => $this->term->id,
    ]);
    
    $enrollment1 = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'section_id' => $section1->id,
        'academic_term_id' => $this->term->id,
        'status' => 'completed',
    ]);
    
    $enrollment2 = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'section_id' => $section2->id,
        'academic_term_id' => $this->term->id,
        'status' => 'completed',
    ]);
    
    Grade::factory()->create([
        'enrollment_id' => $enrollment1->id,
        'grade_points' => 4.0, // A
    ]);
    
    Grade::factory()->create([
        'enrollment_id' => $enrollment2->id,
        'grade_points' => 3.0, // B
    ]);
    
    $result = $this->gpaService->calculateCumulativeGpa($this->student->id);
    
    // Expected: (4.0 * 3 + 3.0 * 4) / (3 + 4) = 24/7 = 3.43
    expect($result)->toBeFloat();
    expect($result)->toBeGreaterThan(3.0);
    expect($result)->toBeLessThan(4.0);
});

it('determines academic standing correctly', function () {
    // Good standing (GPA >= 2.0)
    $standing = $this->gpaService->determineAcademicStanding(3.5);
    expect($standing)->toBe('good_standing');
    
    // Probation (1.0 <= GPA < 2.0)
    $standing = $this->gpaService->determineAcademicStanding(1.5);
    expect($standing)->toBe('probation');
    
    // Dismissal (GPA < 1.0)
    $standing = $this->gpaService->determineAcademicStanding(0.5);
    expect($standing)->toBe('dismissal');
});

it('handles empty enrollment gracefully', function () {
    $result = $this->gpaService->calculateCumulativeGpa($this->student->id);
    
    expect($result)->toBeFloat();
    expect($result)->toBe(0.0);
});

it('calculates term GPA separately', function () {
    $course = Course::factory()->create(['credit_hours' => 3]);
    $section = Section::factory()->create([
        'course_id' => $course->id,
        'academic_term_id' => $this->term->id,
    ]);
    
    $enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'section_id' => $section->id,
        'academic_term_id' => $this->term->id,
        'status' => 'completed',
    ]);
    
    Grade::factory()->create([
        'enrollment_id' => $enrollment->id,
        'grade_points' => 3.7,
    ]);
    
    $this->gpaService->recalculateStudentGpa($this->student->id, $this->term->id);
    
    $this->student->refresh();
    
    expect($this->student->gpa)->not->toBeNull();
    expect($this->student->academic_standing)->toBeIn(['good_standing', 'probation', 'dismissal']);
});
