<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Install extends Command
{
    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:install
                                {--elfinder=ask : Should it install the File Manager. }
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Backpack requirements on dev, publish files and create uploads directory.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
        * "ask" comes by default, when no option is provided, like: "backpack:install"
        * https://laravel.com/docs/6.0/artisan#options
        */
        $install_elfinder = null;

        if ($this->option('elfinder') == 'ask') {
            $install_elfinder = $this->confirm("Install & set up the File Manager (elFinder)? The admin will be able to browse the 'uploads' folder and create/read/modify files and folders there.", 'yes');
        } elseif ($this->option('elfinder') == 'no') {
            $install_elfinder = false;
        } elseif ($this->option('elfinder') == 'yes') {
            $install_elfinder = true;
        } else {
            $this->error('Option not recognized: '.$elfinderOption);

            return false;
        }

        $steps = $install_elfinder ? 11 : 6;

        $this->progressBar = $this->output->createProgressBar($steps);
        $this->progressBar->start();
        $this->info(' Backpack installation started. Please wait...');
        $this->progressBar->advance();

        $this->line(' Publishing configs, langs, views, js and css files');
        $this->executeProcess('php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag="minimum"');

        $this->line(' Publishing config for notifications - prologue/alerts');
        $this->executeProcess('php artisan vendor:publish --provider="Prologue\Alerts\AlertsServiceProvider"');

        $this->line(" Generating users table (using Laravel's default migrations)");
        $this->executeProcess('php artisan migrate');

        $this->line(" Creating App\Models\BackpackUser.php");
        $this->executeProcess('php artisan backpack:publish-user-model');

        $this->line(" Creating App\Http\Middleware\CheckIfAdmin.php");
        $this->executeProcess('php artisan backpack:publish-middleware');

        // elFinder steps
        if ($install_elfinder) {
            $this->line(' Installing barryvdh/laravel-elfinder');
            $this->executeProcess('composer require barryvdh/laravel-elfinder');

            $this->line(' Creating uploads directory');
            switch (DIRECTORY_SEPARATOR) {
                case '/': // unix
                    $this->executeProcess('mkdir -p public/uploads');
                    break;
                case '\\': // windows
                    if (! file_exists('public\uploads')) {
                        $this->executeProcess('mkdir public\uploads');
                    }
                    break;
            }

            $this->line(' Publishing elFinder assets');
            $this->executeProcess('php artisan elfinder:publish');

            $this->line(' Publishing custom elfinder views');
            $this->executeProcess('php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag="elfinder"');

            $this->line(' Adding sidebar menu item for File Manager');
            switch (DIRECTORY_SEPARATOR) {
                case '/': // unix
                    $this->executeProcess('php artisan backpack:add-sidebar-content "<li class="nav-item"><a class="nav-link" href=\"{{ backpack_url(\'elfinder\') }}\"><i class=\"nav-icon fa fa-files-o\"></i> <span>{{ trans(\'backpack::crud.file_manager\') }}</span></a></li>"');
                    break;
                case '\\': // windows
                    $this->executeProcess('php artisan backpack:add-sidebar-content "<li class="nav-item"><a class="nav-link" href=""{{ backpack_url(\'elfinder\') }}""><i class=""nav-icon fa fa-files-o""></i> <span>{{ trans(\'backpack::crud.file_manager\') }}</span></a></li>"');
                    break;
            }
        }
        // end of elFinder steps

        $this->progressBar->finish();
        $this->info(' Backpack installation finished.');
    }

    /**
     * Run a SSH command.
     *
     * @param string $command      The SSH command that needs to be run
     * @param bool   $beforeNotice Information for the user before the command is run
     * @param bool   $afterNotice  Information for the user after the command is run
     *
     * @return mixed Command-line output
     */
    public function executeProcess($command, $beforeNotice = false, $afterNotice = false)
    {
        $this->echo('info', $beforeNotice ? ' '.$beforeNotice : $command);

        $process = new Process($command, null, null, null, $this->option('timeout'), null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->echo('comment', $buffer);
            } else {
                $this->echo('line', $buffer);
            }
        });

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if ($this->progressBar) {
            $this->progressBar->advance();
        }

        if ($afterNotice) {
            $this->echo('info', $afterNotice);
        }
    }

    /**
     * Write text to the screen for the user to see.
     *
     * @param [string] $type    line, info, comment, question, error
     * @param [string] $content
     */
    public function echo($type, $content)
    {
        if ($this->option('debug') == false) {
            return;
        }

        // skip empty lines
        if (trim($content)) {
            $this->{$type}($content);
        }
    }
}
