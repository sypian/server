<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * A trait to manage CRUD operations on neo4j nodes.
 *
 * $label should be always used as a Model name in App\Models and be a neo4j entity.
 */
trait NodeControllerTrait
{
    /**
     * @return JsonResponse
     */
    public function createNode(string $label, Request $request): JsonResponse
    {
        if ($request->has('name')) {
            $class = 'App\Models\\'.$label;
            $node = new $class();
            $node->setName($request->get('name'));

            $entityManager = app()->make('Neo4j\EntityManager');
            $entityManager->persist($node);
            $entityManager->flush();

            return response()->json(['message' => $node->getId()], 200);
        }

        return response()->json(['message' => 'No '.$label.' name defined.'], 405);
    }

    /**
     * @return JsonResponse
     */
    public function getNode(string $label, Request $request): JsonResponse
    {
        $name = $request->get('name');
        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->findOneBy(['name' => $name]);

        if ($node === null) {
            return response()->json(['message' => $label.' "'.$name.'" not found.'], 404);
        }

        return response()->json(['name' => $node->getName(), 'id' => $node->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function updateNode(string $label, Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing '.$label.' node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->nodeExists($label, $nodeId)) {
            return response()->json(['message' => $label.' node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->findOneById($nodeId);

        $node->setName($request->get('name'));
        $entityManager->persist($node);
        $entityManager->flush();
        return response()->json(['name' => $node->getName(), 'id' => $node->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteNode(string $label, Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing '.$label.' node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->nodeExists($label, $nodeId)) {
            return response()->json(['message' => $label.' node with id "'.$nodeId.'" not found.'], 404);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->findOneById($nodeId);

        $entityManager->remove($node);
        $entityManager->flush();
        return response()->json(['message' => $label.' node with id "'.$nodeId.'" got deleted.']);
    }

    /**
     * Returns whether a node with the given node id exists in the database.
     *
     * @return bool
     */
    protected function nodeExists(string $label, string $nodeId): bool
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $categoriesRepository->findOneById($nodeId);

        return $node !== null;
    }
}
