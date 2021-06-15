<?php

namespace Arbory\Base\Console\Commands;

use Database\Seeders\ArboryDatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SeedCommand.
 */
class SeedCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $name = 'arbory:seed';

    /**
     * @var string
     */
    protected $description = 'Seed the database with initial records for Arbory admin interface';

    /**
     * @var DatabaseManager
     */
    protected $resolver;

    /**
     * @param DatabaseManager $resolver
     */
    public function __construct(DatabaseManager $resolver)
    {
        parent::__construct();

        $this->resolver = $resolver;
    }

    /**
     * @return void
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->resolver->setDefaultConnection($this->getDatabase());

        Model::unguarded(function () {
            $this->getSeeder()->run();
        });
    }

    /**
     * @return Seeder
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function getSeeder()
    {
        $class = $this->laravel->make($this->input->getOption('class'));

        return $class->setContainer($this->laravel)->setCommand($this);
    }

    /**
     * @return string
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');

        return $database ?: $this->laravel['config']['database.default'];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'class',
                null,
                InputOption::VALUE_OPTIONAL, '
                The class name of the root seeder',
                ArboryDatabaseSeeder::class,
            ],

            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }
}
