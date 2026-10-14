<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Leaving this null makes Laravel ignore X-Forwarded-For entirely, so every
     * audit row behind a load balancer, Nginx or Cloudflare records the proxy's
     * address instead of the client's. Set TRUSTED_PROXIES to the comma
     * separated addresses or CIDR ranges of the proxies in front of this app.
     *
     * Use '*' only when the app is unreachable except through that proxy -
     * otherwise a client can forge its own address by sending the header.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct()
    {
        // Read through config, not env() directly: once config:cache runs the
        // .env file is no longer loaded and env() outside config files is null.
        $proxies = config('audit.trusted_proxies');

        if ($proxies !== null && $proxies !== '') {
            $this->proxies = in_array($proxies, ['*', '**'], true)
                ? $proxies
                : array_map('trim', explode(',', (string) $proxies));
        }
    }
}
