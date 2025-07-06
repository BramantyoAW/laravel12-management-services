<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MergeGraphQLSchemas extends Command
{
    protected $signature = 'graphql:merge-schemas';
    protected $description = 'Merge all schema.graphql files into graphql/schema.graphql with formatted output and duplicate type/field handling';

    public function handle()
    {
        $outputPath = base_path('graphql/schema.graphql');

        $schemaFiles = array_merge(
            glob(app_path('GraphQL/default_schema.graphql')),
            glob(app_path('GraphQL/Queries/*/schema.graphql')),
            glob(app_path('GraphQL/Mutations/*/schema.graphql'))
        );

        $types = [];
        $queryFields = [];
        $mutationFields = [];
        $otherScalars = '';

        foreach ($schemaFiles as $filePath) {
            $content = File::get($filePath);

            # Collect custom scalars (e.g., scalar DateTime)
            $scalars = [];
            preg_match_all('/scalar\s+[^\s{]+[^\n]*/', $content, $scalars);
            foreach ($scalars[0] as $scalar) {
                $otherScalars .= $scalar . "\n";
            }
            $content = preg_replace('/scalar\s+[^\s{]+[^\n]*/', '', $content);

            # Match all type definitions
            preg_match_all('/type\s+(\w+)\s*{([^}]*)}/s', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $typeName = $match[1];
                $fieldsRaw = trim($match[2]);
                $fieldLines = array_filter(array_map('trim', explode("\n", $fieldsRaw)));

                foreach ($fieldLines as $field) {
                    $fieldName = preg_replace('/\(.*/', '', $field);
                    if ($typeName === 'Query') {
                        $queryFields[$fieldName] = $field;
                    } elseif ($typeName === 'Mutation') {
                        $mutationFields[$fieldName] = $field;
                    } else {
                        $types[$typeName][$fieldName] = $field;
                    }
                }

                # Remove this type from content
                $content = preg_replace('/type\s+' . $typeName . '\s*{[^}]*}/s', '', $content);
            }
        }

        $finalSchema = "";

        # Append custom scalars
        $finalSchema .= trim($otherScalars) . "\n\n";

        # Append other types
        foreach ($types as $typeName => $fields) {
            $finalSchema .= "type {$typeName} {\n";
            foreach ($fields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        # Append Query
        if (!empty($queryFields)) {
            $finalSchema .= "type Query {\n";
            foreach ($queryFields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        # Append Mutation
        if (!empty($mutationFields)) {
            $finalSchema .= "type Mutation {\n";
            foreach ($mutationFields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, trim($finalSchema));

        $this->info('✅ All GraphQL schemas merged successfully with formatting and type deduplication.');
    }
}