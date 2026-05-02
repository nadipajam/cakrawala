<?php

namespace App\Services;

use App\Models\LoginOtpCode;
use App\Models\User;
use App\Notifications\LoginOtpCodeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginOtpService
{
    /**
     * @return array{identifier:string,channel:string,user:\App\Models\User}
     */
    public function sendCode(string $rawIdentifier): array
    {
        [$user, $identifier, $channel] = $this->resolveUserAndChannel($rawIdentifier);

        $ttlMinutes = (int) config('services.login_otp.ttl_minutes', 5);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($user, $identifier, $channel, $code, $ttlMinutes): void {
            LoginOtpCode::query()
                ->where('user_id', $user->id)
                ->where('identifier', $identifier)
                ->where('channel', $channel)
                ->active()
                ->update(['consumed_at' => now()]);

            LoginOtpCode::create([
                'user_id' => $user->id,
                'identifier' => $identifier,
                'channel' => $channel,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes($ttlMinutes),
                'meta' => [
                    'otp_last4' => substr($code, -4),
                ],
            ]);
        });

        $user->notify(new LoginOtpCodeNotification($code, $ttlMinutes));

        return [
            'identifier' => $identifier,
            'channel' => $channel,
            'user' => $user,
        ];
    }

    public function verifyCode(string $rawIdentifier, string $rawCode): User
    {
        [, $identifier, $channel] = $this->resolveUserAndChannel($rawIdentifier);

        $otp = LoginOtpCode::query()
            ->with('user')
            ->where('identifier', $identifier)
            ->where('channel', $channel)
            ->latest()
            ->first();

        if (! $otp || $otp->consumed_at || $otp->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Kode OTP sudah tidak berlaku. Minta kode baru.'],
            ]);
        }

        if ((int) $otp->attempts >= (int) config('services.login_otp.max_attempts', 5)) {
            throw ValidationException::withMessages([
                'code' => ['Batas percobaan OTP terlampaui. Minta kode baru.'],
            ]);
        }

        if (! Hash::check($rawCode, $otp->code_hash)) {
            $otp->increment('attempts');

            throw ValidationException::withMessages([
                'code' => ['Kode OTP tidak sesuai.'],
            ]);
        }

        $otp->update([
            'consumed_at' => now(),
        ]);

        return $otp->user;
    }

    /**
     * @return array{0:\App\Models\User,1:string,2:string}
     */
    protected function resolveUserAndChannel(string $rawIdentifier): array
    {
        $email = mb_strtolower(trim($rawIdentifier));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['Akun dengan email tersebut tidak ditemukan.'],
            ]);
        }

        return [$user, $email, 'email'];
    }
}
