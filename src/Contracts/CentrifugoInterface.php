<?php

declare(strict_types=1);

namespace Alexusmai\Centrifugo\Contracts;

interface CentrifugoInterface
{
    /**
     * Send message into channel.
     *
     * @param  string  $channel
     * @param  array  $data
     * @param  bool  $skipHistory  (optional)
     *
     * @return mixed
     */
    public function publish(string $channel, array $data, bool $skipHistory = false): mixed;

    /**
     * Send message into multiple channel.
     *
     * @param  array  $channels
     * @param  array  $data
     * @param  bool  $skipHistory  (optional)
     *
     * @return mixed
     */
    public function broadcast(array $channels, array $data, bool $skipHistory = false): mixed;

    /**
     * Get channel presence information (all clients currently subscribed to this channel).
     *
     * @param  string  $channel
     *
     * @return mixed
     */
    public function presence(string $channel): mixed;

    /**
     * Get channel presence information in short form (number of clients currently subscribed to this channel).
     *
     * @param  string  $channel
     *
     * @return mixed
     */
    public function presenceStats(string $channel): mixed;

    /**
     * Get channel history information (list of last messages sent into channel).
     *
     * @param  string  $channel
     * @param  int  $limit  (optional)
     * @param  array  $since  (optional)
     * @param  bool  $reverse  (optional)
     *
     * @return mixed
     */
    public function history(string $channel, int $limit = 0, array $since = [], bool $reverse = false): mixed;

    /**
     * Remove channel history information .
     *
     * @param  string  $channel
     *
     * @return mixed
     */
    public function historyRemove(string $channel): mixed;

    /**
     * Subscribe user to channel.
     *
     * @param  string  $channel
     * @param  string  $user
     * @param  string  $client  (optional)
     *
     * @return mixed
     */
    public function subscribe(string $channel, string $user, string $client = ''): mixed;

    /**
     * Unsubscribe user from channel.
     *
     * @param  string  $channel
     * @param  string  $user
     * @param  string  $client  (optional)
     *
     * @return mixed
     */
    public function unsubscribe(string $channel, string $user, string $client = ''): mixed;

    /**
     * Disconnect user by its ID.
     *
     * @param  string  $user_id
     *
     * @return mixed
     */
    public function disconnect(string $user_id, string $client = ''): mixed;

    /**
     * Get channels information (list of currently active channels).
     *
     * @param  string  $pattern  (optional)
     *
     * @return mixed
     */
    public function channels(string $pattern = ''): mixed;

    /**
     * Get stats information about running server nodes.
     *
     * @return mixed
     */
    public function info(): mixed;

    /**
     * Generate connection token.
     *
     * @param  string  $userId
     * @param  int  $exp
     * @param  array  $info
     * @param  array  $channels
     *
     * @return string
     */
    public function generateConnectionToken(
        string $userId = '',
        int $exp = 0,
        array $info = [],
        array $channels = []
    ): string;

    /**
     * Generate private channel token.
     *
     * @param  string  $client
     * @param  string  $channel
     * @param  int  $exp
     * @param  array  $info
     *
     * @return string
     */
    public function generatePrivateChannelToken(
        string $client,
        string $channel,
        int $exp = 0,
        array $info = []
    ): string;
}
