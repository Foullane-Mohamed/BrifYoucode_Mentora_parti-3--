<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\BadgeRepositoryInterface;
use App\Repositories\Interfaces\MentorRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BadgeController extends Controller
{
    /**
     * @var BadgeRepositoryInterface
     */
    private $badgeRepository;

    /**
     * @var StudentRepositoryInterface
     */
    private $studentRepository;

    /**
     * @var MentorRepositoryInterface
     */
    private $mentorRepository;

    /**
     * BadgeController constructor.
     *
     * @param BadgeRepositoryInterface $badgeRepository
     * @param StudentRepositoryInterface $studentRepository
     * @param MentorRepositoryInterface $mentorRepository
     */
    public function __construct(
        BadgeRepositoryInterface $badgeRepository,
        StudentRepositoryInterface $studentRepository,
        MentorRepositoryInterface $mentorRepository
    ) {
        $this->badgeRepository = $badgeRepository;
        $this->studentRepository = $studentRepository;
        $this->mentorRepository = $mentorRepository;
    }

    /**
     * Display a listing of the badges.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $badges = $this->badgeRepository->all();
        
        return response()->json(['badges' => $badges]);
    }

    /**
     * Store a newly created badge in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image_path' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:student,mentor',
            'requirements' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to create badges',
            ], 403);
        }
        
        $data = $validator->validated();
        $badge = $this->badgeRepository->create($data);
        
        return response()->json([
            'message' => 'Badge created successfully',
            'badge' => $badge,
        ], 201);
    }

    /**
     * Display the specified badge.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $badge = $this->badgeRepository->findById($id);
        
        return response()->json(['badge' => $badge]);
    }

    /**
     * Update the specified badge in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'image_path' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:student,mentor',
            'requirements' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to update badges',
            ], 403);
        }
        
        $data = $validator->validated();
        $badge = $this->badgeRepository->update($id, $data);
        
        return response()->json([
            'message' => 'Badge updated successfully',
            'badge' => $badge,
        ]);
    }

    /**
     * Remove the specified badge from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to delete badges',
            ], 403);
        }
        
        $this->badgeRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Badge deleted successfully',
        ]);
    }

    /**
     * Get badges by type.
     *
     * @param string $type
     * @return JsonResponse
     */
    public function getByType(string $type): JsonResponse
    {
        if (!in_array($type, ['student', 'mentor'])) {
            return response()->json([
                'message' => 'Invalid badge type',
            ], 422);
        }
        
        $badges = $this->badgeRepository->getByType($type);
        
        return response()->json(['badges' => $badges]);
    }

    /**
     * Award badge to a student.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function awardToStudent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'badge_id' => 'required|exists:badges,id',
            'student_id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to award badges',
            ], 403);
        }
        
        $result = $this->badgeRepository->awardToStudent($request->badge_id, $request->student_id);
        
        if (!$result) {
            return response()->json([
                'message' => 'Failed to award badge. Make sure the badge is of type student.',
            ], 400);
        }
        
        // Update badge count for student
        $student = $this->studentRepository->findById($request->student_id);
        $badgeCount = $student->badges()->count();
        $this->studentRepository->update($request->student_id, ['badge_count' => $badgeCount]);
        
        return response()->json([
            'message' => 'Badge awarded to student successfully',
        ]);
    }

    /**
     * Award badge to a mentor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function awardToMentor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'badge_id' => 'required|exists:badges,id',
            'mentor_id' => 'required|exists:mentors,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to award badges',
            ], 403);
        }
        
        $result = $this->badgeRepository->awardToMentor($request->badge_id, $request->mentor_id);
        
        if (!$result) {
            return response()->json([
                'message' => 'Failed to award badge. Make sure the badge is of type mentor.',
            ], 400);
        }
        
        return response()->json([
            'message' => 'Badge awarded to mentor successfully',
        ]);
    }

    /**
     * Remove badge from a student.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromStudent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'badge_id' => 'required|exists:badges,id',
            'student_id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to remove badges',
            ], 403);
        }
        
        $this->badgeRepository->removeFromStudent($request->badge_id, $request->student_id);
        
        // Update badge count for student
        $student = $this->studentRepository->findById($request->student_id);
        $badgeCount = $student->badges()->count();
        $this->studentRepository->update($request->student_id, ['badge_count' => $badgeCount]);
        
        return response()->json([
            'message' => 'Badge removed from student successfully',
        ]);
    }

    /**
     * Remove badge from a mentor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromMentor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'badge_id' => 'required|exists:badges,id',
            'mentor_id' => 'required|exists:mentors,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to remove badges',
            ], 403);
        }
        
        $this->badgeRepository->removeFromMentor($request->badge_id, $request->mentor_id);
        
        return response()->json([
            'message' => 'Badge removed from mentor successfully',
        ]);
    }
}