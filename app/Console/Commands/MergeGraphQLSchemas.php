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
        $inputs = [];
        $queryFields = [];
        $mutationFields = [];
        $otherScalars = '';

        foreach ($schemaFiles as $filePath) {
            $content = File::get($filePath);

            // Custom scalars
            preg_match_all('/scalar\s+[^\s{]+[^\n]*/', $content, $scalars);
            foreach ($scalars[0] as $scalar) {
                $otherScalars .= $scalar . "\n";
            }
            $content = preg_replace('/scalar\s+[^\s{]+[^\n]*/', '', $content);

            // Input definitions
            preg_match_all('/input\s+(\w+)\s*{([^}]*)}/s', $content, $inputMatches, PREG_SET_ORDER);
            foreach ($inputMatches as $match) {
                $inputName = $match[1];
                $fieldsRaw = trim($match[2]);
                $fieldLines = array_filter(array_map('trim', explode("\n", $fieldsRaw)));

                foreach ($fieldLines as $field) {
                    $fieldName = preg_replace('/\(.*/', '', $field);
                    $inputs[$inputName][$fieldName] = $field;
                }

                $content = preg_replace('/input\s+' . $inputName . '\s*{[^}]*}/s', '', $content);
            }

            // Type definitions (including Query, Mutation)
            preg_match_all('/type\s+(\w+)\s*{([^}]*)}/s', $content, $typeMatches, PREG_SET_ORDER);
            foreach ($typeMatches as $match) {
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

                $content = preg_replace('/type\s+' . $typeName . '\s*{[^}]*}/s', '', $content);
            }
        }

        $finalSchema = "";

        // Custom Scalars
        $finalSchema .= trim($otherScalars) . "\n\n";

        // Input types
        foreach ($inputs as $inputName => $fields) {
            $finalSchema .= "input {$inputName} {\n";
            foreach ($fields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        // Object types
        foreach ($types as $typeName => $fields) {
            $finalSchema .= "type {$typeName} {\n";
            foreach ($fields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        // Query
        if (!empty($queryFields)) {
            $finalSchema .= "type Query {\n";
            foreach ($queryFields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        // Mutation
        if (!empty($mutationFields)) {
            $finalSchema .= "type Mutation {\n";
            foreach ($mutationFields as $field) {
                $finalSchema .= "  {$field}\n";
            }
            $finalSchema .= "}\n\n";
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, trim($finalSchema));

        $this->info('✅ All GraphQL schemas merged successfully with inputs, deduplication, and formatting.');
    }
}
