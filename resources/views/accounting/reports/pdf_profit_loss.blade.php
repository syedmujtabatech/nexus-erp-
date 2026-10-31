<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 12px; }
        .table th { background-color: #f4f4f4; text-align: left; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .bg-light { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->name ?? 'Company Name' }}</h2>
        <h3>PROFIT & LOSS FINANCIAL STATEMENT</h3>
        <p>Generated Date: {{ date('Y-m-d H:i') }} | Currency: {{ $currency }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Financial Account Category</th>
                <th class="text-end">Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gross Sales Revenue (Invoice Payments Received)</td>
                <td class="text-end fw-bold" style="color: green;">{{ number_format($totalSalesIncome, 2) }}</td>
            </tr>
            <tr>
                <td>Cost of Goods Sold (COGS)</td>
                <td class="text-end fw-bold" style="color: red;">-{{ number_format($cogs, 2) }}</td>
            </tr>
            <tr class="bg-light fw-bold">
                <td>Gross Profit</td>
                <td class="text-end">{{ number_format($grossProfit, 2) }}</td>
            </tr>
            <tr>
                <td>Operating Expenses</td>
                <td class="text-end fw-bold" style="color: red;">-{{ number_format($totalExpenses, 2) }}</td>
            </tr>
            <tr class="bg-light fw-bold" style="font-size: 16px;">
                <td>Net Profit / (Loss)</td>
                <td class="text-end" style="color: {{ $netProfit >= 0 ? 'green' : 'red' }};">
                    {{ number_format($netProfit, 2) }} {{ $currency }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
