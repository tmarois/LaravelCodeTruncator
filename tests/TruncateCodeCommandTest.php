<?php

namespace Tmarois\LaravelCodeTruncator\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Tmarois\LaravelCodeTruncator\CodeTruncatorServiceProvider;
use PHPUnit\Framework\Attributes\Test;

class TruncateCodeCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CodeTruncatorServiceProvider::class];
    }

    #[Test]
    public function it_runs_the_code_truncate_command(): void
    {
        $this->withoutExceptionHandling();

        File::shouldReceive('exists')
            ->andReturn(true);

        $fakeFile = \Mockery::mock(SplFileInfo::class);
        $fakeFile->shouldReceive('getRealPath')->andReturn(__FILE__);
        $fakeFile->shouldReceive('getRelativePathname')->andReturn('app/Services/FakeFile.php');

        File::shouldReceive('allFiles')
            ->andReturn([$fakeFile]);

        File::shouldReceive('get')
            ->andReturn('<?php echo "test";');

        File::shouldReceive('put')
            ->once()
            ->withArgs(function ($path, $content) {
                return str_contains($path, 'truncated_code.txt') && is_string($content);
            });

        $this->artisan('code:truncate')
            ->expectsOutput('Truncated code saved to storage/truncated_code.txt')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_accepts_a_custom_filename_argument(): void
    {
        $this->withoutExceptionHandling();

        File::shouldReceive('exists')
            ->andReturn(true);

        $fakeFile = \Mockery::mock(SplFileInfo::class);
        $fakeFile->shouldReceive('getRealPath')->andReturn(__FILE__);
        $fakeFile->shouldReceive('getRelativePathname')->andReturn('app/Models/CustomFile.php');

        File::shouldReceive('allFiles')
            ->andReturn([$fakeFile]);

        File::shouldReceive('get')
            ->andReturn('<?php echo "test";');

        File::shouldReceive('put')
            ->once()
            ->withArgs(function ($path, $content) {
                return $path === storage_path('custom_output.txt') && is_string($content);
            });

        $this->artisan('code:truncate', [
                'filename' => 'custom_output.txt',
                '--paths' => 'app'
            ])
            ->expectsOutput('Truncated code saved to storage/custom_output.txt')
            ->assertExitCode(0);
    }
}
