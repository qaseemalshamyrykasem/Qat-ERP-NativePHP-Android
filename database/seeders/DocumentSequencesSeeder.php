<?php

namespace Database\Seeders;

use App\Models\DocumentSequence;
use Illuminate\Database\Seeder;

class DocumentSequencesSeeder extends Seeder
{
    public function run(): void
    {
        $sequences = [
            ['doc_type' => 'invoice',          'prefix' => 'INV-', 'padding_length' => 6],
            ['doc_type' => 'purchase',         'prefix' => 'PUR-', 'padding_length' => 6],
            ['doc_type' => 'distribution',     'prefix' => 'DST-', 'padding_length' => 6],
            ['doc_type' => 'journal_entry',    'prefix' => 'JE-',  'padding_length' => 4],
            ['doc_type' => 'receipt_voucher',  'prefix' => 'RV-',  'padding_length' => 6],
            ['doc_type' => 'payment_voucher',  'prefix' => 'PV-',  'padding_length' => 6],
            ['doc_type' => 'transfer',         'prefix' => 'TRF-', 'padding_length' => 6],
            ['doc_type' => 'agent_settlement', 'prefix' => 'STL-', 'padding_length' => 6],
        ];

        foreach ($sequences as $seq) {
            DocumentSequence::firstOrCreate(['doc_type' => $seq['doc_type']], array_merge($seq, ['current_number' => 0]));
        }
    }
}
