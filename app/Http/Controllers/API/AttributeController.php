<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Http\Requests\Attribute\StoreAttributeRequest;
use App\Http\Requests\Attribute\UpdateAttributeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @author Fahed
 * @description Handles CRUD operations for dynamic project attributes
 * @package App\Http\Controllers\API
 */
class AttributeController extends Controller
{
    /**
     * Display a list of all available dynamic attributes.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "project_id": 1,
     *       "name": "department",
     *       "type": "select",
     *       "value": "IT",
     *       "options": ["IT", "HR", "Finance"],
     *       "created_at": "2024-03-10T15:30:00.000000Z"
     *     },
     *     {
     *       "id": 2,
     *       "project_id": 1,
     *       "name": "start_date",
     *       "type": "date",
     *       "value": "2024-03-10",
     *       "options": null,
     *       "created_at": "2024-03-10T15:30:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        $query = Attribute::query();
        
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        $attributes = $query->get();
        return response()->json(['data' => $attributes]);
    }

    /**
     * Store a new dynamic attribute.
     *
     * @param StoreAttributeRequest $request Validated attribute data
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 201 {
     *   "id": 1,
     *   "project_id": 1,
     *   "name": "department",
     *   "type": "select",
     *   "value": "IT",
     *   "options": ["IT", "HR", "Finance"],
     *   "created_at": "2024-03-10T15:30:00.000000Z"
     * }
     * 
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["This attribute name already exists in this project."]
     *   }
     * }
     */
    public function store(StoreAttributeRequest $request)
    {
        // Check for unique name within project
        $exists = Attribute::where('project_id', $request->project_id)
            ->where('name', $request->name)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['This attribute name already exists in this project.']
                ]
            ], 422);
        }

        $attribute = Attribute::create($request->validated());
        return response()->json($attribute, 201);
    }

    /**
     * Display the specified attribute.
     *
     * @param Attribute $attribute
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "id": 1,
     *   "project_id": 1,
     *   "name": "department",
     *   "type": "select",
     *   "value": "IT",
     *   "options": ["IT", "HR", "Finance"]
     * }
     */
    public function show(Attribute $attribute)
    {
        return response()->json($attribute);
    }

    /**
     * Update the specified attribute.
     *
     * @param UpdateAttributeRequest $request Validated update data
     * @param Attribute $attribute
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "id": 1,
     *   "project_id": 1,
     *   "name": "department",
     *   "type": "select",
     *   "value": "Marketing",
     *   "options": ["IT", "HR", "Finance", "Marketing"]
     * }
     * 
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["This attribute name already exists in this project."]
     *   }
     * }
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        // Check for unique name within project if name is being changed
        if ($request->has('name') && $request->name !== $attribute->name) {
            $exists = Attribute::where('project_id', $attribute->project_id)
                ->where('name', $request->name)
                ->where('id', '!=', $attribute->id)
                ->exists();
                
            if ($exists) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'name' => ['This attribute name already exists in this project.']
                    ]
                ], 422);
            }
        }

        $attribute->update($request->validated());
        return response()->json($attribute);
    }

    /**
     * Remove the specified attribute.
     *
     * @param Attribute $attribute
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 204 {}
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return response()->json(null, 204);
    }
}
