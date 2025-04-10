<?php

namespace Tmarois\LaravelCodeTruncator\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CodeTruncatorService
{
    public function truncate(array $paths): array
    {
        $allCode = '';
        $tokensUsed = 0;

        foreach ($paths as $path) {
            $fullPath = base_path($path);

            if (!File::exists($fullPath)) {
                continue;
            }

            $files = File::allFiles($fullPath);

            foreach ($files as $file) {
                $content = File::get($file->getRealPath());
                $summary = $this->summarize($content);

                $absolutePath = $file->getRealPath();
                $relativePath = Str::after($absolutePath, base_path() . DIRECTORY_SEPARATOR);

                $allCode .= "\n\n/** FILE: {$relativePath} **/\n\n" . $summary;

                $tokens = $this->countTokens($summary);
                $tokensUsed += $tokens;
            }
        }

        return [
            'context' => $allCode,
            'tokens' => $tokensUsed,
        ];
    }

    private function summarize(string $content): string
    {
        $content = preg_replace('/^\s*\|[-]+\|.*\n(?:.*\n)*?\s*\|[-]+\|.*$/m', '', $content);
        $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
        $content = preg_replace('/\s*\/\/.*$/m', '', $content);

        $lines = explode("\n", $content);
        $filtered = array_filter($lines, function ($line) {
            $trimmed = trim($line);
            return $trimmed !== '' && ! collect(['use ', 'require', 'namespace', '#', '*', '<?php', '?>'])
                ->some(fn($prefix) => str_starts_with($trimmed, $prefix));
        });

        return implode("\n", $filtered);
    }

    private function countTokens(string $text): int
    {
        return round(str_word_count($text) * 1.33);
    }
}
