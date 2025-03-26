<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * TagController constructor.
     *
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Display a listing of the tags.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $tags = $this->tagRepository->all();
        
        return response()->json(['tags' => $tags]);
    }

    /**
     * Store a newly created tag in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);

        $tag = $this->tagRepository->create($data);
        
        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => $tag,
        ], 201);
    }

    /**
     * Display the specified tag.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $tag = $this->tagRepository->findById($id);
        
        return response()->json(['tag' => $tag]);
    }

    /**
     * Update the specified tag in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);

        $tag = $this->tagRepository->update($id, $data);
        
        return response()->json([
            'message' => 'Tag updated successfully',
            'tag' => $tag,
        ]);
    }

    /**
     * Remove the specified tag from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->tagRepository->deleteById($id);
        
        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);
    }

    /**
     * Search tags by name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tags = $this->tagRepository->findByNameLike($request->name);
        
        return response()->json(['tags' => $tags]);
    }
}