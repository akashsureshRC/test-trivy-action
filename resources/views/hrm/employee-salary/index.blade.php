@extends('layouts.main')
@section('page-title')
    {{ __('Payslip Inputs') }}
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
                            <a href="{{ route('annual-bonuses.create') }}" style="text-decoration: none; color: inherit;">
                                 Annual Bonus
                            </a> 
                            <a href="{{ route('annual-bonuses.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                        padding-top: 10px !important; padding-bottom: 10px !important;">
                            <a href="{{ route('annual-payments.create') }}" style="text-decoration: none; color: inherit;">
                                 Annual Payment
                            </a> 
                            <a href="{{ route('annual-payments.create', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('arbitration-awards.create') }}" style="text-decoration: none; color: inherit;">
                                 Arbitration Award
                            </a> 
                            <a href="{{ route('arbitration-awards.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('dividends-subject.create') }}" style="text-decoration: none; color: inherit;">
                                   Dividends Subject to Income Tax
                            </a> 
                            <a href="{{ route('dividends-subject.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Extra Pay
                            <a href="{{ route('extra-pay.create') }}" style="text-decoration: none; color: inherit;">
                                   Extra Pay
                            </a> 
                            <a href="{{ route('extra-pay.edit', 1) }}" style="text-decoration: none; color: inherit;">
                               
                            </a>
                        </h6>
                        <!--<h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                            Leave Paid Out
                        </h6>-->
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('once-off-commission.create') }}" style="text-decoration: none; color: inherit;">
                                   Once-Off Commission
                            </a> 
                            <a href="{{ route('once-off-commission.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('restraint-of-trade.create') }}" style="text-decoration: none; color: inherit;">
                                  Restraint Of Trade
                            </a>
                            <a href="{{ route('restraint-of-trade.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                 Restraint Of Trade
                            </a>
                        </h6>
                    </div>
                </div>
            </div>


            <div class="col-xxl-4 col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #fde8fd; border-radius: 0px !important;">
                        <h4 class="" style="color: #1d64ae;">
                            Allowance
                        </h4>
                    </div>
                    <div class="card-body" style="padding:0 !important">
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                       
                           <a href="{{ route('broad-based-employees.create') }}" style="text-decoration: none; color: inherit;">
                             Broad Based Employee Share Plan
                        </a>
                        <a href="{{ route('broad-based-employees.edit', 1) }}" style="text-decoration: none; color: inherit;">
                           
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('computer-allowances.create') }}" style="text-decoration: none; color: inherit;">
                                 Computer Allowance
                            </a> 
                            <a href="{{ route('computer-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                               
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                           <a href="{{ route('expense-claims.create') }}" style="text-decoration: none; color: inherit;">
                            Expense Claim
                        </a> 
                        <a href="{{ route('expense-claims.edit', 1) }}" style="text-decoration: none; color: inherit;">
                             
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('equity-instruments.create') }}" style="text-decoration: none; color: inherit;">
                                 Gain on Vesting of Equity Instruments
                            </a> 
                            <a href="{{ route('equity-instruments.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                            </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                        
                          <a href="{{ route('phone-allowances.create') }}" style="text-decoration: none; color: inherit;">
                               Phone Allowance
                        </a>
                        <a href="{{ route('phone-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                             Phone Allowance
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                         
                           <a href="{{ route('relocation-allowances.create') }}" style="text-decoration: none; color: inherit;">
                             Relocation Allowance
                        </a>
                        <a href="{{ route('relocation-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                        </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('allowance-internationals.create') }}" style="text-decoration: none; color: inherit;">
                                 Subsistence Allowance International
                            </a> 
                            <a href="{{ route('allowance-internationals.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                 
                            </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('subsistence-allowances.create') }}" style="text-decoration: none; color: inherit;">
                                Subsistence Allowance Local
                           </a> 
                           <a href="{{ route('subsistence-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
                        <h6 class=""
                        style=" padding-left:17px !important; font-weight: 400;
                            padding-top: 10px !important; padding-bottom: 10px !important;">
                       
                        <a href="{{ route('tool-allowances.create') }}" style="text-decoration: none; color: inherit;">
                            Tool Allowance
                       </a> 
                       <a href="{{ route('tool-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                       </a>
                    </h6>
                    <h6 class=""
                        style=" padding-left:17px !important; font-weight: 400;
                            padding-top: 10px !important; padding-bottom: 10px !important;">
                       
                        <a href="{{ route('uniform-allowances.create') }}" style="text-decoration: none; color: inherit;">
                            Uniform Allowance
                       </a> 
                       <a href="{{ route('uniform-allowances.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                       </a>
                    </h6>
                    </div>
                </div>
            </div>


            <div class="col-xxl-4 col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header" style="background: #fde8fd; border-radius: 0px !important;">
                        <h4 class="" style="color: #1d64ae;">
                            Benefit
                        </h4>
                    </div>
                    <div class="card-body" style="padding:0 !important">
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                                 <a href="{{ route('bursaries.create') }}" style="text-decoration: none; color: inherit;">
                                    Bursaries And Scholarships
                               </a> 
                               <a href="{{ route('bursaries.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                    
                               </a>
                          
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                           <a href="{{ route('employee-benefits.create') }}" style="text-decoration: none; color: inherit;">
                            Employee's Debt Benefit
                       </a> 
                       <a href="{{ route('employee-benefits.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                       </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                        
                           <a href="{{ route('medical-costs.create') }}" style="text-decoration: none; color: inherit;">
                            Medical Costs (Other than medical scheme)
                       </a> 
                       <a href="{{ route('medical-costs.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                       </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                         
                           <a href="{{ route('donations.create') }}" style="text-decoration: none; color: inherit;">
                            Donations
                       </a> 
                       <a href="{{ route('donations.edit', 1) }}" style="text-decoration: none; color: inherit;">
                            
                       </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('repayments.create') }}" style="text-decoration: none; color: inherit;">
                                Repayment Of Loan
                           </a> 
                           <a href="{{ route('repayments.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('staff-purchases.create') }}" style="text-decoration: none; color: inherit;">
                                Staff Purchases
                           </a> 
                           <a href="{{ route('staff-purchases.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
                        <h6 class=""
                            style="border-bottom:1px solid rgb(237, 238, 240); padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                           
                            <a href="{{ route('covid19-disasters.create') }}" style="text-decoration: none; color: inherit;">
                                COVID-19 Disaster Relief
                           </a> 
                           <a href="{{ route('covid19-disasters.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('long-service-awards.create') }}" style="text-decoration: none; color: inherit;">
                                Long Service Award
                           </a> 
                           <a href="{{ route('long-service-awards.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('ters-payouts.create') }}" style="text-decoration: none; color: inherit;">
                                TERS Payout
                           </a> 
                           <a href="{{ route('ters-payouts.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
                        <h6 class=""
                            style=" padding-left:17px !important; font-weight: 400;
                                padding-top: 10px !important; padding-bottom: 10px !important;">
                          
                            <a href="{{ route('termination-lumps.create') }}" style="text-decoration: none; color: inherit;">
                                Termination Lump Sums
                           </a> 
                           <a href="{{ route('termination-lumps.edit', 1) }}" style="text-decoration: none; color: inherit;">
                                
                           </a>
                        </h6>
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
