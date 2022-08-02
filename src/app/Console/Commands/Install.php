<?php

namespace Backpack\CRUD\app\Console\Commands;

use Backpack\CRUD\BackpackServiceProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        $this->infoBlock('Installing Backpack CRUD:', 'Step 1');

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
        $process = new Process(['composer', 'require', '--dev', 'backpack/generators']);
        $process->setTimeout(300);
        $process->run();
        $this->closeProgressBlock();

        // Create users
        $this->createUsers();

        // Addons
        $this->installAddons();

        // Done
        $url = Str::of(config('app.url'))->finish('/')->append('admin/');
        $this->infoBlock('Backpack installation complete.', 'done');
        $this->note("Go to <fg=blue>$url</> to access your new admin panel.");
        $this->note('You may need to run <fg=blue>php artisan serve</> to serve your Laravel project.');
        $this->newLine();
    }

    private function createUsers()
    {
        $userClass = config('backpack.base.user_model_fqn', 'App\Models\User');
        $userModel = new $userClass();

        // Count current users
        $currentUsers = $userModel->count();

        $this->newLine();
        $this->infoBlock('Creating an admin:', 'Step 2');
        $this->note('Quickly jump in your admin panel, using the email & password you choose here.');
        $this->note(sprintf('Currently there %s in the <fg=blue>%s</> table.', trans_choice("{0} are <fg=blue>no users</>|{1} is <fg=blue>1 user</>|[2,*] are <fg=blue>$currentUsers users</>", $currentUsers), $userModel->getTable()));

        $total = 0;
        while ($this->confirm(' Add '.($total ? 'another' : 'an').' admin user?')) {
            $name = $this->ask(' Name');
            $mail = $this->ask(" {$name}'s email");
            $pass = $this->secret(" {$name}'s password");

            try {
                $user = collect([
                    'name' => $name,
                    'email' => $mail,
                    'password' => Hash::make($pass),
                ]);

                // Merge timestamps
                if ($userModel->timestamps) {
                    $user = $user->merge([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }

                $userModel->insert($user->toArray());

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

        $this->deleteLines(3);

        if (! $total) {
            $this->deleteLines();
            $this->note('Skipping creating an admin user.');
            $this->newLine();
        }
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
        // map the addons
        $this->addons = collect($this->addons)
            ->map(function ($class) {
                return (object) $class::$addon;
            });

        // set addons current status (installed / not installed)
        $this->updateAddonsStatus();

        // if all addons are installed do nothing
        if ($this->isEveryAddonInstalled()) {
            return;
        }

        $this->infoBlock('Installing premium Backpack add-ons:', 'Step 3');
        $this->note('Add tons of features and functionality to your admin panel, using our paid add-ons.');
        $this->note('For more information, payment and access please visit <fg=blue>https://backpackforlaravel.com/pricing</>');
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
            $input = (int) $this->listChoice('Would you like to install a premium Backpack add-on? <fg=gray>(enter option number)</>', $this->addons->toArray());

            if ($input < 1 || $input > $this->addons->count()) {
                $this->deleteLines(3);
                break;
            }

            // Clear list
            $this->deleteLines($printedLines + 4 + ($total ? 2 : 0));

            try {
                $addon = $this->addons[$input - 1];

                // Install addon (low verbose level)
                $currentVerbosity = $this->output->getVerbosity();
                $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $this->call($addon->command);
                $this->output->setVerbosity($currentVerbosity);

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
