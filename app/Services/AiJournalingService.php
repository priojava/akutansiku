<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiJournalingService
{
    /**
     * Menerjemahkan kalimat bahasa alami ke dalam Debit & Kredit COA yang sesuai.
     * Menggunakan Google Gemini 1.5 Flash jika API Key tersedia, atau fallback cerdas lokal.
     */
    public function parseNaturalLanguage(int $companyId, string $query): array
    {
        $accounts = Account::where('company_id', $companyId)->where('is_active', true)->get();
        $setting = CompanySetting::where('company_id', $companyId)->first();
        $apiKey = trim($setting?->gemini_api_key ?? '') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY');

        // 1. Jika API Key Gemini tersedia, coba panggil Google AI Studio
        if (!empty($apiKey)) {
            try {
                $geminiResult = $this->callGeminiApi($companyId, $query, $apiKey, $accounts);
                if ($geminiResult !== null) {
                    return $geminiResult;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini AI API call failed, falling back to local heuristic: ' . $e->getMessage());
            }
        }

        // 2. Fallback ke sistem cerdas lokal (Heuristik & Regex)
        return $this->parseNaturalLanguageLocal($companyId, $query, $accounts);
    }

    /**
     * Memanggil Google Gemini 1.5 Flash API via REST
     */
    protected function callGeminiApi(int $companyId, string $query, string $apiKey, $accounts): ?array
    {
        $coaList = $accounts->map(fn($a) => [
            'id' => $a->id,
            'code' => $a->code,
            'name' => $a->name,
            'category' => $a->category,
            'type' => $a->type,
        ])->values()->toArray();

        $coaJson = json_encode($coaList, JSON_UNESCAPED_UNICODE);

        $prompt = <<<EOT
Anda adalah asisten akuntan cerdas Indonesia untuk sistem pembukuan UMKM & Enterprise.
Tugas Anda adalah menerjemahkan kalimat transaksi bisnis ke dalam akun Debit dan Kredit berpasangan (Double Entry) berdasarkan Bagan Akun (Chart of Accounts/COA) yang disediakan.

Kalimat transaksi: "{$query}"

Daftar Akun COA yang tersedia (pilih HANYA ID akun yang ada di daftar ini):
{$coaJson}

Aturan Penjurnalan:
1. Tentukan jenis transaksi: "expense" (pengeluaran), "income" (pemasukan), atau "transfer" (pindah kas/bank).
2. Tentukan ID akun Debit (debit_account_id) dan ID akun Kredit (credit_account_id) dari daftar COA di atas.
   - Contoh pengeluaran via kas tunai: Debit akun Beban, Kredit Kas (1-10001).
   - Contoh pengeluaran via transfer BCA: Debit akun Beban, Kredit Bank BCA (1-10007).
   - Contoh penjualan tunai: Debit Kas (1-10001), Kredit Pendapatan (4-40000).
   - Contoh tarik tunai dari ATM BCA: Debit Kas (1-10001), Kredit Bank BCA (1-10007).
3. Ekstrak nominal uang (amount) sebagai angka murni integer/float tanpa titik/koma pemisah ribuan (contoh: 250000 untuk 250rb atau 1500000 untuk 1.5jt). Jika tidak terdeteksi, isi 0.
4. Buat keterangan ringkas (notes) huruf kapital rapi (contoh: "PEMBELIAN TOKEN LISTRIK KANTOR").
5. Berikan penjelasan singkat dan jelas (explanation) alasan pemilihan akun Debit dan Kredit dalam bahasa Indonesia.

Keluarkan respon HANYA berupa JSON murni dengan format spesifik berikut:
{
  "type": "expense",
  "debit_account_id": 12,
  "credit_account_id": 1,
  "amount": 250000,
  "notes": "PEMBELIAN TOKEN LISTRIK KANTOR",
  "explanation": "Debit Beban Listrik & Kredit Kas Tunai karena pembayaran operasional menggunakan uang kas."
}
EOT;

        $modelsToTry = array_unique([
            config('services.gemini.model', 'gemini-flash-latest'),
            'gemini-flash-latest',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
        ]);

        $response = null;
        foreach ($modelsToTry as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            $resp = Http::timeout(10)->withHeaders([
                'X-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($resp->successful()) {
                $response = $resp;
                break;
            }
        }

        if (!$response || !$response->successful()) {
            Log::warning('Gemini API returned error: ' . ($response ? $response->body() : 'no response'));
            return null;
        }

        $body = $response->json();
        $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$rawText) {
            return null;
        }

        // Bersihkan markdown codeblock jika ada
        $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $rawText));
        $parsed = json_decode($cleanJson, true);
        if (!$parsed || !isset($parsed['debit_account_id'], $parsed['credit_account_id'])) {
            return null;
        }

        $debitAcc = $accounts->firstWhere('id', (int)$parsed['debit_account_id']);
        $creditAcc = $accounts->firstWhere('id', (int)$parsed['credit_account_id']);

        if (!$debitAcc || !$creditAcc) {
            return null;
        }

        $amount = isset($parsed['amount']) && is_numeric($parsed['amount']) ? (float)$parsed['amount'] : $this->extractAmount($query);

        return [
            'success' => true,
            'engine' => 'google_gemini',
            'confidence' => 'high',
            'parsed_data' => [
                'type' => in_array($parsed['type'] ?? '', ['expense', 'income', 'transfer']) ? $parsed['type'] : 'expense',
                'debit_account_id' => $debitAcc->id,
                'debit_account_name' => "{$debitAcc->name} ({$debitAcc->code})",
                'credit_account_id' => $creditAcc->id,
                'credit_account_name' => "{$creditAcc->name} ({$creditAcc->code})",
                'amount' => $amount,
                'notes' => $parsed['notes'] ?? strtoupper(trim($query)),
                'date' => Carbon::now()->format('Y-m-d'),
                'time' => Carbon::now()->format('H:i'),
            ],
            'explanation' => "✨ Google Gemini AI: " . ($parsed['explanation'] ?? "Rekomendasi Debit [{$debitAcc->name}] dan Kredit [{$creditAcc->name}].")
        ];
    }

    /**
     * Algoritma Heuristik & Regex Cerdas Lokal (Offline & Cepat)
     */
    public function parseNaturalLanguageLocal(int $companyId, string $query, $accounts = null): array
    {
        $queryLower = strtolower($query);
        if (!$accounts) {
            $accounts = Account::where('company_id', $companyId)->where('is_active', true)->get();
        }

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
                $debitAccount = $accounts->firstWhere('code', '6-60201') ?: $accounts->firstWhere('code', '6-60300') ?: $hpp;
            } elseif (str_contains($queryLower, 'gaji') || str_contains($queryLower, 'upah')) {
                $debitAccount = $accounts->firstWhere('code', '6-60000') ?: $accounts->firstWhere('code', '6-60100');
            } elseif (str_contains($queryLower, 'bensin') || str_contains($queryLower, 'tol') || str_contains($queryLower, 'parkir')) {
                $debitAccount = $accounts->firstWhere('code', '6-60600') ?: $accounts->firstWhere('code', '6-60202');
            } elseif (str_contains($queryLower, 'makan') || str_contains($queryLower, 'konsumsi')) {
                $debitAccount = $accounts->firstWhere('code', '6-60800') ?: $accounts->firstWhere('code', '6-60205');
            } elseif (str_contains($queryLower, 'iklan') || str_contains($queryLower, 'promosi') || str_contains($queryLower, 'ads')) {
                $debitAccount = $accounts->firstWhere('code', '6-60300') ?: $accounts->firstWhere('code', '6-60001');
            } elseif (str_contains($queryLower, 'sewa')) {
                $debitAccount = $accounts->firstWhere('code', '6-60100') ?: $accounts->firstWhere('code', '6-60400');
            } elseif (str_contains($queryLower, 'atk') || str_contains($queryLower, 'kertas') || str_contains($queryLower, 'print')) {
                $debitAccount = $accounts->firstWhere('code', '6-60400') ?: $accounts->firstWhere('code', '6-60301');
            } elseif (str_contains($queryLower, 'bahan') || str_contains($queryLower, 'kulakan') || str_contains($queryLower, 'stok') || str_contains($queryLower, 'belanja barang')) {
                $debitAccount = $hpp;
            } else {
                $debitAccount = $hpp;
            }
        }

        return [
            'success' => true,
            'engine' => 'local_heuristic',
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
            'explanation' => "⚡ AI Lokal: Merekomendasikan Debit [{$debitAccount?->name}] dan Kredit [{$creditAccount?->name}] berdasarkan kata kunci transaksi."
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
