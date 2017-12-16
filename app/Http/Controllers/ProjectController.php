<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;
use App\Models\Category;

class ProjectController extends Controller
{
    use NodeControllerTrait;

    /**
     * @return JsonResponse
     */
    public function createProject(Request $request): JsonResponse
    {
        if ($request->has('name')) {
            $name = $request->get('name');

            if (!$this->nodeWithNameExists('Project', $name)) {
                $entityManager = app()->make('Neo4j\EntityManager');
                $project = new Project();
                $project->setName($name);

                if ($request->has('categories')) {
                    $categoriesRepo = $entityManager->getRepository(Category::class);

                    foreach ($request->get('categories') as $categoryName) {
                        if ($this->nodeWithNameExists('Category', $categoryName)) {
                            $category = $categoriesRepo->findOneBy(['name' => $categoryName]);
                            $project->belongsTo($category);
                        } else {
                            return response()->json(['message' => 'Category "'.$categoryName.'" does not exist.'], 405);
                        }
                    }
                }

                $entityManager->persist($project);
                $entityManager->flush();

                return response()->json(['message' => $project->getId()], 201);
            }

            return response()->json(['message' => 'Project'.' with name "'.$name.'" already exists.'], 409);
        }

        return response()->json(['message' => 'No '.'Project'.' name defined.'], 405);
    }

    /**
     * @return JsonResponse
     */
    public function getProject(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository(Project::class);
        $node = $nodesRepository->findOneBy(['name' => $name]);

        if ($node === null) {
            return response()->json(['message' => 'Project "'.$name.'" not found.'], 404);
        }

        $categories = [];

        foreach ($node->getCategories() as $projectCategory) {
            $categories[] = $projectCategory->getCategory()->getName();
        }

        return response()->json(['name' => $node->getName(), 'id' => $node->getId(), 'categories' => $categories]);
    }

    /**
     * @return JsonResponse
     */
    public function updateProject(Request $request): JsonResponse
    {
        return $this->updateNode('Project', $request);
    }

    /**
     * @return JsonResponse
     */
    public function deleteProject(Request $request): JsonResponse
    {
        return $this->deleteNode('Project', $request);
    }
}
