<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateCarTransactionsToHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cars:migrate-transactions-to-history {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate car-related transactions to the new car_history table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Running in DRY-RUN mode - No changes will be made');
        }

        $this->info('🔄 Starting migration of car transactions to history...');

        // Get all transactions related to cars
        $carTransactions = \App\Models\Transactions::whereNotNull('morphed_id')
            ->where('morphed_type', 'App\Models\Car')
            ->orderBy('created_at')
            ->get();

        $this->info("📊 Found {$carTransactions->count()} car-related transactions");

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($carTransactions->count());
        $progressBar->start();

        foreach ($carTransactions as $transaction) {
            try {
                // Check if car exists
                $car = \App\Models\Car::find($transaction->morphed_id);
                if (!$car) {
                    $this->warn("⚠️ Car {$transaction->morphed_id} not found, skipping transaction {$transaction->id}");
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Determine action type based on transaction description
                $action = $this->determineActionFromTransaction($transaction);

                // Create history record
                $historyData = [
                    'car_id' => $car->id,
                    'action' => $action,
                    'description' => $transaction->description,
                    'user_id' => $transaction->user_added,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ];

                // Add old/new data for updates
                if ($action === 'update') {
                    $changeInfo = $this->extractChangeInfo($transaction->description);
                    if ($changeInfo) {
                        $historyData['changes'] = $changeInfo;
                        $historyData['field_changed'] = $changeInfo['field'] ?? null;
                    }
                }

                if (!$dryRun) {
                    \App\Models\CarHistory::create($historyData);
                }

                $migrated++;
            } catch (\Exception $e) {
                $this->error("❌ Error migrating transaction {$transaction->id}: {$e->getMessage()}");
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Migration completed!");
        $this->table(['Metric', 'Count'], [
            ['Total transactions found', $carTransactions->count()],
            ['Successfully migrated', $migrated],
            ['Skipped (car not found)', $skipped],
            ['Errors', $errors],
        ]);

        if (!$dryRun && $this->confirm('Do you want to delete the migrated transactions from the transactions table?')) {
            $deleted = \App\Models\Transactions::whereNotNull('morphed_id')
                ->where('morphed_type', 'App\Models\Car')
                ->delete();

            $this->info("🗑️ Deleted {$deleted} transactions from transactions table");
        }

        $this->newLine();
        $this->info('🎉 Migration process completed!');
        return Command::SUCCESS;
    }

    /**
     * Determine action type from transaction
     */
    private function determineActionFromTransaction($transaction): string
    {
        $description = strtolower($transaction->description);

        if (str_contains($description, 'إضافة') || str_contains($description, 'add') || str_contains($description, 'create')) {
            return 'create';
        }

        if (str_contains($description, 'تحديث') || str_contains($description, 'تعديل') || str_contains($description, 'update') || str_contains($description, 'edit')) {
            return 'update';
        }

        if (str_contains($description, 'حذف') || str_contains($description, 'delete')) {
            return 'delete';
        }

        // Default to update for financial transactions
        return 'update';
    }

    /**
     * Extract change information from transaction description
     */
    private function extractChangeInfo(string $description): ?array
    {
        $patterns = [
            '/من\s+([^\s]+)\s+إلى\s+([^\s]+)/u', // Arabic pattern
            '/from\s+([^\s]+)\s+to\s+([^\s]+)/i', // English pattern
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return [
                    'field' => $this->guessFieldName($description),
                    'old' => $matches[1],
                    'new' => $matches[2],
                ];
            }
        }

        return null;
    }

    /**
     * Guess field name from description
     */
    private function guessFieldName(string $description): ?string
    {
        $description = strtolower($description);

        $fieldMappings = [
            'سعر' => 'purchase_price',
            'price' => 'purchase_price',
            'مدفوع' => 'paid_amount',
            'paid' => 'paid_amount',
            'ملاحظة' => 'note',
            'note' => 'note',
        ];

        foreach ($fieldMappings as $keyword => $field) {
            if (str_contains($description, $keyword)) {
                return $field;
            }
        }

        return null;
    }
}
