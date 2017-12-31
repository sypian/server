<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;
use App\Models\Category;
use App\Models\ProjectCategory;

class CategoriesController extends Controller
{
    use NodeControllerTrait;

    /**
     * @return JsonResponse
     */
    public function getCategories(Request $request): JsonResponse
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Category::class);

        if ($request->has('name')) {
            $categories = $categoriesRepository->findBy(['name' => $request->get('name')]);
        } elseif ($request->has('project')) {
            $query = $entityManager->createQuery(
                'MATCH (p:Project)-[:BELONGS_TO]->(c:Category)
                    WHERE p.name = {projectname} RETURN c'
            );
            $query->addEntityMapping('c', Category::class);
            $query->setParameter('projectname', $request->get('project'));
            $categories = $query->execute();
        } else {
            $categories = $categoriesRepository->findAll();
        }

        return response()->json($this->buildCategoriesArray($categories));
    }

    protected function buildCategoriesArray(array $categories): array
    {
        $categoriesArray = [];

        foreach ($categories as $category) {
            $categoryData = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'projects' => [],
            ];

            foreach ($category->getProjects() as $projectCategory) {
                $categoryData['projects'][] = [
                    'id' => $projectCategory->getProject()->getId(),
                    'name' => $projectCategory->getProject()->getName(),
                ];
            }

            $categoriesArray[] = $categoryData;
        }

        return $categoriesArray;
    }
}
