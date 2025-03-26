<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    /**
     * @var EnrollmentRepositoryInterface
     */
    private $enrollmentRepository;

    /**
     * @var CourseRepositoryInterface
     */
    private $courseRepository;

    /**
     * @var StudentRepositoryInterface
     */
    private $studentRepository;

    /**
     * EnrollmentController constructor.
     *
     * @param EnrollmentRepositoryInterface $enrollmentRepository
     * @param CourseRepositoryInterface $courseRepository
     * @param StudentRepositoryInterface $studentRepository
     */
    public function __construct(EnrollmentRepositoryInterface $enrollmentRepository, CourseRepositoryInterface $courseRepository, StudentRepositoryInterface $studentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
        $this->courseRepository = $courseRepository;
        $this->studentRepository = $studentRepository;
    }

    /**
     * Display a listing of the enrollments.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $enrollments = $this->enrollmentRepository->all(['*'], ['course.mentor.user', 'student.user']);
        
        return response()->json(['enrollments' => $enrollments]);
    }

    /**
     * Store a newly created enrollment in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Only students can enroll in courses',
            ], 403);
        }
        
        $student = $this->studentRepository->findByUserId($user->id);
        
        if (!$student) {
            return response()->json([
                'message' => 'You need to create a student profile first',
            ], 403);
        }
        
        if ($this->enrollmentRepository->isEnrolled($student->id, $request->course_id)) {
            return response()->json([
                'message' => 'You are already enrolled in this course',
            ], 409);
        }
        
        $course = $this->courseRepository->findById($request->course_id);
        
        $data = [
            'course_id' => $request->course_id,
            'student_id' => $student->id,
            // For free courses, auto-approve. For paid courses, status will be updated after payment
            'status' => $course->is_free ? 'approved' : 'pending',
            'progress' => 0,
        ];
        
        $enrollment = $this->enrollmentRepository->create($data);
        
        return response()->json([
            'message' => 'Enrollment created successfully',
            'enrollment' => $enrollment,
        ], 201);
    }

    /**
     * Display the specified enrollment.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->findById($id, ['*'], ['course.mentor.user', 'student.user', 'lastWatchedVideo']);
        
        return response()->json(['enrollment' => $enrollment]);
    }

    /**
     * Update the specified enrollment in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:pending,approved,rejected',
            'progress' => 'nullable|integer|min:0|max:100',
            'last_watched_video_id' => 'nullable|exists:videos,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $enrollment = $this->enrollmentRepository->findById($id, ['*'], ['course.mentor', 'student']);
        
        // Only admin or the course's mentor can change status
        if (isset($request->status) && !$user->isAdmin() && $enrollment->course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to change enrollment status',
            ], 403);
        }
        
        // Only admin or the enrolled student can update progress
        if ((isset($request->progress) || isset($request->last_watched_video_id)) && 
            !$user->isAdmin() && 
            $this->studentRepository->findByUserId($user->id)?->id !== $enrollment->student_id) {
            return response()->json([
                'message' => 'Unauthorized to update enrollment progress',
            ], 403);
        }
        
        $data = $validator->validated();
        
        if (isset($data['progress']) && isset($data['last_watched_video_id'])) {
            $enrollment = $this->enrollmentRepository->updateProgress($id, $data['progress'], $data['last_watched_video_id']);
            unset($data['progress']);
            unset($data['last_watched_video_id']);
        } elseif (isset($data['progress'])) {
            $enrollment = $this->enrollmentRepository->updateProgress($id, $data['progress']);
            unset($data['progress']);
        }
        
        if (!empty($data)) {
            $enrollment = $this->enrollmentRepository->update($id, $data);
        }
        
        return response()->json([
            'message' => 'Enrollment updated successfully',
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Remove the specified enrollment from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $enrollment = $this->enrollmentRepository->findById($id, ['*'], ['course.mentor', 'student']);
        
        // Only admin, the course's mentor, or the enrolled student can delete enrollment
        if (!$user->isAdmin() && 
            $enrollment->course->mentor->user_id !== $user->id && 
            $this->studentRepository->findByUserId($user->id)?->id !== $enrollment->student_id) {
            return response()->json([
                'message' => 'Unauthorized to delete this enrollment',
            ], 403);
        }
        
        $this->enrollmentRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Enrollment deleted successfully',
        ]);
    }

    /**
     * Get enrollments by course ID.
     *
     * @param Request $request
     * @param int $courseId
     * @return JsonResponse
     */
    public function getByCourseId(Request $request, int $courseId): JsonResponse
    {
        $user = $request->user();
        $course = $this->courseRepository->findById($courseId, ['*'], ['mentor']);
        
        // Only admin or the course's mentor can see all enrollments for a course
        if (!$user->isAdmin() && $course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to view enrollments for this course',
            ], 403);
        }
        
        $enrollments = $this->enrollmentRepository->getByCourseId($courseId);
        
        return response()->json(['enrollments' => $enrollments]);
    }

    /**
     * Get enrollments by student ID.
     *
     * @param Request $request
     * @param int $studentId
     * @return JsonResponse
     */
    public function getByStudentId(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();
        $student = $this->studentRepository->findById($studentId, ['*'], ['user']);
        
        // Only admin or the student can see their enrollments
        if (!$user->isAdmin() && $student->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to view enrollments for this student',
            ], 403);
        }
        
        $enrollments = $this->enrollmentRepository->getByStudentId($studentId);
        
        return response()->json(['enrollments' => $enrollments]);
    }

    /**
     * Complete an enrollment.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $enrollment = $this->enrollmentRepository->findById($id, ['*'], ['student']);
        
        // Only admin or the enrolled student can mark an enrollment as complete
        if (!$user->isAdmin() && $this->studentRepository->findByUserId($user->id)?->id !== $enrollment->student_id) {
            return response()->json([
                'message' => 'Unauthorized to complete this enrollment',
            ], 403);
        }
        
        $enrollment = $this->enrollmentRepository->complete($id);
        
        return response()->json([
            'message' => 'Enrollment marked as complete',
            'enrollment' => $enrollment,
        ]);
    }
}