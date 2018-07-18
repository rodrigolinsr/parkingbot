<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Carbon\Carbon;
use Parking\Models\Spot;

/**
 * Class NotifyFreeSpaceCommand
 *
 * @package Parking\Packages\Features\SlackBot\Commands
 */
class NotifyFreeSpaceCommand extends AbstractCommand
{
    const NAME = 'free';

    /**
     * @param IncomingMessage|null $message
     */
    public function handle(?IncomingMessage $message)
    {
        $username = $this->getBot()->getUsernameById($message->getSender());

        /** @var Spot $spot */
        $spot = Spot::where('owner_user', $username)->first();

        /**
         * Not able to find any parking spot for the user
         */
        if (empty($spot)) {
            $this->getBot()->reply("You can't use this command because you don't have any parking spot.");

            return;
        }

        $params = $this->getCommandParams($message->getText());
        $dates  = $this->parseDates($params);

        /**
         * @var Carbon $from
         * @var Carbon $to
         */
        [
            'from' => $from,
            'to'   => $to,
        ] = $dates;

        if (!$from || !$to) {
            $this->sendDidNotUnderstand();

            return;
        }

        $now = Carbon::now()->startOfDay();
        if (($from->lt($now) || $to->lt($now)) || $to->lt($from)) {
            $this->getBot()->reply("The period you've informed is invalid.");

            return;
        }

        $alreadyNotified = $spot->freeSpots()
                                ->where(function ($query) use ($from, $to) {
                                    $query->whereBetween('date_from', [$from, $to])
                                          ->orWhereBetween('date_to', [$from, $to]);
                                })->exists();

        if ($alreadyNotified) {
            $this->getBot()->reply("You already have notified your parking spot is free that date.");

            return;
        }

        $spot->freeSpots()->create([
            'date_from' => $from,
            'date_to'   => $to,
        ]);

        if ($from->isSameDay($to)) {
            $message = "Thanks, I've recorded that your parking spot will be free on {$from->format('d/m/Y')}.";
        } else {
            $message = "Thanks, I've recorded that your parking spot will be free " .
                "from {$from->format('d/m/Y')} to {$to->format('d/m/Y')}.";
        }

        $this->getBot()->reply($message);
    }

    /**
     * @return null|string
     */
    public function getHelp(): ?string
    {
        $helpText = "*Notify a parking spot is free*\n";
        $helpText .= $this->getUsage();

        return $helpText;
    }

    /**
     * @return string
     */
    protected function getUsage(): string
    {
        return "{$this->getBotUser()} free DD/MM/YYYY|from DD/MM/YYYY to DD/MM/YYYY|tomorrow|next friday|...";
    }
}
