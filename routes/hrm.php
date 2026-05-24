<?php
// hrm module
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hrm\AllowanceController;
use App\Http\Controllers\Hrm\AnnouncementController;
use App\Http\Controllers\Hrm\AttendanceController;
use App\Http\Controllers\Hrm\BranchController;
use App\Http\Controllers\Hrm\CommissionController;
use App\Http\Controllers\Hrm\CompanyContributionController;

use App\Http\Controllers\Hrm\DepartmentController;
use App\Http\Controllers\Hrm\DesignationController;
use App\Http\Controllers\Hrm\EmployeeController;
use App\Http\Controllers\Hrm\EventController;
use App\Http\Controllers\Hrm\HolidayController;
use App\Http\Controllers\Hrm\HrmController;
use App\Http\Controllers\Hrm\LeaveController;
use App\Http\Controllers\Hrm\OtherPaymentController;
use App\Http\Controllers\Hrm\OvertimeController;
use App\Http\Controllers\Hrm\PaySlipController;
use App\Http\Controllers\Hrm\PayrunController;
use App\Http\Controllers\Hrm\ReportController;
use App\Http\Controllers\Hrm\SaturationDeductionController;

use App\Http\Controllers\Hrm\EmployeeSalaryController;
use App\Http\Controllers\Hrm\IncomePolicyController;
use App\Http\Controllers\Hrm\AccommodationBenefitController;
use App\Http\Controllers\Hrm\CompanyCarUnderOperatingController;
use App\Http\Controllers\Hrm\BursariesScholarshipController;
use App\Http\Controllers\Hrm\CompanyCarController;
use App\Http\Controllers\Hrm\TaxDirectiveEntryController;
use App\Http\Controllers\Hrm\SavingsDeductionController;
use App\Http\Controllers\Hrm\EmployerLoanController;
use App\Http\Controllers\Hrm\TaxOverDeductionController;
use App\Http\Controllers\Hrm\UnionMembershipFeeController;
use App\Http\Controllers\Hrm\BeneficiaryController;
use App\Http\Controllers\Hrm\GarnisheeController;
use App\Http\Controllers\Hrm\IncomeProtectionController;
use App\Http\Controllers\Hrm\MaintenanceOrderController;
use App\Http\Controllers\Hrm\MedicalAidController;
use App\Http\Controllers\Hrm\PensionFundController;
use App\Http\Controllers\Hrm\ProvidentFundController;
use App\Http\Controllers\Hrm\RetirementAnnuitieController;
use App\Http\Controllers\Hrm\EmployeesSalaryController;
use App\Http\Controllers\Hrm\PayslipCommissionController;
use App\Http\Controllers\Hrm\BasicSalaryController;
use App\Http\Controllers\Hrm\AnnualBonusController;
use App\Http\Controllers\Hrm\AnnualPaymentController;
use App\Http\Controllers\Hrm\ArbitrationAwardController;
use App\Http\Controllers\Hrm\DividendsSubjectController;
use App\Http\Controllers\Hrm\ExtraPayController;
use App\Http\Controllers\Hrm\OnceOffCommissionController;
use App\Http\Controllers\Hrm\RestraintOfTradeController;
use App\Http\Controllers\Hrm\BroadBasedEmployeeController;
use App\Http\Controllers\Hrm\ComputerAllowanceController;
use App\Http\Controllers\Hrm\ExpenseClaimController;
use App\Http\Controllers\Hrm\EquityInstrumentController;
use App\Http\Controllers\Hrm\PhoneAllowanceController;
use App\Http\Controllers\Hrm\RelocationAllowanceController;
use App\Http\Controllers\Hrm\AllowanceInternationalController;
use App\Http\Controllers\Hrm\SubsistenceAllowanceController;
use App\Http\Controllers\Hrm\ToolAllowanceController;
use App\Http\Controllers\Hrm\UniformAllowanceController;
use App\Http\Controllers\Hrm\EmployeeBenefitController;
use App\Http\Controllers\Hrm\DonationController;
use App\Http\Controllers\Hrm\RepaymentController;
use App\Http\Controllers\Hrm\StaffPurchaseController;
use App\Http\Controllers\Hrm\Covid19DisasterController;
use App\Http\Controllers\Hrm\LongServiceAwardController;
use App\Http\Controllers\Hrm\TersPayoutController;
use App\Http\Controllers\Hrm\TerminationLumpController;
use App\Http\Controllers\Hrm\BursaryController;
use App\Http\Controllers\Hrm\MedicalCostController;
use App\Http\Controllers\Hrm\SdlRegistrationController;
use App\Http\Controllers\Hrm\CompanySettingController;
use App\Http\Controllers\Hrm\CompanyBasicSalaryController;
use App\Http\Controllers\Hrm\PrimaryBankAccountController;
use App\Http\Controllers\Hrm\AdditionalBankAccountController;
use App\Http\Controllers\Hrm\AddGarnisheeController;
use App\Http\Controllers\Hrm\AddMaintenanceOrderController;
use App\Http\Controllers\Hrm\AddMedicalAidController;
use App\Http\Controllers\Hrm\AddPensionFundController;
use App\Http\Controllers\Hrm\AddProvidentFundController;
use App\Http\Controllers\Hrm\AddRetirementFundController;
use App\Http\Controllers\Hrm\CustomBeneficiaryController;
use App\Http\Controllers\Hrm\CustomReimbursementController;
use App\Http\Controllers\Hrm\CustomBenefitController;
use App\Http\Controllers\Hrm\CustomEmployerContributionController;
use App\Http\Controllers\Hrm\CustomAllowanceController;
use App\Http\Controllers\Hrm\CustomDeductionController;
use App\Http\Controllers\Hrm\CustomIncomeController;
use App\Http\Controllers\Hrm\PayrollController;
use App\Http\Controllers\Hrm\TravelAllowanceController;
use App\Http\Controllers\Hrm\PayrollFilterController;
use App\Models\Hrm\Payroll;
use App\Http\Controllers\Hrm\LeaveManagementController;
use App\Http\Controllers\Hrm\EntitlementPolicyController;
use App\Http\Controllers\Hrm\GeneralSelfServiceSettingController;
use App\Http\Controllers\Hrm\EmployeeEntitlementPolicyController;
use App\Http\Controllers\Hrm\LeaveRecordController;
use App\Http\Controllers\Hrm\FilingController;
use App\Http\Controllers\Hrm\EssManagementController;
use App\Http\Controllers\Hrm\AttendanceReviewController;

Route::prefix('payroll')->group(function () {
    Route::get('/', [PayrollFilterController::class, 'index'])->name('payroll.index');
    Route::get('/create', [PayrollFilterController::class, 'create'])->name('payroll.create');
    Route::post('/store', [PayrollFilterController::class, 'store'])->name('payroll.store');
    Route::get('/{id}/edit', [PayrollFilterController::class, 'edit'])->name('payroll.edit');
    Route::post('/{id}/update', [PayrollFilterController::class, 'update'])->name('payroll.update');
    Route::delete('/{id}', [PayrollFilterController::class, 'destroy'])->name('payroll.destroy');
    Route::get('/payrolls', [PayrollFilterController::class, 'index']); // Remove duplicate name
});

