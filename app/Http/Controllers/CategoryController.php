<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @return Illuminate\Http\Response
     */
    public function createCategory(Request $request)
    {
        if ($request->has('key') && $request->has('properties')) {
            $response = response('', 200);
        } elseif (!$request->has('key')) {
            $response = response('No category key defined', 405);
        } elseif (!$request->has('properties')) {
            $response = response('No category properties defined', 405);
        }

        return $response;
    }
}
