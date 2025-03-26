<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\MentorRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MentorController extends Controller
{
    /**
     * @var MentorRepositoryInterface
     */
    private $mentorRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * MentorController constructor.
     *
     * @param MentorRepositoryInterface $mentorRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(MentorRepositoryInterface $mentorRepository, UserRepositoryInterface $userRepository)
    {
        $this->mentorRepository = $mentorRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the mentors.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $mentors = $this->mentorRepository->all(['*'], ['user']);
        
        return response()->json(['mentors' => $mentors]);
    }

    /**
     * Store a newly created mentor profile in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isMentor()) {
            return response()->json([
                'message' => 'User role must be mentor to create a mentor profile',
            ], 403);
        }
        
        if ($this->mentorRepository->findByUserId($user->id)) {
            return response()->json([
                'message' => 'Mentor profile already exists for this user',
            ], 409);
        }
        
        $validator = Validator::make($request->all(), [
            'speciality' => 'required|string|max:255',
            'description' => 'nullable|string',
            'experience_level' => 'required|string|in:beginner,intermediate,expert',
            'skills' => 'nullable|array',
            'skills.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $user->id;

        $mentor = $this->mentorRepository->create($data);
        
        return response()->json([
            'message' => 'Mentor profile created successfully',
            'mentor' => $mentor,
        ], 201);
    }

    /**
     * Display the specified mentor.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $mentor = $this->mentorRepository->findById($id, ['*'], ['user', 'courses']);
        
        return response()->json(['mentor' => $mentor]);
    }

    /**
     * Update the specified mentor in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $mentor = $this->mentorRepository->findById($id);
        
        if (!$user->isAdmin() && $mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to update this mentor profile',
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'speciality' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'experience_level' => 'nullable|string|in:beginner,intermediate,expert',
            'skills' => 'nullable|array',
            'skills.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $mentor = $this->mentorRepository->update($id, $data);
        
        return response()->json([
            'message' => 'Mentor profile updated successfully',
            'mentor' => $mentor,
        ]);
    }

    /**
     * Remove the specified mentor from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $mentor = $this->mentorRepository->findById($id);
        
        if (!$user->isAdmin() && $mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this mentor profile',
            ], 403);
        }
        
        $this->mentorRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Mentor profile deleted successfully',
        ]);
    }

    /**
     * Get top mentors.
     *
     * @return JsonResponse
     */
    public function getTopMentors(): JsonResponse
    {
        $mentors = $this->mentorRepository->getTopMentors();
        
        return response()->json(['mentors' => $mentors]);
    }

    /**
     * Get mentors by speciality.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBySpeciality(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'speciality' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mentors = $this->mentorRepository->findBySpeciality($request->speciality);
        
        return response()->json(['mentors' => $mentors]);
    }

    /**
     * Get mentor profile by user ID.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getByUserId(int $userId): JsonResponse
    {
        $mentor = $this->mentorRepository->findByUserId($userId);
        
        if (!$mentor) {
            return response()->json([
                'message' => 'Mentor profile not found for this user',
            ], 404);
        }
        
        return response()->json(['mentor' => $mentor]);
    }
}