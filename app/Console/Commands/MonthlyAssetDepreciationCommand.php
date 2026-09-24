<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\AssetDepreciationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MonthlyAssetDepreciationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:depreciate-monthly 
                            {--company_id= : ID Perusahaan tertentu (opsional)}
                            {--period= : Periode bulan format YYYY-MM (default: bulan ini)}
                            {--all-pending : Otomatis proses semua bulan tertunggak}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis hitung dan posting jurnal depresiasi penyusutan aset tetap di akhir bulan';

    /**
     * Execute the console command.
     */
    public function handle(AssetDepreciationService $service): int
    {
        $companyId = $this->option('company_id');
        $period = $this->option('period') ?: Carbon::now()->format('Y-m');
        $allPending = $this->option('all-pending');

        $companies = $companyId 
            ? Company::where('id', $companyId)->get() 
            : Company::all();

        $this->info("Menjalankan proses penyusutan aset otomatis...");

        foreach ($companies as $company) {
            $this->line("<comment>Memproses Perusahaan:</comment> {$company->name} (ID: {$company->id})");

            if ($allPending) {
                $result = $service->executeBulkPendingDepreciation($company->id);
                $this->info("  ✓ Berhasil memproses {$result['total_periods']} periode tertunda ({$result['total_processed_assets']} aset), Total: Rp " . number_format($result['total_depreciated'], 0, ',', '.'));
            } else {
                $result = $service->executePeriodDepreciation($company->id, $period);
                if ($result['processed_count'] > 0) {
                    $this->info("  ✓ Berhasil memposting periode {$period}: {$result['processed_count']} aset, Total: Rp " . number_format($result['total_depreciated'], 0, ',', '.'));
                } else {
                    $this->line("  - Tidak ada aset yang perlu disusutkan untuk periode {$period}.");
                }
            }
        }

        $this->info("Selesai!");
        return Command::SUCCESS;
    }
}
