<?php
namespace Parking\Packages\Features\SlackBot;

use Parking\Packages\Features\SlackBot\Commands\AbstractCommand;
use Parking\Packages\Features\SlackBot\Commands\ClaimFreeSpot;
use Parking\Packages\Features\SlackBot\Commands\CommandNotFound;
use Parking\Packages\Features\SlackBot\Commands\ListFreeSpots;
use Parking\Packages\Features\SlackBot\Commands\NotifyFreeSpaceCommand;
use Parking\Packages\Features\SlackBot\Commands\ShowHelp;

/**
 * Class CommandResolver
 *
 * @package Parking\Packages\Features\SlackBot\Commands
 */
class CommandResolver
{
    /**
     * @param string $message
     *
     * @return AbstractCommand
     */
    public function getCommand(string $message): AbstractCommand
    {
        $parts = explode(' ', $message);

        $commandName = strtolower(array_get($parts, 0, null));

        switch ($commandName) {
            case ClaimFreeSpot::NAME:
                return new ClaimFreeSpot();

            case ListFreeSpots::NAME:
                return new ListFreeSpots();

            case NotifyFreeSpaceCommand::NAME:
                return new NotifyFreeSpaceCommand();

            case ShowHelp::NAME:
                return new ShowHelp();

            default:
                return new CommandNotFound();
        }
    }

    /**
     * @return array
     */
    public static function availableCommands(): array
    {
        return [
            NotifyFreeSpaceCommand::class,
            ClaimFreeSpot::class,
            ListFreeSpots::class,
        ];
    }
}
