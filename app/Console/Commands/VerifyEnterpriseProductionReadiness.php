<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReleaseReadinessService;

class VerifyEnterpriseProductionReadiness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterprise:verify-readiness';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify comprehensive production readiness, database schemas, FreeSWITCH sockets, and 20 enterprise modules';

    /**
     * Execute the console command.
     */
    public function handle(ReleaseReadinessService $service): int
    {
        $this->info("================================================================================");
        $this->info("   ZNEROPBX ENTERPRISE SUITE — PRODUCTION READINESS & INTEGRITY AUDIT (P12)");
        $this->info("================================================================================");

        $results = $service->runComprehensiveSystemAudit();

        $rows = [];
        $allPassed = true;

        foreach ($results['subsystems'] as $sub) {
            $statusText = $sub['passed'] ? '<fg=green>PASS [200 OK]</>' : '<fg=red>FAIL</>';
            if (!$sub['passed']) $allPassed = false;

            $rows[] = [
                $sub['name'],
                $sub['category'],
                $statusText,
                $sub['latency_ms'] . ' ms',
                $sub['details'],
            ];
        }

        $this->table(['Subsystem Tier', 'Category', 'Audit Result', 'Latency', 'Details'], $rows);

        $this->line("");
        $this->info("Overall Production Score: {$results['overall_score_pct']}%");
        $this->info("Artifact Provenance SHA-256: {$results['build_provenance_sha256']}");
        $this->info("Audited At: " . now()->toIso8601String());

        if ($allPassed) {
            $this->info("<fg=green;options=bold>ALL 8 SUBSYSTEM TIERS PASSED VERIFICATION. PRODUCTION READY.</>");
            return Command::SUCCESS;
        } else {
            $this->error("SOME SUBSYSTEM CHECKS FAILED. PLEASE REVIEW AUDIT LOGS.");
            return Command::FAILURE;
        }
    }
}
