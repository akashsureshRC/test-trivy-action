<?php

namespace App\Services;

use App\Models\Hrm\EssDeviceToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;

class EssPushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path(config('firebase.projects.app.credentials')));
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send notification to a single employee.
     */
    public function sendToEmployee(int $employeeId, string $title, string $body, array $data = []): array
    {
        $tokens = EssDeviceToken::getActiveTokens($employeeId);
        
        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'No active device tokens found for employee',
            ];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple employees.
     */
    public function sendToEmployees(array $employeeIds, string $title, string $body, array $data = []): array
    {
        $tokens = EssDeviceToken::whereIn('employee_id', $employeeIds)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();
        
        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'No active device tokens found for employees',
            ];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to specific FCM tokens.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $notification = Notification::create($title, $body);
        
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        $failedTokens = [];
        $successCount = 0;

        try {
            $report = $this->messaging->sendMulticast($message, $tokens);
            
            $successCount = $report->successes()->count();
            
            // Handle failed tokens
            foreach ($report->failures()->getItems() as $failure) {
                $failedTokens[] = $failure->target()->value();
                
                // Deactivate invalid tokens
                if ($this->shouldDeactivateToken($failure->error())) {
                    EssDeviceToken::where('fcm_token', $failure->target()->value())
                        ->update(['is_active' => false]);
                }
            }

            return [
                'success' => true,
                'sent' => $successCount,
                'failed' => count($failedTokens),
                'failed_tokens' => $failedTokens,
            ];

        } catch (MessagingException $e) {
            Log::error('Firebase push notification failed', [
                'error' => $e->getMessage(),
                'tokens_count' => count($tokens),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification to a single token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): array
    {
        $notification = Notification::create($title, $body);
        
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data);

        try {
            $this->messaging->send($message);
            
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
            ];

        } catch (MessagingException $e) {
            Log::error('Firebase push notification failed', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...',
            ]);

            // Deactivate invalid token
            if ($this->shouldDeactivateToken($e)) {
                EssDeviceToken::where('fcm_token', $token)
                    ->update(['is_active' => false]);
            }

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if token should be deactivated based on error.
     */
    protected function shouldDeactivateToken($error): bool
    {
        $invalidTokenErrors = [
            'UNREGISTERED',
            'INVALID_ARGUMENT',
            'NOT_FOUND',
        ];

        if ($error instanceof MessagingException) {
            return in_array($error->errors()[0] ?? '', $invalidTokenErrors);
        }

        return false;
    }

    /**
     * Notification types for ESS app.
     */
    public function sendLeaveApprovalNotification(int $employeeId, string $status, string $leaveType): array
    {
        $title = 'Leave Request ' . ucfirst($status);
        $body = "Your {$leaveType} leave request has been {$status}.";
        
        return $this->sendToEmployee($employeeId, $title, $body, [
            'type' => 'leave_status',
            'status' => $status,
        ]);
    }

    public function sendPayslipNotification(int $employeeId, string $month): array
    {
        $title = 'New Payslip Available';
        $body = "Your payslip for {$month} is now available.";
        
        return $this->sendToEmployee($employeeId, $title, $body, [
            'type' => 'payslip',
            'month' => $month,
        ]);
    }

    public function sendAnnouncementNotification(array $employeeIds, string $title, string $message): array
    {
        return $this->sendToEmployees($employeeIds, $title, $message, [
            'type' => 'announcement',
        ]);
    }
}
