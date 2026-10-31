<?php

namespace App\Services;

use App\Models\RecycledSequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Get the next sequence number for a given model and company.
     * Uses recycled sequence if available, otherwise MAX + 1.
     */
    public static function getNextNumber(int $companyId, string $type, string $prefix = 'INV-'): array
    {
        // 1. Check if there's a recycled/deleted sequence number available
        $recycled = RecycledSequence::where('company_id', $companyId)
            ->where('type', $type)
            ->orderBy('sequence_number', 'asc')
            ->first();

        if ($recycled) {
            $seqNumber = $recycled->sequence_number;
            // Delete from recycled table since it's now being reused
            $recycled->delete();
        } else {
            // 2. Otherwise calculate MAX + 1
            $tableName = match($type) {
                'invoice' => 'invoices',
                'quotation' => 'quotations',
                'purchase' => 'purchases',
                default => 'invoices',
            };

            $maxSeq = DB::table($tableName)
                ->where('company_id', $companyId)
                ->max('sequence_number');

            $seqNumber = ($maxSeq ?? 0) + 1;
        }

        $formattedCode = $prefix . str_pad($seqNumber, 3, '0', STR_PAD_LEFT);

        return [
            'formatted' => $formattedCode,
            'sequence_number' => $seqNumber,
        ];
    }

    /**
     * Recycle a sequence number when a record is deleted.
     */
    public static function recycleNumber(int $companyId, string $type, int $seqNumber): void
    {
        RecycledSequence::firstOrCreate([
            'company_id' => $companyId,
            'type' => $type,
            'sequence_number' => $seqNumber,
        ]);
    }
}
