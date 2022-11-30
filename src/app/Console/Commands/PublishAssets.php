<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PublishAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:publish-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish new CSS and JS assets (will override existing ones).';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->runConsoleCommand(['php', 'artisan', 'vendor:publish', '--provider=Backpack\CRUD\BackpackServiceProvider', '--tag=public', '--force']);
    }

    /**
     * Run a shell command in a separate process.
     *
     * @param  string  $command  Text to be executed.
     * @return void
     */
    private function runConsoleCommand($command)
    {
        $process = new Process($command, null, null, null, 60, null);
        $process->run(function ($type, $buffer) {
            $this->line($buffer);     
        });

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
