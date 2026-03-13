<?php

namespace App\Console\Commands;

use App\Models\FcmToken;
use App\Services\FcmService;
use Illuminate\Console\Command;

class SendGeneralAnnouncement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-general-announcement {title} {body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a push notification to all users';

    /**
     * Execute the console command.
     */
    public function handle(FcmService $fcmService)
    {
        $title = $this->argument('title');
        $body = $this->argument('body');

        $tokens = FcmToken::pluck('token')->toArray();

        if (empty($tokens)) {
            $this->info("No FCM tokens found.");
            return;
        }

        $this->info("Sending announcement to " . count($tokens) . " tokens...");
        
        $fcmService->sendToMany($tokens, $title, $body, ['type' => 'announcement']);

        $this->info("Done.");
    }
}
