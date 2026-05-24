<?php

namespace App\Http\Controllers\Hrm;

use Carbon\Carbon;
use App\Models\Hrm\Covid19Disaster;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\Employee;
use App\Models\Hrm\PaySlip;

class Covid19DisasterController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('hrm.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $employeeId = $request->employee_id;
        $term = $request->term;
        if (!$employeeId) {
            return redirect()->route('payroll.index')->with('error', 'Employee ID is required.');
        }

        $employee = Employee::find($employeeId);

        if (!$employee) {
            return redirect()->route('payroll.index')->with('error', 'Employee not found.');
        }
        return view('hrm.covid19-disasters.create',compact('employee','term'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'affects_wage_eti' => 'nullable|boolean',
            'employee_id' => 'required|exists:employees,id',
        ]);

        try {
            if($request->term){
                $term = $request->term;
                if($request->affects_wage_eti){
                    $affects_wage_eti = $request->affects_wage_eti;
                }else{
                    $affects_wage_eti = 0;
                }
            }else{
                $payslip = PaySlip::where('employee_id',$request->employee_id)->latest('id')->first();

                if ($payslip && $payslip->salary_month) {
                    if($payslip->status == 0){
                        $term = Carbon::parse($payslip->salary_month.'-01')->endOfMonth()->format('Y-m-d');
                    }else{
                        $term = Carbon::parse($payslip->salary_month.'-01')->addMonth()->endOfMonth()->format('Y-m-d');
                    }
                }else{
                    $employee = Employee::findOrFail($request->employee_id);
                    $term = Carbon::parse($employee->date_of_appointment)->endOfMonth()->format('Y-m-d');
                }
                if($request->affects_wage_eti){
                    $affects_wage_eti = $request->affects_wage_eti;
                }else{
                    $affects_wage_eti = 0;
                }
            }
              //$term = $request->term ?? $term;
            Covid19Disaster::create([
                'employee_id' => $request->employee_id,
                'term' => $term,
                'amount' => $request->amount,
                'affects_wage_eti' => $affects_wage_eti,
            ]);

            DB::commit();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])
                    ->with('success', 'Covid Disaster Relief added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('payroll.index', ['employee_id' => $request->employee_id,'term' => $term])->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
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
    public function edit(Request $request, $id)
    {
        $covid19Disaster = Covid19Disaster::find($id);
        $term = $request->query('term', $covid19Disaster->term); 
        return view('hrm.covid19-disasters.edit', compact('covid19Disaster','term'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id )
    {
        $covid19Disaster = Covid19Disaster::findOrFail($id);
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'affects_wage_eti' => 'nullable|boolean',
            'term'               => 'required|date',
        ]);

        $covid19Disaster->update($request->all());
        return redirect()->route('payroll.index', ['employee_id' => $covid19Disaster->employee_id,'term' => $covid19Disaster->term])
                    ->with('success', 'Covid Disaster Relief updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $covid19Disaster = Covid19Disaster::findOrFail($id);
        $employee_id = $covid19Disaster->employee_id;
        $term = $covid19Disaster->term;
        $covid19Disaster->delete();

        return redirect()->route('payroll.index', ['employee_id' => $employee_id,'term' => $term])->with('success', 'Covid19 Disaster deleted successfully.');
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'affects_wage_eti' => 'nullable|boolean',
            'employee_id' => 'required|exists:employees,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'affects_wage_eti' => 'nullable|boolean',
            'term' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return response()->json(['success' => true]);
    }
}
