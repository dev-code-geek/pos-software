<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.payslip') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .payslip-title { font-size: 16px; margin-top: 5px; }
        .details { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .details-left, .details-right { width: 45%; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { display: inline-block; padding: 2px 8px; background: #28a745; color: white; border-radius: 4px; font-size: 10px; }
        [dir="rtl"] th, [dir="rtl"] td { text-align: right; }
        [dir="rtl"] .text-right { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? __('messages.company_name') }}</div>
        <div class="payslip-title">{{ __('messages.payslip_title') }}</div>
        <div>{{ __('messages.payslip_number') }}: {{ $payslipNumber }}</div>
        <div>{{ __('messages.date') }}: {{ $generated_date }}</div>
    </div>

    <div class="details">
        <div class="details-left">
            <strong>{{ __('messages.employee') }}:</strong> {{ $employee->first_name }} {{ $employee->last_name }}<br>
            <strong>{{ __('messages.employee_code') }}:</strong> {{ $employee->employee_code }}<br>
            <strong>{{ __('messages.department') }}:</strong> {{ $employee->department->name ?? 'N/A' }}
        </div>
        <div class="details-right">
            <strong>{{ __('messages.period') }}:</strong> {{ $period->period_name }}<br>
            <strong>{{ __('messages.pay_date') }}:</strong> {{ $period->pay_date->format('d/m/Y') }}<br>
            <strong>{{ __('messages.status') }}:</strong> <span class="badge">{{ __('messages.calc_status_' . strtolower($calculation->payment_status)) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>{{ __('messages.description') }}</th><th class="text-right">{{ __('messages.amount') }}</th></tr>
        </thead>
        <tbody>
            <tr><td>{{ __('messages.basic_salary') }}</td><td class="text-right">{{ number_format($calculation->basic_salary, 2) }}</td></tr>
            @if($transportAllowance > 0)<tr><td>{{ __('messages.transport_allowance') }}</td><td class="text-right">{{ number_format($transportAllowance, 2) }}</td></tr>@endif
            @if($housingAllowance > 0)<tr><td>{{ __('messages.housing_allowance') }}</td><td class="text-right">{{ number_format($housingAllowance, 2) }}</td></tr>@endif
            @if($foodAllowance > 0)<tr><td>{{ __('messages.food_allowance') }}</td><td class="text-right">{{ number_format($foodAllowance, 2) }}</td></tr>@endif
            @if($medicalAllowance > 0)<tr><td>{{ __('messages.medical_allowance') }}</td><td class="text-right">{{ number_format($medicalAllowance, 2) }}</td></tr>@endif
            @if($allowanceAdjustments > 0)<tr><td>{{ __('messages.other_allowances') }}</td><td class="text-right">{{ number_format($allowanceAdjustments, 2) }}</td></tr>@endif
            @if($overtimeAmount > 0)<tr><td>{{ __('messages.overtime') }}</td><td class="text-right">{{ number_format($overtimeAmount, 2) }}</td></tr>@endif
            <tr class="total-row"><td>{{ __('messages.total_earnings') }}</td><td class="text-right">{{ number_format($calculation->total_earnings, 2) }}</td></tr>
            <tr><td>{{ __('messages.paye_tax') }}</td><td class="text-right">{{ number_format($calculation->paye_tax, 2) }}</td></tr>
            <tr><td>{{ __('messages.nssf_employee') }}</td><td class="text-right">{{ number_format($calculation->nssf_employee, 2) }}</td></tr>
            @if($loanDeductions > 0)<tr><td>{{ __('messages.loan_deductions') }}</td><td class="text-right">{{ number_format($loanDeductions, 2) }}</td></tr>@endif
            @if($deductionAdjustments > 0)<tr><td>{{ __('messages.other_deductions') }}</td><td class="text-right">{{ number_format($deductionAdjustments, 2) }}</td></tr>@endif
            <tr class="total-row"><td>{{ __('messages.total_deductions') }}</td><td class="text-right">{{ number_format($calculation->total_deductions, 2) }}</td></tr>
            <tr class="total-row" style="font-size: 14px;"><td><strong>{{ __('messages.net_salary') }}</strong></td><td class="text-right"><strong>{{ number_format($calculation->net_salary, 2) }}</strong></td></tr>
        </tbody>
    </table>

    <div class="footer">
        {{ __('messages.payslip_footer_note') }}
    </div>
</body>
</html>