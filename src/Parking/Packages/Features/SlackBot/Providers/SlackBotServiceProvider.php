<?php
namespace Parking\Packages\Features\SlackBot\Providers;

use App\Providers\PackageServiceProvider;
use Parking\Contracts\Features\SlackBot\BotRunner as BotRunnerContract;
use Parking\Packages\Features\SlackBot\BotMan\BotRunner;
use Parking\Packages\Features\SlackBot\Console\Commands\StartBot;

/**
 * Class SlackBotServiceProvider
 *
 * @package Parking\Packages\Features\SlackBot\Providers
 */
class SlackBotServiceProvider extends PackageServiceProvider
{
    protected $commands = [
        StartBot::class,
    ];

    public function register()
    {
        parent::register();

        $this->app->singleton(BotRunnerContract::class, BotRunner::class);
    }
}
