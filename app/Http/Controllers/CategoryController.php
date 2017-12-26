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
        return $this->deleteNode('Category', $request, $nodeId, [$this, 'removeProjectCategoryRelations']);
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
