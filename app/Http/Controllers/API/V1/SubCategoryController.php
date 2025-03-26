<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    /**
     * @var SubCategoryRepositoryInterface
     */
    private $subCategoryRepository;

    /**
     * SubCategoryController constructor.
     *
     * @param SubCategoryRepositoryInterface $subCategoryRepository
     */
    public function __construct(SubCategoryRepositoryInterface $subCategoryRepository)
    {
        $this->subCategoryRepository = $subCategoryRepository;
    }

    /**
     * Display a listing of the subcategories.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $subcategories = $this->subCategoryRepository->all(['*'], ['category']);
        
        return response()->json(['subcategories' => $subcategories]);
    }

    /**
     * Store a newly created subcategory in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);

        $subcategory = $this->subCategoryRepository->create($data);
        
        return response()->json([
            'message' => 'SubCategory created successfully',
            'subcategory' => $subcategory,
        ], 201);
    }

    /**
     * Display the specified subcategory.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $subcategory = $this->subCategoryRepository->findById($id, ['*'], ['category']);
        
        return response()->json(['subcategory' => $subcategory]);
    }

    /**
     * Update the specified subcategory in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $subcategory = $this->subCategoryRepository->update($id, $data);
        
        return response()->json([
            'message' => 'SubCategory updated successfully',
            'subcategory' => $subcategory,
        ]);
    }

    /**
     * Remove the specified subcategory from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->subCategoryRepository->deleteById($id);
        
        return response()->json([
            'message' => 'SubCategory deleted successfully',
        ]);
    }

    /**
     * Get subcategories by category ID.
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getByCategoryId(int $categoryId): JsonResponse
    {
        $subcategories = $this->subCategoryRepository->getByCategoryId($categoryId);
        
        return response()->json(['subcategories' => $subcategories]);
    }
}