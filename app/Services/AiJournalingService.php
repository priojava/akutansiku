<?php

namespace App\Services;

use App\Models\Account;
use Carbon\Carbon;

class AiJournalingService
{
    /**
     * Menerjemahkan kalimat bahasa alami ke dalam Debit & Kredit COA yang sesuai
     */
    public function parseNaturalLanguage(int $companyId, string $query): array
    {
        $queryLower = strtolower($query);
        $accounts = Account::where('company_id', $companyId)->where('is_active', true)->get();

        // 1. Ekstraksi Nominal
        $amount = $this->extractAmount($queryLower);

        // 2. Default fallback akun
        $kas = $accounts->firstWhere('code', '1-10001');
        $bca = $accounts->firstWhere('code', '1-10007');
        $mandiri = $accounts->firstWhere('code', '1-10003');
        $pendapatan = $accounts->firstWhere('code', '4-40000');
        $hpp = $accounts->firstWhere('code', '5-50000');

        $type = 'expense';
        $debitAccount = null;
        $creditAccount = null;
        $notes = $query;

        // Cek apakah transfer bank / kas
        if (str_contains($queryLower, 'transfer') || str_contains($queryLower, 'pindah kas') || str_contains($queryLower, 'tarik tunai') || str_contains($queryLower, 'setor')) {
            $type = 'transfer';
            if (str_contains($queryLower, 'tarik tunai') || str_contains($queryLower, 'dari bca ke kas')) {
                $debitAccount = $kas;
                $creditAccount = $bca ?: $kas;
            } elseif (str_contains($queryLower, 'setor tunai') || str_contains($queryLower, 'dari kas ke bca')) {
                $debitAccount = $bca ?: $kas;
                $creditAccount = $kas;
            } else {
                $debitAccount = $bca ?: $kas;
                $creditAccount = $kas;
            }
        }
        // Cek Pemasukan / Penjualan
        elseif (str_contains($queryLower, 'penjualan') || str_contains($queryLower, 'pemasukan') || str_contains($queryLower, 'terima') || str_contains($queryLower, 'omset') || str_contains($queryLower, 'laku') || str_contains($queryLower, 'dapat uang')) {
            $type = 'income';
            $creditAccount = $pendapatan;
            if (str_contains($queryLower, 'bca') || str_contains($queryLower, 'transfer')) {
                $debitAccount = $bca ?: $kas;
            } elseif (str_contains($queryLower, 'gopay')) {
                $debitAccount = $accounts->firstWhere('code', '1-10008') ?: $kas;
            } else {
                $debitAccount = $kas;
            }
        }
        // Pengeluaran / Beban
        else {
            $type = 'expense';
            // Sumber bayar
            if (str_contains($queryLower, 'bca') || str_contains($queryLower, 'transfer')) {
                $creditAccount = $bca ?: $kas;
            } elseif (str_contains($queryLower, 'mandiri')) {
                $creditAccount = $mandiri ?: $kas;
            } else {
                $creditAccount = $kas;
            }

            // Target beban
            if (str_contains($queryLower, 'listrik') || str_contains($queryLower, 'pln')) {
                $debitAccount = $accounts->firstWhere('code', '6-60300') ?: $hpp; // Beban Kantor / Beban Pokok
            } elseif (str_contains($queryLower, 'gaji') || str_contains($queryLower, 'upah')) {
                $debitAccount = $accounts->firstWhere('code', '6-60101') ?: $accounts->firstWhere('code', '6-60100');
            } elseif (str_contains($queryLower, 'bensin') || str_contains($queryLower, 'tol') || str_contains($queryLower, 'parkir')) {
                $debitAccount = $accounts->firstWhere('code', '6-60202') ?: $accounts->firstWhere('code', '6-60003');
            } elseif (str_contains($queryLower, 'makan') || str_contains($queryLower, 'konsumsi')) {
                $debitAccount = $accounts->firstWhere('code', '6-60205') ?: $accounts->firstWhere('code', '6-60103');
            } elseif (str_contains($queryLower, 'iklan') || str_contains($queryLower, 'promosi') || str_contains($queryLower, 'ads')) {
                $debitAccount = $accounts->firstWhere('code', '6-60001');
            } elseif (str_contains($queryLower, 'sewa')) {
                $debitAccount = $accounts->firstWhere('code', '6-60400');
            } elseif (str_contains($queryLower, 'atk') || str_contains($queryLower, 'kertas') || str_contains($queryLower, 'print')) {
                $debitAccount = $accounts->firstWhere('code', '6-60301');
            } elseif (str_contains($queryLower, 'bahan') || str_contains($queryLower, 'kulakan') || str_contains($queryLower, 'stok') || str_contains($queryLower, 'belanja barang')) {
                $debitAccount = $hpp;
            } else {
                $debitAccount = $hpp;
            }
        }

        return [
            'success' => true,
            'confidence' => 'high',
            'parsed_data' => [
                'type' => $type,
                'debit_account_id' => $debitAccount?->id,
                'debit_account_name' => $debitAccount ? "{$debitAccount->name} ({$debitAccount->code})" : null,
                'credit_account_id' => $creditAccount?->id,
                'credit_account_name' => $creditAccount ? "{$creditAccount->name} ({$creditAccount->code})" : null,
                'amount' => $amount,
                'notes' => strtoupper(trim(preg_replace('/\b(rp|rb|ribu|jt|juta|\d+[\.,]?\d*)\b/i', '', $query))),
                'date' => Carbon::now()->format('Y-m-d'),
                'time' => Carbon::now()->format('H:i'),
            ],
            'explanation' => "AI merekomendasikan Debit: [{$debitAccount?->name}] dan Kredit: [{$creditAccount?->name}] berdasarkan kata kunci transaksi."
        ];
    }

    private function extractAmount(string $text): float
    {
        // 1. Cek format seperti 1.5jt / 2jt / 100jt
        if (preg_match('/(\d+(?:[\.,]\d+)?)\s*(?:jt|juta)/i', $text, $matches)) {
            $num = floatval(str_replace(',', '.', $matches[1]));
            return $num * 1000000;
        }

        // 2. Cek format 50rb / 250 ribu / 500k
        if (preg_match('/(\d+(?:[\.,]\d+)?)\s*(?:rb|ribu|k)/i', $text, $matches)) {
            $num = floatval(str_replace(',', '.', $matches[1]));
            return $num * 1000;
        }

        // 3. Cek format nominal angka langsung Rp 1.000.000 atau 1000000
        if (preg_match('/(?:rp\.?\s*)?(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d+)?|\d+)/i', $text, $matches)) {
            $cleaned = preg_replace('/[^0-9]/', '', $matches[1]);
            return floatval($cleaned);
        }

        return 0;
    }
}
