<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Backpack\Base\app\Console\Commands\Install as BaseInstall;

class Install extends BaseInstall
{
    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:crud:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make uploads directory and publish assets for Backpack\CRUD dependencies';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $install_elfinder = $this->confirm("Install & set up the File Manager (elFinder)? The admin will be able to browse the 'uploads' folder and create/read/modify files and folders there.", 'yes');

        $steps = $install_elfinder ? 9 : 4;

        $this->progressBar = $this->output->createProgressBar($steps);
        $this->progressBar->start();
        $this->info(" Backpack\CRUD installation started. Please wait...");
        $this->progressBar->advance();

        // elFinder steps
        if ($install_elfinder) {
            $this->line(' Installing barryvdh/laravel-elfinder');
            $this->executeProcess('composer require barryvdh/laravel-elfinder');

            $this->line(' Creating uploads directory');
            $this->executeProcess('mkdir -p public/uploads');

            $this->line(' Publishing elFinder assets');
            $this->executeProcess('php artisan elfinder:publish');

            $this->line(' Publishing custom elfinder views');
            $this->executeProcess('php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="elfinder"');

            $this->line(' Adding sidebar menu item for File Manager');
            $this->executeProcess("php artisan backpack:base:add-sidebar-content '<li><a href=\"{{  backpack_url(\"elfinder\") }}\"><i class=\"fa fa-files-o\"></i> <span>File manager</span></a></li>'");
        }
        // end of elFinder steps

        $this->line(' Publishing CRUD assets');
        $this->executeProcess('php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="public"');

        $this->line(' Publishing CRUD language files');
        $this->executeProcess('php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="lang"');

        $this->line(' Publishing CRUD config file and custom elFinder config file');
        $this->executeProcess('php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="config"');

        $this->progressBar->finish();
        $this->info(" Backpack\CRUD installation finished.");
    }
}
