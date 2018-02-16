<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use Carbon\Carbon;
use Parking\Models\FreeSpot;
use Parking\Models\Spot;
use PhpSlackBot\Command\BaseCommand;

class ClaimFreeSpot extends BaseCommand
{
    protected function configure()
    {
        $this->setName('parkbot claim');
    }

    /**
     * @param $message
     * @param $context
     */
    protected function execute($message, $context)
    {
        $userId   = $this->getCurrentUser();
        $username = $this->getUserNameFromUserId($userId);
        $userImId = $this->getImIdFromUserId($userId);

        /** @var Spot $spot */
        $spot = null;

        /** @var Carbon $date */
        $date = null;

        $spotOwner = '';

        if (isset($message['text'])) {
            $text = str_after($message['text'], $this->getName());
            $parts = array_values(array_filter(explode(' ', $text)));

            if (count($parts)) {
                $targetUserId = str_replace(['<', '@', '>'], '', array_get($parts, 0));

                $spotOwner = $this->getUserNameFromUserId($targetUserId);
                $spot = Spot::where('owner_user', str_replace('@', '', $spotOwner))->first();

                if (!$spot) {
                    $this->send($this->getCurrentChannel(), $this->getCurrentUser(),
                        "Sorry, I was not able to find a parking spot for {$spotOwner}");

                    return;
                }

                if ($spot->owner_user === $username) {
                    $this->send($userImId, null,
                        "You're the owner of this spot already! :smile:");

                    return;
                }

                /**
                 * parkbot claim @zainab 01/01/2018
                 */
                $date = null;
                try {
                    $date = Carbon::createFromFormat('d/m/Y', array_get($parts, 1))->startOfDay();
                } catch (\Exception $e) {
                }

                if (!$date) {
                    /**
                     * Tries to detect the date from the user input.
                     * Eg: parkbot claim @zainab next friday
                     */
                    try {
                        $expression = trim(str_replace(array_get($parts, 0), '', implode(' ', $parts)));
                        $date = (new Carbon($expression))->startOfDay();
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        if (!$spot || !$date) {
            $errorMessage = "I could not understand your command. Please use " .
                "*{$this->getName()} @spotowner DD/MM/YYYY*";
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $errorMessage);

            return;
        }

        /** @var FreeSpot $freeSpot */
        $freeSpot = $spot->freeSpots()->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->get()
            ->filter(function ($freeSpot) use ($date) {
                return !$freeSpot->claims()->where('date_claimed', $date)->exists();
            })->first();

        if (!$freeSpot) {
            $errorMessage = "Sorry, there's no free spot available for *{$date->format('d/m/Y')}*. :disappointed:";
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), $errorMessage);

            return;
        }

        $freeSpot->claims()->create([
            'claimer_user' => $username,
            'date_claimed' => $date,
        ]);

        $ownerAndDateClaim = "claimed {$spotOwner}'s parking spot for *{$date->format('d/m/Y')}* :parking::red_car:";
        $message = "Yay! You've {$ownerAndDateClaim}!";
        $this->send($userImId, null, $message);

        $channelId = $this->getChannelIdFromChannelName(env('SLACK_NOTIFY_CHANNEL', 'general'));
        $message = "<!here> FYI, <@{$userId}> {$ownerAndDateClaim}";
        $this->send($channelId, null, $message);
    }
}
