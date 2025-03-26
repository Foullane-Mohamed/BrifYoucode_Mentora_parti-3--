<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\CourseController;
use App\Http\Controllers\API\V1\EnrollmentController;
use App\Http\Controllers\API\V1\MentorController;
use App\Http\Controllers\API\V1\StudentController;
use App\Http\Controllers\API\V1\SubCategoryController;
use App\Http\Controllers\API\V1\TagController;
use App\Http\Controllers\API\V1\VideoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\BadgeController;
use App\Http\Controllers\API\V1\PaymentController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth routes
Route::prefix('V1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('V1')->group(function () {
    // Categories
    Route::get('categories/with-subcategories', [CategoryController::class, 'indexWithSubcategories']);
    Route::apiResource('categories', CategoryController::class);
    
    // SubCategories
    Route::get('categories/{categoryId}/subcategories', [SubCategoryController::class, 'getByCategoryId']);
    Route::apiResource('subcategories', SubCategoryController::class);
    
    // Tags
    Route::get('tags/search', [TagController::class, 'search']);
    Route::apiResource('tags', TagController::class);
    
    // Mentors
    Route::get('mentors/top', [MentorController::class, 'getTopMentors']);
    Route::get('mentors/speciality', [MentorController::class, 'getBySpeciality']);
    Route::get('users/{userId}/mentor', [MentorController::class, 'getByUserId']);
    Route::apiResource('mentors', MentorController::class);
    
    // Students
    Route::get('students/top', [StudentController::class, 'getTopStudents']);
    Route::get('students/level', [StudentController::class, 'getByLevel']);
    Route::get('users/{userId}/student', [StudentController::class, 'getByUserId']);
    Route::apiResource('students', StudentController::class);
    
    // Courses
    Route::get('courses/search', [CourseController::class, 'search']);
    Route::get('courses/free', [CourseController::class, 'getFreeCourses']);
    Route::get('courses/difficulty/{difficulty}', [CourseController::class, 'getByDifficulty']);
    Route::get('courses/status/{status}', [CourseController::class, 'getByStatus']);
    Route::get('courses/slug/{slug}', [CourseController::class, 'showBySlug']);
    Route::get('mentors/{mentorId}/courses', [CourseController::class, 'getByMentorId']);
    Route::get('categories/{categoryId}/courses', [CourseController::class, 'getByCategoryId']);
    Route::get('subcategories/{subcategoryId}/courses', [CourseController::class, 'getBySubcategoryId']);
    Route::apiResource('courses', CourseController::class);
    
    // Videos
    Route::get('courses/{courseId}/videos', [VideoController::class, 'getByCourseId']);
    Route::get('courses/{courseId}/videos/free-previews', [VideoController::class, 'getFreePreviewsByCourseId']);
    Route::post('courses/{courseId}/videos/reorder', [VideoController::class, 'reorderVideos']);
    Route::apiResource('videos', VideoController::class);
    
    // Enrollments
    Route::get('courses/{courseId}/enrollments', [EnrollmentController::class, 'getByCourseId']);
    Route::get('students/{studentId}/enrollments', [EnrollmentController::class, 'getByStudentId']);
    Route::post('enrollments/{id}/complete', [EnrollmentController::class, 'complete']);
    Route::apiResource('enrollments', EnrollmentController::class);

    Route::get('badges/type/{type}', [BadgeController::class, 'getByType']);
    Route::post('badges/award-to-student', [BadgeController::class, 'awardToStudent']);
    Route::post('badges/award-to-mentor', [BadgeController::class, 'awardToMentor']);
    Route::post('badges/remove-from-student', [BadgeController::class, 'removeFromStudent']);
    Route::post('badges/remove-from-mentor', [BadgeController::class, 'removeFromMentor']);
    Route::apiResource('badges', BadgeController::class);

    Route::get('/', [PaymentController::class, 'index']);
    Route::get('/checkout', [PaymentController::class, 'checkout']);
    Route::get('/success', [PaymentController::class, 'success']);
    Route::get('/cancel', [PaymentController::class, 'cancel']);
    Route::get('/history', [PaymentController::class, 'history']);
    Route::get('/status/{status}', [PaymentController::class, 'getByStatus']);
    Route::get('/courses/{courseId}', [PaymentController::class, 'getByCourseId']);
    Route::get('/{id}', [PaymentController::class, 'show']);
});

// Public routes
Route::prefix('V1')->group(function () {
    // Allow public access to featured courses, categories, etc.
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/with-subcategories', [CategoryController::class, 'indexWithSubcategories']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('subcategories', [SubCategoryController::class, 'index']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('courses/featured', [CourseController::class, 'getFreeCourses']);
});