<?php
namespace App\Http\Controllers\Hrm;
use App\Models\Hrm\Payroll;
use App\Models\Hrm\PayslipCommission;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmployeesSalaryController extends Controller
{
   
        // Fetch employee data from Employee
       // Fetch all employees (you can add conditions if needed)
        
        public function index(Request $request)
        {

            return view('employee-salary.index');
            //$employees = Employee::all(); 
            // Retrieve the commission data from the request
           // $commission = PayslipCommission::latest()->first();

   // return view('hrm.salary.index', compact('commission','employees'));
            
            // Pass the commission data to the view
            //return view('hrm.salary.index', compact('commissionType', 'commissionAmount','employees'));
        }
        
        public function store(Request $request)
        {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'basic_salary' => 'required|numeric|min:0',
            ]);
        
            // Update or create the salary record
            EmployeeSalary::updateOrCreate(
                ['employee_id' => $request->employee_id],
                ['basic_salary' => $request->basic_salary]
            );
        
            return response()->json(['message' => 'Basic salary updated successfully!', 'basic_salary' => $request->basic_salary]);
        }
       
        public function updateBasicSalary(Request $request)
        {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'salary' => 'nullable|numeric|min:0',
                'hourly_rate' => 'nullable|numeric|min:0',
            ]);
        
            if (!$request->filled('salary') && !$request->filled('hourly_rate')) {
                return response()->json(['error' => 'Please enter either Hourly Rate or Fixed Salary.'], 422);
            }
        
            // Store salary in EmployeeSalary table
            EmployeeSalary::updateOrCreate(
                ['employee_id' => $request->employee_id],
                ['basic_salary' => $request->salary]
            );
        
            // Update payroll
            Payroll::updateOrCreate(
                ['employee_id' => $request->employee_id],
                [
                    'basic_salary' => $request->salary,
                    'hourly_rate' => $request->hourly_rate
                ]
            );
        
            return response()->json(['success' => true, 'salary' => $request->salary]);
        }
        public function calculatePayroll($employee_id)
        {
            $salary = EmployeeSalary::where('employee_id', $employee_id)->first();
        
            if (!$salary || is_null($salary->basic_salary)) {
                return response()->json(['error' => 'Basic salary not found!'], 404);
            }
        
            $total_salary = $salary->basic_salary;
        
            return response()->json(['total_salary' => $total_salary]);
        }
}