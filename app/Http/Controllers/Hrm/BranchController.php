<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\Branch;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use App\Models\Hrm\BranchWorkingHour;
use App\Events\Hrm\CreateBranch;
use App\Events\Hrm\DestroyBranch;
use App\Events\Hrm\UpdateBranch;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('branch manage')) {
            $perPage = $request->get('per_page', 10);
            $branches = Branch::where('created_by', '=', creatorId())
                ->where('workspace', getActiveWorkspace())
                ->paginate($perPage)
                ->appends($request->query());
            return view('hrm.branch.index', compact('branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('branch create')) {
            return view('hrm.branch.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('branch create')) {
            $validator = \Validator::make(

                $request->all(),
                [
                    'name' => [
                        'required',
                        \Illuminate\Validation\Rule::unique('branches')->where(function ($query) {
                            return $query->where('workspace', getActiveWorkspace());
                        }),
                    ],
                    'address' => 'nullable|string|max:500',
                    'latitude' => 'nullable|numeric|between:-90,90',
                    'longitude' => 'nullable|numeric|between:-180,180',
                    'attendance_radius' => 'nullable|integer|min:0|max:10000',
                    'clock_in_tolerance_minutes' => 'nullable|integer|min:0|max:60',
                    'clock_out_tolerance_minutes' => 'nullable|integer|min:0|max:60',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();
            try {
                $branch = new Branch();
                $branch->name = $request->name;
                $branch->address = $request->address;
                $branch->latitude = $request->latitude;
                $branch->longitude = $request->longitude;
                $branch->attendance_radius = $request->attendance_radius ?? 100;
                $branch->clock_in_tolerance_minutes = $request->clock_in_tolerance_minutes ?? 15;
                $branch->clock_out_tolerance_minutes = $request->clock_out_tolerance_minutes ?? 15;
                $branch->workspace = getActiveWorkspace();
                $branch->created_by = creatorId();
                $branch->save();

                // Create default working hours for the branch
                BranchWorkingHour::createDefaultForBranch($branch->id);

                event(new CreateBranch($request, $branch));

                DB::commit();

                return response()->json([
                    'success' => __('Branch successfully created.'),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => __('Error creating branch: ') . $e->getMessage(),
                ], 500);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('hrm.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch edit')) {
            if ($branch->created_by == creatorId() &&  $branch->workspace  == getActiveWorkspace()) {
                return view('hrm.branch.edit', compact('branch'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch edit')) {
            if ($branch->created_by == creatorId() &&  $branch->workspace  == getActiveWorkspace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => [
                            'required',
                            \Illuminate\Validation\Rule::unique('branches')->where(function ($query) {
                                return $query->where('workspace', getActiveWorkspace());
                            })->ignore($branch->id),
                        ],
                        'address' => 'nullable|string|max:500',
                        'latitude' => 'nullable|numeric|between:-90,90',
                        'longitude' => 'nullable|numeric|between:-180,180',
                        'attendance_radius' => 'nullable|integer|min:0|max:10000',
                        'clock_in_tolerance_minutes' => 'nullable|integer|min:0|max:60',
                        'clock_out_tolerance_minutes' => 'nullable|integer|min:0|max:60',
                    ]
                );
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $branch->name = $request->name;
                $branch->address = $request->address;
                $branch->latitude = $request->latitude;
                $branch->longitude = $request->longitude;
                $branch->attendance_radius = $request->attendance_radius ?? $branch->attendance_radius;
                $branch->clock_in_tolerance_minutes = $request->clock_in_tolerance_minutes ?? $branch->clock_in_tolerance_minutes;
                $branch->clock_out_tolerance_minutes = $request->clock_out_tolerance_minutes ?? $branch->clock_out_tolerance_minutes;
                $branch->save();

                event(new UpdateBranch($request, $branch));
                return response()->json([
                    'success' => true,
                    'branch' => $branch,
                    'message' => __('Branch successfully Updated.'),
                ]);
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch delete')) {
            if ($branch->created_by == creatorId() &&  $branch->workspace  == getActiveWorkspace()) {
                $employee     = Employee::where('branch_id', $branch->id)->where('workspace_id', getActiveWorkspace())->get();
                if (count($employee) == 0) {
                    Department::where('branch_id', $branch->id)->delete();
                    Designation::where('branch_id', $branch->id)->delete();

                    event(new DestroyBranch($branch));

                    $branch->delete();
                } else {
                    return redirect()->route('branch.index')->with('error', __('This branch has employees. Please remove the employee from this branch.'));
                }

                return redirect()->route('branch.index')->with('success', __('Branch successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function BranchNameEdit()
    {
        if (Auth::user()->isAbleTo('branch name edit')) {
            return view('hrm.branch.branchnameedit');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function saveBranchName(Request $request)
    {
        if (Auth::user()->isAbleTo('branch name edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'hrm_branch_name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            } else {
                $post = $request->all();
                unset($post['_token']);

                foreach ($post as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key' => $key,
                        'workspace' => getActiveWorkspace(),
                        'created_by' => creatorId(),
                    ];
                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
                // Settings Cache forget
                companySettingCacheForget();
                return redirect()->route('branch.index')->with('success', __('Branch Name successfully updated.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show working hours configuration for a branch
     * @param Branch $branch
     * @return Renderable
     */
    public function workingHours(Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch edit')) {
            if ($branch->created_by == creatorId() && $branch->workspace == getActiveWorkspace()) {
                $workingHours = BranchWorkingHour::where('branch_id', $branch->id)
                    ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
                    ->get()
                    ->keyBy('day');
                
                // Ensure all days exist
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days as $day) {
                    if (!isset($workingHours[$day])) {
                        $workingHours[$day] = BranchWorkingHour::create([
                            'branch_id' => $branch->id,
                            'day' => $day,
                            'is_working_day' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                            'start_time' => '08:00:00',
                            'end_time' => '17:00:00',
                        ]);
                    }
                }

                return view('hrm.branch.working-hours', compact('branch', 'workingHours'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update working hours for a branch
     * @param Request $request
     * @param Branch $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateWorkingHours(Request $request, Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch edit')) {
            if ($branch->created_by == creatorId() && $branch->workspace == getActiveWorkspace()) {
                $validator = \Validator::make($request->all(), [
                    'working_hours' => 'required|array',
                    'working_hours.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                    'working_hours.*.is_working_day' => 'required|boolean',
                    'working_hours.*.start_time' => 'nullable|date_format:H:i',
                    'working_hours.*.end_time' => 'nullable|date_format:H:i|after:working_hours.*.start_time',
                    'working_hours.*.lunch_start_time' => 'nullable|date_format:H:i',
                    'working_hours.*.lunch_end_time' => 'nullable|date_format:H:i|after:working_hours.*.lunch_start_time',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                    ], 422);
                }

                DB::beginTransaction();
                try {
                    foreach ($request->working_hours as $data) {
                        BranchWorkingHour::updateOrCreate(
                            [
                                'branch_id' => $branch->id,
                                'day' => $data['day'],
                            ],
                            [
                                'is_working_day' => $data['is_working_day'],
                                'start_time' => $data['is_working_day'] ? $data['start_time'] : null,
                                'end_time' => $data['is_working_day'] ? $data['end_time'] : null,
                                'lunch_start_time' => $data['is_working_day'] ? ($data['lunch_start_time'] ?? null) : null,
                                'lunch_end_time' => $data['is_working_day'] ? ($data['lunch_end_time'] ?? null) : null,
                            ]
                        );
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => __('Working hours updated successfully.'),
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => __('Error updating working hours: ') . $e->getMessage(),
                    ], 500);
                }
            } else {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show geolocation settings for a branch
     * @param Branch $branch
     * @return Renderable
     */
    public function geolocation(Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch edit')) {
            if ($branch->created_by == creatorId() && $branch->workspace == getActiveWorkspace()) {
                return view('hrm.branch.geolocation', compact('branch'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update geolocation settings for a branch
     * @param Request $request
     * @param Branch $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGeolocation(Request $request, Branch $branch)
    {
        if (Auth::user()->isAbleTo('branch edit')) {
            if ($branch->created_by == creatorId() && $branch->workspace == getActiveWorkspace()) {
                $validator = \Validator::make($request->all(), [
                    'address' => 'nullable|string|max:500',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                    'attendance_radius' => 'required|integer|min:10|max:10000',
                    'clock_in_tolerance_minutes' => 'nullable|integer|min:0|max:60',
                    'clock_out_tolerance_minutes' => 'nullable|integer|min:0|max:60',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $branch->address = $request->address;
                $branch->latitude = $request->latitude;
                $branch->longitude = $request->longitude;
                $branch->attendance_radius = $request->attendance_radius;
                $branch->clock_in_tolerance_minutes = $request->clock_in_tolerance_minutes ?? 15;
                $branch->clock_out_tolerance_minutes = $request->clock_out_tolerance_minutes ?? 15;
                $branch->save();

                return response()->json([
                    'success' => true,
                    'branch' => $branch,
                    'message' => __('Geolocation settings updated successfully.'),
                ]);
            } else {
                return response()->json(['error' => __('Permission denied.')], 403);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
