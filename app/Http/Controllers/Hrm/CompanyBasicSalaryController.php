<?php

namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\CompanyBasicSalary;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class CompanyBasicSalaryController extends Controller
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
    public function create()
    {
        return view('hrm.company-basic-salaries.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
       
    

        $request->validate([
            'hourly_rate' => 'required|numeric|min:0',
            'holiday_normal_multiplier' => 'required|numeric|min:1',
            'holiday_overtime_multiplier' => 'required|numeric|min:1',
            'normally_works_multiplier' => 'required|numeric|min:1',
            'normally_off_multiplier' => 'required|numeric|min:1',
        ]);

        CompanyBasicSalary::Create([
            'hourly_rate' => $request->hourly_rate,
            'dont_auto_pay_holidays' => $request->has('dont_auto_pay_holidays'),
            'enable_shifts' => $request->has('enable_shifts'),
            'employee_minimum_pay' => $request->employee_minimum_pay,
            'employee_fixed_component' => $request->employee_fixed_component,
            'work_minimum_pay' => $request->work_minimum_pay,
            'work_fixed_component' => $request->work_fixed_component,
            'override_holiday_pay_rates' => $request->has('override_holiday_pay_rates'),
            'holiday_normal_multiplier' => $request->holiday_normal_multiplier,
            'holiday_overtime_multiplier' => $request->holiday_overtime_multiplier,
            'override_sunday_pay_rates' => $request->has('override_sunday_pay_rates'),
            'normally_works_multiplier' => $request->normally_works_multiplier,
            'normally_off_multiplier' => $request->normally_off_multiplier,
            'separate_overtime_hours' => $request->has('separate_overtime_hours'),
        ]);

        return redirect()->back()->with('success', 'Company Bbasic Salaries Updated Successfully!');
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
        $companybasicSalary = CompanyBasicSalary::findOrFail($id);
        return view('hrm.company-basic-salaries.edit', compact('companybasicSalary'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $companybasicSalary = CompanyBasicSalary::findOrFail($id);

        $request->validate([
            'hourly_rate' => 'required|numeric|min:0',
            'holiday_normal_multiplier' => 'required|numeric|min:1',
            'holiday_overtime_multiplier' => 'required|numeric|min:1',
            'normally_works_multiplier' => 'required|numeric|min:1',
            'normally_off_multiplier' => 'required|numeric|min:1',
        ]);

        $companybasicSalary->update($request->all());

        return redirect()->route('company-basic-salaries.edit', $id)->with('success', 'Company Bbasic Salaries Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function ajaxValidateStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hourly_rate'                  => 'required|numeric|min:0',
            'holiday_normal_multiplier'    => 'required|numeric|min:1',
            'holiday_overtime_multiplier'  => 'required|numeric|min:1',
            'normally_works_multiplier'    => 'required|numeric|min:1',
            'normally_off_multiplier'      => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function ajaxValidateUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'hourly_rate'                  => 'required|numeric|min:0',
            'holiday_normal_multiplier'    => 'required|numeric|min:1',
            'holiday_overtime_multiplier'  => 'required|numeric|min:1',
            'normally_works_multiplier'    => 'required|numeric|min:1',
            'normally_off_multiplier'      => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['success' => true]);
    }
}
