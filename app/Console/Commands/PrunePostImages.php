<?php

namespace App\Console\Commands;

use App\Models\PostImage;
use Illuminate\Console\Command;

class PrunePostImages extends Command
{
    protected $signature = 'posts:prune-images {--hours=24 : Wiek nieprzypisanego obrazu}';

    protected $description = 'Usuwa nieprzypisane obrazy przesłane do edytora postów';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $deleted = 0;

        PostImage::query()
            ->whereNull('post_id')
            ->where('created_at', '<', now()->subHours($hours))
            ->lazyById()
            ->each(function (PostImage $image) use (&$deleted): void {
                $image->delete();
                $deleted++;
            });

        $this->info("Usunięto nieużywane obrazy: {$deleted}.");

        return self::SUCCESS;
    }
}
