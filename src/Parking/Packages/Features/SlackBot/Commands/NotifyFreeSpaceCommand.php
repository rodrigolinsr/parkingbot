<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use Parking\Models\Spot;
use PhpSlackBot\Command\BaseCommand;

class NotifyFreeSpaceCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('parkingspot-free');
    }

    /**
     * @param $message
     * @param $context
     */
    protected function execute($message, $context)
    {
        $userId = $this->getCurrentUser();
        $username = $this->getUserNameFromUserId($userId);
        $userImId = $this->getImIdFromUserId($userId);

        $spot = Spot::where('owner_user', $username)->first();

        /**
         * Not able to find any parking spot for the user
         */
        if (empty($spot)) {
            $this->send($userImId, null,
                "You can't use this command because you don't have any parking spot.");

            return;
        }

        $parts = [];
        if (isset($message['text'])) {
            $parts = array_values(array_filter(explode(' ', $message['text'])));
            dd($parts);
        }

        $this->send($this->getCurrentChannel(), $this->getCurrentUser(),
            'No comprendo. Use "'.$this->getName().' start" or "'.$this->getName().' status"');

//        $this->getUserNameFromUserId($this->getCurrentUser());

//        $this->send(null, '@rodrigolinsr',
//            'Test. Use "'.$this->getName().' start" or "'.$this->getName().' status"');
    }

}