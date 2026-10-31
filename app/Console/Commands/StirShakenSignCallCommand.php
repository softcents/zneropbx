<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Telecom\StirShakenService;
use Illuminate\Support\Facades\DB;

class StirShakenSignCallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterprise:stir-shaken-sign-call 
                            {--caller= : Caller ID phone number}
                            {--dest= : Destination phone number}
                            {--domain= : Domain UUID}
                            {--json : Output raw JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sign an outbound call with an RFC 8588 STIR/SHAKEN PASSporT token and output Identity header';

    /**
     * Execute the console command.
     */
    public function handle(StirShakenService $stirShakenService): int
    {
        $caller = $this->option('caller');
        $dest = $this->option('dest');
        $domainUuid = $this->option('domain') ?: DB::table('v_domains')->value('domain_uuid');

        if (!$caller || !$dest) {
            $this->error("Caller and Destination numbers are required.");
            return Command::FAILURE;
        }

        $res = $stirShakenService->generatePassport($caller, $dest, null, $domainUuid);

        if ($this->option('json')) {
            $this->line(json_encode($res));
            $this->line("Identity: " . $res['identity_header']);
            return Command::SUCCESS;
        }

        $this->info("STIR/SHAKEN PASSporT Generated Successfully");
        $this->line("Attestation: " . $res['attestation']);
        $this->line("Identity: " . $res['identity_header']);

        return Command::SUCCESS;
    }
}
