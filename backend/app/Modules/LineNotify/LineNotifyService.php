<?php

namespace App\Modules\LineNotify;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineNotifyService
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $notifyApiUrl = 'https://notify-api.line.me/api/';
    private $oauthApiUrl = 'https://notify-bot.line.me/oauth/';

    public function __construct()
    {
        $this->clientId = env('LINE_NOTIFY_CLIENT_ID');
        $this->clientSecret = env('LINE_NOTIFY_CLIENT_SECRET');
        $this->redirectUri = env('LINE_NOTIFY_REDIRECT_URI');
    }

    public function getAuthUrl(string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'notify',
            'state' => $state,
        ]);

        return $this->oauthApiUrl . 'authorize?' . $query;
    }

    public function getAccessToken(string $code): ?string
    {
        try {
            $response = Http::asForm()->post($this->oauthApiUrl . 'token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            $data = $response->json();
            return $data['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('LINE Notify Access Token Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendMessage(string $accessToken, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->asForm()->post($this->notifyApiUrl . 'notify', [
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('LINE Notify Send Message Error: ' . $e->getMessage());
            return false;
        }
    }

    public function revokeToken(string $accessToken): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->asForm()->post($this->notifyApiUrl . 'revoke');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('LINE Notify Revoke Token Error: ' . $e->getMessage());
            return false;
        }
    }
}