// testing
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => 'PlanModuleCheck:Hrm'], function () {
    Route::prefix('hrm')->group(function () {
        Route::get('/', [HrmController::class, 'index'])->middleware(['auth']);
    });
    Route::get('dashboard/hrm', [HrmController::class, 'index'])->name('hrm.dashboard')->middleware(['auth']);
    // Attendance
    Route::resource('attendance', AttendanceController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('bulkattendance', [AttendanceController::class, 'BulkAttendance'])->name('attendance.bulkattendance')->middleware(
        [
            'auth',
        ]
    );
    Route::post('bulkattendance', [AttendanceController::class, 'BulkAttendanceData'])->name('attendance.bulkattendance')->middleware(
        [
            'auth',
        ]
    );
    Route::post('attendance/attendance', [AttendanceController::class, 'attendance'])->name('attendance.attendance')->middleware(
        [
            'auth',
        ]
    );

    // Attendance import

    Route::get('attendance/import/export', [AttendanceController::class, 'fileImportExport'])->name('attendance.file.import');
    Route::post('attendance/import', [AttendanceController::class, 'fileImport'])->name('attendance.import');
    Route::get('attendance/import/modal', [AttendanceController::class, 'fileImportModal'])->name('attendance.import.modal');
    Route::post('attendance/data/import/', [AttendanceController::class, 'AttendanceImportdata'])->name('attendance.import.data');

    // Attendance HR Review
    Route::get('attendance-review', [AttendanceReviewController::class, 'index'])->name('attendance.review.index')->middleware(['auth']);
    Route::get('attendance-review/{id}', [AttendanceReviewController::class, 'show'])->name('attendance.review.show')->middleware(['auth']);
    Route::put('attendance-review/{id}', [AttendanceReviewController::class, 'update'])->name('attendance.review.update')->middleware(['auth']);
    Route::post('attendance-review/bulk', [AttendanceReviewController::class, 'bulkReview'])->name('attendance.review.bulk')->middleware(['auth']);
    Route::post('attendance-review/{id}/flag', [AttendanceReviewController::class, 'flag'])->name('attendance.review.flag')->middleware(['auth']);
    Route::get('attendance-review-stats', [AttendanceReviewController::class, 'stats'])->name('attendance.review.stats')->middleware(['auth']);


    // branch
    Route::resource('branch', BranchController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('branchnameedit', [BranchController::class, 'BranchNameEdit'])->middleware(
        [
            'auth',
        ]
    )->name('branchname.edit');
    Route::post('branch-settings', [BranchController::class, 'saveBranchName'])->middleware(
        [
            'auth',
        ]
    )->name('branchname.update');
    
    // Branch geolocation and working hours
    Route::get('branch/{branch}/working-hours', [BranchController::class, 'workingHours'])->middleware(
        [
            'auth',
        ]
    )->name('branch.working-hours');
    Route::put('branch/{branch}/working-hours', [BranchController::class, 'updateWorkingHours'])->middleware(
        [
            'auth',
        ]
    )->name('branch.working-hours.update');
    Route::get('branch/{branch}/geolocation', [BranchController::class, 'geolocation'])->middleware(
        [
            'auth',
        ]
    )->name('branch.geolocation');
    Route::put('branch/{branch}/geolocation', [BranchController::class, 'updateGeolocation'])->middleware(
        [
            'auth',
        ]
    )->name('branch.geolocation.update');
    
    // department
    Route::resource('department', DepartmentController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('departmentnameedit', [DepartmentController::class, 'DepartmentNameEdit'])->middleware(
        [
            'auth',
        ]
    )->name('departmentname.edit');
    Route::post('department-settings', [DepartmentController::class, 'saveDepartmentName'])->middleware(
        [
            'auth',
        ]
    )->name('departmentname.update');
    Route::post('department/newDepartment', [DepartmentController::class, 'newDepartment'])->middleware(
        [
            'auth',
        ]
    )->name('department.new_department');
    Route::get('/get-designations/{departmentId}', [DepartmentController::class, 'getDesignations'])->middleware(
        [
            'auth',
        ]
    );
    Route::get('/get-departments/{branchId}', [DepartmentController::class, 'getDepartmentsByBranch'])->middleware(
        [
            'auth',
        ]
    );
    // designation
    Route::resource('designation', DesignationController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::post('designation/newDesignation', [DesignationController::class, 'newDesignation'])->middleware(
        [
            'auth',
        ]
    )->name('designation.new_designation');
    Route::get('designationnameedit', [DesignationController::class, 'DesignationNameEdit'])->middleware(
        [
            'auth',
        ]
    )->name('designationname.edit');
    Route::post('designation-settings', [DesignationController::class, 'saveDesignationName'])->middleware(
        [
            'auth',
        ]
    )->name('designationname.update');
    
    // Grid route must be defined BEFORE the resource to avoid being captured by {id}
    Route::get('/employees/grid', [EmployeeController::class, 'grid'])
        ->middleware(['auth'])
        ->name('employees.grid');
    
    Route::resource('employees', EmployeeController::class)
        ->middleware(['auth'])
        ->names([
            'index' => 'employees.list',
            'create' => 'employees.new',
            'store' => 'employees.save',
            'show' => 'employees.view',
            'edit' => 'employees.modify',
            'update' => 'employees.update',
            'destroy' => 'employees.destroy',
        ]);
    Route::post('/employees/status/{id}', [EmployeeController::class, 'updateStatus'])
        ->middleware(['auth'])
        ->name('employees.status');
    Route::delete('/employees/{id}/delete', [EmployeeController::class, 'destroy'])
        ->name('employees.delete');
    Route::get('/get-provinces/{country}', [EmployeeController::class, 'getProvinces'])
        ->name('country.provinces.list');

    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    
    // Employee working hours
    Route::get('employee/{id}/working-hours', [EmployeeController::class, 'workingHours'])
        ->middleware(['auth'])
        ->name('employee.working-hours');
    Route::put('employee/{id}/working-hours', [EmployeeController::class, 'updateWorkingHours'])
        ->middleware(['auth'])
        ->name('employee.working-hours.update');
    Route::post('employee/{id}/working-hours/copy-from-branch', [EmployeeController::class, 'copyWorkingHoursFromBranch'])
        ->middleware(['auth'])
        ->name('employee.working-hours.copy-from-branch');

    // Legacy employee routes - redirect to Employee
    Route::post('employee/getdepartmentes', [EmployeeController::class, 'getDepartments'])
        ->name('employee.getdepartments')
        ->middleware(['auth']);
    Route::post('employee/getdesignationes', [EmployeeController::class, 'getDesignations'])
        ->name('employee.getdesignations')
        ->middleware(['auth']);

    // settig in hrm
    Route::post('hrm/setting/store', [HrmController::class, 'setting'])->name('hrm.setting.store')->middleware(['auth']);
    // Leave
    Route::get('leave/{id}/action', [LeaveController::class, 'action'])->name('leave.action')->middleware(
        [
            'auth',
        ]
    );
    Route::post('leave/changeaction', [LeaveController::class, 'changeaction'])->name('leave.changeaction')->middleware(
        [
            'auth',
        ]
    );
    Route::post('leave/jsoncount', [LeaveController::class, 'jsoncount'])->name('leave.jsoncount')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('leave', LeaveController::class)->middleware(
        [
            'auth',
        ]
    );

    // Announcement
    Route::post('announcement/getemployee', [AnnouncementController::class, 'getemployee'])->name('announcement.getemployee')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('announcement', AnnouncementController::class)->middleware(
        [
            'auth',
        ]
    );
    // Holiday
    Route::get('holiday/calender', [HolidayController::class, 'calender'])->name('holiday.calender')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('holiday', HolidayController::class)->middleware(
        [
            'auth',
        ]
    );

    // Holiday import
    Route::get('holiday/import/export', [HolidayController::class, 'fileImportExport'])->name('holiday.file.import')->middleware(['auth']);
    Route::post('holiday/import', [HolidayController::class, 'fileImport'])->name('holiday.import')->middleware(['auth']);
    Route::get('holiday/import/modal', [HolidayController::class, 'fileImportModal'])->name('holiday.import.modal')->middleware(['auth']);
    Route::post('holiday/data/import/', [HolidayController::class, 'holidayImportdata'])->name('holiday.import.data')->middleware(['auth']);

    // Report
    Route::get('report/monthly/attendance', [ReportController::class, 'monthlyAttendance'])->name('report.monthly.attendance')->middleware(
        [
            'auth',
        ]
    );
    Route::get('report/detailed/attendance', [ReportController::class, 'detailedAttendance'])->name('report.detailed.attendance')->middleware(
        [
            'auth',
        ]
    );
    Route::get('report/detailed/attendance/export', [ReportController::class, 'exportDetailedAttendance'])->name('report.detailed.attendance.export')->middleware(
        [
            'auth',
        ]
    );
    Route::post('report/getdepartment', [ReportController::class, 'getdepartment'])->name('report.getdepartment')->middleware(
        [
            'auth',
        ]
    );
    Route::post('report/getemployee', [ReportController::class, 'getemployee'])->name('report.getemployee')->middleware(
        [
            'auth',
        ]
    );
    Route::get('report/leave', [ReportController::class, 'leave'])->name('report.leave')->middleware(
        [
            'auth',
        ]
    );
    Route::get('employee/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name('report.employee.leave')->middleware(
        [
            'auth',
        ]
    );
    Route::get('report/payroll', [ReportController::class, 'Payroll'])->name('report.payroll')->middleware(
        [
            'auth',
        ]
    );
    // Allowance
    Route::resource('allowance', AllowanceController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('allowances/create/{eid}', [AllowanceController::class, 'allowanceCreate'])->name('allowances.create')->middleware(
        [
            'auth',
        ]
    );
    // commissions
    Route::get('commissions/create/{eid}', [CommissionController::class, 'commissionCreate'])->name('commissions.create')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('commission', CommissionController::class)->middleware(
        [
            'auth',
        ]
    );
    // saturationdeduction
    Route::get('saturationdeductions/create/{eid}', [SaturationDeductionController::class, 'saturationdeductionCreate'])->name('saturationdeductions.create')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('saturationdeduction', SaturationDeductionController::class)->middleware(
        [
            'auth',
        ]
    );
    // otherpayment
    Route::get('otherpayments/create/{eid}', [OtherPaymentController::class, 'otherpaymentCreate'])->name('otherpayments.create')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('otherpayment', OtherPaymentController::class)->middleware(
        [
            'auth',
        ]
    );
    // companycontribution
    Route::get('companycontribution/create/{eid}', [CompanyContributionController::class, 'companycontributionCreate'])->name('companycontributions.create')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('companycontribution', CompanyContributionController::class)->middleware(
        [
            'auth',
        ]
    );
    // overtime
    Route::get('overtimes/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name('overtimes.create')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('overtime', OvertimeController::class)->middleware(
        [
            'auth',
        ]
    );
    // Payslip
    Route::resource('payslip', PaySlipController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::post('payslip/search_json', [PaySlipController::class, 'search_json'])->name('payslip.search_json')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/delete/{id}', [PaySlipController::class, 'destroy'])->name('payslip.delete')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/pdf/{id}/{m}', [PaySlipController::class, 'pdf'])->name('payslip.pdf')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/payslipPdf/{id}', [PaySlipController::class, 'payslipPdf'])->name('payslip.payslipPdf')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/paysalary/{id}/{date}', [PaySlipController::class, 'paysalary'])->name('payslip.paysalary')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/send/{id}/{m}', [PaySlipController::class, 'send'])->name('payslip.send')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/editemployee/{id}', [PaySlipController::class, 'editemployee'])->name('payslip.editemployee')->middleware(
        [
            'auth',
        ]
    );

    Route::post('payslip/editemployee/{id}', [PaySlipController::class, 'updateEmployee'])->name('payslip.updateemployee')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/preview/{id}/{term}', [PaySlipController::class, 'preview'])->name('payslip.preview')->middleware(
        [
            'auth',
        ]
    );
    Route::post('payslip/finalize/{id}', [PaySlipController::class, 'finalize'])->name('payslip.finalize')->middleware(
        [
            'auth',
        ]
    );
    Route::get('payslip/un-finalize/{id}', [PaySlipController::class, 'unFinalize'])->name('payslip.unfinalize')->middleware(
        [
            'auth',
        ]
    );
    //Event
    Route::get('event/data/{id}', [EventController::class, 'showData'])->name('eventsshow');
    Route::post('event/getdepartment', [EventController::class, 'getdepartment'])->name('event.getdepartment')->middleware(
        [
            'auth',
        ]
    );
    Route::post('event/getemployee', [EventController::class, 'getemployee'])->name('event.getemployee')->middleware(
        [
            'auth',
        ]
    );
    Route::resource('event', EventController::class)->middleware(
        [
            'auth',
        ]
    );


    // //Experience Certificate
    Route::post('setting/exp/{lang?}', [HrmController::class, 'experienceCertificateupdate'])->name('experiencecertificate.update');
    Route::get('employee/exppdf/{id}', [EmployeeController::class, 'ExpCertificatePdf'])->name('exp.download.pdf');
    Route::get('employee/expdoc/{id}', [EmployeeController::class, 'ExpCertificateDoc'])->name('exp.download.doc');

    // //NOC
    Route::post('setting/noc/{lang?}', [HrmController::class, 'NOCupdate'])->name('noc.update');
    Route::get('employee/nocpdf/{id}', [EmployeeController::class, 'NocPdf'])->name('noc.download.pdf');
    Route::get('employee/nocdoc/{id}', [EmployeeController::class, 'NocDoc'])->name('noc.download.doc');
});
//Route::get('/employee-salary', [EmployeeSalaryController::class, 'index'])->name('employee-salary.index');
Route::middleware(['auth'])->group(function () {
Route::post('/employee-salary/store', [EmployeeSalaryController::class, 'store'])->name('employee-salary.store');
Route::get('/employee-salary', [EmployeeSalaryController::class, 'index'])->name('employee.salary.index');
Route::get('/employee-salary/create', [EmployeeSalaryController::class, 'create'])->name('employee.salary.create');
Route::get('/employee-salary/details', [EmployeeSalaryController::class, 'getSalaryDetail'])->name('employee.salary.detail');



Route::resource('income-policies', IncomePolicyController::class);
Route::get('income-policies/create', [IncomePolicyController::class, 'create'])->name('income-policies.create');
Route::post('income-policies', [IncomePolicyController::class, 'store'])->name('income-policies.store');
Route::get('income-policies/{id}/edit', [IncomePolicyController::class, 'edit'])->name('income-policies.edit');
Route::put('income-policies/{id}', [IncomePolicyController::class, 'update'])->name('income-policies.update');
Route::delete('income-policies/{id}/{term}', [IncomePolicyController::class, 'destroy'])->name('income-policies.destroy');


Route::prefix('hrm')->group(function () {
    Route::resource('accommodation-benefits', AccommodationBenefitController::class);
    Route::get('accommodation-benefits/create', [AccommodationBenefitController::class, 'create'])->name('accommodation-benefits.create');
    Route::delete('accommodation-benefits/{id}/{term}', [AccommodationBenefitController::class, 'destroy'])->name('accommodation-benefits.destroy');
});
Route::prefix('hrm')->group(function () {
    Route::get('company-car-operating', [CompanyCarUnderOperatingController::class, 'index'])->name('company-car-operating.index');
    Route::get('company-car-operating/create', [CompanyCarUnderOperatingController::class, 'create'])->name('company-car-operating.create');

    Route::post('company-car-operating', [CompanyCarUnderOperatingController::class, 'store'])->name('company-car-operating.store'); // ✅ FIXED HERE
    Route::get('company-car-operating/{companyCar}/edit', [CompanyCarUnderOperatingController::class, 'edit'])->name('company-car-operating.edit');
    Route::put('company-car-operating/{companyCar}', [CompanyCarUnderOperatingController::class, 'update'])->name('company-car-operating.update');
    Route::delete('company-car-operating/{id}/{term}', [CompanyCarUnderOperatingController::class, 'destroy'])
        ->name('company-car-operating.destroy');
});

Route::resource('bursaries-scholarships', BursariesScholarshipController::class);

Route::prefix('hrm')->group(function () {
    Route::get('company-cars', [CompanyCarController::class, 'index'])->name('company-cars.index');
    Route::get('company-cars/create', [CompanyCarController::class, 'create'])->name('company-cars.create');
    Route::post('company-cars/store', [CompanyCarController::class, 'store'])->name('company-cars.store');
    Route::get('company-cars/{companyCar}/edit', [CompanyCarController::class, 'edit'])->name('company-cars.edit');
    Route::put('company-cars/{companyCar}', [CompanyCarController::class, 'update'])->name('company-cars.update');
    Route::delete('company-cars/{id}/{term}', [CompanyCarController::class, 'destroy'])->name('company-cars.destroy');
    // routes/web.php
    //Route::delete('company-cars/{company_car}/{employee_id}', [CompanyCarController::class, 'destroy'])->name('company-cars.destroy');

});

//TaxDirectiveEntry
Route::get('tax-directive-entries', [TaxDirectiveEntryController::class, 'index'])->name('tax-directive-entries.index');
Route::get('tax-directive-entries/create', [TaxDirectiveEntryController::class, 'create'])->name('tax-directive-entries.create');
Route::post('tax-directive-entries/store', [TaxDirectiveEntryController::class, 'store'])->name('tax-directive-entries.store');
Route::get('tax-directive-entries/{id}/edit', [TaxDirectiveEntryController::class, 'edit'])->name('tax-directive-entries.edit');
Route::put('tax-directive-entries/{id}', [TaxDirectiveEntryController::class, 'update'])->name('tax-directive-entries.update');
Route::delete('tax-directive-entries/{id}/{term}', [TaxDirectiveEntryController::class, 'destroy'])->name('tax-directive-entries.destroy');

//savins deduction

Route::get('savings-deductions', [SavingsDeductionController::class, 'index'])->name('savings-deductions.index');
Route::get('savings-deductions/create', [SavingsDeductionController::class, 'create'])->name('savings-deductions.create');
Route::post('savings-deductions/store', [SavingsDeductionController::class, 'store'])->name('savings-deductions.store');
Route::get('savings-deductions/{id}/edit', [SavingsDeductionController::class, 'edit'])->name('savings-deductions.edit');
Route::put('savings-deductions/{id}', [SavingsDeductionController::class, 'update'])->name('savings-deductions.update');
Route::delete('savings-deductions/{id}/{term}', [SavingsDeductionController::class, 'destroy'])->name('savings-deductions.destroy');

//employer loan
Route::get('employer-loans', [EmployerLoanController::class, 'index'])->name('employer-loans.index');
Route::get('employer-loans/create', [EmployerLoanController::class, 'create'])->name('employer-loans.create');
Route::post('employer-loans/store', [EmployerLoanController::class, 'store'])->name('employer-loans.store');
Route::get('employer-loans/{id}/edit', [EmployerLoanController::class, 'edit'])->name('employer-loans.edit');
Route::put('employer-loans/{id}', [EmployerLoanController::class, 'update'])->name('employer-loans.update');
Route::delete('employer-loans/{id}/{term}', [EmployerLoanController::class, 'destroy'])->name('employer-loans.destroy');

//tax over deduction
Route::get('tax-over-deduction', [TaxOverDeductionController::class, 'index'])->name('tax-over-deduction.index');
Route::get('tax-over-deduction/create', [TaxOverDeductionController::class, 'create'])->name('tax-over-deduction.create');
Route::post('tax-over-deduction/store', [TaxOverDeductionController::class, 'store'])->name('tax-over-deduction.store');
Route::get('tax-over-deduction/{id}/edit', [TaxOverDeductionController::class, 'edit'])->name('tax-over-deduction.edit');
Route::put('tax-over-deduction/{id}', [TaxOverDeductionController::class, 'update'])->name('tax-over-deduction.update');
Route::delete('tax-over-deduction/{id}/{term}', [TaxOverDeductionController::class, 'destroy'])->name('tax-over-deduction.destroy');

//union membership fees
Route::get('union-membership', [UnionMembershipFeeController::class, 'index'])->name('union-membership.index');
Route::get('union-membership/create', [UnionMembershipFeeController::class, 'create'])->name('union-membership.create');
Route::post('union-membership/store', [UnionMembershipFeeController::class, 'store'])->name('union-membership.store');
Route::get('union-membership/{id}/edit', [UnionMembershipFeeController::class, 'edit'])->name('union-membership.edit');
Route::put('union-membership/{id}', [UnionMembershipFeeController::class, 'update'])->name('union-membership.update');
Route::delete('union-membership/{id}/{term}', [UnionMembershipFeeController::class, 'destroy'])->name('union-membership.destroy');

//beneficiary
Route::get('beneficiary/create', [BeneficiaryController::class, 'create'])->name('beneficiary.create');
Route::post('beneficiary', [BeneficiaryController::class, 'store'])->name('beneficiary.store');
Route::get('beneficiary', [BeneficiaryController::class, 'index'])->name('beneficiary.index');

Route::get('beneficiary/{id}/edit', [BeneficiaryController::class, 'edit'])->name('beneficiary.edit');
Route::put('beneficiary/{id}', [BeneficiaryController::class, 'update'])->name('beneficiary.update');
Route::delete('beneficiary/{id}', [BeneficiaryController::class, 'destroy'])->name('beneficiary.destroy');
Route::put('beneficiary/{id}/status', [BeneficiaryController::class, 'updateStatus'])->name('beneficiary.updateStatus');
//garnishee

Route::get('garnishee/create', [GarnisheeController::class, 'create'])->name('garnishee.create');
Route::post('garnishee', [GarnisheeController::class, 'store'])->name('garnishee.store');
Route::get('garnishee', [GarnisheeController::class, 'index'])->name('garnishee.index');
Route::get('garnishee/{id}/edit', [GarnisheeController::class, 'edit'])->name('garnishee.edit');
Route::put('garnishee/{id}', [GarnisheeController::class, 'update'])->name('garnishee.update');
Route::delete('garnishee/{id}/{term}', [GarnisheeController::class, 'destroy'])->name('garnishee.destroy');
//income protection
Route::get('income-protection/create', [IncomeProtectionController::class, 'create'])->name('income-protection.create');
Route::post('income-protection', [IncomeProtectionController::class, 'store'])->name('income-protection.store');
Route::get('income-protection', [IncomeProtectionController::class, 'index'])->name('income-protection.index');
Route::get('income-protection/{id}/edit', [IncomeProtectionController::class, 'edit'])->name('income-protection.edit');
Route::put('income-protection/{id}', [IncomeProtectionController::class, 'update'])->name('income-protection.update');
Route::delete('income-protection/{id}/{term}', [IncomeProtectionController::class, 'destroy'])->name('income-protection.destroy');
// maintenance order
Route::get('maintenance-order/create', [MaintenanceOrderController::class, 'create'])->name('maintenance-order.create');
Route::post('maintenance-order', [MaintenanceOrderController::class, 'store'])->name('maintenance-order.store');
Route::get('maintenance-order', [MaintenanceOrderController::class, 'index'])->name('maintenance-order.index');
Route::get('maintenance-order/{id}/edit', [MaintenanceOrderController::class, 'edit'])->name('maintenance-order.edit');
Route::put('maintenance-order/{id}', [MaintenanceOrderController::class, 'update'])->name('maintenance-order.update');
Route::delete('maintenance-order/{id}/{term}', [MaintenanceOrderController::class, 'destroy'])
    ->name('maintenance-order.destroy');

//medical aid
Route::get('medical-aid/create', [MedicalAidController::class, 'create'])->name('medical-aid.create');
Route::post('medical-aid', [MedicalAidController::class, 'store'])->name('medical-aid.store');
Route::get('medical-aid', [MedicalAidController::class, 'index'])->name('medical-aid.index');
Route::get('medical-aid/{id}/edit', [MedicalAidController::class, 'edit'])->name('medical-aid.edit');
Route::put('medical-aid/{id}', [MedicalAidController::class, 'update'])->name('medical-aid.update');
Route::delete('medical-aid/{id}/{term}', [MedicalAidController::class, 'destroy'])->name('medical-aid.destroy');
//pension fund
Route::get('pension-fund/create', [PensionFundController::class, 'create'])->name('pension-fund.create');
Route::post('pension-fund', [PensionFundController::class, 'store'])->name('pension-fund.store');
Route::get('pension-fund', [PensionFundController::class, 'index'])->name('pension-fund.index');
Route::get('pension-fund/{id}/edit', [PensionFundController::class, 'edit'])->name('pension-fund.edit');
Route::put('pension-fund/{id}', [PensionFundController::class, 'update'])->name('pension-fund.update');
Route::delete('pension-fund/{id}/{term}', [PensionFundController::class, 'destroy'])
    ->name('pension-fund.destroy');

//provident fund
Route::get('provident-fund/create', [ProvidentFundController::class, 'create'])->name('provident-fund.create');
Route::post('provident-fund', [ProvidentFundController::class, 'store'])->name('provident-fund.store');
Route::get('provident-fund', [ProvidentFundController::class, 'index'])->name('provident-fund.index');
Route::get('provident-fund/{id}/edit', [ProvidentFundController::class, 'edit'])->name('provident-fund.edit');
Route::put('provident-fund/{id}', [ProvidentFundController::class, 'update'])->name('provident-fund.update');
Route::delete('provident-fund/{id}/{term}', [ProvidentFundController::class, 'destroy'])->name('provident-fund.destroy');
Route::delete('/provident-fund/{employee_id}/{id}/{term}', [ProvidentFundController::class, 'destroy'])->name('provident-fund.destroy');



//Retirement Annuity Fund
Route::get('retirement-annuitie/create', [RetirementAnnuitieController::class, 'create'])->name('retirement-annuitie.create');
Route::post('retirement-annuitie', [RetirementAnnuitieController::class, 'store'])->name('retirement-annuitie.store');
Route::get('retirement-annuitie', [RetirementAnnuitieController::class, 'index'])->name('retirement-annuitie.index');
Route::get('retirement-annuitie/{id}/edit', [RetirementAnnuitieController::class, 'edit'])->name('retirement-annuitie.edit');
Route::put('retirement-annuitie/{id}', [RetirementAnnuitieController::class, 'update'])->name('retirement-annuitie.update');
Route::delete('retirement-annuitie/{id}/{term}', [RetirementAnnuitieController::class, 'destroy'])->name('retirement-annuitie.destroy');

//employee salary
// GET route for listing salary records
Route::get('/salary', [EmployeesSalaryController::class, 'index'])->name('salary.index');

// POST route for storing salary details
Route::post('/salary/store', [EmployeesSalaryController::class, 'store'])->name('salary.store');
Route::put('/salary/{id}/delete-commission', [EmployeesSalaryController::class, 'deleteCommission'])
    ->name('salary.delete-commission');
Route::get('/salary', [EmployeesSalaryController::class, 'index'])->name('salary.index');
Route::get('/salary/details', [EmployeesSalaryController::class, 'getSalaryDetails'])->name('salary.details');

Route::post('/get-salary-details', [EmployeesSalaryController::class, 'fetchSalaryDetails']);
Route::post('/save-payslip-commission', [PayslipCommissionController::class, 'store']);
Route::get('/hrm/salary', [EmployeessalaryController::class, 'index'])->name('salary.index');

Route::get('/hrm/salary', [EmployeessalaryController::class, 'getSalaryDetails'])->name('salary.index');

Route::get('salary', [EmployeessalaryController::class, 'index'])->name('salary.index');
Route::post('/hrm/salary/fetch', [EmployeessalaryController::class, 'fetchSalaryDetails'])->name('salary.fetch');
Route::get('/salary/details', [EmployeesSalaryController::class, 'getSalaryDetails'])->name('salary.details');
Route::get('/payslipcommission/create', [PayslipCommissionController::class, 'create'])->name('payslipcommission.create');
Route::get('/employee/salary', [EmployeeSalaryController::class, 'getEmployeeSalary'])->name('employee.salary');
Route::get('/salary/calculate/{employeeId}', [EmployeesSalaryController::class, 'calculateSalary'])->name('salary.calculate');


Route::get('/hrm/salary/details', [EmployeesSalaryController::class, 'getSalaryDetails'])->name('hrm.salary.details');
Route::get('/main', function () {
    return view('modals.welcome'); // Ensure the correct path
});
Route::get('/popup', function () {
    return view('modals.popup'); // This should exist in resources/views/modals/
})->name('popup.show');

//Route::get('/employee-salary', [EmployeeSalaryController::class, 'index'])->name('employee-salary.index');
Route::get('/employee-salary/create', [EmployeeSalaryController::class, 'create'])->name('employee-salary.create');

Route::get('salary/calculate/{employeeId}', [EmployeesSalaryController::class, 'calculateSalary'])->name('salary.calculateSalary');

Route::get('/modals/welcome', function () {
    return view('modals.welcome');
})->name('modals.welcome');
Route::get('/get-employee-salary-details', [EmployeesSalaryController::class, 'getEmployeeSalaryDetails'])->name('get-employee-salary-details');
// Route::post('/payslip-commissions', [PayslipCommissionController::class, 'store'])->name('payslip-commissions.store');
Route::post('salary', [EmployeesSalaryController::class, 'index'])->name('salary.index');
Route::get('/salary/getEmployeeCommission/{employeeId}', [EmployeesSalaryController::class, 'getEmployeeCommission']);
// Route::resource('payslip-commissions', PayslipCommissionController::class);
Route::patch('/payslip-commissions/{payslipCommission}/toggle-status', [PayslipCommissionController::class, 'updateStatus']);

Route::get('payslip-commissions/create', [PayslipCommissionController::class, 'create'])->name('payslip-commissions.create');
Route::post('payslip-commissions', [PayslipCommissionController::class, 'store'])->name('payslip-commissions.store');
Route::get('payslip-commissions/{id}/edit', [PayslipCommissionController::class, 'edit'])->name('payslip-commissions.edit');
Route::put('payslip-commissions/{id}', [PayslipCommissionController::class, 'update'])->name('payslip-commissions.update');
Route::delete('payslip-commissions/{id}/{term}', [PayslipCommissionController::class, 'destroy'])->name('payslip-commissions.destroy');

// Basic Salary
Route::prefix('hrm')->group(function () {
    Route::resource('basic-salariess', BasicSalaryController::class);
    Route::get('basic-salariess/create', [BasicSalaryController::class, 'create'])->name('basic-salariess.create');
    Route::get('/basic-salariess/{basicSalary}/edit', [BasicSalaryController::class, 'edit'])
        ->name('basic-salariess.edit');

    Route::put('/basic-salariess/{basicSalary}', [BasicSalaryController::class, 'update'])
        ->name('basic-salariess.update');

    Route::get('/basic-salariess/{id}/{term}/hourlyPay', [BasicSalaryController::class, 'hourlyPay'])
        ->name('basic-salariess.hourlyPay');
    Route::post('/basic-salariess/{id}/hourlyPay/store', [BasicSalaryController::class, 'hourlyPayStore'])
        ->name('basic-salariess.hourlyPay.store');
});

//AnnualBonus
//Route::get('/employee-salary', [AnnualBonusController::class, 'index'])->name('employee-salary.index');
//Route::get('/employee-salary/create', [AnnualBonusController::class, 'create'])->name('employee-salary.create');
Route::get('annual-bonuses/create', [AnnualBonusController::class, 'create'])->name('annual-bonuses.create');
Route::post('annual-bonuses', [AnnualBonusController::class, 'store'])->name('annual-bonuses.store');

Route::get('annual-bonuses/{id}/edit', [AnnualBonusController::class, 'edit'])->name('annual-bonuses.edit');
Route::put('annual-bonuses/{id}', [AnnualBonusController::class, 'update'])->name('annual-bonuses.update');
Route::delete('annual-bonuses/{id}', [AnnualBonusController::class, 'destroy'])->name('annual-bonuses.destroy');

// Annual Payments

Route::get('annual-payments/create', [AnnualPaymentController::class, 'create'])->name('annual-payments.create');
Route::post('annual-payments', [AnnualPaymentController::class, 'store'])->name('annual-payments.store');

Route::get('annual-payments/{id}/edit', [AnnualPaymentController::class, 'edit'])->name('annual-payments.edit');
Route::put('annual-payments/{id}', [AnnualPaymentController::class, 'update'])->name('annual-payments.update');
Route::delete('annual-payments/{id}', [AnnualPaymentController::class, 'destroy'])->name('annual-payments.destroy');

//ArbitrationAward
Route::get('arbitration-awards/create', [ArbitrationAwardController::class, 'create'])->name('arbitration-awards.create');
Route::post('arbitration-awards', [ArbitrationAwardController::class, 'store'])->name('arbitration-awards.store');

Route::get('arbitration-awards/{id}/edit', [ArbitrationAwardController::class, 'edit'])->name('arbitration-awards.edit');
Route::put('arbitration-awards/{id}', [ArbitrationAwardController::class, 'update'])->name('arbitration-awards.update');
Route::delete('arbitration-awards/{id}', [ArbitrationAwardController::class, 'destroy'])->name('arbitration-awards.destroy');

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/dividends-subject/create', [DividendsSubjectController::class, 'create'])->name('dividends-subject.create');
    Route::post('/dividends-subject/store', [DividendsSubjectController::class, 'store'])->name('dividends-subject.store');
    Route::get('/dividends-subject/{id}/edit', [DividendsSubjectController::class, 'edit'])->name('dividends-subject.edit');
    Route::put('/dividends-subject/{id}', [DividendsSubjectController::class, 'update'])->name('dividends-subject.update');
    Route::delete('/dividends-subject/{id}', [DividendsSubjectController::class, 'destroy'])->name('dividends-subject.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/extra-pay/create', [ExtraPayController::class, 'create'])->name('extra-pay.create');
    Route::post('/extra-pay/store', [ExtraPayController::class, 'store'])->name('extra-pay.store');
    Route::get('/extra-pay/{id}/edit', [ExtraPayController::class, 'edit'])->name('extra-pay.edit');
    Route::put('/extra-pay/{id}', [ExtraPayController::class, 'update'])->name('extra-pay.update');
    Route::delete('/extra-pay/{id}', [ExtraPayController::class, 'destroy'])->name('extra-pay.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/once-off-commission/create', [OnceOffCommissionController::class, 'create'])->name('once-off-commission.create');
    Route::post('/once-off-commission/store', [OnceOffCommissionController::class, 'store'])->name('once-off-commission.store');
    Route::get('/once-off-commission/{id}/edit', [OnceOffCommissionController::class, 'edit'])->name('once-off-commission.edit');
    Route::put('/once-off-commission/{id}', [OnceOffCommissionController::class, 'update'])->name('once-off-commission.update');
    Route::delete('/once-off-commission/{id}', [OnceOffCommissionController::class, 'destroy'])->name('once-off-commission.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/restraint-of-trade/create', [RestraintOfTradeController::class, 'create'])->name('restraint-of-trade.create');
    Route::post('/restraint-of-trade/store', [RestraintOfTradeController::class, 'store'])->name('restraint-of-trade.store');
    Route::get('/restraint-of-trade/{id}/edit', [RestraintOfTradeController::class, 'edit'])->name('restraint-of-trade.edit');
    Route::put('/restraint-of-trade/{id}', [RestraintOfTradeController::class, 'update'])->name('restraint-of-trade.update');
    Route::delete('/restraint-of-trade/{id}', [RestraintOfTradeController::class, 'destroy'])->name('restraint-of-trade.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/broad-based-employees/create', [BroadBasedEmployeeController::class, 'create'])->name('broad-based-employees.create');
    Route::post('/broad-based-employees/store', [BroadBasedEmployeeController::class, 'store'])->name('broad-based-employees.store');
    Route::get('/broad-based-employees/{id}/edit', [BroadBasedEmployeeController::class, 'edit'])->name('broad-based-employees.edit');
    Route::put('/broad-based-employees/{id}', [BroadBasedEmployeeController::class, 'update'])->name('broad-based-employees.update');
    Route::delete('broad-based-employees/{id}', [BroadBasedEmployeeController::class, 'destroy'])->name('broad-based-employees.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/computer-allowances/create', [ComputerAllowanceController::class, 'create'])->name('computer-allowances.create');
    Route::post('/computer-allowances/store', [ComputerAllowanceController::class, 'store'])->name('computer-allowances.store');
    Route::get('/computer-allowances/{id}/edit', [ComputerAllowanceController::class, 'edit'])->name('computer-allowances.edit');
    Route::put('/computer-allowances/{id}', [ComputerAllowanceController::class, 'update'])->name('computer-allowances.update');
    Route::delete('computer-allowances/{id}', [ComputerAllowanceController::class, 'destroy'])->name('computer-allowances.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/expense-claims/create', [ExpenseClaimController::class, 'create'])->name('expense-claims.create');
    Route::post('/expense-claims/store', [ExpenseClaimController::class, 'store'])->name('expense-claims.store');
    Route::get('/expense-claims/{id}/edit', [ExpenseClaimController::class, 'edit'])->name('expense-claims.edit');
    Route::put('/expense-claims/{id}', [ExpenseClaimController::class, 'update'])->name('expense-claims.update');
    Route::delete('expense-claims/{id}', [ExpenseClaimController::class, 'destroy'])->name('expense-claims.destroy');
});


Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {

    Route::get('/equity-instruments/create', [EquityInstrumentController::class, 'create'])->name('equity-instruments.create');
    Route::post('/equity-instruments/store', [EquityInstrumentController::class, 'store'])->name('equity-instruments.store');
    Route::get('/equity-instruments/edit/{id}', [EquityInstrumentController::class, 'edit'])->name('equity-instruments.edit');
    Route::put('/equity-instruments/update/{id}', [EquityInstrumentController::class, 'update'])->name('equity-instruments.update');
    Route::delete('/equity-instruments/{id}', [EquityInstrumentController::class, 'destroy'])->name('equity-instruments.destroy');

    // Route::delete('/delete/{id}', [EquityInstrumentController::class, 'destroy'])->name('equity-instruments.destroy'); // Delete Data
});
//PhoneAllowance
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {

    Route::get('/phone-allowances/create', [PhoneAllowanceController::class, 'create'])->name('phone-allowances.create');
    Route::post('/phone-allowances/store', [PhoneAllowanceController::class, 'store'])->name('phone-allowances.store');
    Route::get('/phone-allowances/edit/{id}', [PhoneAllowanceController::class, 'edit'])->name('phone-allowances.edit');
    Route::put('/phone-allowances/update/{id}', [PhoneAllowanceController::class, 'update'])->name('phone-allowances.update');
    Route::delete('/phone-allowances/delete/{id}', [PhoneAllowanceController::class, 'destroy'])->name('phone-allowances.destroy'); // Delete Data
});


Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    // Route::get('/', [RelocationAllowanceController::class, 'index'])->name('relocation-allowances.index');
    Route::get('/relocation-allowances/create', [RelocationAllowanceController::class, 'create'])->name('relocation-allowances.create');
    Route::post('/relocation-allowances/store', [RelocationAllowanceController::class, 'store'])->name('relocation-allowances.store');
    Route::get('/relocation-allowances/edit/{id}', [RelocationAllowanceController::class, 'edit'])->name('relocation-allowances.edit');
    Route::put('/relocation-allowances/update/{id}', [RelocationAllowanceController::class, 'update'])->name('relocation-allowances.update');
    Route::delete('/relocation-allowances/delete/{id}', [RelocationAllowanceController::class, 'destroy'])->name('relocation-allowances.destroy');
});


Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    // Route::get('/', [AllowanceInternationalController::class, 'index'])->name('relocation-allowances.index');
    Route::get('/allowance-internationals/create', [AllowanceInternationalController::class, 'create'])->name('allowance-internationals.create');
    Route::post('/allowance-internationals/store', [AllowanceInternationalController::class, 'store'])->name('allowance-internationals.store');
    Route::get('/allowance-internationals/edit/{id}', [AllowanceInternationalController::class, 'edit'])->name('allowance-internationals.edit');
    Route::put('/allowance-internationals/update/{id}', [AllowanceInternationalController::class, 'update'])->name('allowance-internationals.update');
    Route::delete('/delete/{id}', [AllowanceInternationalController::class, 'destroy'])->name('allowance-internationals.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/subsistence-allowances/create', [SubsistenceAllowanceController::class, 'create'])->name('subsistence-allowances.create');
    Route::post('/subsistence-allowances/store', [SubsistenceAllowanceController::class, 'store'])->name('subsistence-allowances.store');
    Route::get('/subsistence-allowances/{id}/edit', [SubsistenceAllowanceController::class, 'edit'])->name('subsistence-allowances.edit');
    Route::put('/subsistence-allowances/{id}', [SubsistenceAllowanceController::class, 'update'])->name('subsistence-allowances.update');
    Route::delete('/subsistence-allowances/{id}', [SubsistenceAllowanceController::class, 'destroy'])->name('subsistence-allowances.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/tool-allowances/create', [ToolAllowanceController::class, 'create'])->name('tool-allowances.create');
    Route::post('/tool-allowances/store', [ToolAllowanceController::class, 'store'])->name('tool-allowances.store');
    Route::get('/tool-allowances/{id}/edit', [ToolAllowanceController::class, 'edit'])->name('tool-allowances.edit');
    Route::put('/tool-allowances/{id}', [ToolAllowanceController::class, 'update'])->name('tool-allowances.update');
    Route::delete('/tool-allowances/{id}', [ToolAllowanceController::class, 'destroy'])->name('tool-allowances.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/uniform-allowances/create', [UniformAllowanceController::class, 'create'])->name('uniform-allowances.create');
    Route::post('/uniform-allowances/store', [UniformAllowanceController::class, 'store'])->name('uniform-allowances.store');
    Route::get('/uniform-allowances/{id}/edit', [UniformAllowanceController::class, 'edit'])->name('uniform-allowances.edit');
    Route::put('/uniform-allowances/{id}', [UniformAllowanceController::class, 'update'])->name('uniform-allowances.update');
    Route::delete('/uniform-allowances/{id}', [UniformAllowanceController::class, 'destroy'])->name('uniform-allowances.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/employee-benefits/create', [EmployeeBenefitController::class, 'create'])->name('employee-benefits.create');
    Route::post('/employee-benefits/store', [EmployeeBenefitController::class, 'store'])->name('employee-benefits.store');
    Route::get('/employee-benefits/{id}/edit', [EmployeeBenefitController::class, 'edit'])->name('employee-benefits.edit');
    Route::put('/employee-benefits/{id}', [EmployeeBenefitController::class, 'update'])->name('employee-benefits.update');
    Route::delete('/employee-benefits/{id}', [EmployeeBenefitController::class, 'destroy'])->name('employee-benefits.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/donations/create', [DonationController::class, 'create'])->name('donations.create');
    Route::post('/donations/store', [DonationController::class, 'store'])->name('donations.store');
    Route::get('/donations/{id}/edit', [DonationController::class, 'edit'])->name('donations.edit');
    Route::put('/donations/{id}', [DonationController::class, 'update'])->name('donations.update');
    Route::delete('/donations/{id}', [DonationController::class, 'destroy'])->name('donations.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/repayments/create', [RepaymentController::class, 'create'])->name('repayments.create');
    Route::post('/repayments/store', [RepaymentController::class, 'store'])->name('repayments.store');
    Route::get('/repayments/{id}/edit', [RepaymentController::class, 'edit'])->name('repayments.edit');
    Route::put('/repayments/{id}', [RepaymentController::class, 'update'])->name('repayments.update');
    Route::delete('/repayments/{id}', [RepaymentController::class, 'destroy'])->name('repayments.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/staff-purchases/create', [StaffPurchaseController::class, 'create'])->name('staff-purchases.create');
    Route::post('/staff-purchases/store', [StaffPurchaseController::class, 'store'])->name('staff-purchases.store');
    Route::get('/staff-purchases/{id}/edit', [StaffPurchaseController::class, 'edit'])->name('staff-purchases.edit');
    Route::put('/staff-purchases/{id}', [StaffPurchaseController::class, 'update'])->name('staff-purchases.update');
    Route::delete('/staff-purchases/{id}', [StaffPurchaseController::class, 'destroy'])->name('staff-purchases.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/covid19-disasters/create', [Covid19DisasterController::class, 'create'])->name('covid19-disasters.create');
    Route::post('/covid19-disasters/store', [Covid19DisasterController::class, 'store'])->name('covid19-disasters.store');
    Route::get('/covid19-disasters/{id}/edit', [Covid19DisasterController::class, 'edit'])->name('covid19-disasters.edit');
    Route::put('/covid19-disasters/{id}', [Covid19DisasterController::class, 'update'])->name('covid19-disasters.update');
    Route::delete('/covid19-disasters/{id}', [Covid19DisasterController::class, 'destroy'])->name('covid19-disasters.destroy');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/long-service-awards/create', [LongServiceAwardController::class, 'create'])->name('long-service-awards.create');
    Route::post('/long-service-awards/store', [LongServiceAwardController::class, 'store'])->name('long-service-awards.store');
    Route::get('/long-service-awards/{id}/edit', [LongServiceAwardController::class, 'edit'])->name('long-service-awards.edit');
    Route::put('/long-service-awards/{id}', [LongServiceAwardController::class, 'update'])->name('long-service-awards.update');
    Route::delete('/long-service-awards/{id}', [LongServiceAwardController::class, 'destroy'])->name('long-service-awards.destroy');
});
Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/ters-payouts/create', [TersPayoutController::class, 'create'])->name('ters-payouts.create');
    Route::post('/ters-payouts/store', [TersPayoutController::class, 'store'])->name('ters-payouts.store');
    Route::get('/ters-payouts/{id}/edit', [TersPayoutController::class, 'edit'])->name('ters-payouts.edit');
    Route::put('/ters-payouts/{id}', [TersPayoutController::class, 'update'])->name('ters-payouts.update');
    Route::delete('/ters-payouts/{id}', [TersPayoutController::class, 'destroy'])->name('ters-payouts.destroy');
});

