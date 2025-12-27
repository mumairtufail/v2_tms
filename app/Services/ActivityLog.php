<?php

namespace App\Services;

use App\Models\ActivityLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog
{
    /**
     * Log a user activity.
     *
     * @param string $action
     * @param array $additionalData
     * @return void
     */
    public function log(string $action, array $additionalData = [])
    {
        $user = Auth::user();

        if ($user) {

            $data = is_array($additionalData) ? $additionalData : [];
            // Create a new ActivityLog entry
            ActivityLogs::create([
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::url(),
                'method' => Request::method(),
                'is_successful' => $additionalData['is_successful'] ?? true,
                'data' => json_encode($data),

            ]);
        }
    }


    /**
     * Log profile update.
     *
     * @param array $changes
     * @return void
     */
    public function logProfileUpdate(array $changes)
    {
        $this->log('User updated profile', [
            'changes' => $changes,
        ]);
    }

    // You can add more methods for different types of activities...
}
