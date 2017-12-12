<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoryController extends Controller
{
    use NodeControllerTrait;

    /**
     * @return JsonResponse
     */
    public function createCategory(Request $request): JsonResponse
    {
        return $this->createNode('Category', $request);
    }

    /**
     * @return JsonResponse
     */
    public function getCategory(Request $request): JsonResponse
    {
        return $this->getNode('Category', $request);
    }

    /**
     * @return JsonResponse
     */
    public function updateCategory(Request $request): JsonResponse
    {
        return $this->updateNode('Category', $request);
    }

    /**
     * @return JsonResponse
     */
    public function deleteCategory(Request $request): JsonResponse
    {
        return $this->deleteNode('Category', $request);
    }
}
