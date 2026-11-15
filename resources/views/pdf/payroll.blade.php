<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Payroll Statement</title>

    <style>
        @page {
            margin: 35px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #263445;
            font-size: 11px;
            line-height: 1.6;
        }

        .top-line {
            height: 5px;
            background: #2563eb;
            margin-bottom: 28px;
        }

        .header {
            width: 100%;
            margin-bottom: 30px;
        }

        .header td {
            vertical-align: top;
        }

        .brand {
            font-size: 23px;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 1px;
        }

        .brand span {
            color: #2563eb;
        }

        .subtitle {
            color: #8492a6;
            font-size: 10px;
        }

        .document-title {
            text-align: right;
        }

        .document-title h1 {
            font-size: 21px;
            color: #1e3a5f;
            margin: 0;
        }

        .document-title p {
            color: #8492a6;
            margin: 3px 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .employee-card {
            background: #f4f7fb;
            border: 1px solid #e5ebf3;
            padding: 18px;
            margin-bottom: 28px;
        }

        .employee-table {
            width: 100%;
        }

        .employee-table td {
            width: 50%;
            padding: 5px 0;
        }

        .label {
            color: #8794a5;
            font-size: 9px;
            text-transform: uppercase;
        }

        .value {
            color: #263445;
            font-weight: bold;
            font-size: 11px;
        }

        .payroll-info {
            width: 100%;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e8edf4;
            padding: 12px;
        }

        .info-box .value {
            margin-top: 4px;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .salary-table th {
            background: #1e3a5f;
            color: #ffffff;
            padding: 12px;
            text-align: left;
            font-size: 10px;
        }

        .salary-table td {
            padding: 12px;
            border-bottom: 1px solid #e9eef5;
        }

        .salary-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .amount {
            text-align: right;
            font-weight: bold;
        }

        .total-card {
            background: #eff6ff;
            border: 1px solid #d6e7ff;
            padding: 20px;
            margin-top: 25px;
            text-align: right;
        }

        .total-label {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }

        .total-amount {
            color: #1d4ed8;
            font-size: 25px;
            font-weight: bold;
            margin-top: 5px;
        }

        .payment-status {
            margin-top: 15px;
            color: #15803d;
            font-weight: bold;
            font-size: 10px;
        }

        .footer {
            margin-top: 45px;
            padding-top: 15px;
            border-top: 1px solid #e5ebf3;
            text-align: center;
            color: #94a3b8;
            font-size: 9px;
        }

        .footer strong {
            color: #64748b;
        }
    </style>
</head>

<body>

    <div class="top-line"></div>

    <!-- Header -->
    <table class="header">
        <tr>
            <td>
                <div class="brand">USTA<span>.</span>HR</div>
                <div class="subtitle">Human Resources Management</div>
            </td>

            <td class="document-title">
                <h1>Payroll Statement</h1>
                <p>Employee Compensation Report</p>
                <p>
                    Generated: {{ now()->format('d M Y') }}
                </p>
            </td>
        </tr>
    </table>

    <!-- Employee Information -->
    <div class="section-title">Employee Information</div>

    <div class="employee-card">

        <table class="employee-table">
            <tr>
                <td>
                    <div class="label">Employee Name</div>
                    <div class="value">
                        {{ $employee->first_name ?? '' }}
                        {{ $employee->last_name ?? '' }}
                    </div>
                </td>

                <td>
                    <div class="label">Employee ID</div>
                    <div class="value">{{ $employee->id }}</div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="label">Job Title</div>
                    <div class="value">
                        {{ $employee->jobTitle->name ?? 'N/A' }}
                    </div>
                </td>

                <td>
                    <div class="label">Level</div>
                    <div class="value">
                        {{ $employee->level->name ?? 'N/A' }}
                    </div>
                </td>
            </tr>
        </table>

    </div>

    <!-- Payroll Period -->
    <div class="section-title">Payroll Period</div>

    <table class="payroll-info">
        <tr>
            <td width="50%">
                <div class="info-box">
                    <div class="label">Period Start</div>
                    <div class="value">
                        {{ $record->period_start->format('d M Y') }}
                    </div>
                </div>
            </td>

            <td width="50%">
                <div class="info-box">
                    <div class="label">Period End</div>
                    <div class="value">
                        {{ $record->period_end->format('d M Y') }}
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="info-box">
                    <div class="label">Working Hours</div>
                    <div class="value">
                        {{ number_format($record->pay_period_hours_minutes / 60, 2) }} Hours
                    </div>
                </div>
            </td>

            <td>
                <div class="info-box">
                    <div class="label">Overtime Hours</div>
                    <div class="value">
                        {{ number_format($record->overtime_hours_minutes / 60, 2) }} Hours
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Salary Breakdown -->
    <div class="section-title">Salary Breakdown</div>

    <table class="salary-table">

        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="amount">
                    {{ number_format((float) $record->basic_salary, 2) }}
                </td>
            </tr>

            <tr>
                <td>Overtime Pay</td>
                <td class="amount">
                    {{ number_format((float) $record->overtime_pay, 2) }}
                </td>
            </tr>

            <tr>
                <td>Bonus</td>
                <td class="amount">
                    {{ number_format((float) $record->bonus, 2) }}
                </td>
            </tr>

            <tr>
                <td>Reimbursement</td>
                <td class="amount">
                    {{ number_format((float) $record->reimbursement, 2) }}
                </td>
            </tr>

            <tr>
                <td>Tax</td>
                <td class="amount">
                    - {{ number_format((float) $record->tax, 2) }}
                </td>
            </tr>
        </tbody>

    </table>

    <!-- Total -->
    <div class="total-card">

        <div class="total-label">Net Total Salary</div>

        <div class="total-amount">
            {{ number_format((float) $record->total_salary, 2) }}
            {{ $record->currency }}
        </div>

        @if($record->paid_on)
            <div class="payment-status">
                PAID ON {{ $record->paid_on->format('d M Y') }}
            </div>
        @endif

    </div>

    <!-- Footer -->
    <div class="footer">
        <strong>USTA HR</strong> &nbsp;|&nbsp;
        Employee Payroll Statement
        <br>
        This document contains confidential employee compensation information.
    </div>

</body>
</html>