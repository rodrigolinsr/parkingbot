<?php
namespace Parking\Packages\Features\SlackBot\Providers;

use App\Providers\PackageServiceProvider;
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
}