<?php
namespace Parking\Contracts\Features\SlackBot;

/**
 * Interface BotRunner
 *
 * @package Parking\Contracts\Features\SlackBot
 */
interface BotRunner
{
    /**
     * Initialises and config the bot
     *
     * @return BotRunner
     */
    function init(): BotRunner;

    /**
     * Run the bot. This will probably be a infinite loop (websocket).
     *
     * @return void
     */
    function run();

    /**
     * Replies to a command sent previously
     *
     * @param string $message
     *
     * @return void
     */
    function reply(string $message);

    /**
     * Gets the bot user id given by Slack
     *
     * @return string
     */
    function getBotUserId(): string;

    /**
     * @param string $userId
     *
     * @return null|string
     */
    function getUsernameById(string $userId): ?string;
}