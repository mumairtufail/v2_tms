<?php

namespace App\Plugins\QuickBooks\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ApiClient
{
    protected $config;
    protected $baseUrl;
    protected $accessToken;
    protected $realmId;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://sandbox-quickbooks.api.intuit.com', '/');
        $this->accessToken = $config['access_token'] ?? null;
        $this->realmId = $config['realm_id'] ?? null;

        if (!$this->accessToken || !$this->realmId) {
            throw new Exception("QuickBooks Access Token or Realm ID is missing.");
        }
    }

    protected function getUrl($endpoint)
    {
        // QuickBooks API v3 requires minorversion parameter
        // Using version 70 which is the latest stable version
        $url = "{$this->baseUrl}/v3/company/{$this->realmId}/" . ltrim($endpoint, '/');
        
        // Add minorversion parameter
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $url .= $separator . 'minorversion=70';
        
        Log::channel('plugins')->debug("QuickBooks API URL constructed", [
            'endpoint' => $endpoint,
            'full_url' => $url,
            'realm_id' => $this->realmId
        ]);
        
        return $url;
    }

    protected function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function get($endpoint, $params = [])
    {
        Log::channel('plugins')->info("QuickBooks API GET Request: $endpoint", ['params' => $params]);
        
        $response = Http::withoutVerifying()
            ->withHeaders($this->headers())
            ->get($this->getUrl($endpoint), $params);

        return $this->handleResponse($response, $endpoint, $params, 'GET');
    }

    public function post($endpoint, $data = [])
    {
        Log::channel('plugins')->info("QuickBooks API POST Request: $endpoint", ['data' => $data]);

        $response = Http::withoutVerifying()
            ->withHeaders($this->headers())
            ->post($this->getUrl($endpoint), $data);

        return $this->handleResponse($response, $endpoint, $data, 'POST');
    }

    protected function handleResponse($response, $endpoint = null, $params = [], $method = 'GET')
    {
        if ($response->status() === 401) {
            Log::channel('plugins')->warning("QuickBooks API Token Expired. Attempting refresh.");
            
            // Token expired, attempt refresh
            if ($this->refreshToken()) {
                Log::channel('plugins')->info("QuickBooks API Token Refreshed. Retrying request.");
                
                // Retry original request
                if ($method === 'POST') {
                    return $this->post($endpoint, $params);
                } else {
                    return $this->get($endpoint, $params);
                }
            } else {
                Log::channel('plugins')->error("QuickBooks API Token Refresh Failed.");
            }
        }

        if ($response->successful()) {
            Log::channel('plugins')->info("QuickBooks API Response Success", ['status' => $response->status(), 'body' => $response->json()]);
            return $response->json();
        }

        // Handle errors (token expiry, validation, etc.)
        Log::channel('plugins')->error("QuickBooks API Error", [
            'status' => $response->status(),
            'body' => $response->body(),
            'endpoint' => $endpoint
        ]);
        
        // Parse the error to provide a user-friendly message
        $errorBody = $response->json();
        $userMessage = "An error occurred while communicating with QuickBooks.";
        
        // Handle QuickBooks standard fault format
        if (isset($errorBody['fault']['error'][0])) {
            $errorDetail = $errorBody['fault']['error'][0];
            
            // Map common error codes to user-friendly messages
            if (isset($errorDetail['code'])) {
                switch ($errorDetail['code']) {
                    case '3200':
                        $userMessage = "Your QuickBooks session has expired. Please reconnect QuickBooks in the plugin settings.";
                        break;
                    case '6000':
                        $userMessage = "Invalid data was sent to QuickBooks. Please check your information and try again.";
                        break;
                    default:
                        $userMessage = isset($errorDetail['detail']) ? $errorDetail['detail'] : "QuickBooks returned an error. Please check the logs for details.";
                }
            }
        }
        // Handle non-standard error formats (e.g., Spring Boot errors)
        elseif (isset($errorBody['error']) || isset($errorBody['message'])) {
            $errorType = $errorBody['error'] ?? 'Error';
            $errorMessage = $errorBody['message'] ?? 'Unknown error';
            
            // Provide more context based on HTTP status
            if ($response->status() === 404) {
                $userMessage = "QuickBooks API endpoint not found. This may indicate a configuration issue. Please verify your QuickBooks connection settings.";
            } else {
                $userMessage = "QuickBooks error ({$errorType}): {$errorMessage}";
            }
        }
        
        throw new Exception($userMessage);
    }

    protected function refreshToken()
    {
        $tokenUrl = "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer";
        $clientId = $this->config['client_id'] ?? null;
        $clientSecret = $this->config['client_secret'] ?? null;
        $refreshToken = $this->config['refresh_token'] ?? null;
        $configId = $this->config['config_id'] ?? null;

        if (!$clientId || !$clientSecret || !$refreshToken) {
            Log::channel('plugins')->error("QuickBooks Token Refresh: Missing credentials", [
                'has_client_id' => !empty($clientId),
                'has_client_secret' => !empty($clientSecret),
                'has_refresh_token' => !empty($refreshToken)
            ]);
            return false;
        }

        $authHeader = base64_encode("$clientId:$clientSecret");

        Log::channel('plugins')->info("QuickBooks: Attempting token refresh", ['config_id' => $configId]);

        $response = Http::withoutVerifying()
            ->asForm()
            ->withHeaders([
                'Authorization' => "Basic $authHeader",
                'Accept' => 'application/json',
            ])->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        if ($response->successful()) {
            $tokens = $response->json();
            
            Log::channel('plugins')->info("QuickBooks: Token refresh successful");
            
            // Update local state
            $this->accessToken = $tokens['access_token'];
            $this->config['refresh_token'] = $tokens['refresh_token']; // Update refresh token as it might rotate
            
            // Update Database
            if ($configId) {
                $configModel = \App\Models\PluginConfiguration::find($configId);
                if ($configModel) {
                    $newConfig = $configModel->configuration;
                    $newConfig['access_token'] = $tokens['access_token'];
                    $newConfig['refresh_token'] = $tokens['refresh_token'];
                    $configModel->update(['configuration' => $newConfig]);
                    Log::channel('plugins')->info("QuickBooks: Database updated with new tokens");
                }
            }
            
            return true;
        }

        Log::channel('plugins')->error("QuickBooks: Token refresh failed", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return false;
    }
}
