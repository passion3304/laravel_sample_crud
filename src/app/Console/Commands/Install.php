<?php

namespace Backpack\CRUD\app\Console\Commands;

use Backpack\CRUD\BackpackServiceProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class Install extends Command
{
    use Traits\PrettyCommandOutput;

    protected $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Backpack requirements on dev, publish files and create uploads directory.';

    /**
     * Addons variable.
     *
     * @var array
     */
    protected $addons = [
        Addons\RequirePro::class,
        Addons\RequireDevTools::class,
        Addons\RequireEditableColumns::class,
    ];

    /**
     * Execute the console command.
     *
     * @return mixed Command-line output
     */
    public function handle()
    {
        $this->infoBlock('Backpack installation started.');

        // Publish files
        $this->progressBlock('Publishing configs, views, js and css files');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => BackpackServiceProvider::class,
            '--tag' => 'minimum',
        ]);
        $this->closeProgressBlock();

        // Create users table
        $this->progressBlock('Creating users table');
        $this->executeArtisanProcess('migrate', $this->option('no-interaction') ? ['--no-interaction' => true] : []);
        $this->closeProgressBlock();

        // Create CheckIfAdmin middleware
        $this->progressBlock('Creating CheckIfAdmin middleware');
        $this->executeArtisanProcess('backpack:publish-middleware');
        $this->closeProgressBlock();

        // Install Backpack Generators
        $this->progressBlock('Installing Backpack Generators');
        // $process = new Process(['composer', 'require', '--dev', 'backpack/generators']);
        // $process->setTimeout(300);
        // $process->run();
        $this->closeProgressBlock();

        // Create users
        $this->createUsers();

        // Addons
        $this->installAddons();

        // Done
        $url = Str::of(config('app.url'))->finish('/')->append('admin/');
        $this->infoBlock('Backpack installation complete.', 'done');
        $this->note("Head to <fg=blue>$url</> to view your new admin panel");
        $this->note('You may need to run `php artisan serve` to serve your project.');
        $this->newLine();
    }

    private function createUsers()
    {
        $userClass = config('backpack.base.user_model_fqn', 'App\Models\User');
        $userModel = new $userClass();

        // Count current users
        $currentUsers = $userModel->count();

        $this->newLine();
        $this->infoBlock('Create a user to access your admin panel');
        $this->note('By adding an user you\'ll be able to quickly jump in your admin panel.');
        $this->note('Currently there '.trans_choice("{0} are <fg=blue>no users</>|{1} is <fg=blue>1 user</>|[2,*] are <fg=blue>$currentUsers users</>", $currentUsers).' in the database');

        $total = 0;
        while ($this->confirm(' Add '.($total ? 'another' : 'an').' admin user?')) {
            $name = $this->ask(' Name');
            $mail = $this->ask(" {$name}'s email");
            $pass = $this->secret(" {$name}'s password");

            try {
                $userModel->insert([
                    'name' => $name,
                    'email' => $mail,
                    'password' => bcrypt($pass),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $this->deleteLines(12);
                $this->progressBlock('Adding admin user');
                $this->closeProgressBlock();
                $this->note($name);
                $this->note($mail);

                $total++;
            } catch (\Throwable$e) {
                $this->errorBlock($e->getMessage());
            }
        }

        $this->deleteLines(1);
    }

    private function isEveryAddonInstalled()
    {
        return collect($this->addons)->every(function ($addon) {
            return file_exists($addon->path);
        });
    }

    private function updateAddonsStatus()
    {
        $this->addons = $this->addons->each(function (&$addon) {
            $isInstalled = file_exists($addon->path);
            $addon->status = $isInstalled ? 'installed' : 'not installed';
            $addon->statusColor = $isInstalled ? 'green' : 'yellow';
        });
    }

    private function installAddons()
    {
        // map the addons status

        $this->addons = collect($this->addons)
            ->map(function ($class) {
                return (object) $class::$addon;
            });

        $this->updateAddonsStatus();

        // if all addons are installed do nothing
        if ($this->isEveryAddonInstalled()) {
            return;
        }

        $this->newLine();
        $this->infoBlock('Backpack addons');
        $this->note('We believe these addons are everything you need to build admin panels of any complexity.');
        $this->note('However, addons are paid, for more info, payment and access please visit https://backpackforlaravel.com/addons');
        $this->newLine();

        // Calculate the printed line count
        $printedLines = $this->addons
            ->map(function ($e) {
                return count($e->description);
            })
            ->reduce(function ($sum, $item) {
                return $sum + $item + 2;
            }, 0);

        $total = 0;
        while (! $this->isEveryAddonInstalled()) {
            $input = (int) $this->listChoice('Would you like to install any Backpack Addon? <fg=gray>(enter option number)</>', $this->addons->toArray());

            if ($input < 1 || $input > $this->addons->count()) {
                break;
            }

            // Clear list
            $this->deleteLines($printedLines + 4 + ($total ? 2 : 0));

            try {
                $addon = $this->addons[$input - 1];

                // Install addon
                $this->call($addon->command);

                // refresh list
                $this->updateAddonsStatus();

                $total++;
            } catch (\Throwable $e) {
                $this->errorBlock($e->getMessage());
            }

            $this->line('  ──────────', 'fg=gray');
            $this->newLine();
        }
    }
}
