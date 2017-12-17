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
        $response = $this->verifyNodeByName($label, $request);

        if ($response !== null) {
            return $response;
        }

        $class = 'App\Models\\'.$label;
        $node = new $class();
        $node->setName($request->get('name'));

        $entityManager = app()->make('Neo4j\EntityManager');
        $entityManager->persist($node);
        $entityManager->flush();

        return response()->json(['message' => $node->getId()], 201);
    }

    /**
     * Verifies a node from a request using a given name and returns a failure response or null
     * if the request is ok.
     *
     * @return null|JsonResponse
     */
    public function verifyNodeByName(string $label, Request $request): ?JsonResponse
    {
        if ($request->has('name')) {
            $name = $request->get('name');

            if (!$this->nodeWithNameExists($label, $name)) {
                return null;
            }

            return response()->json(['message' => $label.' with name "'.$name.'" already exists.'], 409);
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
        $response = $this->verifyNodeById($label, $request);

        if ($response !== null) {
            return $response;
        }

        $nodeId = $request->get('id');
        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->findOneById($nodeId);

        $node->setName($request->get('name'));
        $entityManager->persist($node);
        $entityManager->flush();
        return response()->json(['name' => $node->getName(), 'id' => $node->getId()]);
    }

    /**
     * Verifies a node from a request using a given id and returns a failure response or null
     * if the request is ok.
     *
     * @return null|JsonResponse
     */
    public function verifyNodeById(string $label, Request $request): ?JsonResponse
    {
        if (!$request->has('id')) {
            return response()->json(['message' => 'Missing '.$label.' node id.'], 405);
        }

        $nodeId = $request->get('id');

        if (!$this->nodeWithIdExists($label, $nodeId)) {
            return response()->json(['message' => $label.' node with id "'.$nodeId.'" not found.'], 404);
        }

        return null;
    }

    /**
     * Returns whether a node with the given node id exists in the database.
     *
     * @return bool
     */
    protected function nodeWithIdExists(string $label, string $nodeId): bool
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $categoriesRepository->findOneById($nodeId);

        return $node !== null;
    }

    /**
     * Returns whether a node with the given name exists in the database.
     *
     * @return bool
     */
    protected function nodeWithNameExists(string $label, string $name): bool
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $categoriesRepository->findOneBy(['name' => $name]);

        return $node !== null;
    }
}
