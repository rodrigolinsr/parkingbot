<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use Carbon\Carbon;
use Parking\Models\Spot;
use PhpSlackBot\Command\BaseCommand;

class NotifyFreeSpaceCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('parkbot free');
    }

    /**
     * @param $message
     * @param $context
     */
    protected function execute($message, $context)
    {
        $userId   = $this->getCurrentUser();
        $username = $this->getUserNameFromUserId($userId);

        /** @var Spot $spot */
        $spot = Spot::where('owner_user', $username)->first();

        /**
         * Not able to find any parking spot for the user
         */
        if (empty($spot)) {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(),
                "You can't use this command because you don't have any parking spot.");

            return;
        }

        $from = null;
        $to   = null;
        if (isset($message['text'])) {
            $text = str_after($message['text'], $this->getName());
            $parts = array_values(array_filter(explode(' ', $text)));

            if (count($parts)) {
                /**
                 * parkbot free from 01/01/2018 to 10/01/2018
                 */
                if (array_get($parts, 0) === 'from' && array_get($parts, 2) === 'to') {
                    try {
                        $from = Carbon::createFromFormat('d/m/Y', array_get($parts, 1))->startOfDay();
                        $to   = Carbon::createFromFormat('d/m/Y', array_get($parts, 3))->endOfDay();
                    } catch (\Exception $e) {
                    }
                } else {
                    /**
                     * parkbot free 01/01/2018
                     */
                    try {
                        $from = Carbon::createFromFormat('d/m/Y', array_get($parts, 0))->startOfDay();
                        $to   = $from->copy()->endOfDay();
                    } catch (\Exception $e) {
                    }

                    if (!$from && !$to) {
                        /**
                         * Tries to detect the date from the user input.
                         * Eg: parkbot free next friday
                         */
                        try {
                            $from = (new Carbon(implode(' ', $parts)))->startOfDay();
                            $to   = $from->copy()->endOfDay();
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }

        if (!$from || !$to) {
            $errorMessage = "I could not understand your command. Please use " .
                "*{$this->getName()} DD/MM/YYYY* or *{$this->getName()} from DD/MM/YYYY to DD/MM/YYYY* " .
                "or *{$this->getName()} today|tomorrow|next monday|...*";;
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $errorMessage);

            return;
        }

        if (($from->lt(Carbon::now()) || $to->lt(Carbon::now())) || $to->lt($from)) {
            $errorMessage = "The period you've notified is invalid.";
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $errorMessage);

            return;
        }

        $alreadyNotified = $spot->freeSpots()->whereBetween('date_from', [$from, $to])
            ->orWhereBetween('date_to', [$from, $to])
            ->exists();

        if ($alreadyNotified) {
            $message = "You already have notified your parking spot is free that date.";
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $message);

            return;
        }

        $spot->freeSpots()->create([
            'date_from' => $from,
            'date_to' => $to,
        ]);

        if ($from->isSameDay($to)) {
            $message = "Thanks, I've recorded that you're parking spot will be free on {$from->format('d/m/Y')}.";
        } else {
            $message = "Thanks, I've recorded that you're parking spot will be free " .
                "from {$from->format('d/m/Y')} to {$to->format('d/m/Y')}.";
        }

        $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $message);
    }
}