<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Cloud\Firestore\FirestoreClient;

class UpdateMartItemsId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mart-items:update-ids {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update mart items to add missing ID fields (document name = id)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting mart items ID update process...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('mart_items');
            $documents = $collection->documents();

            $totalItems = 0;
            $updatedItems = 0;
            $skippedItems = 0;

            $this->info('Scanning mart items collection...');

            foreach ($documents as $document) {
                $totalItems++;
                $docId = $document->id();
                $data = $document->data();

                // Check if ID field already exists and matches document ID
                if (isset($data['id']) && $data['id'] === $docId) {
                    $skippedItems++;
                    continue;
                }

                // Item needs ID field update
                if ($dryRun) {
                    $itemName = $data['name'] ?? 'Unknown';
                    $this->line("Would update: {$itemName} (ID: {$docId})");
                } else {
                    try {
                        // Get document reference and update the document to add the ID field
                        $docRef = $collection->document($docId);
                        $docRef->update([
                            ['path' => 'id', 'value' => $docId]
                        ]);
                        
                        $itemName = $data['name'] ?? 'Unknown';
                        $this->info("Updated: {$itemName} (ID: {$docId})");
                        $updatedItems++;
                    } catch (\Exception $e) {
                        $this->error("Failed to update item {$docId}: " . $e->getMessage());
                    }
                }
            }

            // Display summary
            $this->newLine();
            $this->info('=== SUMMARY ===');
            $this->info("Total items scanned: {$totalItems}");
            $this->info("Items already have ID: {$skippedItems}");
            
            if ($dryRun) {
                $this->warn("Items that would be updated: " . ($totalItems - $skippedItems));
                $this->warn('Run without --dry-run to apply changes');
            } else {
                $this->info("Items updated: {$updatedItems}");
                $this->info("Items skipped: {$skippedItems}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error updating mart items: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
