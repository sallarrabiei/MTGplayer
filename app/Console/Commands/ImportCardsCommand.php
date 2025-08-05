<?php

namespace App\Console\Commands;

use App\Services\CardImportService;
use Illuminate\Console\Command;

class ImportCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mtg:import-cards 
                            {--url= : Custom JSON URL to import from}
                            {--batch-size=500 : Number of cards to process per batch}
                            {--force : Force import without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import MTG cards from JSON data source';

    protected CardImportService $importService;

    public function __construct(CardImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🃏 MTG Card Import Tool');
        $this->info('========================');

        // Set custom URL if provided
        if ($this->option('url')) {
            $this->importService->setJsonUrl($this->option('url'));
            $this->info('Using custom URL: ' . $this->option('url'));
        }

        // Set batch size
        $batchSize = (int) $this->option('batch-size');
        $this->importService->setBatchSize($batchSize);
        $this->info('Batch size: ' . $batchSize);

        // Confirmation prompt
        if (!$this->option('force')) {
            if (!$this->confirm('This will import/update MTG card data. Continue?')) {
                $this->info('Import cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Starting import...');
        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $stats = $this->importService->importCards();

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('Import completed! 🎉');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Processed', number_format($stats['total_processed'])],
                ['Imported', number_format($stats['imported'])],
                ['Updated', number_format($stats['updated'])],
                ['Errors', number_format($stats['errors'])],
                ['Duration', $stats['duration'] . ' seconds'],
            ]
        );

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  {$stats['errors']} errors occurred. Check the logs for details.");
            return Command::FAILURE;
        }

        $this->info('✅ Import completed successfully!');
        return Command::SUCCESS;
    }
}