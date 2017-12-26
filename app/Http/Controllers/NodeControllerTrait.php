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
     * @return JsonResponse
     */
    public function getNode(string $label, Request $request, int $id): JsonResponse
    {
        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->find($id);

        if ($node === null) {
            $this->addError($label.' with id "'.$id.'" not found.');
            return $this->generateJsonResponse(404);
        }

        return response()->json(['name' => $node->getName(), 'id' => $node->getId()]);
    }

    /**
     * @return JsonResponse
     */
    public function updateNode(string $label, Request $request, int $id): JsonResponse
    {
        if (!$this->nodeWithIdExists($label, $id)) {
            $this->addError($label.' with id "'.$id.'" not found.');
            return $this->generateJsonResponse(404);
        }

        if ($request->has('id') && $request->get('id') != $id) {
            $this->addError("Changing the $label id is not allowed.");
            return $this->generateJsonResponse(400);
        }

        $entityManager = app()->make('Neo4j\EntityManager');
        $nodesRepository = $entityManager->getRepository('App\Models\\'.$label);
        $node = $nodesRepository->find($id);

        $node->setName($request->get('name'));
        $entityManager->persist($node);
        $entityManager->flush();
        return response()->json(['name' => $node->getName(), 'id' => $id]);
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
