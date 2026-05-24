@extends('layouts.main')
@section('page-title')
    {{ __('Manage Payslip') }}
@endsection
@section('page-breadcrumb')
    {{ __('Payslip') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                    <div class="d-flex row align-items-center justify-content-end">
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 col-12 mx-2 ">
                            <div class="btn-box">
                                {{ Form::label('month', __('Select Month'), ['class' => 'form-label']) }}
                                {{ Form::select('month', $month, date('m'), ['class' => 'form-control ', 'id' => 'month']) }}

                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 col-12 mx-2 ">
                            <div class="btn-box">
                                {{ Form::label('year', __('Select Year'), ['class' => 'form-label']) }}
                                {{ Form::select('year', $year, date('Y'), ['class' => 'form-control ']) }}

                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-rc-primary"
                            onclick="document.getElementById('payslip_form').submit(); return false;"
                            data-bs-toggle="tooltip" title="{{ __('payslip') }}"
                            data-original-title="{{ __('payslip') }}">
                            {{ __('Generate Payslip') }}
                        </a>
                        @stack('addButtonHook')
                    </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>

        </div>
        <div class="col-12">
            <x-rc-table title="{{ __('Find Employee Payslip') }}" titleIcon="ti ti-file-invoice">
                <x-rc-table.filter action="" method="GET">
                    <x-rc-table.filter-group label="{{ __('Month') }}">
                        <select class="form-control month_date" name="year" tabindex="-1" aria-hidden="true">
                            <option value="--">--</option>
                            @foreach ($month as $k => $mon)
                                @php
                                    $selected = date('m') - 1 == $k ? 'selected' : '';
                                @endphp
                                <option value="{{ $k }}" {{ $selected }}>{{ $mon }}</option>
                            @endforeach
                        </select>
                    </x-rc-table.filter-group>
                    <x-rc-table.filter-group label="{{ __('Year') }}">
                        {{ Form::select('year', $years, date('Y'), ['class' => 'form-control year_date']) }}
                    </x-rc-table.filter-group>
                </x-rc-table.filter>

                <x-rc-table.content>
                    <table class="rc-table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                <th class="col-id">{{ __('Employee Id') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Payroll Type') }}</th>
                                <th class="col-amount">{{ __('Salary') }}</th>
                                <th class="col-amount">{{ __('Net Salary') }}</th>
                                <th class="col-status">{{ __('Status') }}</th>
                                <th class="col-actions">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
    <!-- [ basic-table ] end -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();

                if (month == '') {
                    month = '{{ date('m', strtotime('last month')) }}';
                    year = '{{ date('Y') }}';
                }

                var datePicker = year + '-' + month;

                $.ajax({
                    url: '{{ route('payslip.search_json') }}',
                    type: 'POST',
                    data: {
                        "datePicker": datePicker,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        var datatable_data = {
                            data: data
                        };

                        function renderstatus(data, cell, row) {
                            if (data == 'Paid')
                                return '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                            else
                                return '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                        }

                        function renderButton(data, cell, row) {
                            var $div = $(row);
                            employee_id = $div.find('td:eq(0)').text();
                            status = $div.find('td:eq(6)').text();

                            var month = $(".month_date").val();
                            var year = $(".year_date").val();
                            var id = employee_id;
                            var payslip_id = data;


                            var clickToPaid = '';
                            var payslip = '';
                            var view = '';
                            var edit = '';
                            var deleted = '';
                            var form = '';

                            if (data != 0) {
                                var payslip =
                                    '<a href="#" data-url="{{ url('payslip/pdf/') }}/' + id +
                                    '/' + datePicker +
                                    '" data-size="md-pdf"  data-ajax-popup="true" class="btn btn-rc-primary" data-title="{{ __('Employee Payslip') }}">' +
                                    '{{ __('Payslip') }}' + '</a> ';
                            }

                            if (status == "UnPaid" && data != 0) {
                                clickToPaid = '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                    '/' + datePicker + '"  class="view-btn primary-bg btn-sm">' +
                                    '{{ __('Click To Paid') }}' + '</a>  ';
                            }

                            if (data != 0) {
                                view =
                                    '<a href="#" data-url="{{ url('payslip/showemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn gray-bg" data-title="{{ __('View Employee Detail') }}">' +
                                    '{{ __('View') }}' + '</a>';
                            }

                            if (data != 0 && status == "UnPaid") {
                                edit =
                                    '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" data-size="lg" class="view-btn blue-bg" data-title="{{ __('Edit Employee salary') }}">' +
                                    '{{ __('Edit') }}' + '</a>';
                            }

                            var url = '{{ route('payslip.delete', ':id') }}';
                            url = url.replace(':id', payslip_id);

                            @if (\Auth::user()->type == 'company')
                                if (valueOfElement[7] != 0) {
                                    var deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn btn btn-danger ms-1 btn-sm"  >' +
                                        '{{ __('Delete') }}' + '</a>';
                                }
                            @else
                                var deleted = '';
                            @endif

                            return view + payslip + clickToPaid + edit + deleted + form;
                        }
                        var tr = '';
                        if (data.length > 0) {
                            $.each(data, function(indexInArray, valueOfElement) {
                                var status =
                                    '<span class="rc-status rc-status-danger">' +
                                    valueOfElement[6] + '</span>';
                                if (valueOfElement[6] == 'Paid') {
                                    var status =
                                        '<span class="rc-status rc-status-success">' +
                                        valueOfElement[6] + '</span>';
                                }

                                var id = valueOfElement[0];
                                var employee_id = valueOfElement[1];
                                var payslip_id = valueOfElement[7];

                                if (valueOfElement[7] != 0) {
                                    var payslip =
                                        '<a href="#" data-url="{{ url('payslip/pdf/') }}/' +
                                        id +
                                        '/' + datePicker +
                                        '" data-size="lg"  data-ajax-popup="true" class="rc-table-action rc-table-action-warning" data-title="{{ __('Employee Payslip') }}">' +
                                        '{{ __('Payslip') }}' + '</a> ';
                                }
                                if (valueOfElement[6] == "UnPaid" && valueOfElement[7] != 0) {
                                    var clickToPaid =
                                        '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                        '/' + datePicker +
                                        '"  class="rc-table-action rc-table-action-primary">' +
                                        '{{ __('Click To Paid') }}' + '</a>  ';
                                } else {
                                    var clickToPaid = '';
                                }

                                if (valueOfElement[7] != 0 && valueOfElement[6] == "UnPaid") {
                                    var edit =
                                        '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                        payslip_id +
                                        '"  data-ajax-popup="true" data-size="lg" class="rc-table-action rc-table-action-info" data-title="{{ __('Edit Employee salary') }}">' +
                                        '{{ __('Edit') }}' + '</a>';
                                } else {
                                    var edit = '';
                                }


                                var url = '{{ route('payslip.delete', ':id') }}';
                                url = url.replace(':id', payslip_id);

                                @if (\Auth::user()->type != 'employee')
                                    if (valueOfElement[7] != 0) {
                                        var deleted = '<a href="#"  data-url="' + url +
                                            '" class="payslip_delete rc-table-action rc-table-action-danger"  >' +
                                            '{{ __('Delete') }}' + '</a>';
                                    }
                                @else
                                    var deleted = '';
                                @endif
                                var url_employee = valueOfElement['url'];

                                tr +=
                                    '<tr> ' +
                                    '<td class="col-id"> <a class="btn btn-outline-primary" href="' +
                                    url_employee + '">' +
                                    valueOfElement[1] + '</a></td> ' +
                                    '<td>' + valueOfElement[2] + '</td> ' +
                                    '<td>' + valueOfElement[3] + '</td>' +
                                    '<td class="col-amount">' + valueOfElement[4] + '</td>' +
                                    '<td class="col-amount">' + valueOfElement[5] + '</td>' +
                                    '<td class="col-status">' + status + '</td>' +
                                    '<td class="col-actions">' + payslip + clickToPaid + edit + deleted + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            var tr = '<tr><td colspan="7" class="text-center p-4">' +
                                '<div class="rc-table-empty">' +
                                '<i class="ti ti-file-invoice fs-1 text-muted"></i>' +
                                '<h5 class="mt-2">{{ __('No Payslips Found') }}</h5>' +
                                '<p class="text-muted">{{ __('No entries found for the selected period') }}</p>' +
                                '</div></td></tr>';
                        }

                        $('#pc-dt-render-column-cells tbody').html(tr);
                        var table = document.querySelector("#pc-dt-render-column-cells");
                        var datatable = new simpleDatatables.DataTable(table);
                    },
                    error: function(data) {

                    }

                });

            }

            $(document).on("change", ".month_date,.year_date", function() {
                callback();
            });

            $(document).on("click", ".payslip_delete", function() {
                var url = $(this).data('url');
                openRcConfirmDialog('Are you sure?', 'Are you sure you want to delete this payslip?').then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {
                            toastrs('Success', 'Payslip Deleted Successfully', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 800)
                        },

                    });
                });
            });
        });
    </script>
@endpush
