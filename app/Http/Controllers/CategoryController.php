<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * @return Response
     */
    public function createCategory(Request $request): JsonResponse
    {
        if ($request->has('name') && count($request->except('name')) === 0) {
            $category = new Category();
            $category->setName($request->get('name'));

            $entityManager = app()->make('Neo4j\EntityManager');
            $entityManager->persist($category);
            $entityManager->flush();

            $response = response()->json(['message' => $category->getId()], 200);
        } elseif (count($request->all()) === 0) {
            $response = response()->json(['message' => 'Empty request.'], 405);
        } elseif (!$request->has('name')) {
            $response = response()->json(['message' => 'No category name defined.'], 405);
        } elseif (count($request->except('name')) > 0) {
            $invalidParams = array_keys($request->except('name'));

            if (count($invalidParams) > 1) {
                $paramStr = '';

                foreach ($invalidParams as $paramKey) {
                    $paramStr .= '"'.$paramKey.'", ';
                }

                $paramStr = trim($paramStr, ', ');
                $message = sprintf('Properties %s not supported.', $paramStr);
            } else {
                $message = 'Property "'.reset($invalidParams).'" not supported.';
            }

            $response = response()->json(['message' => $message], 405);
        }

        return $response;
    }

    /**
     * @return Response
     */
    public function getCategory(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $entityManager = app()->make('Neo4j\EntityManager');
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $category = $categoriesRepository->findOneBy(['name' => $name]);

        if ($category === null) {
            $response = response()->json(['message' => 'Category "'.$name.'" not found.'], 404);
        } else {
            $response = response()->json(['name' => $category->getName(), 'id' => $category->getId()]);
        }

        return $response;
    }

    /**
     * @return Response
     */
    public function updateCategory(Request $request): JsonResponse
    {
        if (!$request->has('id')) {
            $response = response()->json(['message' => 'Missing category node id.'], 405);
        } else {
            $id = $request->get('id');
            $name = $request->get('name');
            $entityManager = app()->make('Neo4j\EntityManager');
            $categoriesRepository = $entityManager->getRepository(Category::class);
            $category = $categoriesRepository->findOneById($id);

            if ($category === null) {
                $response = response()->json(['message' => 'Category node with id "'.$id.'" not found.'], 404);
            } else {
                $category->setName($name);
                $entityManager->persist($category);
                $entityManager->flush();
                $response = response()->json(['name' => $category->getName(), 'id' => $category->getId()]);
            }
        }

        return $response;
    }
}
