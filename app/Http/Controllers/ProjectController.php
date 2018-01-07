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
        $response = $this->verifyNodeByName('Project', $request);

        if ($response !== null) {
            return $response;
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $project = new Project();
        $project->setName($request->get('name'));

        if ($request->has('categories')) {
            $categoriesRepo = $entityManager->getRepository(Category::class);

            foreach ($request->get('categories') as $categoryName) {
                if ($this->nodeWithNameExists('Category', $categoryName)) {
                    $category = $categoriesRepo->findOneBy(['name' => $categoryName]);
                    $project->belongsTo($category);
                } else {
                    $this->addError('Category "'.$categoryName.'" does not exist.');
                    return $this->generateJsonResponse(400);
                }
            }
        }

        $entityManager->persist($project);
        $entityManager->flush();

        $this->addToPayload($project->getId(), 'id');
        return $this->generateJsonResponse(200);
    }

    /**
     * @return JsonResponse
     */
    public function getProject(Request $request, int $nodeId): JsonResponse
    {
        $response = $this->verifyNodeById('Project', $nodeId);

        if ($response !== null) {
            return $response;
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository(Project::class);
        $node = $nodesRepository->find($nodeId);
        $categories = [];

        foreach ($node->getCategories() as $projectCategory) {
            $categories[] = [
                'id' => $projectCategory->getCategory()->getId(),
                'name' => $projectCategory->getCategory()->getName(),
            ];
        }

        return response()->json(['id' => $nodeId, 'name' => $node->getName(), 'categories' => $categories]);
    }

    /**
     * @return JsonResponse
     */
    public function updateProject(Request $request, int $nodeId): JsonResponse
    {
        $response = $this->verifyNodeById('Project', $nodeId);

        if ($response !== null) {
            return $response;
        }

        if ($request->has('id') && $request->get('id') != $nodeId) {
            $this->addError('Changing the Project id is not allowed.');
            return $this->generateJsonResponse(400);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository(Project::class);
        $project = $nodesRepository->find($nodeId);

        if ($request->has('categories')) {
            $categoryRepository = $entityManager->getRepository(Category::class);

            foreach ($request->get('categories') as $categoryName) {
                if (!$this->nodeWithNameExists('Category', $categoryName)) {
                    $this->addError('Category "'.$categoryName.'" not found.');
                    return $this->generateJsonResponse(404);
                }
            }

            $this->removeProjectCategoryRelations($project);

            foreach ($request->get('categories') as $categoryName) {
                $category = $categoryRepository->findOneBy(['name' => $categoryName]);
                $project->belongsTo($category);
            }
        }

        if ($request->has('name')) {
            $project->setName($request->get('name'));
        }

        $entityManager->persist($project);
        $entityManager->flush();
        return response()->json(['name' => $project->getName(), 'id' => $project->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteProject(Request $request, int $nodeId): JsonResponse
    {
        return $this->deleteNode('Project', $request, $nodeId, [$this, 'removeProjectCategoryRelations']);
    }

    public function removeProjectCategoryRelations(Project $project)
    {
        $entityManager = app()->make('Neo4j\EntityManager');

        foreach ($project->getCategories() as $projectCategory) {
            $project->getCategories()->removeElement($projectCategory);
            $category = $projectCategory->getCategory();
            $category->getProjects()->removeElement($projectCategory);
            $entityManager->persist($category);
            $entityManager->persist($project);
            $entityManager->remove($projectCategory);
            $entityManager->flush();
        }
    }
}
