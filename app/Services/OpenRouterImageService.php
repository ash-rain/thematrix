<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterImageService
{
    protected string $apiKey;

    protected string $baseUrl = 'https://openrouter.ai/api/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
    }

    /**
     * Generate an image using OpenRouter's image generation models
     */
    public function generateImage(
        string $prompt,
        string $model = 'bytedance-seed/seedream-4.5',
        ?string $size = null
    ): array {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
                ->timeout(120)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                    'temperature' => 1.0,
                    'max_tokens' => 1024,
                ]);

            if ($response->failed()) {
                Log::error('OpenRouter image generation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException('Image generation failed: '.$response->body());
            }

            $data = $response->json();

            // Extract the image URL from the response
            $content = $data['choices'][0]['message']['images'][0]['image_url']['url'] ?? null;

            if (! $content) {
                throw new \RuntimeException('No image content in response');
            }

            // Handle base64 data URI (e.g. data:image/jpeg;base64,...)
            if (str_starts_with($content, 'data:image/')) {
                return [
                    'url' => $content,
                    'content' => $content,
                    'usage' => $data['usage'] ?? null,
                ];
            }

            // Parse the markdown image format that OpenRouter returns
            // Format: ![image](https://...)
            if (preg_match('/!\[.*?\]\((https?:\/\/[^\)]+)\)/', $content, $matches)) {
                return [
                    'url' => $matches[1],
                    'content' => $content,
                    'usage' => $data['usage'] ?? null,
                ];
            }

            // If the content is just a URL
            if (filter_var($content, FILTER_VALIDATE_URL)) {
                return [
                    'url' => $content,
                    'content' => $content,
                    'usage' => $data['usage'] ?? null,
                ];
            }

            throw new \RuntimeException('Could not extract image URL from response');
        } catch (\Exception $e) {
            Log::error('OpenRouter image generation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Download an image from a URL (or decode a base64 data URI) and store it
     */
    public function downloadAndStore(string $url, string $disk = 'public', ?string $path = null): string
    {
        if (str_starts_with($url, 'data:image/')) {
            preg_match('/^data:image\/(\w+);base64,/', $url, $matches);
            $extension = $matches[1] ?? 'png';
            $imageContent = base64_decode(substr($url, strpos($url, ',') + 1), true);

            if ($imageContent === false) {
                throw new \RuntimeException('Failed to decode base64 image data');
            }
        } else {
            $extension = 'png';
            $imageContent = Http::timeout(30)->get($url)->body();
        }

        $filename = $path ?? 'images/'.uniqid().'.'.$extension;

        \Storage::disk($disk)->put($filename, $imageContent);

        return $filename;
    }
}
