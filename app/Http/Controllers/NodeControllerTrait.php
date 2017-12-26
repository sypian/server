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
    use JsonResponseTrait;

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

        $this->addToPayload($node->getId(), 'id');
        return $this->generateJsonResponse(200);
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

            $this->addError($label.' with name "'.$name.'" already exists.');
            return $this->generateJsonResponse(400);
        }

        $this->addError('No '.$label.' name defined.');
        return $this->generateJsonResponse(400);
    }

    /**
     * Verifies a node by a node id and returns a failure response on failure or null if the id exists.
     *
     * @return null|JsonResponse
     */
    public function verifyNodeById(string $label, int $nodeId): ?JsonResponse
    {
        if (!$this->nodeWithIdExists($label, $nodeId)) {
            $this->addError($label.' with id "'.$nodeId.'" not found.');
            return $this->generateJsonResponse(404);
        }

        return null;
    }

    /**
     * @return JsonResponse
     */
    public function getNode(string $label, Request $request, int $nodeId): JsonResponse
    {
        $response = $this->verifyNodeById($label, $nodeId);

        if ($response !== null) {
            return $response;
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->find($nodeId);

        return response()->json(['name' => $node->getName(), 'id' => $node->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function updateNode(string $label, Request $request, int $nodeId): JsonResponse
    {
        $response = $this->verifyNodeById($label, $nodeId);

        if ($response !== null) {
            return $response;
        }

        if ($request->has('id') && $request->get('id') != $nodeId) {
            $this->addError("Changing the $label id is not allowed.");
            return $this->generateJsonResponse(400);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->find($nodeId);

        $node->setName($request->get('name'));
        $entityManager->persist($node);
        $entityManager->flush();
        return response()->json(['name' => $node->getName(), 'id' => $nodeId]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteNode(string $label, Request $request, int $nodeId, callable $removeRelations): JsonResponse
    {
        $response = $this->verifyNodeById($label, $nodeId);

        if ($response !== null) {
            return $response;
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->findOneById($nodeId);

        // Use the Closure to remove potencial relations!
        $removeRelations($node);

        $entityManager->remove($node);
        $entityManager->flush();
        $this->addToPayload($label.' with id "'.$nodeId.'" got deleted.');
        return $this->generateJsonResponse(200);
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
