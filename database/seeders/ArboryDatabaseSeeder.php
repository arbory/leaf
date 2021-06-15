<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\DatabaseManager;
use Waavi\Translation\Repositories\LanguageRepository;

class ArboryDatabaseSeeder extends Seeder
{
    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    public function __construct(
        LanguageRepository $languageRepository,
        DatabaseManager $databaseManager
    ) {
        $this->languageRepository = $languageRepository;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedLocales();
    }

    /**
     * @return void
     */
    protected function seedLocales()
    {
        if ($this->languageRepository->getModel()->all()->isEmpty()) {
            $this->languageRepository->create([
                'locale' => 'en',
                'name' => 'English',
            ]);
        }
    }
}
