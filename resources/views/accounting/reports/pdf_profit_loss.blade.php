<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit &amp; Loss Statement</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; padding: 25px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #2563eb; padding-bottom: 12px; }
        .header h2 { margin: 0 0 5px 0; color: #0f172a; font-size: 20px; }
        .header h3 { margin: 0 0 5px 0; color: #2563eb; font-size: 15px; letter-spacing: 0.5px; }
        .header p { margin: 0; color: #64748b; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 10px 14px; }
        .table th { background-color: #f1f5f9; text-align: left; font-size: 12px; text-transform: uppercase; color: #475569; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .bg-subtotal { background-color: #f8fafc; }
        .bg-total { background-color: #eff6ff; border-top: 2px solid #2563eb; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->name ?? 'Enterprise Workspace' }}</h2>
        <h3>PROFIT &amp; LOSS FINANCIAL STATEMENT (INCOME STATEMENT)</h3>
        <p>Report Sequence: Total Revenue → COGS → Gross Profit → OPEX → Operating Profit → Net Profit</p>
        <p>Generated: {{ date('F d, Y h:i A') }} | Currency: {{ $currency }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 70%;">Accounting Category / GAAP Sequence</th>
                <th class="text-end" style="width: 30%;">Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1. Total Revenue (Sales Income &amp; Realized Invoiced Receipts)</td>
                <td class="text-end fw-bold" style="color: #16a34a;">+{{ number_format($totalRevenue, 2) }}</td>
            </tr>
            <tr>
                <td>2. Less: Cost of Goods Sold (COGS - Product Purchase &amp; Materials)</td>
                <td class="text-end fw-bold" style="color: #dc2626;">-{{ number_format($cogs, 2) }}</td>
            </tr>
            <tr class="bg-subtotal fw-bold">
                <td>3. Gross Profit (Total Revenue − COGS)</td>
                <td class="text-end" style="color: {{ $grossProfit >= 0 ? '#2563eb' : '#dc2626' }};">
                    {{ number_format($grossProfit, 2) }}
                </td>
            </tr>
            <tr>
                <td>4. Less: Operating Expenses (OPEX - Salaries, Rent, Utilities, Admin Overhead)</td>
                <td class="text-end fw-bold" style="color: #dc2626;">-{{ number_format($operatingExpenses, 2) }}</td>
            </tr>
            <tr class="bg-subtotal fw-bold">
                <td>5. Operating Profit / EBIT (Gross Profit − Operating Expenses)</td>
                <td class="text-end" style="color: {{ $operatingProfit >= 0 ? '#0284c7' : '#dc2626' }};">
                    {{ number_format($operatingProfit, 2) }}
                </td>
            </tr>
            <tr class="bg-total fw-bold" style="font-size: 15px;">
                <td>6. Net Profit / (Loss) — Final Bottom-Line</td>
                <td class="text-end" style="color: {{ $netProfit >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ number_format($netProfit, 2) }} {{ $currency }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Nexus ERP Financial Accounting Engine • GAAP &amp; IFRS Compliant Financial Reporting
    </div>
</body>
</html>
