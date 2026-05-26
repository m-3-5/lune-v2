<?php

namespace App\Console\Commands;

use App\Support\GoogleCredentials;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ListProcessorsRequest;
use Illuminate\Console\Command;

class GoogleListProcessors extends Command
{
    protected $signature = 'jlune:google-list-processors';

    protected $description = 'Elenca i processor Document AI nel progetto';

    public function handle(): int
    {
        $path = GoogleCredentials::resolvePath();
        $project = config('google.project_id');

        foreach (['eu', 'us'] as $location) {
            $this->info("=== {$location} ===");
            try {
                $client = new DocumentProcessorServiceClient(['credentials' => $path]);
                $parent = $client->locationName($project, $location);
                $request = new ListProcessorsRequest;
                $request->setParent($parent);
                $count = 0;
                foreach ($client->listProcessors($request) as $processor) {
                    $count++;
                    $this->line($processor->getName());
                    $this->line('  display: '.$processor->getDisplayName());
                    $this->line('  type: '.$processor->getType());
                }
                if ($count === 0) {
                    $this->warn('  (nessun processor)');
                }
                $client->close();
            } catch (\Throwable $e) {
                $this->error('  '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
