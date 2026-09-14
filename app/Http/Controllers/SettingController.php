<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function main()
    {
        $company = $this->getActiveCompany();
        $settings = $company->settings ?? CompanySetting::firstOrCreate(['company_id' => $company->id]);

        return view('settings.main', compact('company', 'settings'));
    }

    public function updateMain(Request $request)
    {
        $company = $this->getActiveCompany();
        $settings = $company->settings ?? CompanySetting::firstOrCreate(['company_id' => $company->id]);

        $settings->update([
            'preview_transaksi' => $request->has('preview_transaksi'),
            'number_format' => $request->input('number_format', '1,000,000.00'),
            'decimal_places' => intval($request->input('decimal_places', 0)),
            'cache_reports' => $request->has('cache_reports'),
            'cache_ar_ap' => $request->has('cache_ar_ap'),
            'gemini_api_key' => $request->filled('gemini_api_key') ? trim($request->input('gemini_api_key')) : null,
        ]);

        return back()->with('success', 'Pengaturan Utama dan integrasi Google AI berhasil disimpan.');
    }

    /**
     * Uji koneksi langsung ke Google Gemini API
     */
    public function testGeminiAi(Request $request)
    {
        $company = $this->getActiveCompany();
        $apiKey = trim($request->input('gemini_api_key', ''));

        if (empty($apiKey)) {
            $settings = $company->settings;
            $apiKey = trim($settings?->gemini_api_key ?? '') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API Key Gemini belum diisi. Silakan masukkan API Key Google AI Studio Anda terlebih dahulu.'
            ], 422);
        }

        $modelsToTry = [
            'gemini-flash-latest',
            config('services.gemini.model', 'gemini-flash-latest'),
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
            'gemini-1.5-pro',
        ];

        $lastError = 'Gagal terhubung ke layanan Google.';

        foreach (array_unique($modelsToTry) as $model) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withHeaders([
                        'X-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($endpoint, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => 'Balas hanya format JSON: {"status": "ok", "message": "Koneksi Google Gemini Berhasil!"}']
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'responseMimeType' => 'application/json',
                        ]
                    ]);

                if ($response->status() === 503) {
                    usleep(500000);
                    $response = \Illuminate\Support\Facades\Http::timeout(10)
                        ->withHeaders([
                            'X-goog-api-key' => $apiKey,
                            'Content-Type' => 'application/json',
                        ])
                        ->post($endpoint, [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => 'Balas hanya format JSON: {"status": "ok", "message": "Koneksi Google Gemini Berhasil!"}']
                                    ]
                                ]
                            ],
                            'generationConfig' => [
                                'temperature' => 0.1,
                                'responseMimeType' => 'application/json',
                            ]
                        ]);
                }

                if ($response->successful()) {
                    $body = $response->json();
                    $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $parsed = json_decode(trim(preg_replace('/^```(?:json)?|```$/m', '', $text)), true);

                    return response()->json([
                        'success' => true,
                        'message' => $parsed['message'] ?? 'Koneksi ke Google Gemini AI Berhasil!',
                        'model' => $model,
                    ]);
                }

                $lastError = $response->json('error.message') ?? ('Error HTTP ' . $response->status());
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal terhubung: ' . $lastError
        ], 400);
    }

    public function accountMappings()
    {
        $company = $this->getActiveCompany();
        $settings = $company->settings ?? CompanySetting::firstOrCreate(['company_id' => $company->id]);
        
        $accounts = Account::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Helper to find account ID by standard code prefix if currently null
        $defaultMap = [
            'account_inventory_id' => $accounts->firstWhere('code', '1-10200')?->id ?? $accounts->firstWhere('code', '1-10201')?->id,
            'account_sales_id' => $accounts->firstWhere('code', '4-40000')?->id ?? $accounts->firstWhere('code', '4-40001')?->id,
            'account_sales_return_id' => $accounts->firstWhere('code', '4-40200')?->id,
            'account_sales_discount_id' => $accounts->firstWhere('code', '4-40100')?->id,
            'account_goods_in_transit_id' => $accounts->firstWhere('code', '1-10200')?->id,
            'account_cogs_id' => $accounts->firstWhere('code', '5-50000')?->id ?? $accounts->firstWhere('code', '5-50001')?->id,
            'account_purchase_return_id' => $accounts->firstWhere('code', '5-50200')?->id ?? $accounts->firstWhere('code', '5-50100')?->id,
            'account_expense_id' => $accounts->firstWhere('code', '6-60900')?->id ?? $accounts->firstWhere('code', '6-60100')?->id,
            'account_unbilled_purchases_id' => $accounts->firstWhere('code', '2-20101')?->id ?? $accounts->firstWhere('code', '2-20100')?->id,
            'account_receivable_id' => $accounts->firstWhere('code', '1-10100')?->id ?? $accounts->firstWhere('code', '1-10101')?->id,
            'account_payable_id' => $accounts->firstWhere('code', '2-20100')?->id,
            'account_cash_drawer_id' => $accounts->firstWhere('code', '1-10001')?->id ?? $accounts->firstWhere('code', '1-10000')?->id,
            'account_rounding_diff_id' => $accounts->firstWhere('code', '8-80900')?->id ?? $accounts->firstWhere('code', '8-80000')?->id,
            'account_sales_deposit_id' => $accounts->firstWhere('code', '2-20208')?->id ?? $accounts->firstWhere('code', '2-20200')?->id,
            'account_purchase_downpayment_id' => $accounts->firstWhere('code', '1-10403')?->id ?? $accounts->firstWhere('code', '1-10400')?->id,
        ];

        return view('settings.account_mappings', compact('company', 'settings', 'accounts', 'defaultMap'));
    }

    public function updateAccountMappings(Request $request)
    {
        $company = $this->getActiveCompany();
        $settings = $company->settings ?? CompanySetting::firstOrCreate(['company_id' => $company->id]);

        $validated = $request->validate([
            'account_inventory_id' => 'nullable|exists:accounts,id',
            'account_sales_id' => 'nullable|exists:accounts,id',
            'account_sales_return_id' => 'nullable|exists:accounts,id',
            'account_sales_discount_id' => 'nullable|exists:accounts,id',
            'account_goods_in_transit_id' => 'nullable|exists:accounts,id',
            'account_cogs_id' => 'nullable|exists:accounts,id',
            'account_purchase_return_id' => 'nullable|exists:accounts,id',
            'account_expense_id' => 'nullable|exists:accounts,id',
            'account_unbilled_purchases_id' => 'nullable|exists:accounts,id',
            'account_receivable_id' => 'nullable|exists:accounts,id',
            'account_payable_id' => 'nullable|exists:accounts,id',
            'account_cash_drawer_id' => 'nullable|exists:accounts,id',
            'account_rounding_diff_id' => 'nullable|exists:accounts,id',
            'account_sales_deposit_id' => 'nullable|exists:accounts,id',
            'account_purchase_downpayment_id' => 'nullable|exists:accounts,id',
        ]);

        $settings->update($validated);

        return back()->with('success', 'Pemetaan Akun Perkiraan (Default Accounts) berhasil diperbarui.');
    }

    public function profile()
    {
        $company = $this->getActiveCompany();
        $user = auth()->user() ?? User::first();

        return view('settings.profile', compact('company', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user() ?? User::first();

        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'address' => 'nullable|string',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function employees()
    {
        $company = $this->getActiveCompany();
        $employees = $company->users()->withPivot('role')->get();

        // Roles definition & permission matrix
        $rolesMatrix = [
            'admin' => [
                'name' => 'Administrator (Owner)',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                'description' => 'Akses penuh ke semua fitur, kunci/buka saldo awal, dan pengaturan sistem.',
                'can_lock_coa' => true,
                'can_transactions' => true,
                'can_master_data' => true,
                'can_reports' => true,
                'can_settings' => true,
            ],
            'accountant' => [
                'name' => 'Akuntan (Finance)',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                'description' => 'Akses penuh transaksi, master data, dan semua laporan keuangan.',
                'can_lock_coa' => false,
                'can_transactions' => true,
                'can_master_data' => true,
                'can_reports' => true,
                'can_settings' => false,
            ],
            'cashier' => [
                'name' => 'Kasir / Staf Operasional',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'description' => 'Hanya mencatat transaksi harian (Pemasukan & Pengeluaran).',
                'can_lock_coa' => false,
                'can_transactions' => true,
                'can_master_data' => false,
                'can_reports' => false,
                'can_settings' => false,
            ],
            'auditor' => [
                'name' => 'Auditor (Read-Only)',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'description' => 'Hanya melihat laporan keuangan dan history transaksi tanpa izin mengubah data.',
                'can_lock_coa' => false,
                'can_transactions' => false,
                'can_master_data' => false,
                'can_reports' => true,
                'can_settings' => false,
            ],
        ];

        return view('settings.employees', compact('company', 'employees', 'rolesMatrix'));
    }

    public function storeEmployee(Request $request)
    {
        $company = $this->getActiveCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|in:admin,accountant,cashier,auditor',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt('password123'),
            'default_company_id' => $company->id,
        ]);

        $company->users()->attach($newUser->id, ['role' => $validated['role']]);

        return redirect()->route('settings.employees')->with('success', "Karyawan '{$validated['name']}' berhasil ditambahkan dengan peran " . ucfirst($validated['role']));
    }

    public function resetData(Request $request)
    {
        $user = auth()->user() ?? $request->user() ?? User::first();
        $company = $this->getActiveCompany();

        if (!$user || !$user->isAdmin($company->id)) {
            return back()->with('error', 'Akses Ditolak: Hanya Owner / Administrator yang memiliki wewenang untuk mereset data pembukuan.');
        }

        $validated = $request->validate([
            'reset_type' => 'required|in:transactions_only,initial_balances,factory_reset',
            'confirmation_text' => 'required|in:RESET,reset',
        ], [
            'confirmation_text.in' => 'Konfirmasi gagal. Anda wajib mengetik kata "RESET" persis seperti instruksi.',
        ]);

        DB::transaction(function () use ($company, $validated) {
            $type = $validated['reset_type'];

            if ($type === 'transactions_only' || $type === 'factory_reset') {
                // Hapus semua Jurnal dan Transaksi
                $entryIds = JournalEntry::where('company_id', $company->id)->pluck('id');
                JournalItem::whereIn('journal_entry_id', $entryIds)->delete();
                JournalEntry::where('company_id', $company->id)->delete();
                Transaction::where('company_id', $company->id)->delete();
            }

            if ($type === 'initial_balances' || $type === 'factory_reset') {
                // Reset Saldo Awal COA menjadi 0 dan buka kunci
                Account::where('company_id', $company->id)->update([
                    'initial_debit' => 0,
                    'initial_credit' => 0,
                ]);
                $company->update(['is_initial_balance_locked' => false]);
            }
        });

        $msg = match ($validated['reset_type']) {
            'transactions_only' => '🗑️ Seluruh Data Transaksi & Jurnal Testing berhasil DIRESET/DIHAPUS. Master Akun COA & Saldo Awal tetap aman.',
            'initial_balances' => '🔄 Saldo Awal seluruh Akun COA berhasil DIRESET ke 0 dan kunci saldo awal telah DIBUKA.',
            'factory_reset' => '⚠️ FACTORY RESET BERHASIL: Seluruh data transaksi, jurnal, dan saldo awal telah dikembalikan ke kondisi awal (bersih) untuk memulai pembukuan baru.',
        };

        return redirect()->route('dashboard')->with('success', $msg);
    }
}