Route::prefix('hrm')->group(function () {

    Route::get('/termination-lumps/create', [TerminationLumpController::class, 'create'])->name('termination-lumps.create');
    Route::post('/termination-lumps/store', [TerminationLumpController::class, 'store'])->name('termination-lumps.store');
    Route::get('/termination-lumps/{id}/edit', [TerminationLumpController::class, 'edit'])->name('termination-lumps.edit');
    Route::put('/termination-lumps/{id}', [TerminationLumpController::class, 'update'])->name('termination-lumps.update');
    Route::delete('/termination-lumps/{id}', [TerminationLumpController::class, 'destroy'])->name('termination-lumps.destroy');
});
Route::prefix('hrm')->group(function () {

    Route::get('/bursaries/create', [BursaryController::class, 'create'])->name('bursaries.create');
    Route::post('/bursaries/store', [BursaryController::class, 'store'])->name('bursaries.store');
    Route::get('/bursaries/{id}/edit', [BursaryController::class, 'edit'])->name('bursaries.edit');
    Route::put('/bursaries/{id}', [BursaryController::class, 'update'])->name('bursaries.update');
    Route::delete('/bursaries/{id}', [BursaryController::class, 'destroy'])->name('bursaries.destroy');
});
Route::prefix('hrm')->group(function () {

    Route::get('/medical-costs/create', [MedicalCostController::class, 'create'])->name('medical-costs.create');
    Route::post('/medical-costs/store', [MedicalCostController::class, 'store'])->name('medical-costs.store');
    Route::get('/medical-costs/{id}/edit', [MedicalCostController::class, 'edit'])->name('medical-costs.edit');
    Route::put('/medical-costs/{id}', [MedicalCostController::class, 'update'])->name('medical-costs.update');
    Route::delete('/medical-costs/{id}', [MedicalCostController::class, 'destroy'])->name('medical-costs.destroy');
});
Route::get('/payroll-calculation', function () {
    return view('hrm::payroll-calculation.index'); // Using the registered namespace
})->name('payroll-calculation.index');
Route::get('/payroll-calculation/create', function () {
    return view('hrm::payroll-calculation.index'); // Using the registered namespace
})->name('payroll-calculation.create');
Route::get('/payroll-calculation/edit', function () {
    return view('hrm::payroll-calculation.edit'); // Using the registered namespace
})->name('payroll-calculation.create');

