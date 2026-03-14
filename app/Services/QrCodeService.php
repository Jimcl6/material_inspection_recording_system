<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserQrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    /**
     * Generate QR code data for user
     */
    public function generateQrData(User $user, string $employeeId, string $employmentStatus): string
    {
        $qrData = [
            'id' => $user->id,
            'employee_id' => $employeeId,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $employmentStatus,
            'generated_at' => now()->toISOString(),
            'signature' => hash_hmac('sha256', $user->id . $employeeId, config('app.key'))
        ];

        return Crypt::encrypt(json_encode($qrData));
    }

    /**
     * Validate and decode QR data
     */
    public function validateQrData(string $qrData): ?array
    {
        try {
            $decrypted = Crypt::decrypt($qrData);
            $data = json_decode($decrypted, true);

            if (!$data || !isset($data['id'], $data['employee_id'], $data['signature'])) {
                Log::warning('Invalid QR data structure', ['qr_data' => $qrData]);
                return null;
            }

            // Verify signature
            $expectedSignature = hash_hmac('sha256', $data['id'] . $data['employee_id'], config('app.key'));
            if (!hash_equals($expectedSignature, $data['signature'])) {
                Log::warning('QR data signature mismatch', ['user_id' => $data['id'] ?? 'unknown']);
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Failed to decode QR data', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find user by QR data
     */
    public function findUserByQrData(string $qrData): ?User
    {
        $data = $this->validateQrData($qrData);
        
        if (!$data) {
            return null;
        }

        $user = User::find($data['id']);
        
        if (!$user || !$user->isActive()) {
            return null;
        }

        // Verify employee ID matches
        if ($user->qrCode && $user->qrCode->employee_id !== $data['employee_id']) {
            Log::warning('Employee ID mismatch', [
                'user_id' => $user->id,
                'stored_id' => $user->qrCode->employee_id,
                'scanned_id' => $data['employee_id']
            ]);
            return null;
        }

        return $user;
    }

    /**
     * Create or update user QR code
     */
    public function createOrUpdateQrCode(
        User $user,
        string $employeeId,
        string $employmentStatus = 'regular',
        ?\DateTime $hireDate = null,
        ?\DateTime $contractEndDate = null
    ): UserQrCode {
        $qrData = $this->generateQrData($user, $employeeId, $employmentStatus);

        return UserQrCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'qr_data' => $qrData,
                'employee_id' => $employeeId,
                'employment_status' => $employmentStatus,
                'hire_date' => $hireDate,
                'contract_end_date' => $contractEndDate,
                'is_active' => true,
                'last_scanned_at' => now(),
            ]
        );
    }

    /**
     * Deactivate user QR code
     */
    public function deactivateQrCode(User $user): bool
    {
        $qrCode = $user->qrCode;
        
        if (!$qrCode) {
            return false;
        }

        return $qrCode->update(['is_active' => false]);
    }

    /**
     * Record QR code scan
     */
    public function recordScan(UserQrCode $qrCode): bool
    {
        return $qrCode->update(['last_scanned_at' => now()]);
    }

    /**
     * Generate QR code image URL (for display)
     */
    public function generateQrImageUrl(string $qrData, int $size = 200): string
    {
        // Using Google Charts API for QR code generation
        // In production, you might want to use a local QR code library
        $encodedData = urlencode($qrData);
        
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encodedData}&choe=UTF-8";
    }

    /**
     * Check if QR code is expired or expiring soon
     */
    public function checkQrCodeStatus(UserQrCode $qrCode): array
    {
        $status = [
            'is_active' => $qrCode->is_active,
            'is_expired' => false,
            'is_expiring_soon' => false,
            'days_until_expiry' => null,
        ];

        if ($qrCode->employment_status === 'contractual' && $qrCode->contract_end_date) {
            $daysUntil = now()->diffInDays($qrCode->contract_end_date, false);
            
            if ($daysUntil < 0) {
                $status['is_expired'] = true;
            } elseif ($daysUntil <= 30) {
                $status['is_expiring_soon'] = true;
            }
            
            $status['days_until_expiry'] = $daysUntil;
        }

        return $status;
    }
}
