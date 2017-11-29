<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * @return Illuminate\Http\Response
     */
    public function createCategory(Request $request)
    {
        if ($request->has('name') && count($request->except('name')) === 0) {
            $category = new Category();
            $category->setName($request->get('name'));

            $entityManager = app()->make('Neo4j\EntityManager');
            $entityManager->persist($category);
            $entityManager->flush();

            $response = response($category->getId(), 200);
        } elseif (count($request->all()) === 0) {
            $response = response('Empty request.', 405);
        } elseif (!$request->has('name')) {
            $response = response('No category name defined.', 405);
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

            $response = response($message, 405);
        }

        return $response;
    }
}
