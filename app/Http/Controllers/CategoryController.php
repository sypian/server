<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoryController extends Controller
{
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

    /**
     * @return JsonResponse
     */
    public function getCategory(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $category = $categoriesRepository->findOneBy(['name' => $name]);

        if ($category === null) {
            return response()->json(['message' => 'Category "'.$name.'" not found.'], 404);
        }

        return response()->json(['name' => $category->getName(), 'id' => $category->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function updateCategory(Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing category node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->categoryExists($nodeId)) {
            return response()->json(['message' => 'Category node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $category = $categoriesRepository->findOneById($nodeId);

        $category->setName($request->get('name'));
        $entityManager->persist($category);
        $entityManager->flush();
        return response()->json(['name' => $category->getName(), 'id' => $category->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteCategory(Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing category node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->categoryExists($nodeId)) {
            return response()->json(['message' => 'Category node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $category = $categoriesRepository->findOneById($nodeId);

        $entityManager->remove($category);
        $entityManager->flush();
        return response()->json(['message' => 'Category node with id "'.$nodeId.'" got deleted.']);
    }

    /**
     * Returns whether a category with the given node id exists in the database.
     *
     * @return bool
     */
    protected function categoryExists(string $nodeId): bool
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $category = $categoriesRepository->findOneById($nodeId);

        return $category !== null;
    }
}
