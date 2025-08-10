<?php

namespace App\Jobs;

use App\Models\Shortlink;
use App\Models\ShortlinkEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecordShortlinkHit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $shortlinkId, public array $payload)
    {
        $this->onQueue('shortlinks');
    }

    public function handle(): void
    {
        // Insert event
        ShortlinkEvent::create(array_merge($this->payload, [
            'shortlink_id' => $this->shortlinkId,
            'clicked_at' => now(),
        ]));

        // Increment counter atomically
        Shortlink::where('id', $this->shortlinkId)->update([
            'clicks' => DB::raw('clicks + 1')
        ]);
    }
}
