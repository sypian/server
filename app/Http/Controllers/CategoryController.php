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
        if ($request->has('key') && $request->has('properties')) {
            $category = new Category();
            $category->setName($request->get('key'));

            $entityManager = app()->make('Neo4j\EntityManager');
            $entityManager->persist($category);
            $entityManager->flush();

            $response = response($category->getId(), 200);
        } elseif (!$request->has('key')) {
            $response = response('No category key defined', 405);
        } elseif (!$request->has('properties')) {
            $response = response('No category properties defined', 405);
        }

        return $response;
    }
}