//Sdl Reigisration
Route::prefix('hrm')->group(function () {
    // Route::resource('sdl-registrations', SdlRegistrationController::class);
    Route::get('/sdl-registrations/create', [SdlRegistrationController::class, 'create'])->name('sdl-registrations.create');
    Route::post('/sdl-registrations/store', [SdlRegistrationController::class, 'store'])->name('sdl-registrations.store');
    Route::get('/sdl-registrations/{id}/edit', [SdlRegistrationController::class, 'edit'])->name('sdl-registrations.edit');
    Route::put('/sdl-registrations/{id}', [SdlRegistrationController::class, 'update'])->name('sdl-registrations.update');
});

Route::prefix('hrm')->group(function () {
    // Route::resource('sdl-registrations', SdlRegistrationController::class);
    Route::get('company-settings', [CompanySettingController::class, 'index'])->name('company-settings.index');
    Route::get('/company-settings/create', [CompanySettingController::class, 'create'])->name('company-settings.create');
    Route::post('/company-settings/store', [CompanySettingController::class, 'store'])->name('company-settings.store');
    Route::get('/company-settings/{id}/edit', [CompanySettingController::class, 'edit'])->name('company-settings.edit');
    Route::put('/company-settings/{id}', [CompanySettingController::class, 'update'])->name('company-settings.update');
});

