<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIProxyService
{
    protected $fastApiUrl;

    public function __construct()
    {
        $this->fastApiUrl = env('FASTAPI_RECOMMEND_URL', 'http://fastapi-recommend:8001');
    }

    public function recommendRooms(string $keywords, int $limit = 5): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->fastApiUrl}/api/recommend", [
                'keywords' => $keywords,
                'limit' => $limit,
            ]);

            $response->throw(); // Throw exception on client/server errors

            return $response->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("AI Recommendation API Error: " . $e->getMessage());
            // Return a default empty array on error
            return ['recommendations' => []];
        } catch (\Exception $e) {
            Log::error("General AIProxyService Error: " . $e->getMessage());
            return ['recommendations' => []];
        }
    }
}
