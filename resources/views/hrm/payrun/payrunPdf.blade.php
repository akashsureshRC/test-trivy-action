<!DOCTYPE html>
<html>
<head>
    <title>Payrun PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .company-name { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        .tax-info { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 20px; }
        .payment-info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
        .total { font-weight: bold; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>

    @foreach($payruns->groupBy('payment_method') as $paymentMethod => $groupedPayruns)
        <!--@if($logoUrl)-->
        <!--    <img src="{{ $logoUrl }}" style="max-width: 200px;">-->
        <!--@endif-->
        <!-- <img src="{{ getFile($logo) . '?' . time() }}" alt="{{ $settings['company_name'] ? $settings['company_name'] : ''}}" style="width: 100px; height: auto;"> -->
        <div class="company-name">{{ $settings['company_name'] ? $settings['company_name'] : ''}}</div>
        <div class="tax-info">
            @if(isset($settings['sdl_number']))
                SDL Number: {{ $settings['sdl_number'] ? $settings['sdl_number'] : ''}}<br>
            @endif
            @if (isset($settings['tax_number']))
                Vat (PAYE) Number: {{ $settings['tax_number'] ? $settings['tax_number'] : '' }}<br>
            @endif
            @if (isset($settings['uif_number']))
                Uif Number: {{ $settings['uif_number'] ? $settings['uif_number'] : '' }}
            @endif
        </div>
        <div class="payment-info">
            <strong>Payment Information</strong><br>
            Period ending: {{ $term }}<br>
            Pay frequency: Monthly, ending on the 31st<br>
            Payment Method: {{ $paymentMethod }}<br>
            Pay Point: Unassigned
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Number</th>
                    <th>Nett Pay</th>
                    @if($paymentMethod == 'EFT')
                        <th>Bank</th>
                        <th>Account Type</th>
                        <th>Account Number</th>
                        <th>Branch Code</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($groupedPayruns as $payrun)
                    <tr>
                        <td>{{ $payrun->employee_details->first_name }} {{ $payrun->employee_details->last_name }}</td>
                        <td>{{ $payrun->employee_details->employee_id }}</td>
                        <td>R {{ number_format($payrun->nett_pay, 2) }}</td>
                        @if($paymentMethod == 'EFT')
                            <td>{{ $payrun->employee_details->bank }}</td>
                            <td>{{ $payrun->employee_details->account_type }}</td>
                            <td>{{ $payrun->employee_details->account_number }}</td>
                            <td>{{ $payrun->employee_details->branch_code }}</td>
                        @endif
                    </tr>
                    @php $total += $payrun->nett_pay; @endphp
                @endforeach
            </tbody>
        </table>

        <div class="total">Total: R {{ number_format($total, 2) }}</div>
        <hr>
    @endforeach

</body>
</html>
