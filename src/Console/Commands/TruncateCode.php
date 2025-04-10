<?php

namespace Tmarois\LaravelCodeTruncator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tmarois\LaravelCodeTruncator\Services\CodeTruncatorService;

class TruncateCode extends Command
{
    protected $signature = 'code:truncate 
        {filename=truncated_code.txt : The output file name} 
        {--paths= : Comma-separated list of directories to scan}';

    protected $description = 'Truncate Laravel codebase to fit within AI context limits';

    protected array $defaultPaths = [
        'database/migrations',
        'app/Services',
        'app/Models',
        'app/Helpers',
        'app/Enums',
        'app/Http/Controllers',
        'app/Http/Middleware',
    ];

    public function handle(CodeTruncatorService $service): void
    {
        $filename = $this->argument('filename');
        $pathsOption = $this->option('paths');

        $paths = $pathsOption
            ? array_map('trim', explode(',', $pathsOption))
            : $this->defaultPaths;

        $output = $service->truncate($paths);

        File::put(storage_path($filename), $output['context']);

        $this->info("Truncated code saved to storage/{$filename}");
        $this->info("Token count: {$output['tokens']}");
    }
}
