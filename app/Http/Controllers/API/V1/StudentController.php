<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * @var StudentRepositoryInterface
     */
    private $studentRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * StudentController constructor.
     *
     * @param StudentRepositoryInterface $studentRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(StudentRepositoryInterface $studentRepository, UserRepositoryInterface $userRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the students.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $students = $this->studentRepository->all(['*'], ['user']);
        
        return response()->json(['students' => $students]);
    }

    /**
     * Store a newly created student profile in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'User role must be student to create a student profile',
            ], 403);
        }
        
        if ($this->studentRepository->findByUserId($user->id)) {
            return response()->json([
                'message' => 'Student profile already exists for this user',
            ], 409);
        }
        
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'interests' => 'nullable|array',
            'interests.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $user->id;
        $data['badge_count'] = 0;

        $student = $this->studentRepository->create($data);
        
        return response()->json([
            'message' => 'Student profile created successfully',
            'student' => $student,
        ], 201);
    }

    /**
     * Display the specified student.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $student = $this->studentRepository->findById($id, ['*'], ['user', 'courses', 'badges']);
        
        return response()->json(['student' => $student]);
    }

    /**
     * Update the specified student in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $student = $this->studentRepository->findById($id);
        
        if (!$user->isAdmin() && $student->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to update this student profile',
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'interests' => 'nullable|array',
            'interests.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $student = $this->studentRepository->update($id, $data);
        
        return response()->json([
            'message' => 'Student profile updated successfully',
            'student' => $student,
        ]);
    }

    /**
     * Remove the specified student from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $student = $this->studentRepository->findById($id);
        
        if (!$user->isAdmin() && $student->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this student profile',
            ], 403);
        }
        
        $this->studentRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Student profile deleted successfully',
        ]);
    }

    /**
     * Get top students.
     *
     * @return JsonResponse
     */
    public function getTopStudents(): JsonResponse
    {
        $students = $this->studentRepository->getTopStudents();
        
        return response()->json(['students' => $students]);
    }

    /**
     * Get students by level.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByLevel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|string|in:beginner,intermediate,advanced',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $students = $this->studentRepository->findByLevel($request->level);
        
        return response()->json(['students' => $students]);
    }

    /**
     * Get student profile by user ID.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getByUserId(int $userId): JsonResponse
    {
        $student = $this->studentRepository->findByUserId($userId);
        
        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found for this user',
            ], 404);
        }
        
        return response()->json(['student' => $student]);
    }
}