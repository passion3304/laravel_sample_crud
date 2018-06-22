<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;

class Overwrite extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backpack:crud:overwrite';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:crud:overwrite
                            {type : what are you overwriting? field/column/filter/button}
                            {name : name of the field/column/filter/button}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes a built in field/column/filter/button so you can make changes in your project. Please note you won\'t be getting any updates for these files after you publish them.';

    public $packageDir = 'vendor/backpack/crud/src/resources/views/';
    public $appDir = 'resources/views/vendor/backpack/crud/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->type = strtolower($this->argument('type'));
        $this->name = strtolower($this->argument('name'));

        switch ($this->type) {
            case 'field':
            case 'fields':
                return $this->publishField();
            break;
            case 'button':
            case 'buttons':
                return $this->publishButton();
            break;
            case 'column':
            case 'columns':
                return $this->publishColumn();
            break;
            case 'filter':
            case 'filters':
                return $this->publishFilter();
            break;
            default:
                return $this->error('Please pick from field, column, filter or button');
            break;
        }
    }

    protected function processPublish($file, $label)
    {
        $sourceFile = $this->packageDir.$file.'.blade.php';
        $copiedFile = $this->appDir.$file.'.blade.php';

        if (! file_exists($sourceFile)) {
            return $this->error(
                'Cannot find source '.$label.' at '
                .$sourceFile.
                ' - make sure you\'ve picked a real '.$label.' type'
            );
        } else {
            $canCopy = true;

            if (file_exists($copiedFile)) {
                $canCopy = $this->confirm(
                    'File already exists at '
                    .$copiedFile.
                    ' - do you want to overwrite it?'
                );
            }

            if ($canCopy) {
                $path = pathinfo($copiedFile);

                if (! file_exists($path['dirname'])) {
                    mkdir($path['dirname'], 0755, true);
                }

                if (copy($sourceFile, $copiedFile)) {
                    $this->info('Copied to '.$copiedFile);
                } else {
                    return $this->error(
                        'Failed to copy '
                        .$sourceFile.
                        ' to '
                        .$copiedFile.
                        ' for unknown reason'
                    );
                }
            }
        }
    }

    protected function publishField()
    {
        return $this->processPublish('fields/'.$this->name, 'field');
    }

    protected function publishColumn()
    {
        return $this->processPublish('columns/'.$this->name, 'column');
    }

    protected function publishButton()
    {
        return $this->processPublish('buttons/'.$this->name, 'button');
    }

    protected function publishFilter()
    {
        return $this->processPublish('filters/'.$this->name, 'filter');
    }
}
