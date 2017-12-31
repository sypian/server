<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;
use App\Models\Category;
use App\Models\ProjectCategory;

class ProjectsController extends Controller
{
    use NodeControllerTrait;

    /**
     * @return JsonResponse
     */
    public function getProjects(Request $request): JsonResponse
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $projectsRepository = $entityManager->getRepository(Project::class);

        if ($request->has('name')) {
            $projects = $projectsRepository->findBy(['name' => $request->get('name')]);
        } elseif ($request->has('category')) {
            $query = $entityManager->createQuery(
                'MATCH (p:Project)-[:BELONGS_TO]->(c:Category)
                    WHERE c.name = {catname} RETURN p'
            );
            $query->addEntityMapping('p', Project::class);
            $query->setParameter('catname', $request->get('category'));
            $projects = $query->execute();
        } else {
            $projects = $projectsRepository->findAll();
        }

        return response()->json($this->buildProjectsArray($projects));
    }

    protected function buildProjectsArray(array $projects): array
    {
        $projectsArray = [];

        foreach ($projects as $project) {
            $projectData = [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'categories' => [],
            ];

            foreach ($project->getCategories() as $projectCategory) {
                $projectData['categories'][] = [
                    'id' => $projectCategory->getCategory()->getId(),
                    'name' => $projectCategory->getCategory()->getName(),
                ];
            }

            $projectsArray[] = $projectData;
        }

        return $projectsArray;
    }
}
