<?php

namespace App\Jobs;

use App\Models\LoginDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecordLoginDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 15;

    public function __construct(
        protected int $userId,
        protected string $ip,
        protected string $userType,
        protected int $createdBy,
        protected int $workspaceId,
        protected ?string $userAgent = null,
        protected ?string $acceptLanguage = null,
        protected ?string $httpReferer = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // IP geolocation lookup (no longer blocks the login response)
        $details = [];

        try {
            $response = Http::timeout(5)->get('https://ip-api.com/json/' . $this->ip);

            if ($response->successful()) {
                $query = $response->json();
                if (is_array($query) && isset($query['status']) && $query['status'] === 'success') {
                    $details = $query;
                }
            }
        } catch (\Exception $e) {
            Log::debug('RecordLoginDetail: IP geolocation failed', [
                'ip' => $this->ip,
                'error' => $e->getMessage(),
            ]);
        }

        // Browser / device detection
        if ($this->userAgent && class_exists('\WhichBrowser\Parser')) {
            $whichbrowser = new \WhichBrowser\Parser($this->userAgent);
            $details['browser_name'] = $whichbrowser->browser->name ?? null;
            $details['os_name'] = $whichbrowser->os->name ?? null;
        }

        $details['browser_language'] = $this->acceptLanguage
            ? mb_substr($this->acceptLanguage, 0, 2)
            : null;

        $details['device_type'] = $this->userAgent
            ? getDeviceType($this->userAgent)
            : null;

        if ($this->httpReferer) {
            $referrer = parse_url($this->httpReferer);
            $details['referrer_host'] = $referrer['host'] ?? null;
            $details['referrer_path'] = $referrer['path'] ?? null;
        }

        LoginDetail::create([
            'user_id'    => $this->userId,
            'ip'         => $this->ip,
            'date'       => now()->format('Y-m-d H:i:s'),
            'details'    => json_encode($details),
            'type'       => $this->userType,
            'created_by' => $this->createdBy,
            'workspace'  => $this->workspaceId,
        ]);
    }
}
