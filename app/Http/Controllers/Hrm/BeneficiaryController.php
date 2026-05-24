<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\Beneficiary;
use App\Models\Hrm\Employee;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BeneficiaryController extends Controller
{
    protected function ensureEmployeeInWorkspace(int $employeeId): Employee
    {
        $employee = Employee::where('id', $employeeId)->first();

        if (!$employee) {
            abort(403, 'Unauthorized employee access.');
        }

        return $employee;
    }

    protected function findWorkspaceBeneficiaryOrFail(int $id): Beneficiary
    {
        return Beneficiary::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $beneficiaries = Beneficiary::whereHas('employee', function ($query) {
            $query->where('id', '>', 0);
        })->with('employee')->paginate(10);
        return view('hrm.beneficiary.index', compact('beneficiaries'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $employees = Employee::select('id', 'first_name', 'last_name')->get();
        return view('hrm.beneficiary.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'amount_per_month' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        $this->ensureEmployeeInWorkspace((int) $validatedData['employee_id']);

        Beneficiary::create($validatedData);

        return redirect()->route('beneficiary.index')->with('success', 'Beneficiary created successfully!');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('hrm.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $beneficiary = $this->findWorkspaceBeneficiaryOrFail((int) $id);
        $employees = Employee::select('id', 'first_name', 'last_name')->get();
        return view('hrm.beneficiary.edit', compact('beneficiary', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'amount_per_month' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        $this->ensureEmployeeInWorkspace((int) $validatedData['employee_id']);

        $beneficiary = $this->findWorkspaceBeneficiaryOrFail((int) $id);
        $beneficiary->update($validatedData);

        return redirect()->route('beneficiary.list')->with('success', 'Beneficiary updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $beneficiary = $this->findWorkspaceBeneficiaryOrFail((int) $id);
        $beneficiary->delete();

        return redirect()->route('beneficiary.list')->with('success', 'Beneficiary deleted successfully!');
    }


    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $beneficiary = $this->findWorkspaceBeneficiaryOrFail((int) $id);

        if (!$beneficiary) {
            return response()->json([
                'status' => false,
                'message' => 'Beneficiary not found!',
            ], 404);
        }

        $beneficiary->status = $request->status;
        $beneficiary->save();

        return response()->json([
            'status' => true,
            'newStatus' => $beneficiary->status,
            'message' => 'Beneficiary status updated successfully!',
        ]);
    }
}
