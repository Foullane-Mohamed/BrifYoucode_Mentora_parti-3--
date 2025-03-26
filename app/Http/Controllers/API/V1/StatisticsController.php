<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\BadgeRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Interfaces\MentorRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * @var CourseRepositoryInterface
     */
    private $courseRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var StudentRepositoryInterface
     */
    private $studentRepository;

    /**
     * @var MentorRepositoryInterface
     */
    private $mentorRepository;

    /**
     * @var EnrollmentRepositoryInterface
     */
    private $enrollmentRepository;

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var BadgeRepositoryInterface
     */
    private $badgeRepository;

    /**
     * @var VideoRepositoryInterface
     */
    private $videoRepository;

    /**
     * StatisticsController constructor.
     *
     * @param CourseRepositoryInterface $courseRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TagRepositoryInterface $tagRepository
     * @param StudentRepositoryInterface $studentRepository
     * @param MentorRepositoryInterface $mentorRepository
     * @param EnrollmentRepositoryInterface $enrollmentRepository
     * @param PaymentRepositoryInterface $paymentRepository
     * @param BadgeRepositoryInterface $badgeRepository
     * @param VideoRepositoryInterface $videoRepository
     */
    public function __construct(
        CourseRepositoryInterface $courseRepository,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        StudentRepositoryInterface $studentRepository,
        MentorRepositoryInterface $mentorRepository,
        EnrollmentRepositoryInterface $enrollmentRepository,
        PaymentRepositoryInterface $paymentRepository,
        BadgeRepositoryInterface $badgeRepository,
        VideoRepositoryInterface $videoRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->studentRepository = $studentRepository;
        $this->mentorRepository = $mentorRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->paymentRepository = $paymentRepository;
        $this->badgeRepository = $badgeRepository;
        $this->videoRepository = $videoRepository;
    }

    /**
     * Get course statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function courseStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isMentor()) {
            return response()->json([
                'message' => 'Unauthorized to view course statistics',
            ], 403);
        }
        
        // If user is a mentor, only show stats for their courses
        $coursesQuery = $user->isAdmin() 
            ? DB::table('courses') 
            : DB::table('courses')->where('mentor_id', $user->mentor->id);
        
        $totalCourses = $coursesQuery->count();
        
        $coursesByStatus = $coursesQuery
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();
        
        $coursesByDifficulty = $coursesQuery
            ->select('difficulty', DB::raw('count(*) as count'))
            ->groupBy('difficulty')
            ->get()
            ->keyBy('difficulty')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();
        
        $totalFree = $coursesQuery->where('is_free', true)->count();
        $totalPaid = $coursesQuery->where('is_free', false)->count();
        
        $mostPopular = DB::table('courses')
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->select('courses.id', 'courses.title', DB::raw('count(enrollments.id) as enrollments_count'))
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                return $query->where('courses.mentor_id', $user->mentor->id);
            })
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'total_courses' => $totalCourses,
            'courses_by_status' => $coursesByStatus,
            'courses_by_difficulty' => $coursesByDifficulty,
            'total_free' => $totalFree,
            'total_paid' => $totalPaid,
            'most_popular' => $mostPopular,
        ]);
    }

    /**
     * Get category statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to view category statistics',
            ], 403);
        }
        
        $totalCategories = DB::table('categories')->count();
        $totalSubcategories = DB::table('sub_categories')->count();
        
        $coursesByCategory = DB::table('categories')
            ->leftJoin('courses', 'categories.id', '=', 'courses.category_id')
            ->select('categories.id', 'categories.name', DB::raw('count(courses.id) as courses_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('courses_count', 'desc')
            ->get();
        
        $coursesBySubcategory = DB::table('sub_categories')
            ->leftJoin('courses', 'sub_categories.id', '=', 'courses.sub_category_id')
            ->select('sub_categories.id', 'sub_categories.name', DB::raw('count(courses.id) as courses_count'))
            ->groupBy('sub_categories.id', 'sub_categories.name')
            ->orderBy('courses_count', 'desc')
            ->get();
        
        $enrollmentsByCategory = DB::table('categories')
            ->leftJoin('courses', 'categories.id', '=', 'courses.category_id')
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->select('categories.id', 'categories.name', DB::raw('count(enrollments.id) as enrollments_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('enrollments_count', 'desc')
            ->get();
        
        return response()->json([
            'total_categories' => $totalCategories,
            'total_subcategories' => $totalSubcategories,
            'courses_by_category' => $coursesByCategory,
            'courses_by_subcategory' => $coursesBySubcategory,
            'enrollments_by_category' => $enrollmentsByCategory,
        ]);
    }

    /**
     * Get tag statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tagStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to view tag statistics',
            ], 403);
        }
        
        $totalTags = DB::table('tags')->count();
        
        $coursesByTag = DB::table('tags')
            ->leftJoin('course_tag', 'tags.id', '=', 'course_tag.tag_id')
            ->select('tags.id', 'tags.name', DB::raw('count(course_tag.course_id) as courses_count'))
            ->groupBy('tags.id', 'tags.name')
            ->orderBy('courses_count', 'desc')
            ->get();
        
        $mostUsedTags = DB::table('tags')
            ->leftJoin('course_tag', 'tags.id', '=', 'course_tag.tag_id')
            ->select('tags.id', 'tags.name', DB::raw('count(course_tag.course_id) as courses_count'))
            ->groupBy('tags.id', 'tags.name')
            ->orderBy('courses_count', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'total_tags' => $totalTags,
            'courses_by_tag' => $coursesByTag,
            'most_used_tags' => $mostUsedTags,
        ]);
    }

    /**
     * Get enrollment statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enrollmentStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isMentor()) {
            return response()->json([
                'message' => 'Unauthorized to view enrollment statistics',
            ], 403);
        }
        
        $enrollmentsQuery = DB::table('enrollments');
        
        if ($user->isMentor()) {
            $enrollmentsQuery = $enrollmentsQuery
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->where('courses.mentor_id', $user->mentor->id);
        }
        
        $totalEnrollments = $enrollmentsQuery->count();
        
        $enrollmentsByStatus = $enrollmentsQuery
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();
        
        $completionRate = $enrollmentsQuery
            ->select(DB::raw('COUNT(CASE WHEN progress = 100 THEN 1 END) as completed'), DB::raw('COUNT(*) as total'))
            ->first();
        
        $completionRatePercentage = $completionRate->total > 0
            ? round(($completionRate->completed / $completionRate->total) * 100, 2)
            : 0;
        
        $enrollmentTrend = $enrollmentsQuery
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->limit(30)
            ->get();
        
        return response()->json([
            'total_enrollments' => $totalEnrollments,
            'enrollments_by_status' => $enrollmentsByStatus,
            'completion_rate' => $completionRatePercentage,
            'enrollment_trend' => $enrollmentTrend,
        ]);
    }

    /**
     * Get revenue statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function revenueStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isMentor()) {
            return response()->json([
                'message' => 'Unauthorized to view revenue statistics',
            ], 403);
        }
        
        $paymentsQuery = DB::table('payments')
            ->where('status', 'completed');
        
        if ($user->isMentor()) {
            $paymentsQuery = $paymentsQuery
                ->join('courses', 'payments.course_id', '=', 'courses.id')
                ->where('courses.mentor_id', $user->mentor->id);
        }
        
        $totalRevenue = $paymentsQuery->sum('amount');
        $totalTransactions = $paymentsQuery->count();
        
        $revenueByCourse = DB::table('payments')
            ->join('courses', 'payments.course_id', '=', 'courses.id')
            ->where('payments.status', 'completed')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                return $query->where('courses.mentor_id', $user->mentor->id);
            })
            ->select('courses.id', 'courses.title', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id) as transactions'))
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('revenue', 'desc')
            ->get();
        
        $revenueByMonth = $paymentsQuery
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as revenue'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'revenue' => $item->revenue,
                ];
            });
        
        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'revenue_by_course' => $revenueByCourse,
            'revenue_by_month' => $revenueByMonth,
        ]);
    }


  /**
     * Get user statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to view user statistics',
            ], 403);
        }
        
        $totalUsers = DB::table('users')->count();
        $totalAdmins = DB::table('users')->where('role', 'admin')->count();
        $totalMentors = DB::table('users')->where('role', 'mentor')->count();
        $totalStudents = DB::table('users')->where('role', 'student')->count();
        
        $newUsersThisMonth = DB::table('users')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        
        $activeUsersThisMonth = DB::table('users')
            ->where('last_active_at', '>=', now()->startOfMonth())
            ->count();
        
        $usersByMonth = DB::table('users')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'count' => $item->count,
                ];
            });
        
        $usersByRole = [
            'admin' => $totalAdmins,
            'mentor' => $totalMentors,
            'student' => $totalStudents,
        ];
        
        return response()->json([
            'total_users' => $totalUsers,
            'users_by_role' => $usersByRole,
            'new_users_this_month' => $newUsersThisMonth,
            'active_users_this_month' => $activeUsersThisMonth,
            'users_by_month' => $usersByMonth,
        ]);
    }

    /**
     * Get badge statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function badgeStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to view badge statistics',
            ], 403);
        }
        
        $totalBadges = DB::table('badges')->count();
        $totalStudentBadges = DB::table('badges')->where('type', 'student')->count();
        $totalMentorBadges = DB::table('badges')->where('type', 'mentor')->count();
        
        $mostCommonStudentBadges = DB::table('badges')
            ->join('student_badge', 'badges.id', '=', 'student_badge.badge_id')
            ->where('badges.type', 'student')
            ->select('badges.id', 'badges.name', DB::raw('count(student_badge.student_id) as count'))
            ->groupBy('badges.id', 'badges.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        $mostCommonMentorBadges = DB::table('badges')
            ->join('mentor_badge', 'badges.id', '=', 'mentor_badge.badge_id')
            ->where('badges.type', 'mentor')
            ->select('badges.id', 'badges.name', DB::raw('count(mentor_badge.mentor_id) as count'))
            ->groupBy('badges.id', 'badges.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        $studentsWithMostBadges = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.id', 'users.name', 'students.badge_count')
            ->orderBy('students.badge_count', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'total_badges' => $totalBadges,
            'total_student_badges' => $totalStudentBadges,
            'total_mentor_badges' => $totalMentorBadges,
            'most_common_student_badges' => $mostCommonStudentBadges,
            'most_common_mentor_badges' => $mostCommonMentorBadges,
            'students_with_most_badges' => $studentsWithMostBadges,
        ]);
    }

    /**
     * Get dashboard statistics (summary of all stats).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && !$user->isMentor()) {
            return response()->json([
                'message' => 'Unauthorized to view dashboard statistics',
            ], 403);
        }
        
        // User stats (admin only)
        $userStats = null;
        if ($user->isAdmin()) {
            $userStats = [
                'total_users' => DB::table('users')->count(),
                'total_mentors' => DB::table('users')->where('role', 'mentor')->count(),
                'total_students' => DB::table('users')->where('role', 'student')->count(),
                'new_users_this_month' => DB::table('users')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),
            ];
        }
        
        // Course stats
        $coursesQuery = $user->isAdmin() 
            ? DB::table('courses') 
            : DB::table('courses')->where('mentor_id', $user->mentor->id);
        
        $courseStats = [
            'total_courses' => $coursesQuery->count(),
            'published_courses' => $coursesQuery->where('status', 'published')->count(),
            'draft_courses' => $coursesQuery->where('status', 'draft')->count(),
            'archived_courses' => $coursesQuery->where('status', 'archived')->count(),
        ];
        
        // Enrollment stats
        $enrollmentsQuery = DB::table('enrollments');
        
        if ($user->isMentor()) {
            $enrollmentsQuery = $enrollmentsQuery
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->where('courses.mentor_id', $user->mentor->id);
        }
        
        $totalEnrollments = $enrollmentsQuery->count();
        $completedEnrollments = $enrollmentsQuery->where('progress', 100)->count();
        
        $enrollmentStats = [
            'total_enrollments' => $totalEnrollments,
            'completed_enrollments' => $completedEnrollments,
            'completion_rate' => $totalEnrollments > 0 
                ? round(($completedEnrollments / $totalEnrollments) * 100, 2) 
                : 0,
        ];
        
        // Revenue stats
        $paymentsQuery = DB::table('payments')
            ->where('status', 'completed');
        
        if ($user->isMentor()) {
            $paymentsQuery = $paymentsQuery
                ->join('courses', 'payments.course_id', '=', 'courses.id')
                ->where('courses.mentor_id', $user->mentor->id);
        }
        
        $revenueStats = [
            'total_revenue' => $paymentsQuery->sum('amount'),
            'total_transactions' => $paymentsQuery->count(),
            'revenue_this_month' => $paymentsQuery
                ->where('payments.created_at', '>=', now()->startOfMonth())
                ->sum('amount'),
        ];
        
        // Recent enrollments
        $recentEnrollments = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                return $query->where('courses.mentor_id', $user->mentor->id);
            })
            ->select('enrollments.id', 'enrollments.created_at', 'courses.title as course_title', 'users.name as student_name')
            ->orderBy('enrollments.created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Popular courses
        $popularCourses = DB::table('courses')
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                return $query->where('courses.mentor_id', $user->mentor->id);
            })
            ->select('courses.id', 'courses.title', DB::raw('count(enrollments.id) as enrollments_count'))
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'user_stats' => $userStats,
            'course_stats' => $courseStats,
            'enrollment_stats' => $enrollmentStats,
            'revenue_stats' => $revenueStats,
            'recent_enrollments' => $recentEnrollments,
            'popular_courses' => $popularCourses,
        ]);
    }
}