@extends('layouts.main')
<!-- @section('page-title')
    {{ __('Dashboard') }}
@endsection -->
<!-- @section('page-breadcrumb')
    {{ __('Hrm') }}
@endsection -->
<style>
    .page-header-title {
        display: none !important;
    }
    .breadcrumb {
        display: none !important;
    }

</style>
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Hrm/Resources/assets/css/main.css') }}">
@endpush
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="d-flex justify-content-center align-items-center" style="margin-top: 45px;">
        <div class="col-xxl-8 col-xl-10">
            <div class=" ">
            <div class="card-header card-body table-border-style text-center">
                <h2 class="" style="line-height: 40px;">
                    One Stop Solutions for all your 
                    <br>
                    Management Needs
                </h2>
                </div>
                <div class="card-body " style="margin-top:55px">
                    <div class="d-flex" style="gap:6px">
                        <div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
                            <div class="">
                                <a href="/" target="_blank">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#fff6ea; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/book.png" style="height: 35px; width: 35px;">
                                        </div>
                                    </div>
                                    <div class="h6 mb-0">
                                        CRM
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
                            <div class="">
                                <a href="/" target="_blank">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#e3feed; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px" >
                                            <img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/pharmacy.png" style="height: 35px; width: 35px;">
                                        </div>
                                    </div>
                                    <div class="h6 mb-0">
                                      Inventory
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
                            <div class="">
                                <a href="/" target="_blank">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#fee9f1; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/support.png" style="height: 35px; width: 35px;">
                                        </div>
                                    </div>
                                    <div class="h6 mb-0">
                                    Sales
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
						<div class="">
							<a href="/" target="_blank">
								<div class="rounded d-flex justify-content-center align-content-center mx-auto " style="background:#def3f8; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
									<div class="symbol symbol-30px">
										<img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/web.png" style="height: 35px; width: 35px;">
									</div>
								</div>
								<div class="h6 mb-0">
									Purchase
								</div>
							</a>
						</div>
					</div>
                    </div>

                    <div class="d-flex " style="gap:6px">
					<div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
						<div class="">
							<a href="/" target="_blank">
								<div class="rounded d-flex justify-content-center align-content-center mx-auto " style="background:#f8e1f8; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
									<div class="symbol symbol-30px">
										<img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/solution.png" style="height: 35px; width: 35px;">
									</div>
								</div>
								<div class="h6 mb-0">
									Payroll
								</div>
							</a>
						</div>
					</div>
					<div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
						<div class="">
							<a href="/" target="_blank">
								<div class="rounded d-flex justify-content-center align-content-center mx-auto " style="background:#fce8e9; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
									<div class="symbol symbol-30px">
										<img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/live.png" style="height: 35px; width: 35px;">
									</div>
								</div>
								<div class="h6 mb-0">
									Accounting
								</div>
							</a>
						</div>
					</div>
                    <div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
						<div class="">
							<a href="/" target="_blank">
								<div class="rounded d-flex justify-content-center align-content-center mx-auto " style="background:#def3f8; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
									<div class="symbol symbol-30px">
										<img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/web.png" style="height: 35px; width: 35px;">
									</div>
								</div>
								<div class="h6 mb-0">
									Asset Management
								</div>
							</a>
						</div>
					</div>
					<div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
						<div class="">
							<a href="/" target="_blank">
								<div class="rounded d-flex justify-content-center align-content-center mx-auto " style="background:#f8e1f8; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
									<div class="symbol symbol-30px">
										<img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/solution.png" style="height: 35px; width: 35px;">
									</div>
								</div>
								<div class="h6 mb-0">
									Laybuy
								</div>
							</a>
						</div>
					</div>
				</div>


                <div class="d-flex gap-12" style="">
					
					<div class="col-md-3 card d-flex justify-content-center align-content-center mx-auto text-center" 
                        style="padding-top: 25px; padding-bottom: 25px; border-radius:7px; border: 1px solid #f8e1f8; margin-bottom: 10px !important;">
						<div class="">
							<a href="/" target="_blank">
								<div class="rounded d-flex justify-content-center align-content-center mx-auto " style="background:#fce8e9; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
									<div class="symbol symbol-30px">
										<img alt="Pic" src="https://admin.rcpos.co.za/assets/media/logos/live.png" style="height: 35px; width: 35px;">
									</div>
								</div>
								<div class="h6 mb-0">
									Help Desk
								</div>
							</a>
						</div>
					</div>
				</div>


                </div>
            </div>
        </div>
    </div>




    <div class="row">
        @if (!in_array(Auth::user()->type, Auth::user()->not_emp_type))
            <div class="col-xxl-12">
                <div class="row">
                    <div class="col-xxl-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __("Holiday's ") }}</h5>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-5">
                        <div class="card" style="height: 232px;">
                            <div class="card-header">
                                <h5>{{ __('Mark Attandance ') }}<span>{{ companyDateFormate(date('Y-m-d')) }}</span></h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted pb-0-5">
                                    {{ __('My Office Time: ' . $officeTime['startTime'] . ' to ' . $officeTime['endTime']) }}
                                </p>
                                <div class="row">
                                    <div class="col-md-6 float-right border-right">
                                        {{ Form::open(['url' => 'attendance/attendance', 'method' => 'post']) }}

                                        @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                            <button type="submit" value="0" name="in" id="clock_in"
                                                class="btn btn-rc-primary">{{ __('CLOCK IN') }}</button>
                                        @else
                                            <button type="submit" value="0" name="in" id="clock_in" class="btn btn-rc-primary disabled"
                                                disabled>{{ __('CLOCK IN') }}</button>
                                        @endif
                                        {{ Form::close() }}
                                    </div>
                                    <div class="col-md-6 float-left">
                                        @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                            {{ Form::model($employeeAttendance, ['route' => ['attendance.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                                            <button type="submit" value="1" name="out" id="clock_out"
                                                class="btn btn-danger">{{ __('CLOCK OUT') }}</button>
                                        @else
                                            <button type="submit" value="1" name="out" id="clock_out"
                                                class="btn btn-danger disabled" disabled>{{ __('CLOCK OUT') }}</button>
                                        @endif
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Announcement List') }}</h5>
                            </div>
                            <div class="card-body" style="height: 270px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @forelse ($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ companyDateFormate($announcement->start_date) }}</td>
                                                    <td>{{ companyDateFormate($announcement->end_date) }}</td>
                                                    <td>{{ $announcement->description }}</td>
                                                </tr>
                                            @empty
                                                @include('layouts.nodatafound')
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- <div class="col-xxl-12">
                <div class="col-xxl-12">
                    <div class="row">
                        <div class="col-xl-5">
                            <div class="card">
                                <div class="card-header card-body table-border-style">
                                    <h5>{{ __("Today's Not Clock In") }}</h5>
                                </div>
                                <div class="card-body" style="height: 290px; overflow:auto">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @foreach ($notClockIns as $notClockIn)
                                                    <tr>
                                                        <td>{{ $notClockIn->name }}</td>
                                                        <td><span class="absent-btn">{{ __('Absent') }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header card-body table-border-style">
                                    <h5>{{ __('Announcement List') }}</h5>
                                </div>
                                <div class="card-body" style="height: 270px; overflow:auto">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Start Date') }}</th>
                                                    <th>{{ __('End Date') }}</th>
                                                    <th>{{ __('Description') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @forelse ($announcements as $announcement)
                                                    <tr>
                                                        <td>{{ $announcement->title }}</td>
                                                        <td>{{ companyDateFormate($announcement->start_date) }}</td>
                                                        <td>{{ companyDateFormate($announcement->end_date) }}</td>
                                                        <td>{{ $announcement->description }}</td>
                                                    </tr>
                                                @empty
                                                    @include('layouts.nodatafound')
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ __("Holiday's & Event's") }}</h5>
                                </div>
                                <div class="card-body card-635 ">
                                    <div id='calendar' class='calendar'></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        @endif
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('Modules/Hrm/Resources/assets/js/main.min.js') }}"></script>
    <script type="text/javascript">
        (function () {
            var etitle;
            var etype;
            var etypeclass;
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    timeGridDay: "{{ __('Day') }}",
                    timeGridWeek: "{{ __('Week') }}",
                    dayGridMonth: "{{ __('Month') }}"
                },
                themeSystem: 'bootstrap',
                slotDuration: '00:10:00',
                navLinks: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                handleWindowResize: true,
                events: {!! json_encode($events) !!},
            });
            calendar.render();
        })();
    </script>
@endpush