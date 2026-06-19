<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SequenceService — atomic, lock-based document number generation.
 * Migrated from legacy SequenceService; uses row-level locking for concurrency safety.
 */
class SequenceService
{
    /**
     * Generate the next number for a doc type, with prefix & padding.
     * Example: next('invoice', 'INV', 6) -> "INV000123"
     */
    public function next(string $docType, string $prefix = '', int $padding = 4): string
    {
        return DB::transaction(function () use ($docType, $prefix, $padding) {
            $seq = DocumentSequence::where('doc_type', $docType)->lockForUpdate()->first();

            if (! $seq) {
                $seq = DocumentSequence::create([
                    'doc_type'       => $docType,
                    'prefix'         => $prefix,
                    'current_number' => 0,
                    'padding_length' => $padding,
                ]);
                // Lock again after insert (avoid race)
                $seq = DocumentSequence::where('doc_type', $docType)->lockForUpdate()->first();
            }

            $next = $seq->current_number + 1;
            $seq->update(['current_number' => $next]);

            $pad = max(1, $seq->padding_length ?: $padding);
            $numStr = str_pad((string) $next, $pad, '0', STR_PAD_LEFT);

            return $seq->prefix . $numStr;
        });
    }

    /**
     * Generate a dated reference number, e.g. JE-20260619-0001
     */
    public function nextDated(string $docType, string $prefix, string $dateStr = null): string
    {
        $dateStr = $dateStr ?? now()->format('Ymd');
        return DB::transaction(function () use ($docType, $prefix, $dateStr) {
            $seq = DocumentSequence::where('doc_type', $docType)->lockForUpdate()->first();
            if (! $seq) {
                $seq = DocumentSequence::create([
                    'doc_type'       => $docType,
                    'prefix'         => $prefix . '-' . $dateStr . '-',
                    'current_number' => 0,
                    'padding_length' => 4,
                ]);
            }
            $next = $seq->current_number + 1;
            $seq->update(['current_number' => $next]);
            return $prefix . '-' . $dateStr . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }
}
