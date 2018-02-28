<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Carbon\Carbon;
use Parking\Models\FreeSpot;
use Parking\Models\FreeSpotClaim;
use Parking\Models\Spot;

class ListFreeSpots extends AbstractCommand
{
    const NAME = 'list';

    /**
     * @param IncomingMessage|null $message
     */
    public function handle(?IncomingMessage $message)
    {
        $now = Carbon::now()->startOfDay();

        $params = $this->getCommandParams($message->getText());

        if (empty($params)) {
            $dates = [
                'from' => $now,
                'to'   => $now->copy()->endOfDay(),
            ];
        } else {
            $dates = $this->parseDates($params);
        }

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

        if ($to->lt($from)) {
            $this->getBot()->reply("The period you've informed is invalid.");

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
                    $spotsFree[$now->toDateString()][] = $row->spot->description;
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
                        $message .= "- {$spot->description} - claimed by " .
                            "{$claim->claimer_user}\n";
                    } else {
                        $message .= "- {$spot->description} - not free\n";
                    }
                }
            }

            $this->getBot()->reply($message);

            return;
        }

        $message = "Here's the free spots on the period you asked for:\n";
        foreach ($spotsFree as $dateString => $owners) {
            $date = new Carbon($dateString);
            $message .= "- *{$date->format('d/m/Y')}*: ".implode(', ', $owners)."\n";
        }

        $this->getBot()->reply($message);
    }

    /**
     * @return null|string
     */
    public function getHelp(): ?string
    {
        $helpText  = "*List free spots*\n";
        $helpText .= $this->getUsage();

        return $helpText;
    }

    /**
     * @return string
     */
    protected function getUsage(): ?string
    {
        return "{$this->getBotUser()} list DD/MM/YYYY|from DD/MM/YYYY to DD/MM/YYYY|tomorrow|next friday|...";
    }
}