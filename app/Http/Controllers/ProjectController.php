<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;

class ProjectController extends Controller
{
    use NodeControllerTrait;

    /**
     * @return JsonResponse
     */
    public function createProject(Request $request): JsonResponse
    {
        return $this->createNode('Project', $request);
    }

    /**
     * @return JsonResponse
     */
    public function getProject(Request $request): JsonResponse
    {
        return $this->getNode('Project', $request);
    }

    /**
     * @return JsonResponse
     */
    public function updateProject(Request $request): JsonResponse
    {
        return $this->updateNode('Project', $request);
    }

    /**
     * @return JsonResponse
     */
    public function deleteProject(Request $request): JsonResponse
    {
        return $this->deleteNode('Project', $request);
    }
}
