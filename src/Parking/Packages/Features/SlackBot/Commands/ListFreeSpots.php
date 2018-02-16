<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use Carbon\Carbon;
use Parking\Models\FreeSpot;
use Parking\Models\FreeSpotClaim;
use Parking\Models\Spot;
use PhpSlackBot\Command\BaseCommand;

class ListFreeSpots extends BaseCommand
{
    protected function configure()
    {
        $this->setName('parkbot list');
    }

    protected function execute($message, $context)
    {
        if (isset($message['text'])) {
            $text = str_after($message['text'], $this->getName());
            $parts = array_values(array_filter(explode(' ', $text)));

            $from = $to = null;

            if (count($parts)) {
                /**
                 * parkbot list from 01/01/2018 to 10/01/2018
                 */
                if (array_get($parts, 0) === 'from' && array_get($parts, 2) === 'to') {
                    try {
                        $from = Carbon::createFromFormat('d/m/Y', array_get($parts, 1))->startOfDay();
                        $to   = Carbon::createFromFormat('d/m/Y', array_get($parts, 3))->endOfDay();
                    } catch (\Exception $e) {
                    }
                } else {
                    /**
                     * parkbot list 01/01/2018
                     */
                    try {
                        $from = Carbon::createFromFormat('d/m/Y', array_get($parts, 0))->startOfDay();
                        $to   = $from->copy()->endOfDay();
                    } catch (\Exception $e) {
                    }

                    if (!$from && !$to) {
                        /**
                         * Tries to detect the date from the user input.
                         * Eg: parkbot list next friday
                         */
                        try {
                            $from = (new Carbon(implode(' ', $parts)))->startOfDay();
                            $to   = $from->copy()->endOfDay();
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            /**
             * Lists for today only
             */
            if ((!$from || !$to) && $message['text'] == $this->getName()) {
                $from = Carbon::now()->startOfDay();
                $to   = $from->copy()->endOfDay();
            }

            if (!$from || !$to) {
                $errorMessage = "I could not understand your command. Please use " .
                    "*{$this->getName()} DD/MM/YYYY* or *{$this->getName()} from DD/MM/YYYY to DD/MM/YYYY* " .
                    "or *{$this->getName()} today|tomorrow|next monday|...*";
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $errorMessage);

                return;
            }

            if (($from->lt(Carbon::now()->startOfDay()) || $to->lt(Carbon::now()->startOfDay())) || $to->lt($from)) {
                $errorMessage = "The period you've notified is invalid.";
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $errorMessage);

                return;
            }

            $spotsFree = [];
            for ($now = $from->copy(); $now->lte($to); $now->addDay()) {
                $rows = FreeSpot::query()
                    ->where('date_from', '<=', $now)
                    ->where('date_to', '>=', $now)
                    ->get()
                    ->filter(function ($freeSpot) use ($now) {
                        return !$freeSpot->claims()->where('date_claimed', $now)->exists();
                    });

                if ($rows->isNotEmpty()) {
                    $spotsFree[$now->toDateString()] = [];
                    /** @var FreeSpot $row */
                    foreach ($rows as $row) {
                        $spotsFree[$now->toDateString()][] = $row->spot->owner_user;
                    }
                }
            }

            if (empty($spotsFree)) {
                $message = "Sorry, there's no free parking spot for the period you asked for";
                if ($from->isSameDay($to)) {
                    $message .= " (*{$from->format('d/m/Y')}*).\n";
                } else {
                    $message .= " (*{$from->format('d/m/Y')} to {$to->format('d/m/Y')}*).\n";
                }

                $message .= "Here's an overview of this day:\n";

                if ($from->isSameDay(($to))) {
                    $spots = Spot::all();
                    /** @var Spot $spot */
                    foreach ($spots as $spot) {
                        $freeSpotsIds = $spot->freeSpots()->where('spot_id', $spot->id)->pluck('id')->all();
                        $claim       = FreeSpotClaim::query()
                                                     ->whereIn('free_spot_id', $freeSpotsIds)
                                                     ->where('date_claimed', $from)
                                                     ->first();
                        if ($claim) {
                            $message .= "- <@{$this->getUserIdFromUserName($spot->owner_user)}> - claimed by " .
                                "<@{$this->getUserIdFromUserName($claim->claimer_user)}>\n";
                        } else {
                            $message .= "- <@{$this->getUserIdFromUserName($spot->owner_user)}> - not free\n";
                        }
                    }
                }

                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $message);

                return;
            }

            $message = "Here's the free spots on the period you asked for:\n";
            foreach ($spotsFree as $dateString => $owners) {
                $date = new Carbon($dateString);
                $message .= "- *{$date->format('d/m/Y')}*: ".implode(', ', $owners)."\n";
            }

            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $message);
        }
    }
}