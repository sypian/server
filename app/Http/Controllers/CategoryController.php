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

    /**
     * @return JsonResponse
     */
    public function createCategory(Request $request): JsonResponse
    {
        if ($request->has('name') && count($request->except('name')) === 0) {
            $category = new Category();
            $category->setName($request->get('name'));

            $entityManager = app()->make('Neo4j\EntityManager');
            $entityManager->persist($category);
            $entityManager->flush();

            return response()->json(['message' => $category->getId()], 200);
        }

        return $this->getErrorResponseOnCreate($request);
    }

    /**
     * @return JsonResponse
     */
    protected function getErrorResponseOnCreate(Request $request): JsonResponse
    {
        if (count($request->all()) === 0) {
            return response()->json(['message' => 'Empty request.'], 405);
        } elseif (!$request->has('name')) {
            return response()->json(['message' => 'No category name defined.'], 405);
        } elseif (count($request->except('name')) > 0) {
            $invalidParams = array_keys($request->except('name'));

            if (count($invalidParams) > 1) {
                return response()->json(
                    ['message' => sprintf('Properties %s not supported.', $this->getFormattedParams($invalidParams))],
                    405
                );
            }
        }

        return response()->json(['message' => 'Property "'.reset($invalidParams).'" not supported.'], 405);
    }
}
