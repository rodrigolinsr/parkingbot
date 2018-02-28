<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Parking\Contracts\Features\SlackBot\BotRunner;
use Parking\Packages\Features\SlackBot\CommandResolver;

/**
 * Class ShowHelp
 *
 * @package Parking\Packages\Features\SlackBot\Commands
 */
class ShowHelp extends AbstractCommand
{
    const NAME = 'help';

    /**
     * @param IncomingMessage|null $message
     */
    public function handle(?IncomingMessage $message)
    {
        $helpText  = "Here's a list of available command I'm able to understand:\n\n";

        foreach (CommandResolver::availableCommands() as $availableCommandClass) {
            /** @var AbstractCommand $availableCommand */
            $availableCommand = (new $availableCommandClass);
            $helpText .= $availableCommand->getHelp()."\n\n";
        }

        app(BotRunner::class)->reply($helpText);
    }
}
