<?php
 
 namespace App\Services;
 
 use App\Models\FcmToken;
 use App\Models\User;
 use Kreait\Firebase\Factory;
 use Kreait\Firebase\Messaging\CloudMessage;
 use Kreait\Firebase\Messaging\Notification;
 use Kreait\Firebase\Messaging\AndroidConfig;
 use Kreait\Firebase\Exception\Messaging\MessagingException;
 use Kreait\Firebase\Exception\Messaging\NotFound;
 use Kreait\Firebase\Exception\Messaging\AuthenticationError;
 use Illuminate\Support\Facades\Log;
 
 class FcmService
 {
     protected $messaging;
 
     public function __construct()
     {
         $credentialsPath = config('services.firebase.credentials');
 
         if (!file_exists($credentialsPath)) {
             Log::error("Firebase credentials file not found at: {$credentialsPath}");
             return;
         }
 
         $factory = (new Factory)->withServiceAccount($credentialsPath);
         $this->messaging = $factory->createMessaging();
     }
 
     /**
      * Send notification to a specific user (all their tokens).
      */
     public function sendToUser(User $user, string $title, string $body, array $data = []): void
     {
         $tokens = $user->fcmTokens()->pluck('token')->toArray();
         
         if (empty($tokens)) {
             return;
         }
 
         $this->sendToMany($tokens, $title, $body, $data);
     }
 
     /**
      * Send notification to multiple tokens.
      */
     public function sendToMany(array $tokens, string $title, string $body, array $data = []): void
     {
         if (!$this->messaging || empty($tokens)) {
             return;
         }
 
         $notification = Notification::create($title, $body);
         
         // FCM data payload must contain only strings
         $formattedData = array_map(fn($val) => (string)$val, $data);
 
         $message = CloudMessage::new()
             ->withNotification($notification)
             ->withAndroidConfig(
                 AndroidConfig::fromArray([
                     'priority' => 'high',
                     'notification' => [
                         'channel_id' => 'high_importance_channel',
                     ],
                 ])
             )
             ->withData($formattedData);
 
         try {
             $report = $this->messaging->sendMulticast($message, $tokens);
             
             if ($report->hasFailures()) {
                 foreach ($report->failures()->getItems() as $failure) {
                     $token = $failure->target()->value();
                     $reason = $failure->error() ? $failure->error()->getMessage() : 'Unknown error';
                     
                     // If token is invalid or unregistered, remove it
                     if ($failure->messageWasSentToUnknownToken() || $failure->messageTargetWasInvalid()) {
                         FcmToken::where('token', $token)->delete();
                         Log::info("Removed invalid FCM token: {$token}. Reason: {$reason}");
                     } else {
                         Log::warning("FCM send failure for token {$token}: {$reason}");
                     }
                 }
             }
         } catch (MessagingException | AuthenticationError $e) {
             Log::error("Firebase Messaging error: " . $e->getMessage());
         } catch (\Exception $e) {
             Log::error("General error sending FCM: " . $e->getMessage());
         }
     }
 }
