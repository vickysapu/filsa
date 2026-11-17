<?php

namespace Database\Seeders;

use App\Models\DataTable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DataTableSeeder extends Seeder
{
    public function run(): void
    {
        $modelPath = app_path('Models');
        $namespace = 'App\\Models\\';
        $excludedModels = [
            'DataTable', 
            'User',      
        ];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modelPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filename = $file->getBasename('.php');
                
                if (in_array($filename, $excludedModels)) {
                    continue;
                }

                $relativePath = str_replace($modelPath, '', $file->getPath());
                $subNamespace = trim(str_replace('/', '\\', $relativePath), '\\');
                
                $fullClassName = $namespace . ($subNamespace ? $subNamespace . '\\' : '') . $filename;

                if (class_exists($fullClassName)) {
                    $reflection = new \ReflectionClass($fullClassName);
                    
                    if (!$reflection->isAbstract() && $reflection->isSubclassOf(Model::class)) {
                        try {
                            $modelInstance = new $fullClassName();
                            $tableName = $modelInstance->getTable();

                            $exists = DataTable::where('table_name', $tableName)->exists();

                            if (!$exists) {
                                DataTable::create([
                                    'id'         => Str::uuid(), 
                                    'table_name' => $tableName,
                                    'status'     => true,
                                ]);
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }
    }
}