<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Laravel;

use Illuminate\Support\ServiceProvider;
use Rakibdevs\MrzParser\MrzParser;

class MrzParserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MrzParser::class, static fn () => new MrzParser());
        $this->app->alias(MrzParser::class, 'mrz-parser');
    }
}
