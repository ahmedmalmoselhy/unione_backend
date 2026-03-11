<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CourseRating;
use App\Models\Professor;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    use Concerns\DashboardScopeAware;

    /**
     * GET /dashboard/ratings
     * Aggregated course/professor rating statistics.
     */
    public function index(Request $request)
    {
        $query = Professor::with(['user', 'department'])
            ->withCount([
                'sections as rated_count' => fn ($q) => $q->whereHas('enrollments.rating'),
            ])
            ->withAvg(
                ['sections as avg_rating' => fn ($q) => $q->whereHas('enrollments.rating')],
                'enrollments.rating.rating'
            );

        // Scope-aware filtering
        if ($scopedDeptId = $this->scopedDepartmentId()) {
            $query->where('department_id', $scopedDeptId);
        } elseif ($scopedFacultyId = $this->scopedFacultyId()) {
            $query->whereHas('department', fn ($q) => $q->where('faculty_id', $scopedFacultyId));
        }

        $professors = $query->orderByDesc('avg_rating')->paginate(20);

        // Per-professor breakdown: query CourseRatings grouped by professor
        $statsMap = CourseRating::selectRaw('
                sections.professor_id,
                AVG(course_ratings.rating) as avg_rating,
                COUNT(course_ratings.id) as total_ratings,
                SUM(CASE WHEN course_ratings.rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN course_ratings.rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN course_ratings.rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN course_ratings.rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN course_ratings.rating = 1 THEN 1 ELSE 0 END) as one_star
            ')
            ->join('enrollments', 'course_ratings.enrollment_id', '=', 'enrollments.id')
            ->join('sections',    'enrollments.section_id',    '=', 'sections.id')
            ->groupBy('sections.professor_id')
            ->get()
            ->keyBy('professor_id');

        return view('dashboard.ratings.index', compact('professors', 'statsMap'));
    }
}
