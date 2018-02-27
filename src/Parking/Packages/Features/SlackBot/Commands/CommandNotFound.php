<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Parking\Contracts\Features\SlackBot\BotRunner;

/**
 * Class CommandNotFound
 *
 * @package Parking\Packages\Features\SlackBot\Commands
 */
class CommandNotFound extends AbstractCommand
{
    /**
     * @param IncomingMessage|null $message
     */
    public function handle(?IncomingMessage $message)
    {
        app(BotRunner::class)->reply('Sorry mate, could not understand your command. :confused: ' .
            "Type *{$this->getBotUser()} help* to learn how to use me :simple_smile:");
    }
}
