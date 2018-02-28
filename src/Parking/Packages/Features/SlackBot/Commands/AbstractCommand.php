<?php
namespace Parking\Packages\Features\SlackBot\Commands;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Carbon\Carbon;
use Parking\Contracts\Features\SlackBot\BotRunner;

/**
 * Class AbstractCommand
 *
 * @package Parking\Packages\Features\SlackBot\Commands
 */
abstract class AbstractCommand
{
    const NAME = '';

    /**
     * @param IncomingMessage|null $message
     *
     * @return void
     */
    abstract public function handle(?IncomingMessage $message);

    /**
     * @return string
     */
    public function getHelp(): ?string
    {
        return null;
    }

    /**
     * @return null|string
     */
    protected function getUsage(): ?string
    {
        return null;
    }

    /**
     * @return string
     */
    protected function getBotUser(): string
    {
        $botUserId = app(BotRunner::class)->getBotUserId();

        return "<@{$botUserId}>";
    }

    /**
     * @param string $message
     *
     * @return array
     */
    protected function getCommandParams(string $message): array
    {
        $paramsStart = strpos(strtolower($message), static::NAME);

        return array_values(array_filter(explode(' ', substr($message, $paramsStart + strlen(static::NAME)))));
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function parseDates(array $params): array
    {
        $from = $to = null;

        /**
         * @bot command from DD/MM/YYYY to DD/MM/YYYY
         */
        if (array_get($params, 0) === 'from' && array_get($params, 2) === 'to') {
            try {
                $from = Carbon::createFromFormat('d/m/Y', array_get($params, 1))->startOfDay();
                $to   = Carbon::createFromFormat('d/m/Y', array_get($params, 3))->endOfDay();
            } catch (\Exception $e) {
            }
        } else {
            /**
             * @bot command DD/MM/YYYY
             */
            try {
                $from = Carbon::createFromFormat('d/m/Y', array_get($params, 0))->startOfDay();
                $to   = $from->copy()->endOfDay();
            } catch (\Exception $e) {
            }

            if (!$from && !$to) {
                /**
                 * @bot command tomorrow
                 */
                try {
                    $from = (new Carbon(implode(' ', $params)))->startOfDay();
                    $to   = $from->copy()->endOfDay();
                } catch (\Exception $e) {
                }
            }
        }

        return [
            'from' => $from,
            'to'   => $to,
        ];
    }

    protected function sendDidNotUnderstand()
    {
        $message  = "Sorry, I was not able to understand your command.\n\n";
        $message .= $this->getHelp();

        app(BotRunner::class)->reply($message);
    }

    /**
     * @return BotRunner
     */
    protected function getBot(): BotRunner
    {
        return app(BotRunner::class);
    }
}