Route::prefix('hrm')->group(function () {
    // Route::resource('sdl-registrations', SdlRegistrationController::class);
    Route::get('company-basic-salaries', [CompanyBasicSalaryController::class, 'index'])->name('company-settings.index');
    Route::get('/company-basic-salaries/create', [CompanyBasicSalaryController::class, 'create'])->name('company-basic-salaries.create');
    Route::post('/company-basic-salaries/store', [CompanyBasicSalaryController::class, 'store'])->name('company-basic-salaries.store');
    Route::get('/company-basic-salaries/{id}/edit', [CompanyBasicSalaryController::class, 'edit'])->name('company-basic-salaries.edit');
    Route::put('/company-basic-salaries/{id}', [CompanyBasicSalaryController::class, 'update'])->name('company-basic-salaries.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/primary-bank-accounts/create', [PrimaryBankAccountController::class, 'create'])->name('primary-bank-accounts.create');
    Route::post('/primary-bank-accounts/store', [PrimaryBankAccountController::class, 'store'])->name('primary-bank-accounts.store');
    Route::get('/primary-bank-accounts/{id}/edit', [PrimaryBankAccountController::class, 'edit'])->name('primary-bank-accounts.edit');
    Route::put('/primary-bank-accounts/{id}', [PrimaryBankAccountController::class, 'update'])->name('primary-bank-accounts.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/additional-bank-accounts/create', [AdditionalBankAccountController::class, 'create'])->name('additional-bank-accounts.create');
    Route::post('/additional-bank-accounts/store', [AdditionalBankAccountController::class, 'store'])->name('additional-bank-accounts.store');
    Route::get('/additional-bank-accounts/{id}/edit', [AdditionalBankAccountController::class, 'edit'])->name('additional-bank-accounts.edit');
    Route::put('/additional-bank-accounts/{id}', [AdditionalBankAccountController::class, 'update'])->name('additional-bank-accounts.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/add-garnishees/create', [AddGarnisheeController::class, 'create'])->name('add-garnishees.create');
    Route::post('/add-garnishees/store', [AddGarnisheeController::class, 'store'])->name('add-garnishees.store');
    Route::get('/add-garnishees/{id}/edit', [AddGarnisheeController::class, 'edit'])->name('add-garnishees.edit');
    Route::put('/add-garnishees/{id}', [AddGarnisheeController::class, 'update'])->name('add-garnishees.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/add-maintenance/create', [AddMaintenanceOrderController::class, 'create'])->name('add-maintenance.create');
    Route::post('/add-maintenance/store', [AddMaintenanceOrderController::class, 'store'])->name('add-maintenance.store');
    Route::get('/add-maintenance/{id}/edit', [AddMaintenanceOrderController::class, 'edit'])->name('add-maintenance.edit');
    Route::put('/add-maintenance/{id}', [AddMaintenanceOrderController::class, 'update'])->name('add-maintenance.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/add-medical-aid/create', [AddMedicalAidController::class, 'create'])->name('add-medical-aid.create');
    Route::post('/add-medical-aid/store', [AddMedicalAidController::class, 'store'])->name('add-medical-aid.store');
    Route::get('/add-medical-aid/{id}/edit', [AddMedicalAidController::class, 'edit'])->name('add-medical-aid.edit');
    Route::put('/add-medical-aid/{id}', [AddMedicalAidController::class, 'update'])->name('add-medical-aid.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/add-pension-funds/create', [AddPensionFundController::class, 'create'])->name('add-pension-funds.create');
    Route::post('/add-pension-funds/store', [AddPensionFundController::class, 'store'])->name('add-pension-funds.store');
    Route::get('/add-pension-funds/{id}/edit', [AddPensionFundController::class, 'edit'])->name('add-pension-funds.edit');
    Route::put('/add-pension-funds/{id}', [AddPensionFundController::class, 'update'])->name('add-pension-funds.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/add-provident-funds/create', [AddProvidentFundController::class, 'create'])->name('add-provident-funds.create');
    Route::post('/add-provident-funds/store', [AddProvidentFundController::class, 'store'])->name('add-provident-funds.store');
    Route::get('/add-provident-funds/{id}/edit', [AddProvidentFundController::class, 'edit'])->name('add-provident-funds.edit');
    Route::put('/add-provident-funds/{id}', [AddProvidentFundController::class, 'update'])->name('add-provident-funds.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/add-retirement-funds/create', [AddRetirementFundController::class, 'create'])->name('add-retirement-funds.create');
    Route::post('/add-retirement-funds/store', [AddRetirementFundController::class, 'store'])->name('add-retirement-funds.store');
    Route::get('/add-retirement-funds/{id}/edit', [AddRetirementFundController::class, 'edit'])->name('add-retirement-funds.edit');
    Route::put('/add-retirement-funds/{id}', [AddRetirementFundController::class, 'update'])->name('add-retirement-funds.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/custom-beneficiaries', [CustomBeneficiaryController::class, 'index'])->name('custom-beneficiaries.index');
    Route::get('/custom-beneficiaries/create', [CustomBeneficiaryController::class, 'create'])->name('custom-beneficiaries.create');
    Route::post('/custom-beneficiaries/store', [CustomBeneficiaryController::class, 'store'])->name('custom-beneficiaries.store');
    Route::get('/custom-beneficiaries/{id}/edit', [CustomBeneficiaryController::class, 'edit'])->name('custom-beneficiaries.edit');
    Route::put('/custom-beneficiaries/{id}', [CustomBeneficiaryController::class, 'update'])->name('custom-beneficiaries.update');
});


Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/custom-reimbursements', [CustomReimbursementController::class, 'index'])->name('custom-reimbursements.index');
    Route::get('/custom-reimbursements/create', [CustomReimbursementController::class, 'create'])->name('custom-reimbursements.create');
    Route::post('/custom-reimbursements/store', [CustomReimbursementController::class, 'store'])->name('custom-reimbursements.store');
    Route::get('/custom-reimbursements/{id}/edit', [CustomReimbursementController::class, 'edit'])->name('custom-reimbursements.edit');
    Route::put('/custom-reimbursements/{id}', [CustomReimbursementController::class, 'update'])->name('custom-reimbursements.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    //Route::get('/custom-reimbursements', [CustomBenefitController::class, 'index'])->name('custom-reimbursements.index');
    Route::get('/custom-benefits/create', [CustomBenefitController::class, 'create'])->name('custom-benefits.create');
    Route::post('/custom-benefits/store', [CustomBenefitController::class, 'store'])->name('custom-benefits.store');
    Route::get('/custom-benefits/{id}/edit', [CustomBenefitController::class, 'edit'])->name('custom-benefits.edit');
    Route::put('/custom-benefits/{id}', [CustomBenefitController::class, 'update'])->name('custom-benefits.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    //Route::get('/custom-reimbursements', [CustomBenefitController::class, 'index'])->name('custom-reimbursements.index');
    Route::get('/custom-employer/create', [CustomEmployerContributionController::class, 'create'])->name('custom-employer.create');
    Route::post('/custom-employer/store', [CustomEmployerContributionController::class, 'store'])->name('custom-employer.store');
    Route::get('/custom-employer/{id}/edit', [CustomEmployerContributionController::class, 'edit'])->name('custom-employer.edit');
    Route::put('/custom-employer/{id}', [CustomEmployerContributionController::class, 'update'])->name('custom-employer.update');
});


Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    //Route::get('/custom-reimbursements', [CustomBenefitController::class, 'index'])->name('custom-reimbursements.index');
    Route::get('/custom-allowances/create', [CustomAllowanceController::class, 'create'])->name('custom-allowances.create');
    Route::post('/custom-allowances', [CustomAllowanceController::class, 'store'])->name('custom-allowances.store');
    Route::get('/custom-allowances/{id}/edit', [CustomAllowanceController::class, 'edit'])->name('custom-allowances.edit');
    Route::put('/custom-allowances/{id}', [CustomAllowanceController::class, 'update'])->name('custom-allowances.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {

    Route::get('/custom-deductions/create', [CustomDeductionController::class, 'create'])->name('custom-deductions.create');
    Route::post('/custom-deductions/store', [CustomDeductionController::class, 'store'])->name('custom-deductions.store');
    Route::get('/custom-deductions/{id}/edit', [CustomDeductionController::class, 'edit'])->name('custom-deductions.edit');
    Route::put('/custom-deductions/{id}', [CustomDeductionController::class, 'update'])->name('custom-deductions.update');
});

