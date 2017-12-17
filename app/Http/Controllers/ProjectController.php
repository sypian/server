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
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing Project node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->nodeWithIdExists('Project', $nodeId)) {
            return response()->json(['message' => 'Project node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository(Project::class);
        $project = $nodesRepository->findOneById($nodeId);

        if ($request->has('categories')) {
            $categoryRepository = $entityManager->getRepository(Category::class);

            foreach ($request->get('categories') as $categoryName) {
                if (!$this->nodeWithNameExists('Category', $categoryName)) {
                    return response()->json(['message' => 'Category "'.$categoryName.'" not found.'], 404);
                }
            }

            $this->removeProjectCategoryRelations($project);

            foreach ($request->get('categories') as $categoryName) {
                $category = $categoryRepository->findOneBy(['name' => $categoryName]);
                $project->belongsTo($category);
            }
        }

        $project->setName($request->get('name'));
        $entityManager->persist($project);
        $entityManager->flush();
        return response()->json(['name' => $project->getName(), 'id' => $project->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteProject(Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing Project node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->nodeWithIdExists('Project', $nodeId)) {
            return response()->json(['message' => 'Project node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository(Project::class);
        $project = $nodesRepository->findOneById($nodeId);

        // Relations between the project and categories have to be removed first!
        $this->removeProjectCategoryRelations($project);

        $entityManager->remove($project);
        $entityManager->flush();
        return response()->json(['message' => 'Project node with id "'.$nodeId.'" got deleted.']);
    }

    public function removeProjectCategoryRelations($project)
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
