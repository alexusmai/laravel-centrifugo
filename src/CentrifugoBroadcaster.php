<?php

declare(strict_types=1);

namespace Alexusmai\Centrifugo;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CentrifugoBroadcaster extends Broadcaster
{
    /**
     * The Centrifugo SDK instance.
     *
     * @var Contracts\CentrifugoInterface
     */
    protected $centrifugo;

    /**
     * Create a new broadcaster instance.
     *
     * @param  Centrifugo  $centrifugo
     */
    public function __construct(Centrifugo $centrifugo)
    {
        $this->centrifugo = $centrifugo;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  Request  $request
     *
     * @return mixed
     */
    public function auth($request): mixed
    {
        $channelName = $this->normalizeChannelName($request->channel);

        if (empty($channelName) ||
            ($this->isGuardedChannel($request->channel) &&
                !$this->retrieveUser($request, $channelName))) {
            throw new AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request, $channelName
        );
    }

    /**
     * Return the valid authentication response.
     *
     * @param  Request  $request
     * @param  mixed  $result
     *
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result): mixed
    {
        $channelName = $this->normalizeChannelName($request->channel);
        $user = $this->retrieveUser($request, $channelName);

        return json_encode([
            'data' => [
                'channel' => $request->channel,
                'token'   => $this->centrifugo->generatePrivateChannelToken(
                    (string) $user->id,
                    $request->channel,
                    0,
                    $request->get('info', [])
                ),
            ]
        ]);
    }

    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     *
     * @return void
     * @throws GuzzleException
     */
    public function broadcast(array $channels, $event, array $payload = []): void
    {
        if (empty($channels)) {
            return;
        }

        $payload['event'] = $event;

        if (config('broadcasting.connections.centrifugo.use_namespace')) {
            $channels = array_map(function ($channel) {

                if (Str::startsWith($channel, 'private-')) {
                    return str_replace(
                        'private-',
                        config('broadcasting.connections.centrifugo.private_namespace'),
                        (string) $channel
                    );
                }

                if (Str::startsWith($channel, 'presence-')) {
                    return str_replace(
                        'presence-',
                        config('broadcasting.connections.centrifugo.presence_namespace'),
                        (string) $channel
                    );
                }

                return config('broadcasting.connections.centrifugo.default_namespace').$channel;
            }, array_values($channels));
        }

        $response = $this->centrifugo->broadcast($this->formatChannels($channels), $payload);

        if (is_array($response) && !isset($response['error'])) {
            return;
        }

        throw new BroadcastException(
            $response['error'] instanceof Exception ? $response['error']->getMessage() : $response['error']
        );
    }

    /**
     * @param $channel
     *
     * @return bool
     */
    protected function isGuardedChannel($channel): bool
    {
        if (config('broadcasting.connections.centrifugo.use_namespace')) {
            return Str::startsWith($channel, [
                config('broadcasting.connections.centrifugo.private_namespace'),
                config('broadcasting.connections.centrifugo.presence_namespace')
            ]);
        }

        return Str::startsWith($channel, ['private-', 'presence-']);
    }

    /**
     * @param $channel
     *
     * @return string
     */
    protected function normalizeChannelName($channel): string
    {
        if (config('broadcasting.connections.centrifugo.use_namespace')) {
            $namespaces = [
                config('broadcasting.connections.centrifugo.default_namespace'),
                config('broadcasting.connections.centrifugo.private_namespace'),
                config('broadcasting.connections.centrifugo.presence_namespace')
            ];

            foreach ($namespaces as $prefix) {
                if (Str::startsWith($channel, $prefix)) {
                    return Str::replaceFirst($prefix, '', $channel);
                }
            }

            return $channel;
        }

        foreach (['private-encrypted-', 'private-', 'presence-'] as $prefix) {
            if (Str::startsWith($channel, $prefix)) {
                return Str::replaceFirst($prefix, '', $channel);
            }
        }

        return $channel;
    }
}