Route::prefix('hrm')->middleware(['web', 'auth'])->group(function () {
    Route::get('/custom-incomes/create', [CustomIncomeController::class, 'create'])->name('custom-incomes.create');
    Route::post('/custom-incomes/store', [CustomIncomeController::class, 'store'])->name('custom-incomes.store');
    Route::get('/custom-incomes/{id}/edit', [CustomIncomeController::class, 'edit'])->name('custom-incomes.edit');
    Route::put('/custom-incomes/{id}', [CustomIncomeController::class, 'update'])->name('custom-incomes.update');
});

Route::get('/payroll', function () {
    return view('hrm::payroll.index');
});
Route::get('/payroll/{employee}', [PayrollController::class, 'index'])->name('payroll.index');
Route::post('/update-basic-salary', [PayrollController::class, 'updateBasicSalary'])->name('update.basic.salary');
Route::get('/get-basic-salary', [PayrollController::class, 'getBasicSalary'])->name('get.basic.salary');
Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index'); // Load first employee
//Route::get('/payroll/{employee}', [PayrollController::class, 'index']); //

Route::get('/payroll/index', [PayrollController::class, 'index'])->name('payroll.index');
Route::get('/payroll/create-payslip/{id}', [PayrollController::class, 'createPayslip'])->name('payroll.create-payslip');
Route::post('/payroll/once-off-payslip/{id}', [PayrollController::class, 'onceOffPayslip'])->name('payroll.once-off-payslip');
Route::post('/payroll/next-payslip/{id}', [PayrollController::class, 'nextPayslip'])->name('payroll.next-payslip');
//payrun
Route::get('/payrun', [PayrunController::class, 'index'])->name('payrun.index');
Route::post('/payrun/store', [PayrunController::class, 'store'])->name('payrun.store');
Route::get('/payrun/showPdf/{term}/finalized', [PayrunController::class, 'finalized_pdf'])->name('payrun.finalized.pdf');
Route::get('/payrun/showPdf/{term}/pending', [PayrunController::class, 'pending_pdf'])->name('payrun.pending.pdf');
Route::get('/payrun/{term}/bulkFinalisation', [PayrunController::class, 'bulkFinalisation'])->name('payrun.bulkFinalisation');
Route::post('/payrun/{term}/bulkUnFinalisation', [PayrunController::class, 'bulkUnFinalisation'])->name('payrun.bulkUnFinalisation');
Route::post('/payrun/bulkFinalisation/store', [PayrunController::class, 'bulkFinalisationStore'])->name('payrun.bulkFinalisation.store');
Route::get('/payrun/payslip/{term}', [PayrunController::class, 'payslip_pdf'])->name('payrun.payslips.pdf');
Route::get('/payrun/pdf/{term}/{type}', [PayrunController::class, 'payrun_pdf'])->name('payrun.pdf');

//Route::post('/employee-salary/store', [EmployeeSalaryController::class, 'store'])->name('employee.salary.store');
Route::get('/employee-salary/create', [EmployeeSalaryController::class, 'create'])->name('employee-salary.create');
Route::get('/basic-salariess/create', [BasicSalaryController::class, 'create'])->name('basic-salariess.create');
Route::post('/basic-salaries/store', [BasicSalaryController::class, 'store'])->name('basic.salaries-store');
Route::post('/payroll/update-basic-salary', [PayrollController::class, 'updateBasicSalary'])->name('payroll.update.basic.salary');
Route::post('/basic-salaries/store', [BasicSalaryController::class, 'store'])->name('basic-salaries.store');
Route::delete('/payroll/{id}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
Route::prefix('hrm')->group(function () {
    Route::get('basic-salaries/create', [BasicSalaryController::class, 'create'])->name('basic-salaries.create');
    Route::get('/payroll/fetch', [PayrollController::class, 'fetch'])->name('payroll.fetch');
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/store', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('/basic-salaries/create/{id}', [BasicSalaryController::class, 'create']);

    Route::post('travel-allowances/store', [TravelAllowanceController::class, 'store'])->name('travel-allowances.store');
    Route::get('travel-allowances/create', [TravelAllowanceController::class, 'create'])->name('travel-allowances.create');
    Route::get('travel-allowances/{id}/edit', [TravelAllowanceController::class, 'edit'])->name('travel-allowances.edit');
    Route::put('travel-allowances/{id}', [TravelAllowanceController::class, 'update'])->name('travel-allowances.update');
    Route::delete('travel-allowances/{id}/{term}/delete', [TravelAllowanceController::class, 'destroy'])->name('travel-allowances.destroy');

    Route::get('/payroll/regularinputs', [PayrollController::class, 'regularInputs'])->name('payroll.regularinputs');
    Route::get('/payroll/payslipinputs', [PayrollController::class, 'payslipInputs'])->name('payroll.payslipinputs');
});
Route::get('/employee-salary.create', [EmployeeSalaryController::class, 'create'])->name('employee-salary.create');
Route::resource('employee-salary', EmployeeSalaryController::class);
Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
Route::group(['prefix' => 'hrm', 'as' => 'hrm::'], function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::put('/payroll/update-basic-salary/{employeeId}', [PayrollController::class, 'updateBasicSalary'])->name('payroll.updateBasicSalary');
    Route::post('/payroll/saveSalary', [PayrollController::class, 'saveSalary'])->name('payroll.saveSalary');
    Route::post('/payroll/saveSalary', [PayrollController::class, 'saveSalary'])->name('payroll.saveSalary');

    Route::get('hrm/basic-salariess/{basicSalary}/edit', [BasicSalaryController::class, 'edit'])->name('basic-salariess.edit');
    Route::put('hrm/basic-salariess/{basicSalaryId}/update', [BasicSalaryController::class, 'update'])
        ->name('basic-salariess.update');
    //Route::get('/hrm/basic-salariess/{basicSalary}/edit/{employee_id}', [BasicSalaryController::class, 'edit'])->name('basic-salariess.edit');
    Route::post('/payroll/calculate/{basicSalary}', [PayrollController::class, 'calculatePayroll']);
});
Route::get('hrm/basic-salariess/{basicSalaryId}/edit', [BasicSalaryController::class, 'edit'])
    ->name('basic-salariess.edit');
Route::group(['prefix' => 'hrm', 'as' => 'hrm::'], function () {});
Route::get('hrm/basic-salariess/{basicSalaryId}/edit', [BasicSalaryController::class, 'edit'])
    ->name('basic-salariess.edit');
Route::get('hrm/basic-salariess/edit/{employeeId}', [BasicSalaryController::class, 'edit'])
    ->name('basic-salariess.edit');

// INcome Policy
Route::group(['prefix' => 'hrm', 'as' => 'hrm::'], function () {
    Route::get('/payroll/fetch/{employee_id}', [PayrollController::class, 'fetchPayrollDetails']);
    Route::get('/income-policies/create/{employeeId}', [IncomePolicyController::class, 'create'])->name('income-policies.create');
    Route::get('/payroll/{employeeId}', [PayrollController::class, 'index']);
    Route::get('hrm/income-policies/{incomePolicy}/edit', [IncomePolicyController::class, 'edit'])
        ->name('income-policies.edit');
    Route::get('hrm/income-policies/edit/{employeeId}', [IncomePolicyController::class, 'edit'])
        ->name('income-policies.edit');
    Route::get('income-policies/{id}/edit', [IncomePolicyController::class, 'edit'])->name('income-policies.edit');
    Route::get('/payroll/{employee_id?}', [PayrollController::class, 'index'])->name('payroll.index');
});
Route::post('/hrm/travel-allowances/store', [TravelAllowanceController::class, 'store'])->name('travel-allowances.store');

Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
Route::get('/payroll/{employeeId}', [PayrollController::class, 'index']);

Route::get('hrm/basic-salariess/edit/{employeeId}', [BasicSalaryController::class, 'edit'])->name('basic-salariess.edit');

Route::get('hrm/company-car-under-operating/create', [CompanyCarUnderOperatingController::class, 'create'])->name('company-car-under-operating.create');
Route::post('hrm/company-car-under-operating/store', [CompanyCarUnderOperatingController::class, 'store'])->name('company-car-under-operating.store');

// Payroll routes
Route::get('hrm/payroll/index', [PayrollController::class, 'index'])->name('payroll.index');
Route::post('hrm/payroll/store', [PayrollController::class, 'store'])->name('payroll.store');

Route::get('/filtered-payrolls', [PayrollFilterController::class, 'index'])->name('payroll.filter');

Route::get('union-membership', 'App\Http\Controllers\Hrm\UnionMembershipFeeController@index')->name('union-membership.index');
Route::group(['prefix' => 'hrm', 'as' => 'hrm::'], function () {
    Route::get('travel-allowances/{id}/edit', [TravelAllowanceController::class, 'edit'])
        ->name('travel-allowances.edit');
    // Route::get('travel-allowances/{employeeId}/edit', [TravelAllowanceController::class, 'edit'])->name('travel-allowances.edit');
    // Route::get('travel-allowances/{travel-allowance}/edit', [TravelAllowanceController::class, 'edit'])->name('travel-allowances.edit');
    Route::put('travel-allowances/{travel-allowance}', [TravelAllowanceController::class, 'update'])->name('travel-allowances.update');
});
Route::get('/income-policies/{id}/edit', [IncomePolicyController::class, 'edit'])->name('income-policies.edit');
// Route::get('bursaries-scholarships/{bursaries_scholarship}/edit', [BursariesScholarshipController::class, 'edit'])
// ->name('bursaries-scholarships.edit');
Route::get('bursaries-scholarships/{id}/edit', [BursariesScholarshipController::class, 'edit'])->name('bursaries-scholarships.edit');
Route::delete('bursaries-scholarships/{id}/{term}', [BursariesScholarshipController::class, 'destroy'])->name('bursaries-scholarships.destroy');

Route::get(
    'accommodation-benefits/{accommodation_benefit}/edit',
    [AccommodationBenefitController::class, 'edit']
)->name('accommodation-benefits.edit');


Route::prefix('payroll')->group(function () {
    Route::get('/', [PayrollFilterController::class, 'index'])->name('payroll.index');
    Route::get('/create', [PayrollFilterController::class, 'create'])->name('payroll.create');
    Route::post('/store', [PayrollFilterController::class, 'store'])->name('payroll.store');
    Route::get('/{id}/edit', [PayrollFilterController::class, 'edit'])->name('payroll.edit');
    Route::post('/{id}/update', [PayrollFilterController::class, 'update'])->name('payroll.update');
    Route::delete('/{id}', [PayrollFilterController::class, 'destroy'])->name('payroll.destroy');

    Route::get('travel-allowances/{id}/edit', [TravelAllowanceController::class, 'edit'])
        ->name('travel-allowances.edit');
});

Route::prefix('admin')->group(function () {
    // Route::resource('travel-allowances', TravelAllowanceController::class);
});

//Route::get('/payroll/index/{employee_id}', [PayrollController::class, 'index'])->name('payroll.index');
Route::post('/designation/store', [DesignationController::class, 'store'])->name('designation.store');

