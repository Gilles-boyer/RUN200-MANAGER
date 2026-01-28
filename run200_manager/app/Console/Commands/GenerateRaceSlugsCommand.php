<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Race;
use Illuminate\Console\Command;

final class GenerateRaceSlugsCommand extends Command
{
    protected $signature = 'races:generate-slugs {--force : Force regeneration of all slugs}';

    protected $description = 'Generate slugs for races that don\'t have one';

    public function handle(): int
    {
        $query = Race::query();

        if (! $this->option('force')) {
            $query->whereNull('slug')->orWhere('slug', '');
        }

        $races = $query->get();

        if ($races->isEmpty()) {
            $this->info('No races need slug generation.');

            return self::SUCCESS;
        }

        $this->info("Generating slugs for {$races->count()} races...");

        $bar = $this->output->createProgressBar($races->count());
        $bar->start();

        foreach ($races as $race) {
            $race->slug = Race::generateUniqueSlug($race->name, $race->id);
            $race->saveQuietly();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! All slugs generated.');

        return self::SUCCESS;
    }
}
