<?php

namespace App\Jobs;

use App\Services\OpenRouterImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateSceneImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $prompt,
        public string $sessionId,
        public string $model = 'bytedance-seed/seedream-4.5'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OpenRouterImageService $imageService): void
    {
        try {
            Log::info('Generating scene image', [
                'session' => $this->sessionId,
                'model' => $this->model,
            ]);

            // Generate the image
            $result = $imageService->generateImage($this->prompt, $this->model);

            // Download and store the image
            $path = $imageService->downloadAndStore($result['url']);

            // Cache the path for the frontend to retrieve
            Cache::put("game_scene_image:{$this->sessionId}", $path, now()->addHours(2));

            Log::info('Scene image generated successfully', [
                'session' => $this->sessionId,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate scene image', [
                'session' => $this->sessionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Scene image generation job failed permanently', [
            'session' => $this->sessionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
