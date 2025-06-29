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
        $outputPath = base_path('graphql/schema.graphql');

        $schemaFiles = array_merge(
            glob(app_path('GraphQL/default_schema.graphql')),
            glob(app_path('GraphQL/Queries/*/schema.graphql')),
            glob(app_path('GraphQL/Mutations/*/schema.graphql'))
        );

        $queryFields = '';
        $mutationFields = '';
        $others = '';

        foreach ($schemaFiles as $filePath) {
            $content = File::get($filePath);

            // Ambil isi type Query
            if (preg_match('/type\s+Query\s*{([^}]*)}/s', $content, $match)) {
                $queryFields .= "\n" . trim($match[1]);
                // Hapus bagian type Query dari content
                $content = preg_replace('/type\s+Query\s*{[^}]*}/s', '', $content);
            }

            // Ambil isi type Mutation
            if (preg_match('/type\s+Mutation\s*{([^}]*)}/s', $content, $match)) {
                $mutationFields .= "\n" . trim($match[1]);
                // Hapus bagian type Mutation dari content
                $content = preg_replace('/type\s+Mutation\s*{[^}]*}/s', '', $content);
            }

            $others .= "\n\n# ====== {$filePath} ======\n" . trim($content);
        }

        // Gabungkan hasil akhir
        $mergedSchema = $others;

        if (!empty(trim($queryFields))) {
            $mergedSchema .= "\n\ntype Query {\n" . $queryFields . "\n}";
        }

        if (!empty(trim($mutationFields))) {
            $mergedSchema .= "\n\ntype Mutation {\n" . $mutationFields . "\n}";
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $mergedSchema);

        $this->info('✅ All schemas merged, with combined Query and Mutation types.');
    }
}
