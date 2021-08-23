<?php

namespace Backpack\CRUD\app\Console\Commands;

use File;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RequireDevTools extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;

    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:devtools:require
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install DevTools with its requirements on dev.';

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(15);
        $this->progressBar->minSecondsBetweenRedraws(0);
        $this->progressBar->maxSecondsBetweenRedraws(120);
        $this->progressBar->setRedrawFrequency(1);

        $this->progressBar->start();

        $this->info(' DevTools installation started. Please wait...');
        $this->progressBar->advance();

        // Check if auth exists
        $details = null;
        $process = new Process(['composer', 'config', 'http-basic.backpackforlaravel.com']);
        $process->run(function ($type, $buffer) use (&$details) {
            if ($type !== Process::ERR && $buffer !== '') {
                $details = json_decode($buffer);
            }
            $this->progressBar->advance();
        });

        // Create an auth.json file
        if (!$details) {
            $this->info(' Creating auth.json file with DevTools auth details');

            $this->line(' (Find your access token details on https://backpackforlaravel.com/user/tokens)');
            $username = $this->ask('Access token username');
            $password = $this->ask('Access token password');

            $process = new Process(['composer', 'config', 'http-basic.backpackforlaravel.com', $username, $password]);
            $process->run(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    $this->error('Could not write to auth.json file.');
                }
                $this->progressBar->advance();
            });
        }

        // Require package
        $process = new Process(['composer', 'require', '--dev', 'backpack/devtools']);
        $process->run(function ($type, $buffer) {
            $this->progressBar->advance();
        });

        // Finish
        $this->progressBar->finish();
        $this->info(' DevTools installation finished.');

        // DevTools inside installer
        $this->info('');
        $this->info(' DevTools requirements started. Please wait...');
        $this->call('backpack:devtools:install');
    }
}
