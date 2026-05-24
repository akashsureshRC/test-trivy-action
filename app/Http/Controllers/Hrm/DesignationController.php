<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use Exception;
use App\Models\Hrm\Branch;
use App\Models\Hrm\Employee;
use App\Events\Hrm\CreateDesignation;
use App\Events\Hrm\DestroyDesignation;
use App\Events\Hrm\UpdateDesignation;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('designation manage')) {
            $perPage = $request->get('per_page', 10);
            $designations = Designation::where('created_by', '=', creatorId())
                ->where('workspace', getActiveWorkspace())
                ->with(['branch', 'department'])
                ->paginate($perPage)
                ->appends($request->query());

            return view('hrm.designation.index', compact('designations'));
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
        if (Auth::user()->isAbleTo('designation create')) {
            $branchs = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');

            return view('hrm.designation.create', compact('branchs', 'departments'));
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
        if (Auth::user()->isAbleTo('designation create')) {
            $workspace = getActiveWorkspace();
            $creator = creatorId();
            $validator = \Validator::make($request->all(), [
                'branch_id' => ['required', 'exists:branches,id'], 
                'department_id' => ['required', 'exists:departments,id'],
                'name' => [
                    'required',
                    Rule::unique('designations')->where(function ($query) use ($request, $workspace) {
                        return $query->where('branch_id', $request->branch_id)
                            ->where('department_id', $request->department_id)
                            ->where('workspace', $workspace);
                    }),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            try {
                $branch = Department::where('id', $request->department_id)
                    ->where('created_by', '=', creatorId())
                    ->where('workspace', getActiveWorkspace())
                    ->first()->branch->id;
            } catch (Exception $e) {
                $branch = null;
            }

            $designation = new Designation();
            $designation->branch_id = $branch;
            $designation->department_id = $request->department_id;
            $designation->name = $request->name;
            $designation->workspace = getActiveWorkspace();
            $designation->created_by = creatorId();
            $designation->save();

            event(new CreateDesignation($request, $designation));
            return response()->json([
                'success' => __('Designation successfully created.'),
            ]);
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
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
    public function edit(Designation $designation)
    {
        if (Auth::user()->isAbleTo('designation edit')) {
            if ($designation->created_by == creatorId() &&  $designation->workspace  == getActiveWorkspace()) {
                $branchs = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
                $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->get()->pluck('name', 'id');
                return view('hrm.designation.edit', compact('designation', 'departments', 'branchs'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
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
    public function update(Request $request, Designation $designation)
    {
        if (Auth::user()->isAbleTo('designation edit')) {
            if ($designation->created_by == creatorId() &&  $designation->workspace  == getActiveWorkspace()) {
                $validator = \Validator::make($request->all(), [
    'department_id' => ['required', 'exists:departments,id'],
    'name' => [
        'required',
        Rule::unique('designations')
            ->where(function ($query) use ($request) {
                return $query->where('branch_id', $request->branch_id)
                             ->where('department_id', $request->department_id)
                             ->where('workspace', getActiveWorkspace());
            })
            ->ignore($designation->id),
    ],
]);

                 if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }
                try {
                    $branch = Department::where('id', $request->department_id)->where('created_by', '=', creatorId())->where('workspace', getActiveWorkspace())->first()->branch->id;
                } catch (Exception $e) {
                    $branch = null;
                }
                $designation->branch_id     = $branch;
                $designation->department_id = $request->department_id;
                $designation->name          = $request->name;
                $designation->save();

                event(new UpdateDesignation($request, $designation));
  return response()->json([
        'success' => true,
        'designation' => $designation,
        'message' => __('Designation successfully updated.'),
    ]);
                return redirect()->route('designation.index')->with('success', __('Designation  successfully updated.'));
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
    public function destroy(Designation $designation)
    {
        if (Auth::user()->isAbleTo('designation delete')) {
            if ($designation->created_by == creatorId() &&  $designation->workspace  == getActiveWorkspace()) {
                $employee     = Employee::where('designation_id', $designation->id)->where('workspace_id', getActiveWorkspace())->get();
                if (count($employee) == 0) {
                    event(new DestroyDesignation($designation));

                    $designation->delete();
                } else {
                    return redirect()->route('designation.index')->with('error', __('This designation has employees. Please remove the employee from this designation.'));
                }
                return redirect()->route('designation.index')->with('success', __('Designation successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function DesignationNameEdit()
    {
        if (Auth::user()->isAbleTo('designation name edit')) {
            return view('hrm.designation.designationnameedit');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function saveDesignationName(Request $request)
    {
        if (Auth::user()->isAbleTo('designation name edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'hrm_designation_name' => 'required',
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
                return redirect()->route('designation.index')->with('success', __('Designation Name successfully updated.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function newDesignation(Request $request)
{
    if (!Auth::user()->isAbleTo('designation create')) {
        return response()->json(['error' => __('Permission denied.')], 401);
    }

    $workspace = getActiveWorkspace();

    $validator = Validator::make($request->all(), [
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('designations')->where(function ($query) use ($request, $workspace) {
                return $query->where('branch_id', $request->branch_id)
                             ->where('department_id', $request->department_id)
                             ->where('workspace', $workspace);
            }),
        ],
        'branch_id' => 'required|integer|exists:branches,id',
        'department_id' => 'required|integer|exists:departments,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $designation = Designation::create([
            'name' => $request->name,
            'branch_id' => $request->branch_id,
            'department_id' => $request->department_id,
            'workspace' => $workspace,
            'created_by' => creatorId(),
        ]);

        return response()->json([
            'success' => true,
            'designation' => $designation,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to create designation: ' . $e->getMessage(),
        ], 500);
    }
}
}
