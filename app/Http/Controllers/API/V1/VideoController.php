<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * @var VideoRepositoryInterface
     */
    private $videoRepository;

    /**
     * @var CourseRepositoryInterface
     */
    private $courseRepository;

    /**
     * VideoController constructor.
     *
     * @param VideoRepositoryInterface $videoRepository
     * @param CourseRepositoryInterface $courseRepository
     */
    public function __construct(VideoRepositoryInterface $videoRepository, CourseRepositoryInterface $courseRepository)
    {
        $this->videoRepository = $videoRepository;
        $this->courseRepository = $courseRepository;
    }

    /**
     * Display a listing of the videos.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $videos = $this->videoRepository->all(['*'], ['course']);
        
        return response()->json(['videos' => $videos]);
    }

    /**
     * Store a newly created video in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|string|max:255',
            'duration' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
            'is_free_preview' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $course = $this->courseRepository->findById($request->course_id, ['*'], ['mentor']);
        
        if (!$user->isAdmin() && $course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to add videos to this course',
            ], 403);
        }
        
        $data = $validator->validated();
        
        // If order is not provided, get the highest current order and add 1
        if (!isset($data['order'])) {
            $existingVideos = $this->videoRepository->getByCourseId($data['course_id']);
            $data['order'] = $existingVideos->isEmpty() ? 0 : $existingVideos->max('order') + 1;
        }
        
        $video = $this->videoRepository->create($data);
        
        return response()->json([
            'message' => 'Video created successfully',
            'video' => $video,
        ], 201);
    }

    /**
     * Display the specified video.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $video = $this->videoRepository->findById($id, ['*'], ['course']);
        
        return response()->json(['video' => $video]);
    }

    /**
     * Update the specified video in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
            'is_free_preview' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $video = $this->videoRepository->findById($id, ['*'], ['course.mentor']);
        
        if (!$user->isAdmin() && $video->course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to update this video',
            ], 403);
        }
        
        $data = $validator->validated();
        $video = $this->videoRepository->update($id, $data);
        
        return response()->json([
            'message' => 'Video updated successfully',
            'video' => $video,
        ]);
    }

    /**
     * Remove the specified video from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $video = $this->videoRepository->findById($id, ['*'], ['course.mentor']);
        
        if (!$user->isAdmin() && $video->course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this video',
            ], 403);
        }
        
        $this->videoRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Video deleted successfully',
        ]);
    }

    /**
     * Get videos by course ID.
     *
     * @param int $courseId
     * @return JsonResponse
     */
    public function getByCourseId(int $courseId): JsonResponse
    {
        $videos = $this->videoRepository->getByCourseId($courseId);
        
        return response()->json(['videos' => $videos]);
    }

    /**
     * Get free preview videos by course ID.
     *
     * @param int $courseId
     * @return JsonResponse
     */
    public function getFreePreviewsByCourseId(int $courseId): JsonResponse
    {
        $videos = $this->videoRepository->getFreePreviewsByCourseId($courseId);
        
        return response()->json(['videos' => $videos]);
    }

    /**
     * Reorder videos for a course.
     *
     * @param Request $request
     * @param int $courseId
     * @return JsonResponse
     */
    public function reorderVideos(Request $request, int $courseId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $course = $this->courseRepository->findById($courseId, ['*'], ['mentor']);
        
        if (!$user->isAdmin() && $course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to reorder videos for this course',
            ], 403);
        }
        
        $videos = $this->videoRepository->reorderVideos($courseId, $request->orders);
        
        return response()->json([
            'message' => 'Videos reordered successfully',
            'videos' => $videos,
        ]);
    }
}