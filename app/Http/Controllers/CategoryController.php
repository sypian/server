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
    public function getCategory(Request $request, int $nodeId): JsonResponse
    {
        return $this->getNode('Category', $request, $nodeId);
    }

    /**
     * @return JsonResponse
     */
    public function updateCategory(Request $request, int $nodeId): JsonResponse
    {
        return $this->updateNode('Category', $request, $nodeId);
    }

    /**
     * @return JsonResponse
     */
    public function deleteCategory(Request $request, int $nodeId): JsonResponse
    {
        if (!$this->nodeWithIdExists('Category', $nodeId)) {
            $this->addError('Category with id "'.$nodeId.'" not found.');
            return $this->generateJsonResponse(404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository(Category::class);
        $category = $nodesRepository->find($nodeId);

        // Relations between the category and categories have to be removed first!
        $this->removeProjectCategoryRelations($category);

        $entityManager->remove($category);
        $entityManager->flush();
        return response()->json(['message' => 'Category node with id "'.$nodeId.'" got deleted.']);
    }

    public function removeProjectCategoryRelations($category)
    {
        $entityManager = app()->make('Neo4j\EntityManager');

        foreach ($category->getProjects() as $projectCategory) {
            $category->getProjects()->removeElement($projectCategory);
            $project = $projectCategory->getProject();
            $project->getCategories()->removeElement($projectCategory);
            $entityManager->persist($category);
            $entityManager->persist($project);
            $entityManager->remove($projectCategory);
            $entityManager->flush();
        }
    }
}
