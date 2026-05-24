<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Hrm\Payroll;
use App\Models\Hrm\Garnishee;
use App\Models\Hrm\IncomeProtection;
use App\Models\Hrm\MaintenanceOrder;
use App\Models\Hrm\MedicalAid;
use App\Models\Hrm\PensionFund;
use App\Models\Hrm\ProvidentFund;
use App\Models\Hrm\RetirementAnnuitie;
use App\Models\Hrm\UnionMembershipFee;
use App\Models\Hrm\TaxOverDeduction;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PayrollFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $query = Payroll::query();
    
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
            $this->updatePayrollNetPay($request->employee_id); 
        }
    
        $payrolls = $query->get();
    
        return view('payroll.index', compact('payrolls'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function updatePayrollNetPay($employee_id)
{
    $payroll = Payroll::where('employee_id', $employee_id)->first();

    if ($payroll) {
        $deductions = $this->calculateDeductions($employee_id);
        $payroll->net_pay = max($payroll->gross_salary - $deductions, 0);
        $payroll->save(); // Store updated net pay in database
    }
}

    /**
     * Calculate total deductions for an employee
     * @param int $employeeId
     * @return float
     */
    private function calculateDeductions($employee_id)
    {
       
        $garnishee = Garnishee::where('employee_id', $employee_id)->sum('installment');
        $incomeProtection = IncomeProtection::where('employee_id', $employee_id)->sum('amount_deducted');
        $maintenanceOrder = MaintenanceOrder::where('employee_id', $employee_id)->sum('amount');
        $medicalAid = MedicalAid::where('employee_id', $employee_id)->sum('contribution');
        $pensionFund = PensionFund::where('employee_id', $employee_id)->sum('contribution');
        $providentFund = ProvidentFund::where('employee_id', $employee_id)->sum('contribution');
        $retirementAnnuity = RetirementAnnuitie::where('employee_id', $employee_id)->sum('contribution');
        $unionFee = UnionMembershipFee::where('employee_id', $employee_id)->sum('fee');
        $voluntaryTax = TaxOverDeduction::where('employee_id', $employee_id)->sum('amount');
    
       
        $totalDeductions = $garnishee + $incomeProtection + $maintenanceOrder + $medicalAid +
                           $pensionFund + $providentFund + $retirementAnnuity + $unionFee + $voluntaryTax;
    
        return $totalDeductions;
    }
}
