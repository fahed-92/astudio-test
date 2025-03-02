<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @author Fahed
 * @description Handles CRUD operations for projects and their dynamic attributes
 * @package App\Http\Controllers\API
 */
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Test Project",
     *       "description": "A test project",
     *       "status": "active",
     *       "created_at": "2024-03-10T15:30:00.000000Z",
     *       "updated_at": "2024-03-10T15:30:00.000000Z",
     *       "users": [
     *         {
     *           "id": 1,
     *           "name": "John Doe",
     *           "email": "john@example.com"
     *         }
     *       ],
     *       "attributes": [
     *         {
     *           "id": 1,
     *           "name": "department",
     *           "value": "IT"
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = Project::query();

        // Only show projects the user has access to
        $query->whereHas('users', function (Builder $query) {
            $query->where('users.id', auth()->id());
        });

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user if provided
        if ($request->has('user_id')) {
            $query->whereHas('users', function (Builder $query) use ($request) {
                $query->where('users.id', $request->user_id);
            });
        }

        // Filter by name if provided in filters
        if ($request->has('filters')) {
            $filters = $request->filters;
            foreach ($filters as $key => $value) {
                if ($key === 'name') {
                    $query->where('name', 'like', "%{$value}%");
                } else {
                    $query->whereHas('attributes', function (Builder $query) use ($key, $value) {
                        $query->where('name', $key)
                            ->where('value', 'like', "%{$value}%");
                    });
                }
            }
        }

        $projects = $query->with(['users', 'attributes'])->paginate();
        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 201 {
     *   "id": 1,
     *   "name": "Test Project",
     *   "description": "A test project",
     *   "status": "active",
     *   "created_at": "2024-03-10T15:30:00.000000Z",
     *   "updated_at": "2024-03-10T15:30:00.000000Z",
     *   "users": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   ],
     *   "attributes": []
     * }
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        // Always attach the current user
        $userIds = array_unique(array_merge([$request->user()->id], $request->user_ids ?? []));
        $project->users()->attach($userIds);

        // Add attributes if provided
        if ($request->has('attributes')) {
            foreach ($request->attributes as $name => $value) {
                $project->setProjectAttributeValue($name, $value);
            }
        }

        return response()->json($project->load(['users', 'attributes']), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "id": 1,
     *   "name": "Test Project",
     *   "description": "A test project",
     *   "status": "active",
     *   "created_at": "2024-03-10T15:30:00.000000Z",
     *   "updated_at": "2024-03-10T15:30:00.000000Z",
     *   "users": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   ],
     *   "attributes": [
     *     {
     *       "id": 1,
     *       "name": "department",
     *       "value": "IT"
     *     }
     *   ]
     * }
     * 
     * @response 404 {
     *   "message": "Project not found."
     * }
     */
    public function show(Project $project)
    {
        if (!$project->users()->where('users.id', auth()->id())->exists()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        return response()->json($project->load(['users', 'attributes']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "id": 1,
     *   "name": "Updated Project",
     *   "description": "An updated project",
     *   "status": "active",
     *   "created_at": "2024-03-10T15:30:00.000000Z",
     *   "updated_at": "2024-03-10T15:30:00.000000Z",
     *   "users": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     }
     *   ],
     *   "attributes": [
     *     {
     *       "id": 1,
     *       "name": "department",
     *       "value": "Engineering"
     *     }
     *   ]
     * }
     * 
     * @response 404 {
     *   "message": "Project not found."
     * }
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        if (!$project->users()->where('users.id', auth()->id())->exists()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->update($request->validated());

        // Update users if provided, ensuring current user remains
        if ($request->has('user_ids')) {
            $userIds = array_unique(array_merge([$request->user()->id], $request->user_ids));
            $project->users()->sync($userIds);
        }

        // Update attributes if provided
        if ($request->has('attributes')) {
            foreach ($request->attributes as $name => $value) {
                $project->setProjectAttributeValue($name, $value);
            }
        }

        return response()->json($project->load(['users', 'attributes']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 204 {}
     * 
     * @response 404 {
     *   "message": "Project not found."
     * }
     */
    public function destroy(Project $project)
    {
        if (!$project->users()->where('users.id', auth()->id())->exists()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->delete();
        return response()->noContent();
    }
}