Route::prefix('hrm')->name('hrm.')->group(function () {
    Route::get('/leave-management', [LeaveManagementController::class, 'index'])->name('leave-management.index');
    Route::get('/leave-management/create', [LeaveManagementController::class, 'create'])->name('leave-management.create');
    Route::post('/leave-management/store', [LeaveManagementController::class, 'store'])->name('leave-management.store');
    Route::get('/leave-management/{id}/edit', [LeaveManagementController::class, 'edit'])->name('leave-management.edit');
    Route::put('/leave-management/{id}', [LeaveManagementController::class, 'update'])->name('leave-management.update');
    Route::delete('/leave-management/{id}', [LeaveManagementController::class, 'destroy'])->name('leave-management.destroy');
    Route::get('/leave-management/{id}', [LeaveManagementController::class, 'show'])->name('leave-management.show');
});
Route::get('/leave-management', [LeaveManagementController::class, 'index'])->name('leave-management.index');
// enititlement policy 
Route::get('/hrm/entitlement-policies/create', [LeaveManagementController::class, 'create'])->name('hrm.entitlement-policies.create');
Route::prefix('hrm')->group(function () {
    // List all policies
    Route::get('/entitlement-policies', [EntitlementPolicyController::class, 'index'])->name('entitlement-policies.index');

    // Show create form
    Route::get('/entitlement-policies/create', [EntitlementPolicyController::class, 'create'])->name('entitlement-policies.create');

    // Store new policy
    Route::post('/entitlement-policies/store', [EntitlementPolicyController::class, 'store'])->name('entitlement-policies.store');
    Route::post('/entitlement-policies/store-range', [EntitlementPolicyController::class, 'storeRange'])->name('entitlement-policies.create-range');
    Route::post('/entitlement-policies/update-range', [EntitlementPolicyController::class, 'updateRange'])->name('entitlement-policies.update-range');
    Route::post('/entitlement-policies/delete-range', [EntitlementPolicyController::class, 'deleteRange'])->name('entitlement-policies.delete-range');

    // Show edit form
    Route::get('/entitlement-policies/{id}/edit', [EntitlementPolicyController::class, 'edit'])->name('entitlement-policies.edit');

    // Update policy
    Route::put('/entitlement-policies/{id}', [EntitlementPolicyController::class, 'update'])->name('entitlement-policies.update');

    // Delete policy
    Route::delete('/entitlement-policies/{id}', [EntitlementPolicyController::class, 'destroy'])->name('entitlement-policies.destroy');
    Route::get('/leave-management/sick', [EntitlementPolicyController::class, 'showSickLeave'])->name('leave-management.sick');
    Route::get('/leave-management/family', [EntitlementPolicyController::class, 'showFamilyLeave'])->name('leave-management.family');
    Route::get('/leave-management/{type}', [EntitlementPolicyController::class, 'showLeaveType'])->name('leave-management.dynamic');
});


Route::get('/hrm/general-self-service-settings', [GeneralSelfServiceSettingController::class, 'index'])->name('general-self-service-settings');
Route::get('/hrm/general-self-service-settings/create', [GeneralSelfServiceSettingController::class, 'create'])->name('general-self-service-settings.create');
Route::post('/hrm/general-self-service-settings/store', [GeneralSelfServiceSettingController::class, 'store'])->name('general-self-service-settings.store');
Route::get('/hrm/general-self-service-settings/{id}/edit', [GeneralSelfServiceSettingController::class, 'edit'])->name('general-self-service-settings.edit');
Route::post('/hrm/general-self-service-settings/update', [GeneralSelfServiceSettingController::class, 'update'])->name('general-self-service-settings.update');
Route::delete('/hrm/general-self-service-settings/{id}', [GeneralSelfServiceSettingController::class, 'destroy'])->name('general-self-service-settings.destroy');

Route::resource('employee-entitlement', EmployeeEntitlementPolicyController::class)->middleware(['auth']);
Route::get('module-assets/hrm/{filename}', function ($filename) {
    $path = module_path('hrm') . '/Resources/assets/' . $filename;
    if (!file_exists($path))
        abort(404);
    return response()->file($path);
});



Route::prefix('monthly-submission')->name('monthly-submission.')->group(function () {
    Route::get('/', [FilingController::class, 'index'])->name('index');
    Route::get('/create', [FilingController::class, 'create'])->name('create');
    Route::post('/store', [FilingController::class, 'store'])->name('store');
    Route::get('/{month}', [FilingController::class, 'show'])->name('show');
    Route::get('/{month}/uif', [FilingController::class, 'showUIF'])->name('show-uif');
    Route::get('/{month}/emp201-pdf', [FilingController::class, 'exportEMP201PDF'])->name('emp201-pdf');
    Route::get('/{month}/uif-pdf', [FilingController::class, 'exportUIFPDF'])->name('uif-pdf');
    Route::post('/{month}/eti-inputs', [FilingController::class, 'storeETIInputs'])->name('eti-inputs');
    Route::post('/{month}/finalize-emp201', [FilingController::class, 'finalizeEMP201'])->name('finalize-emp201');
});

// Keep existing filing routes for other filing types
Route::get('/filing/create', [FilingController::class, 'index'])->name('filing.create');
Route::get('/filing/view/{month?}', function ($month = null) {
    if ($month) {
        return app(FilingController::class)->show($month);
    }
    return view('hrm::filing.emp201-view');
})->name('filing.view');

Route::get('/filing/return', [FilingController::class, 'oidReturn'])->name('filing.return');
Route::post('/filing/return/export', [FilingController::class, 'exportOIDExcel'])->name('filing.return.export');

Route::get('/filing/annual', [FilingController::class, 'biFiling'])->name('filing.annual');
Route::get('/filing/annual/employee/{payslipId}/pdf', [FilingController::class, 'exportEmployeeBiFilingPDF'])->name('employee-bi-filing.pdf');
Route::get('bi-filing/emp501-pdf/{season}', [FilingController::class, 'exportEMP501PDF'])->name('bi-filing.emp501-pdf');
Route::get('/filing/leaverecord', [LeaveRecordController::class, 'index'])->name('filing.leaverecord');
Route::get('/filing/tax-year-report', [FilingController::class, 'taxYearReport'])->name('filing.tax-year-report');
Route::post('/filing/leaverecord/store', [LeaveRecordController::class, 'store'])->name('leaverecord.store');
Route::get('/leaverecord/balances/{employeeId}', [LeaveRecordController::class, 'getEmployeeLeaveBalances'])->name('leaverecord.getbalances');
Route::post('/employees/validate', [EmployeeController::class, 'ajaxValidate'])->name('employees.ajax-validate');

// AJAX Validation Routes
Route::post('/company-cars/validate', [CompanyCarController::class, 'ajaxValidateStore'])->name('company-cars.ajax-validate-store');
Route::match(['post', 'put'], '/company-cars/validate-update/{id}', [CompanyCarController::class, 'ajaxValidateUpdate'])->name('company-cars.ajax-validate-update');
Route::post('/company-car-operating/validate', [CompanyCarUnderOperatingController::class, 'ajaxValidateStore'])->name('company-car-operating.ajax-validate-store');
Route::match(['post', 'put'], '/company-car-operating/validate-update/{id}', [CompanyCarUnderOperatingController::class, 'ajaxValidateUpdate'])->name('company-car-operating.ajax-validate-update');
Route::post('/maintenance-order/validate', [MaintenanceOrderController::class, 'ajaxValidateStore'])->name('maintenance-order.ajax-validate-store');
Route::match(['post', 'put'], '/maintenance-order/validate-update/{id}', [MaintenanceOrderController::class, 'ajaxValidateUpdate'])->name('maintenance-order.ajax-validate-update');
Route::post('/add-maintenance/validate', [AddMaintenanceOrderController::class, 'ajaxValidateStore'])->name('add-maintenance.ajax-validate-store');
Route::match(['post', 'put'], '/add-maintenance/validate-update/{id}', [AddMaintenanceOrderController::class, 'ajaxValidateUpdate'])->name('add-maintenance.ajax-validate-update');
Route::post('/basic-salariess/validate', [BasicSalaryController::class, 'ajaxValidateStore'])->name('basic-salariess.ajax-validate-store');
Route::match(['post', 'put'], '/basic-salariess/validate-update/{id}', [BasicSalaryController::class, 'ajaxValidateUpdate'])->name('basic-salariess.ajax-validate-update');
Route::post('/accommodation-benefits/validate', [AccommodationBenefitController::class, 'ajaxValidateStore'])->name('accommodation-benefits.ajax-validate-store');
Route::match(['post', 'put'], '/accommodation-benefits/validate-update/{id}', [AccommodationBenefitController::class, 'ajaxValidateUpdate'])->name('accommodation-benefits.ajax-validate-update');
Route::post('/employer-loans/validate', [EmployerLoanController::class, 'ajaxValidateStore'])->name('employer-loans.ajax-validate-store');
Route::match(['post', 'put'], '/employer-loans/validate-update/{id}', [EmployerLoanController::class, 'ajaxValidateUpdate'])->name('employer-loans.ajax-validate-update');
Route::post('/garnishee/validate', [GarnisheeController::class, 'ajaxValidateStore'])->name('garnishee.ajax-validate-store');
Route::match(['post', 'put'], '/garnishee/validate-update/{id}', [GarnisheeController::class, 'ajaxValidateUpdate'])->name('garnishee.ajax-validate-update');
Route::post('/income-policies/validate', [IncomePolicyController::class, 'ajaxValidateStore'])->name('income-policies.ajax-validate-store');
Route::match(['post', 'put'], '/income-policies/validate-update/{id}', [IncomePolicyController::class, 'ajaxValidateUpdate'])->name('income-policies.ajax-validate-update');
Route::post('/savings-deductions/validate', [SavingsDeductionController::class, 'ajaxValidateStore'])->name('savings-deductions.ajax-validate-store');
Route::match(['post', 'put'], '/savings-deductions/validate-update/{id}', [SavingsDeductionController::class, 'ajaxValidateUpdate'])->name('savings-deductions.ajax-validate-update');
Route::post('/custom-deductions/validate', [CustomDeductionController::class, 'ajaxValidateStore'])->name('custom-deductions.ajax-validate-store');
Route::match(['post', 'put'], '/custom-deductions/validate-update/{id}', [CustomDeductionController::class, 'ajaxValidateUpdate'])->name('custom-deductions.ajax-validate-update');
Route::post('/extra-pay/validate', [ExtraPayController::class, 'ajaxValidateStore'])->name('extra-pay.ajax-validate-store');
Route::match(['post', 'put'], '/extra-pay/validate-update/{id}', [ExtraPayController::class, 'ajaxValidateUpdate'])->name('extra-pay.ajax-validate-update');
Route::post('/once-off-commission/validate', [OnceOffCommissionController::class, 'ajaxValidateStore'])->name('once-off-commission.ajax-validate-store');
Route::match(['post', 'put'], '/once-off-commission/validate-update/{id}', [OnceOffCommissionController::class, 'ajaxValidateUpdate'])->name('once-off-commission.ajax-validate-update');
Route::post('/payslip-commissions/validate', [PayslipCommissionController::class, 'ajaxValidateStore'])->name('payslip-commissions.ajax-validate-store');
Route::match(['post', 'put'], '/payslip-commissions/validate-update/{id}', [PayslipCommissionController::class, 'ajaxValidateUpdate'])->name('payslip-commissions.ajax-validate-update');
Route::post('/company-basic-salaries/validate', [CompanyBasicSalaryController::class, 'ajaxValidateStore'])->name('company-basic-salaries.ajax-validate-store');
Route::match(['post', 'put'], '/company-basic-salaries/validate-update/{id}', [CompanyBasicSalaryController::class, 'ajaxValidateUpdate'])->name('company-basic-salaries.ajax-validate-update');
Route::post('/donations/validate', [DonationController::class, 'ajaxValidateStore'])->name('donations.ajax-validate-store');
Route::match(['post', 'put'], '/donations/validate-update/{id}', [DonationController::class, 'ajaxValidateUpdate'])->name('donations.ajax-validate-update');
Route::post('/repayments/validate', [RepaymentController::class, 'ajaxValidateStore'])->name('repayments.ajax-validate-store');
Route::match(['post', 'put'], '/repayments/validate-update/{id}', [RepaymentController::class, 'ajaxValidateUpdate'])->name('repayments.ajax-validate-update');
Route::post('/staff-purchases/validate', [StaffPurchaseController::class, 'ajaxValidateStore'])->name('staff-purchases.ajax-validate-store');
Route::match(['post', 'put'], '/staff-purchases/validate-update/{id}', [StaffPurchaseController::class, 'ajaxValidateUpdate'])->name('staff-purchases.ajax-validate-update');
Route::post('/bursaries/validate', [BursaryController::class, 'ajaxValidateStore'])->name('bursaries.ajax-validate-store');
Route::match(['post', 'put'], '/bursaries/validate-update/{id}', [BursaryController::class, 'ajaxValidateUpdate'])->name('bursaries.ajax-validate-update');
Route::post('/add-garnishees/validate', [AddGarnisheeController::class, 'ajaxValidateStore'])->name('add-garnishees.ajax-validate-store');
Route::match(['post', 'put'], '/add-garnishees/validate-update/{id}', [AddGarnisheeController::class, 'ajaxValidateUpdate'])->name('add-garnishees.ajax-validate-update');

