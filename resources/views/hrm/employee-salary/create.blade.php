@extends('layouts.main')
@section('page-title')
    {{ __('Regular Inputs') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Calculations') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <div class="" style="margin-top:40px">


        <div class="row py-2">
            <div class="col-xxl-4 col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #fde8fd; border-radius: 0px !important;">
                        <h4 class="" style="color: #1d64ae;">
                            Income
                        </h4>
                    </div>
                    <div class="card-body" style="padding:0 !important">
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                        padding-top: 10px !important; padding-bottom: 10px !important;">
                            <a href="{{ route('payslip-commissions.create') }}" style="text-decoration: none; color: inherit;">
                                 Commission
                            <a href="{{ route('payslip-commissions.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                        padding-top: 10px !important; padding-bottom: 10px !important;">
                            <a href="{{ route('income-policies.create') }}" style="text-decoration: none; color: inherit;">
                                 Loss of Income Policy 
                            </a> 
                            <a href="{{ route('income-policies.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>

                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                        padding-top: 10px !important; padding-bottom: 10px !important;">
                      
                 
                            <a  href="{{ route('basic-salaries.create', ['employee_id' => request()->employee_id]) }}" style="text-decoration: none; color: inherit;">
                                 Basic Salary 
                            </a> 
                            <a href="{{ route('basic-salariess.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('travel-allowances.create', ['employee_id' => request()->employee_id]) }}"  style="text-decoration: none; color: inherit;">
                                Travel Allowance
                            </a> 
                            <a href="{{ route('travel-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                 
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('accommodation-benefits.create') }}" style="text-decoration: none; color: inherit;">
                                   Accommodation Benefit
                            </a> 
                            <a href="{{ route('accommodation-benefits.edit', 1) }}" style="text-decoration: none; color: inherit;">
                               
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('bursaries-scholarships.create') }}" style="text-decoration: none; color: inherit;">
                                  Bursaries And Scholarships (Regular)
                            </a> 
                            <a href="{{ route('bursaries-scholarships.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                 
                            </a>
                        </h6>
                        <h6 class=""
                        style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                            padding-top: 10px !important; padding-bottom: 10px !important;">
                      
                        <a href="{{ route('company-cars.create') }}" style="text-decoration: none; color: inherit;">
                             Company Car
                        </a> 
                        <a href="{{ route('company-cars.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                        </a>
                    </h6>
                    <h6 class=""
                    style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                        padding-top: 10px !important; padding-bottom: 10px !important;">
                  
                    <a href="{{ route('company-car-operating.create') }}" style="text-decoration: none; color: inherit;">
                         Company Car Under Operating Lease
                    </a> 
                    <a href="{{ route('company-car-operating.create', 1) }}" style="text-decoration: none; color: inherit;">
                        
                    </a>
                </h6>
                

                        <!--<h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Leave Paid Out
                        </h6>-->
                        <!--<h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('once-off-commission.create') }}" style="text-decoration: none; color: inherit;">
                                Create   Once-Off Commission
                            </a> |
                            <a href="{{ route('once-off-commission.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                Edit   Once-Off Commission
                            </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('restraint-of-trade.create') }}" style="text-decoration: none; color: inherit;">
                                Create   Restraint Of Trade
                            </a> |
                            <a href="{{ route('restraint-of-trade.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                Edit   Restraint Of Trade
                            </a>
                        </h6>-->
                    </div>
                </div>
            </div>


            <div class="col-xxl-4 col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #fde8fd; border-radius: 0px !important;">
                        <h4 class="" style="color: #1d64ae;">
                           Deduction
                        </h4>
                    </div>
                    <div class="card-body" style="padding:0 !important">
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                       
                           <a href="{{ route('garnishee.create') }}" style="text-decoration: none; color: inherit;">
                             Garnishee 
                        </a> 
                        <a href="{{ route('garnishee.edit', 1) }}" style="text-decoration: none; color: inherit;">
                           
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('income-protection.create') }}" style="text-decoration: none; color: inherit;">
                                Income Protection
                            </a> 
                            <a href="{{ route('income-protection.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                           <a href="{{ route('maintenance-order.create') }}" style="text-decoration: none; color: inherit;">
                             Maintenance Order  
                        </a> 
                        <a href="{{ route('maintenance-order.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('medical-aid.create') }}" style="text-decoration: none; color: inherit;">
                                  Medical Aid
                            </a> 
                            <a href="{{ route('medical-aid.edit', 1) }}" style="text-decoration: none; color: inherit;">
                               
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                        
                          <a href="{{ route('pension-fund.create') }}" style="text-decoration: none; color: inherit;">
                             Pension Fund
                        </a> 
                        <a href="{{ route('pension-fund.edit', 1) }}" style="text-decoration: none; color: inherit;">
                             
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                         
                           <a href="{{ route('provident-fund.create') }}" style="text-decoration: none; color: inherit;">
                            Provident Fund
                        </a> 
                        <a href="{{ route('provident-fund.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('retirement-annuitie.create') }}" style="text-decoration: none; color: inherit;">
                                 Retirement Annuity Fund
                            </a> 
                            <a href="{{ route('retirement-annuitie.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                 
                            </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                             <a href="{{ route('union-membership.create') }}" style="text-decoration: none; color: inherit;">
                                 Union Membership Fee

                            </a> 
                            <a href="{{ route('union-membership.edit', 1) }}" style="text-decoration: none; color: inherit;">
                               
                            </a>
                        </h6>
                        <h6 class=""
                        style=" padding-left:17px !important; font-weight: 400;
                            padding-top: 10px !important; padding-bottom: 10px !important;">
                      

                         <a href="{{ route('tax-over-deduction.create') }}" style="text-decoration: none; color: inherit;">
                            Voluntary Tax Over-Deduction

                        </a> 
                        <a href="{{ route('tax-over-deduction.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            

                        </a>
                    </h6>
                    </div>
                </div>
            </div>


            <div class="col-xxl-4 col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #fde8fd; border-radius: 0px !important;">
                        <h4 class="" style="color: #1d64ae;">
                            Other
                        </h4>
                    </div>
                    <div class="card-body" style="padding:0 !important">
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                           <a href="{{ route('employer-loans.create') }}" style="text-decoration: none; color: inherit;">
                             Employer Loan

                        </a> 
                        <a href="{{ route('employer-loans.edit', 1) }}" style="text-decoration: none; color: inherit;">
                              

                        </a>

                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                           <a href="{{ route('savings-deductions.create') }}" style="text-decoration: none; color: inherit;">
                             Savings

                        </a> 
                        <a href="{{ route('savings-deductions.edit', 1) }}" style="text-decoration: none; color: inherit;">
                             
                        </a>

                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                           <a href="{{ route('tax-directive-entries.create') }}" style="text-decoration: none; color: inherit;">
                               Tax Directive

                        </a> 
                        <a href="{{ route('tax-directive-entries.edit', 1) }}" style="text-decoration: none; color: inherit;">
                           
                        </a> 
                        </h6>
                       <!-- <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Dividends Subject to Income Tax
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Extra Pay
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Leave Paid Out
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Once-Off Commission
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Restraint Of Trade
                        </h6>-->
                    </div>
                </div>
            </div>


            <!--<div class="d-flex justify-content-start " style="margin-bottom: 20px;">
                <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal"
                   >Cancel</button>
                <input class="btn btn-rc-primary" type="submit" value="Save">
            </div>-->
        </div>
    </div>

    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
@endsection
