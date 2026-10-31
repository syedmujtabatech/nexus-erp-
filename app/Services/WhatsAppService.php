<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Clean and format phone number for international WhatsApp standards.
     * e.g. 03183284737 -> 923183284737
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, hyphens, plus signs, brackets
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // If local Pakistani 03xx format (11 digits starting with 0), convert to 923xx
        if (str_starts_with($cleaned, '03') && strlen($cleaned) === 11) {
            $cleaned = '92' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Generate structured, professional WhatsApp message for an Invoice.
     */
    public static function buildInvoiceMessage(Invoice $invoice): string
    {
        $customerName = $invoice->customer->name ?? 'Valued Customer';
        $company = $invoice->customer->company ?? auth()->user()->currentCompany;
        $companyName = $company->name ?? 'Nexus ERP';
        $currency = $company->currency ?? 'PKR';
        $due = max(0, $invoice->total - $invoice->paid_amount);
        $dueDate = $invoice->due_date ? date('M d, Y', strtotime($invoice->due_date)) : 'Upon Receipt';
        $viewUrl = url("/invoices/{$invoice->id}");

        $msg = "👋 *Hello {$customerName}*,\n\n";
        $msg .= "Thank you for your business with *{$companyName}*!\n\n";
        $msg .= "📄 *INVOICE BILLING STATEMENT*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🧾 *Invoice #:* {$invoice->invoice_number}\n";
        $msg .= "📅 *Billing Date:* " . date('M d, Y', strtotime($invoice->date)) . "\n";
        $msg .= "⏰ *Payment Due:* {$dueDate}\n\n";
        $msg .= "💵 *Total Amount:* " . number_format($invoice->total, 2) . " {$currency}\n";
        $msg .= "✅ *Paid to Date:* " . number_format($invoice->paid_amount, 2) . " {$currency}\n";
        $msg .= "⚠️ *Outstanding Due:* *" . number_format($due, 2) . " {$currency}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "🔗 *View / Print Digital Invoice:* \n{$viewUrl}\n\n";
        $msg .= "Please let us know once payment has been arranged. Thank you for choosing *{$companyName}*! ✨";

        return $msg;
    }

    /**
     * Generate Click-to-Chat WhatsApp link (wa.me).
     */
    public static function getInvoiceWhatsAppUrl(Invoice $invoice): string
    {
        $phone = $invoice->customer->phone ?? '';
        $cleanPhone = self::formatPhoneNumber($phone);
        $message = self::buildInvoiceMessage($invoice);

        return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    }

    /**
     * Automated API Dispatch (if WhatsApp Gateway API token configured in .env).
     */
    public static function sendAutomated(string $phone, string $message): array
    {
        $apiUrl = config('services.whatsapp.api_url');
        $apiToken = config('services.whatsapp.token');

        if (!$apiUrl || !$apiToken) {
            return [
                'success' => false,
                'mode' => 'click_to_chat',
                'message' => 'WhatsApp Cloud API credentials not set. Click-to-Chat active.',
            ];
        }

        try {
            $cleanPhone = self::formatPhoneNumber($phone);
            $response = Http::timeout(10)->post($apiUrl, [
                'token' => $apiToken,
                'to' => $cleanPhone,
                'body' => $message,
            ]);

            if ($response->successful()) {
                ActivityLogger::log('whatsapp_send', "Automated WhatsApp notification delivered to {$cleanPhone}");
                return ['success' => true, 'response' => $response->json()];
            }

            return ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error("WhatsApp API Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
