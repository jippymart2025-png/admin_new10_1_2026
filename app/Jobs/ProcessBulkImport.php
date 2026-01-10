<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class ProcessBulkImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $collection;
    protected $lookupData;
    protected $skipInvalidRows;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct($data, $collection, $lookupData, $skipInvalidRows = false)
    {
        $this->data = $data;
        $this->collection = $collection;
        $this->lookupData = $lookupData;
        $this->skipInvalidRows = $skipInvalidRows;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('Processing bulk import job', [
                'data_count' => count($this->data),
                'collection' => $this->collection
            ]);

            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
                'databaseId' => config('firestore.database_id'),
                'timeout' => config('firestore.timeout', 15),
            ]);

            $collection = $firestore->collection($this->collection);
            
            foreach ($this->data as $row) {
                try {
                    // Process each row
                    $this->processRow($row, $collection);
                } catch (\Exception $e) {
                    Log::error('Error processing row in bulk import', [
                        'error' => $e->getMessage(),
                        'row' => $row
                    ]);
                    
                    if (!$this->skipInvalidRows) {
                        throw $e;
                    }
                }
            }

            Log::info('Bulk import job completed successfully');
            
        } catch (\Exception $e) {
            Log::error('Bulk import job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Process a single row
     */
    private function processRow($row, $collection)
    {
        // Implement your row processing logic here
        // This is a placeholder - implement based on your specific needs
        $collection->add($row);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::critical('Bulk import job failed permanently', [
            'error' => $exception->getMessage(),
            'data_count' => count($this->data),
            'collection' => $this->collection
        ]);
    }
}
