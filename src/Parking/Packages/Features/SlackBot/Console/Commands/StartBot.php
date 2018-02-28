<?php
namespace Parking\Packages\Features\SlackBot\Console\Commands;

use Illuminate\Console\Command;
use Parking\Contracts\Features\SlackBot\BotRunner;

/**
 * Class StartBot
 *
 * @package Parking\Packages\SlackBot\Console\Commands
 */
class StartBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slackbot:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spins up the bot';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        app(BotRunner::class)->init()->run();
    }
}