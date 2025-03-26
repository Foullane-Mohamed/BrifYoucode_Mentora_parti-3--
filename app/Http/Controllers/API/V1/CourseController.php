<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\MentorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * @var CourseRepositoryInterface
     */
    private $courseRepository;

    /**
     * @var MentorRepositoryInterface
     */
    private $mentorRepository;

    /**
     * CourseController constructor.
     *
     * @param CourseRepositoryInterface $courseRepository
     * @param MentorRepositoryInterface $mentorRepository
     */
    public function __construct(CourseRepositoryInterface $courseRepository, MentorRepositoryInterface $mentorRepository)
    {
        $this->courseRepository = $courseRepository;
        $this->mentorRepository = $mentorRepository;
    }

    /**
     * Display a listing of the courses.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        
        if ($request->has('filter')) {
            $courses = $this->courseRepository->filter($request->filter, $perPage);
        } else {
            $courses = $this->courseRepository->all(['*'], ['mentor.user', 'category', 'subcategory', 'tags']);
        }
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Store a newly created course in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isMentor() && !$user->isAdmin()) {
            return response()->json([
                'message' => 'You are not authorized to create courses',
            ], 403);
        }
        
        if ($user->isMentor()) {
            $mentor = $this->mentorRepository->findByUserId($user->id);
            
            if (!$mentor) {
                return response()->json([
                    'message' => 'You need to create a mentor profile first',
                ], 403);
            }
            
            $mentorId = $mentor->id;
        } else {
            $validator = Validator::make($request->all(), [
                'mentor_id' => 'required|exists:mentors,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $mentorId = $request->mentor_id;
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'status' => 'required|in:draft,published,archived',
            'is_free' => 'required|boolean',
            'price' => 'nullable|required_if:is_free,false|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['mentor_id'] = $mentorId;
        $data['slug'] = Str::slug($data['title']);
        
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }
        
        $tags = $data['tags'] ?? [];
        unset($data['tags']);
        
        $course = $this->courseRepository->create($data);
        
        if (!empty($tags)) {
            $this->courseRepository->syncTags($course->id, $tags);
        }
        
        $course = $this->courseRepository->findById($course->id, ['*'], ['mentor.user', 'category', 'subcategory', 'tags']);
        
        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course,
        ], 201);
    }

    /**
     * Display the specified course.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $course = $this->courseRepository->findById($id, ['*'], ['mentor.user', 'category', 'subcategory', 'tags', 'videos']);
        
        return response()->json(['course' => $course]);
    }

    /**
     * Display the specified course by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $course = $this->courseRepository->findBySlug($slug);
        
        return response()->json(['course' => $course]);
    }

    /**
     * Update the specified course in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $course = $this->courseRepository->findById($id, ['*'], ['mentor']);
        
        if (!$user->isAdmin() && $course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to update this course',
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'status' => 'nullable|in:draft,published,archived',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        
        if (isset($data['status']) && $data['status'] === 'published' && $course->status !== 'published') {
            $data['published_at'] = now();
        }
        
        $tags = $data['tags'] ?? null;
        unset($data['tags']);
        
        $course = $this->courseRepository->update($id, $data);
        
        if (isset($tags)) {
            $this->courseRepository->syncTags($course->id, $tags);
        }
        
        $course = $this->courseRepository->findById($course->id, ['*'], ['mentor.user', 'category', 'subcategory', 'tags']);
        
        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course,
        ]);
    }

    /**
     * Remove the specified course from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $course = $this->courseRepository->findById($id, ['*'], ['mentor']);
        
        if (!$user->isAdmin() && $course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this course',
            ], 403);
        }
        
        $this->courseRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Course deleted successfully',
        ]);
    }

    /**
     * Get courses by mentor ID.
     *
     * @param int $mentorId
     * @return JsonResponse
     */
    public function getByMentorId(int $mentorId): JsonResponse
    {
        $courses = $this->courseRepository->getByMentorId($mentorId);
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Get courses by category ID.
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getByCategoryId(int $categoryId): JsonResponse
    {
        $courses = $this->courseRepository->getByCategoryId($categoryId);
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Get courses by subcategory ID.
     *
     * @param int $subcategoryId
     * @return JsonResponse
     */
    public function getBySubcategoryId(int $subcategoryId): JsonResponse
    {
        $courses = $this->courseRepository->getBySubcategoryId($subcategoryId);
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Search courses.
     *
     * @param Request $request
     * @return Json
     * /**
     * Search courses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:3',
            'per_page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perPage = $request->get('per_page', 10);
        $courses = $this->courseRepository->search($request->query, $perPage);
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Get free courses.
     *
     * @return JsonResponse
     */
    public function getFreeCourses(): JsonResponse
    {
        $courses = $this->courseRepository->getFreeCourses();
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Get courses by difficulty.
     *
     * @param string $difficulty
     * @return JsonResponse
     */
    public function getByDifficulty(string $difficulty): JsonResponse
    {
        $courses = $this->courseRepository->getByDifficulty($difficulty);
        
        return response()->json(['courses' => $courses]);
    }

    /**
     * Get courses by status.
     *
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus(string $status): JsonResponse
    {
        $courses = $this->courseRepository->getByStatus($status);
        
        return response()->json(['courses' => $courses]);
    }
}