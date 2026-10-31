<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; }
        .invoice-box { padding: 30px; border: 1px solid #eee; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; color: #0056b3; }
        .invoice-title { font-size: 28px; text-align: right; color: #555; }
        .details-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .details-table th { background: #f8f9fa; text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        .details-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .total-box { margin-top: 20px; float: right; width: 300px; }
        .total-box table { width: 100%; }
        .total-box td { padding: 5px 0; }
        .grand-total { font-size: 18px; font-weight: bold; color: #0056b3; border-top: 2px solid #0056b3; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table width="100%" class="header">
            <tr>
                <td>
                    <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
                    <div>{{ $company->address ?? 'Company Address' }}</div>
                    <div>Email: {{ $company->email ?? '' }}</div>
                </td>
                <td align="right">
                    <div class="invoice-title">INVOICE</div>
                    <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                    <div><strong>Date:</strong> {{ $invoice->date }}</div>
                    <div><strong>Due Date:</strong> {{ $invoice->due_date }}</div>
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 20px;">
            <strong>Billed To:</strong><br>
            {{ $invoice->customer->name ?? 'N/A' }}<br>
            {{ $invoice->customer->company_name ?? '' }}<br>
            {{ $invoice->customer->email ?? '' }}
        </div>

        <table class="details-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price ({{ $company->currency ?? 'USD' }})</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td align="right">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount:</td>
                    <td align="right">-{{ number_format($invoice->discount, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax:</td>
                    <td align="right">+{{ number_format($invoice->tax, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Due:</td>
                    <td align="right">{{ number_format($invoice->total, 2) }} {{ $company->currency ?? 'USD' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
