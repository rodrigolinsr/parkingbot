<?php
namespace Parking\Packages\Features\SlackBot\Console\Commands;

use Illuminate\Console\Command;
use Parking\Packages\Features\SlackBot\Commands\NotifyFreeSpaceCommand;
use PhpSlackBot\Bot;

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
        $bot = new Bot();
        $bot->setToken(env('SLACK_API_KEY'));
        $bot->loadInternalCommands();
        $bot->loadCommand(new NotifyFreeSpaceCommand());

        try {
            $bot->run();
        } catch (\Exception $e) {
            die($e);
        }
    }
}