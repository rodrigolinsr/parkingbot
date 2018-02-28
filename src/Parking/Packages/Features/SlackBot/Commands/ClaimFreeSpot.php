<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Carbon\Carbon;
use Parking\Models\FreeSpot;
use Parking\Models\Spot;

/**
 * Class ClaimFreeSpot
 *
 * @package Parking\Packages\Features\SlackBot\Commands
 */
class ClaimFreeSpot extends AbstractCommand
{
    const NAME = 'claim';

    /**
     * @param IncomingMessage|null $message
     */
    public function handle(?IncomingMessage $message)
    {
        $username = $this->getBot()->getUsernameById($message->getSender());

        /** @var Spot $spot */
        $spot = null;

        $spotOwner = '';

        $params = $this->getCommandParams($message->getText());

        if (count($params)) {
            $targetUserId = str_replace(['<', '@', '>'], '', array_get($params, 0));

            $spotOwner = $this->getBot()->getUsernameById($targetUserId);
            $spot      = Spot::where('owner_user', $spotOwner)->first();

            if (!$spot) {
                $this->getBot()->reply("Sorry, I was not able to find a parking spot for ".array_get($params, 0));

                return;
            }

            if ($spot->owner_user === $username) {
                $this->getBot()->reply("You're the owner of this spot already! :smile:");

                return;
            }
        }

        array_shift($params);
        $dates  = $this->parseDates($params);

        /**
         * @var Carbon $from
         * @var Carbon $to
         */
        [
            'from' => $from,
            'to'   => $to,
        ] = $dates;

        if (!$spot || !$from || !$to || !$from->isSameDay($to)) {
            $this->sendDidNotUnderstand();

            return;
        }

        $date = $from->copy();

        /** @var FreeSpot $freeSpot */
        $freeSpot = $spot->freeSpots()->where('date_from', '<=', $date)
                         ->where('date_to', '>=', $date)
                         ->get()
                         ->filter(function ($freeSpot) use ($date) {
                             return !$freeSpot->claims()->where('date_claimed', $date)->exists();
                         })->first();

        if (!$freeSpot) {
            $errorMessage = "Sorry, there's no free spot available for *{$date->format('d/m/Y')}*. :disappointed:";
            $this->getBot()->reply($errorMessage);

            return;
        }

        $freeSpot->claims()->create([
            'claimer_user' => $username,
            'date_claimed' => $date,
        ]);

        $ownerAndDateClaim = "claimed {$spotOwner}'s parking spot for *{$date->format('d/m/Y')}* :parking::red_car:";
        $message = "Yay! You've {$ownerAndDateClaim}!";

        $this->getBot()->reply($message);
    }

    /**
     * @return null|string
     */
    public function getHelp(): ?string
    {
        $helpText  = "*Claim a free spot*\n";
        $helpText .= $this->getUsage();

        return $helpText;
    }

    /**
     * @return string
     */
    protected function getUsage(): string
    {
        return "{$this->getBotUser()} claim @spotowner DD/MM/YYYY|tomorrow|next monday|...";
    }
}
