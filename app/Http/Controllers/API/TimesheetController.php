<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Http\Requests\Timesheet\StoreTimesheetRequest;
use App\Http\Requests\Timesheet\UpdateTimesheetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @author Fahed
 * @description Handles CRUD operations for timesheet entries and time tracking
 * @package App\Http\Controllers\API
 */
class TimesheetController extends Controller
{
    /**
     * Display a paginated list of timesheet entries.
     * Supports filtering by user, project, and date range.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @queryParam user_id integer Filter entries by user ID
     * @queryParam project_id integer Filter entries by project ID
     * @queryParam date_from date Filter entries from this date
     * @queryParam date_to date Filter entries until this date
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "project_id": 1,
     *       "task_name": "Development",
     *       "date": "2024-03-10",
     *       "hours": 8,
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe"
     *       },
     *       "project": {
     *         "id": 1,
     *         "name": "Project Alpha"
     *       }
     *     }
     *   ],
     *   "links": {},
     *   "meta": {}
     * }
     */
    public function index(Request $request)
    {
        $query = Timesheet::with(['user', 'project']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $timesheets = $query->paginate(10);
        return response()->json($timesheets);
    }

    /**
     * Store a new timesheet entry.
     *
     * @param StoreTimesheetRequest $request Validated timesheet data
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 201 {
     *   "id": 1,
     *   "user_id": 1,
     *   "project_id": 1,
     *   "task_name": "Development",
     *   "date": "2024-03-10",
     *   "hours": 8,
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe"
     *   },
     *   "project": {
     *     "id": 1,
     *     "name": "Project Alpha"
     *   }
     * }
     */
    public function store(StoreTimesheetRequest $request)
    {
        $timesheet = $request->user()->timesheets()->create($request->all());

        return response()->json($timesheet->load(['user', 'project']), 201);
    }

    /**
     * Display the specified timesheet entry.
     *
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "id": 1,
     *   "user_id": 1,
     *   "project_id": 1,
     *   "task_name": "Development",
     *   "date": "2024-03-10",
     *   "hours": 8,
     *   "user": {},
     *   "project": {}
     * }
     */
    public function show(Timesheet $timesheet)
    {
        return response()->json($timesheet->load(['user', 'project']));
    }

    /**
     * Update the specified timesheet entry.
     *
     * @param UpdateTimesheetRequest $request Validated update data
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "id": 1,
     *   "task_name": "Updated Task",
     *   "hours": 6,
     *   "user": {},
     *   "project": {}
     * }
     */
    public function update(UpdateTimesheetRequest $request, Timesheet $timesheet)
    {
        $timesheet->update($request->all());

        return response()->json($timesheet->load(['user', 'project']));
    }

    /**
     * Remove the specified timesheet entry.
     *
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 204 {}
     */
    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();
        return response()->json(null, 204);
    }
}
