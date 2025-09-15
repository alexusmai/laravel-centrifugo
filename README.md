<h1 align="center">Laravel + Centrifugo 4</h1>
<h2 align="center">Centrifugo broadcast driver for Laravel</h2>

## Introduction

Centrifugo broadcaster for laravel , based on:

- [LaraComponents/centrifugo-broadcaster](https://github.com/LaraComponents/centrifugo-broadcaster)
- [centrifugal/phpcent](https://github.com/centrifugal/phpcent)
- [denis660/laravel-centrifugo](https://github.com/denis660/laravel-centrifugo)

## Features

- Compatible with [Centrifugo 4](https://github.com/centrifugal/centrifugo) 🚀
- Wrapper over [Centrifugo HTTP API](https://centrifugal.dev/docs/server/server_api) 🔌
- Authentication with JWT token (HMAC algorithm) for anonymous, authenticated user and private channel 🗝️

## Requirements

- PHP >= 8.0
- Laravel 9.0 - 12
- guzzlehttp/guzzle 7
- Centrifugo Server 4

## Installation

Require this package with composer:

```bash
composer req alexusmai/laravel-centrifugo
```

Open your config/app.php and add the following to the providers array:

```php
'providers' => [
    // And uncomment BroadcastServiceProvider
    App\Providers\BroadcastServiceProvider::class,
],
```

Open your config/broadcasting.php and add new connection like this:

```php
        'centrifugo' => [
            'driver' => 'centrifugo',
            'token_hmac_secret_key' => env('CENTRIFUGO_TOKEN_HMAC_SECRET_KEY',''),
            'api_key'               => env('CENTRIFUGO_API_KEY',''),
            'url'                   => env('CENTRIFUGO_URL', 'http://localhost:8000'), // centrifugo api url
            'verify'                => env('CENTRIFUGO_VERIFY', false), // Verify host ssl if centrifugo uses this
            'ssl_key'               => env('CENTRIFUGO_SSL_KEY', null), // Self-Signed SSl Key for Host (require verify=true)
            'use_namespace'         => env('CENTRIFUGO_USE_NAMESPACE', false),
            'default_namespace'     => env('CENTRIFUGO_DEFAULT_NAMESPACE', 'default:'),
            'private_namespace'     => env('CENTRIFUGO_PRIVATE_NAMESPACE', 'private:'),
            'presence_namespace'    => env('CENTRIFUGO_PRESENCE_NAMESPACE', 'presence:'),
        ],
```

Also you should add these two lines to your .env file:

```
CENTRIFUGO_TOKEN_HMAC_SECRET_KEY=token_hmac_secret_key-from-centrifugo-config
CENTRIFUGO_API_KEY=api_key-from-centrifugo-config
CENTRIFUGO_URL=http://localhost:8000
```

These lines are optional:

```
CENTRIFUGO_SSL_KEY=/etc/ssl/some.pem
CENTRIFUGO_VERIFY=false
```

[Centrifugo namespaces](https://centrifugal.dev/docs/server/channels)

```
CENTRIFUGO_USE_NAMESPACE=true           // use centrifugo namespaces
CENTRIFUGO_DEFAULT_NAMESPACE=default:   // add to channel name default namespace - default:channel_name
CENTRIFUGO_PRIVATE_NAMESPACE=private:   // change default "private-" laravel prefix to private namespace - private-channel_name -> private:channel_name
CENTRIFUGO_PRESENCE_NAMESPACE=presence: // change default "presence-" laravel prefix to presence namespace - presence-channel_name -> presence:channel_name
```

Don't forget to change `BROADCAST_DRIVER` setting in .env file!

```
BROADCAST_DRIVER=centrifugo
```

## Basic Usage

To configure Centrifugo server, read [official documentation](https://centrifugal.dev)

For broadcasting events, see [official documentation of laravel](https://laravel.com/docs/9.x/broadcasting)

A simple client usage example:

```php
<?php
declare(strict_types = 1);

namespace App\Http\Controllers;


use denis660\Centrifugo\Centrifugo;
use Illuminate\Support\Facades\Auth;

class ExampleController
{

    public function example(Centrifugo $centrifugo)
    {
        // Send message into channel
        $centrifugo->publish('news', ['message' => 'Hello world']);

        // Generate connection token
        $token = $centrifugo->generateConnectionToken((string)Auth::id(), 0, [
            'name' => Auth::user()->name,
        ]);

        // Generate private channel token
        $apiSign = $centrifugo->generatePrivateChannelToken((string)Auth::id(), 'channel', time() + 5 * 60, [
            'name' => Auth::user()->name,
        ]);

        //Get a list of currently active channels.
        $centrifugo->channels();

        //Get channel presence information (all clients currently subscribed on this channel).
        $centrifugo->presence('news');

    }
}
```

### Available methods

| Name                                                                                               | Description                                                                          |
|----------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------|
| publish(string $channel, array $data, $skipHistory = false)                                        | Send message into channel.                                                           |
| broadcast(array $channels, array $data, $skipHistory = false)                                      | Send message into multiple channel.                                                  |
| presence(string $channel)                                                                          | Get channel presence information (all clients currently subscribed on this channel). |
| presenceStats(string $channel)                                                                     | Get channel presence information in short form (number of clients).                  |
| history(string $channel, $limit = 0, $since = [], $reverse = false)                                | Get channel history information (list of last messages sent into channel).           |
| historyRemove(string $channel)                                                                     | Remove channel history information.                                                  |
| subscribe(string $channel,  string $user, $client = '')                                            | subscribe user from channel.                                                         |
| unsubscribe(string $channel, string $user, string $client = '')                                    | Unsubscribe user from channel.                                                       |
| disconnect(string $user_id)                                                                        | Disconnect user by it's ID.                                                          |
| channels(string $pattern = '')                                                                     | Get channels information (list of currently active channels).                        |
| info()                                                                                             | Get stats information about running server nodes.                                    |
| generateConnectionToken(string $userId = '', int $exp = 0, array $info = [], array $channels = []) | Generate connection token.                                                           |
| generatePrivateChannelToken(string $client, string $channel, int $exp = 0, array $info = [])       | Generate private channel token.                                                      |
