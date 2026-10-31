<?php

namespace App\Console\Commands;

use App\Services\ContactCenter\CallbackEngineService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessContactCenterCallbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contact-center:process-callbacks {--batch=10 : The maximum number of callbacks to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process eligible pending contact center customer callbacks';

    /**
     * Execute the console command.
     */
    public function handle(CallbackEngineService $engine): int
    {
        $batch = (int) $this->option('batch') ?: 10;
        $this->info("Scanning for pending contact center callbacks (batch size: {$batch})...");

        try {
            $count = $engine->processPendingCallbacks($batch);
            if ($count > 0) {
                $this->info("Successfully processed {$count} callback(s).");
            } else {
                $this->line("No pending callbacks due for execution.");
            }
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error processing callbacks: " . $e->getMessage());
            Log::error("Callback process error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return Command::FAILURE;
        }
    }
}
