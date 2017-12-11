<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function createProject(Request $request): JsonResponse
    {
        if ($request->has('name')) {
            $project = new Project();
            $project->setName($request->get('name'));

            $entityManager = app()->make('Neo4j\EntityManager');
            $entityManager->persist($project);
            $entityManager->flush();

            return response()->json(['message' => $project->getId()], 200);
        }

        return response()->json(['message' => 'No project name defined.'], 405);
    }

    /**
     * @return JsonResponse
     */
    public function getProject(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $entityManager = app()->make('Neo4j\EntityManager');
        $projectsRepository = $entityManager->getRepository(Project::class);
        $project = $projectsRepository->findOneBy(['name' => $name]);

        if ($project === null) {
            return response()->json(['message' => 'Project "'.$name.'" not found.'], 404);
        }

        return response()->json(['name' => $project->getName(), 'id' => $project->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function updateProject(Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing project node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->projectExists($nodeId)) {
            return response()->json(['message' => 'Project node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $projectsRepository = $entityManager->getRepository(Project::class);
        $project = $projectsRepository->findOneById($nodeId);

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
            return response()->json(['message' => 'Missing project node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->projectExists($nodeId)) {
            return response()->json(['message' => 'Project node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $projectsRepository = $entityManager->getRepository(Project::class);
        $project = $projectsRepository->findOneById($nodeId);

        $entityManager->remove($project);
        $entityManager->flush();
        return response()->json(['message' => 'Project node with id "'.$nodeId.'" got deleted.']);
    }

    /**
     * Returns whether a project with the given node id exists in the database.
     *
     * @return bool
     */
    protected function projectExists(string $nodeId): bool
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Project::class);
        $project = $categoriesRepository->findOneById($nodeId);

        return $project !== null;
    }
}
