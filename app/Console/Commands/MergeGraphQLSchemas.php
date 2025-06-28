<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MergeGraphQLSchemas extends Command
{
    protected $signature = 'graphql:merge-schemas';
    protected $description = 'Merge all schema.graphql files into graphql/schema.graphql';

    public function handle()
    {
        // Lokasi utama schema hasil merge
        $outputPath = base_path('graphql/schema.graphql');

        // Folder modul tempat schema berada (misal di App/GraphQL/Queries/**/schema.graphql)
        $moduleSchemas = array_merge(
            glob(app_path('GraphQL/default_schema.graphql')),
            glob(app_path('GraphQL/Queries/*/schema.graphql')),
            glob(app_path('GraphQL/Mutations/*/schema.graphql'))
        );

        $merged = '';

        foreach ($moduleSchemas as $filePath) {
            $fileName = basename($filePath);
            $merged .= "\n\n# ====== {$filePath} ======\n";
            $merged .= File::get($filePath);
        }

        // Simpan ke file utama
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $merged);

        $this->info('✅ All module schemas merged into graphql/schema.graphql');
    }
}
