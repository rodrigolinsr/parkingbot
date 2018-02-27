<?php
namespace Parking\Packages\Features\SlackBot\BotMan;

use BotMan\BotMan\BotMan;
use BotMan\Drivers\Slack\Extensions\User;
use Illuminate\Support\Collection;
use React\EventLoop\Factory;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Slack\SlackRTMDriver;
use React\EventLoop\StreamSelectLoop;
use Parking\Contracts\Features\SlackBot\BotRunner as Contract;

/**
 * Class BotRunner
 *
 * @package Parking\Packages\Features\SlackBot\BotMan
 */
class BotRunner implements Contract
{
    /** @var Collection */
    protected $users;

    /** @var StreamSelectLoop */
    protected $loop;

    /** @var BotMan */
    protected $botMan;

    /**
     * @inheritdoc
     */
    public function init(): Contract
    {
        $this->loop   = Factory::create();
        $this->botMan = $this->createBotMan();

        /** @var SlackRTMDriver $driver */
        $driver = $this->botMan->getDriver();
        $driver->getClient()->on('hello', function () {
            $this->loadUsers();
        });

        (new MessagesListener($this->botMan))->attachListener();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->loop->run();
    }

    /**
     * @inheritdoc
     */
    public function reply(string $message)
    {
        $this->botMan->reply($message);
    }

    /**
     * @inheritdoc
     */
    public function getBotUserId(): string
    {
        return $this->botMan->getDriver()->getBotUserId();
    }

    function getUsernameById(string $userId): ?string
    {
        /** @var \Slack\User $slackUser */
        $slackUser = $this->users->get($userId);

        return $slackUser->getUsername();
    }

    /**
     * @return BotMan
     */
    protected function createBotMan(): BotMan
    {
        $config = [
            'slack' => [
                'token' => env('SLACK_API_KEY'),
            ],
        ];

        // Load driver
        DriverManager::loadDriver(SlackRTMDriver::class);

        return BotManFactory::createForRTM($config, $this->loop);
    }

    protected function loadUsers()
    {
        $this->users = new Collection();

        /** @var $user User */
        $user = $this->botMan->getUser();

        $user->getClient()->getUsers()->then(function ($users) {
            /** @var \Slack\User $user */
            foreach ($users as $user) {
                $this->users->put($user->getId(), $user);
            }
        });
    }
}
