<?php
namespace Parking\Packages\Features\SlackBot\BotMan;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Parking\Contracts\Features\SlackBot\BotRunner as BotRunnerContract;
use Parking\Packages\Features\SlackBot\CommandResolver;

/**
 * Class MessagesListener
 *
 * @package Parking\Packages\Features\SlackBot\BotMan
 */
class MessagesListener
{
    /** @var BotMan */
    protected $botMan;

    /**
     * MessagesListener constructor.
     *
     * @param BotMan $botMan
     */
    public function __construct(BotMan $botMan)
    {
        $this->botMan = $botMan;
    }

    /**
     * @return void
     */
    public function attachListener()
    {
        $this->botMan->fallback(function (BotMan $bot) {
            /**
             * When receiving edited messages, the bot should not react
             */
            if ($this->isMessageEdit($bot->getMessage())) {
                return;
            }

            /**
             * Only listen to messages that starts with the bot user (mention)
             */
            if ($this->isBotMention($bot->getMessage())) {
                /**
                 * Removes the bot name from the message
                 */
                $messageFiltered = trim(Str::after($bot->getMessage()->getText(), $this->getBotUserIdWrapped()));

                $command = (new CommandResolver())->getCommand($messageFiltered);
                $command->handle($bot->getMessage());
            }
        });
    }

    /**
     * @param IncomingMessage $message
     *
     * @return bool
     */
    protected function isMessageEdit(IncomingMessage $message): bool
    {
        /** @var Collection $payload */
        $payload = $message->getPayload();

        return $payload->has('previous_message');
    }

    /**
     * @param IncomingMessage $message
     *
     * @return bool
     */
    protected function isBotMention(IncomingMessage $message): bool
    {
        return Str::startsWith($message->getText(), $this->getBotUserIdWrapped());
    }

    /**
     * @return string
     */
    protected function getBotUserIdWrapped(): string
    {
        $botUserId = app(BotRunnerContract::class)->getBotUserId();

        return "<@{$botUserId}>";
    }
}