// Regular Inputs AJAX Validation Routes
Route::post('/travel-allowances/validate', [TravelAllowanceController::class, 'ajaxValidateStore'])->name('travel-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/travel-allowances/validate-update/{id}', [TravelAllowanceController::class, 'ajaxValidateUpdate'])->name('travel-allowances.ajax-validate-update');
Route::post('/bursaries-scholarships/validate', [BursariesScholarshipController::class, 'ajaxValidateStore'])->name('bursaries-scholarships.ajax-validate-store');
Route::match(['post', 'put'], '/bursaries-scholarships/validate-update/{id}', [BursariesScholarshipController::class, 'ajaxValidateUpdate'])->name('bursaries-scholarships.ajax-validate-update');
Route::post('/income-protection/validate', [IncomeProtectionController::class, 'ajaxValidateStore'])->name('income-protection.ajax-validate-store');
Route::match(['post', 'put'], '/income-protection/validate-update/{id}', [IncomeProtectionController::class, 'ajaxValidateUpdate'])->name('income-protection.ajax-validate-update');
Route::post('/medical-aid/validate', [MedicalAidController::class, 'ajaxValidateStore'])->name('medical-aid.ajax-validate-store');
Route::match(['post', 'put'], '/medical-aid/validate-update/{id}', [MedicalAidController::class, 'ajaxValidateUpdate'])->name('medical-aid.ajax-validate-update');
Route::post('/pension-fund/validate', [PensionFundController::class, 'ajaxValidateStore'])->name('pension-fund.ajax-validate-store');
Route::match(['post', 'put'], '/pension-fund/validate-update/{id}', [PensionFundController::class, 'ajaxValidateUpdate'])->name('pension-fund.ajax-validate-update');
Route::post('/provident-fund/validate', [ProvidentFundController::class, 'ajaxValidateStore'])->name('provident-fund.ajax-validate-store');
Route::match(['post', 'put'], '/provident-fund/validate-update/{id}', [ProvidentFundController::class, 'ajaxValidateUpdate'])->name('provident-fund.ajax-validate-update');
Route::post('/retirement-annuitie/validate', [RetirementAnnuitieController::class, 'ajaxValidateStore'])->name('retirement-annuitie.ajax-validate-store');
Route::match(['post', 'put'], '/retirement-annuitie/validate-update/{id}', [RetirementAnnuitieController::class, 'ajaxValidateUpdate'])->name('retirement-annuitie.ajax-validate-update');
Route::post('/union-membership/validate', [UnionMembershipFeeController::class, 'ajaxValidateStore'])->name('union-membership.ajax-validate-store');
Route::match(['post', 'put'], '/union-membership/validate-update/{id}', [UnionMembershipFeeController::class, 'ajaxValidateUpdate'])->name('union-membership.ajax-validate-update');
Route::post('/tax-over-deduction/validate', [TaxOverDeductionController::class, 'ajaxValidateStore'])->name('tax-over-deduction.ajax-validate-store');
Route::match(['post', 'put'], '/tax-over-deduction/validate-update/{id}', [TaxOverDeductionController::class, 'ajaxValidateUpdate'])->name('tax-over-deduction.ajax-validate-update');
Route::post('/tax-directive-entries/validate', [TaxDirectiveEntryController::class, 'ajaxValidateStore'])->name('tax-directive-entries.ajax-validate-store');
Route::match(['post', 'put'], '/tax-directive-entries/validate-update/{id}', [TaxDirectiveEntryController::class, 'ajaxValidateUpdate'])->name('tax-directive-entries.ajax-validate-update');

// Payslip Inputs AJAX Validation Routes
Route::post('/annual-bonuses/validate', [AnnualBonusController::class, 'ajaxValidateStore'])->name('annual-bonuses.ajax-validate-store');
Route::match(['post', 'put'], '/annual-bonuses/validate-update/{id}', [AnnualBonusController::class, 'ajaxValidateUpdate'])->name('annual-bonuses.ajax-validate-update');
Route::post('/annual-payments/validate', [AnnualPaymentController::class, 'ajaxValidateStore'])->name('annual-payments.ajax-validate-store');
Route::match(['post', 'put'], '/annual-payments/validate-update/{id}', [AnnualPaymentController::class, 'ajaxValidateUpdate'])->name('annual-payments.ajax-validate-update');
Route::post('/arbitration-awards/validate', [ArbitrationAwardController::class, 'ajaxValidateStore'])->name('arbitration-awards.ajax-validate-store');
Route::match(['post', 'put'], '/arbitration-awards/validate-update/{id}', [ArbitrationAwardController::class, 'ajaxValidateUpdate'])->name('arbitration-awards.ajax-validate-update');
Route::post('/dividends-subject/validate', [DividendsSubjectController::class, 'ajaxValidateStore'])->name('dividends-subject.ajax-validate-store');
Route::match(['post', 'put'], '/dividends-subject/validate-update/{id}', [DividendsSubjectController::class, 'ajaxValidateUpdate'])->name('dividends-subject.ajax-validate-update');
Route::post('/restraint-of-trade/validate', [RestraintOfTradeController::class, 'ajaxValidateStore'])->name('restraint-of-trade.ajax-validate-store');
Route::match(['post', 'put'], '/restraint-of-trade/validate-update/{id}', [RestraintOfTradeController::class, 'ajaxValidateUpdate'])->name('restraint-of-trade.ajax-validate-update');
Route::post('/broad-based-employees/validate', [BroadBasedEmployeeController::class, 'ajaxValidateStore'])->name('broad-based-employees.ajax-validate-store');
Route::match(['post', 'put'], '/broad-based-employees/validate-update/{id}', [BroadBasedEmployeeController::class, 'ajaxValidateUpdate'])->name('broad-based-employees.ajax-validate-update');
Route::post('/computer-allowances/validate', [ComputerAllowanceController::class, 'ajaxValidateStore'])->name('computer-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/computer-allowances/validate-update/{id}', [ComputerAllowanceController::class, 'ajaxValidateUpdate'])->name('computer-allowances.ajax-validate-update');
Route::post('/expense-claims/validate', [ExpenseClaimController::class, 'ajaxValidateStore'])->name('expense-claims.ajax-validate-store');
Route::match(['post', 'put'], '/expense-claims/validate-update/{id}', [ExpenseClaimController::class, 'ajaxValidateUpdate'])->name('expense-claims.ajax-validate-update');
Route::post('/equity-instruments/validate', [EquityInstrumentController::class, 'ajaxValidateStore'])->name('equity-instruments.ajax-validate-store');
Route::match(['post', 'put'], '/equity-instruments/validate-update/{id}', [EquityInstrumentController::class, 'ajaxValidateUpdate'])->name('equity-instruments.ajax-validate-update');
Route::post('/phone-allowances/validate', [PhoneAllowanceController::class, 'ajaxValidateStore'])->name('phone-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/phone-allowances/validate-update/{id}', [PhoneAllowanceController::class, 'ajaxValidateUpdate'])->name('phone-allowances.ajax-validate-update');
Route::post('/relocation-allowances/validate', [RelocationAllowanceController::class, 'ajaxValidateStore'])->name('relocation-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/relocation-allowances/validate-update/{id}', [RelocationAllowanceController::class, 'ajaxValidateUpdate'])->name('relocation-allowances.ajax-validate-update');
Route::post('/allowance-internationals/validate', [AllowanceInternationalController::class, 'ajaxValidateStore'])->name('allowance-internationals.ajax-validate-store');
Route::match(['post', 'put'], '/allowance-internationals/validate-update/{id}', [AllowanceInternationalController::class, 'ajaxValidateUpdate'])->name('allowance-internationals.ajax-validate-update');
Route::post('/subsistence-allowances/validate', [SubsistenceAllowanceController::class, 'ajaxValidateStore'])->name('subsistence-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/subsistence-allowances/validate-update/{id}', [SubsistenceAllowanceController::class, 'ajaxValidateUpdate'])->name('subsistence-allowances.ajax-validate-update');
Route::post('/tool-allowances/validate', [ToolAllowanceController::class, 'ajaxValidateStore'])->name('tool-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/tool-allowances/validate-update/{id}', [ToolAllowanceController::class, 'ajaxValidateUpdate'])->name('tool-allowances.ajax-validate-update');
Route::post('/uniform-allowances/validate', [UniformAllowanceController::class, 'ajaxValidateStore'])->name('uniform-allowances.ajax-validate-store');
Route::match(['post', 'put'], '/uniform-allowances/validate-update/{id}', [UniformAllowanceController::class, 'ajaxValidateUpdate'])->name('uniform-allowances.ajax-validate-update');
Route::post('/employee-benefits/validate', [EmployeeBenefitController::class, 'ajaxValidateStore'])->name('employee-benefits.ajax-validate-store');
Route::match(['post', 'put'], '/employee-benefits/validate-update/{id}', [EmployeeBenefitController::class, 'ajaxValidateUpdate'])->name('employee-benefits.ajax-validate-update');
Route::post('/medical-costs/validate', [MedicalCostController::class, 'ajaxValidateStore'])->name('medical-costs.ajax-validate-store');
Route::match(['post', 'put'], '/medical-costs/validate-update/{id}', [MedicalCostController::class, 'ajaxValidateUpdate'])->name('medical-costs.ajax-validate-update');
Route::post('/covid19-disasters/validate', [Covid19DisasterController::class, 'ajaxValidateStore'])->name('covid19-disasters.ajax-validate-store');
Route::match(['post', 'put'], '/covid19-disasters/validate-update/{id}', [Covid19DisasterController::class, 'ajaxValidateUpdate'])->name('covid19-disasters.ajax-validate-update');
Route::post('/long-service-awards/validate', [LongServiceAwardController::class, 'ajaxValidateStore'])->name('long-service-awards.ajax-validate-store');
Route::match(['post', 'put'], '/long-service-awards/validate-update/{id}', [LongServiceAwardController::class, 'ajaxValidateUpdate'])->name('long-service-awards.ajax-validate-update');
Route::post('/ters-payouts/validate', [TersPayoutController::class, 'ajaxValidateStore'])->name('ters-payouts.ajax-validate-store');
Route::match(['post', 'put'], '/ters-payouts/validate-update/{id}', [TersPayoutController::class, 'ajaxValidateUpdate'])->name('ters-payouts.ajax-validate-update');
Route::post('/termination-lumps/validate', [TerminationLumpController::class, 'ajaxValidateStore'])->name('termination-lumps.ajax-validate-store');
Route::match(['post', 'put'], '/termination-lumps/validate-update/{id}', [TerminationLumpController::class, 'ajaxValidateUpdate'])->name('termination-lumps.ajax-validate-update');

Route::get('/bi-filing/tax-certificate-export/{season}', [FilingController::class, 'exportTaxCertificate'])->name('bi-filing.tax-certificate-export');
// Route::get('/filing/leaverecord', function () {
//     return view('hrm::filing.leaverecord');
// })->name('filing.leaverecord');
Route::get('/leaverecord/fetch', [LeaveRecordController::class, 'fetch'])->name('leaverecord.fetch');
// ESS Management Routes (Admin Side)
Route::prefix('ess-management')->name('ess-management.')->middleware(['auth'])->group(function () {
    Route::get('/', [EssManagementController::class, 'index'])->name('index');
    Route::post('/{id}/send-invitation', [EssManagementController::class, 'sendInvitation'])->name('send-invitation');
    Route::post('/{id}/resend-invitation', [EssManagementController::class, 'resendInvitation'])->name('resend-invitation');
    Route::post('/bulk-invitations', [EssManagementController::class, 'sendBulkInvitations'])->name('bulk-invitations');
    Route::post('/{id}/disable', [EssManagementController::class, 'disableAccess'])->name('disable');
    Route::post('/{id}/enable', [EssManagementController::class, 'enableAccess'])->name('enable');
});

// Attendance Reports
Route::prefix('report')->name('report.')->middleware(['auth'])->group(function () {
    Route::get('/detailed-attendance', [ReportController::class, 'detailedAttendance'])->name('detailed-attendance');
    Route::get('/detailed-attendance/export', [ReportController::class, 'exportDetailedAttendance'])->name('detailed-attendance.export');
});

});
