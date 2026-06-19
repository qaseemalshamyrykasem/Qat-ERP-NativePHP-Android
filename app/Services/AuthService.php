<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class AuthService
{
    public function login(string $username, string $password): array
    {
        if ($this->isLoginLocked($username)) {
            $remaining = $this->getLockoutRemaining($username);
            $minutes = (int) ceil($remaining / 60);
            $this->logActivity('محاولة دخول مقفلة', 'auth', null, 'اسم المستخدم: ' . $username);
            $this->recordLoginAttempt($username, false);
            return [
                'success' => false,
                'message' => 'تم قفل الحساب بسبب محاولات فاشلة متعددة. يرجى المحاولة بعد ' . $minutes . ' دقيقة',
            ];
        }

        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'يرجى إدخال اسم المستخدم وكلمة المرور'];
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->recordLoginAttempt($username, false);
            $remaining = $this->getRemainingAttempts($username);
            if ($remaining <= 0) {
                return ['success' => false, 'message' => 'تم قفل الحساب بسبب محاولات فاشلة متعددة'];
            }
            return ['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة. محاولات متبقية: ' . $remaining];
        }

        if (! $user->status) {
            return ['success' => false, 'message' => 'هذا الحساب معطل. يرجى التواصل مع الإدارة'];
        }

        $this->clearFailedAttempts($username);

        auth()->login($user, true);
        session()->regenerate();

        $user->update(['last_login' => now()]);

        $this->logActivity('تسجيل دخول', 'auth', (int) $user->id, 'تسجيل دخول ناجح');

        return ['success' => true, 'message' => 'تم تسجيل الدخول بنجاح', 'user' => $user];
    }

    public function logout(): void
    {
        $userId = auth()->id();
        $this->logActivity('تسجيل خروج', 'auth', $userId, 'تسجيل خروج');
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    /**
     * Issue a Sanctum token for API/mobile clients.
     */
    public function issueApiToken(User $user, string $deviceName = 'mobile'): array
    {
        $token = $user->createToken($deviceName, ['*'])->plainTextToken;
        return ['token' => $token, 'user' => $user];
    }

    public function isLoginLocked(string $username): bool
    {
        $ip = Request::ip() ?? '0.0.0.0';
        $lockoutSecs = config('qat.login_lockout_minutes') * 60;
        $threshold = now()->subSeconds($lockoutSecs);
        $attempts = LoginAttempt::where('username', $username)
            ->where('ip_address', $ip)
            ->where('attempted_at', '>', $threshold)
            ->where('success', false)
            ->count();
        return $attempts >= config('qat.login_max_attempts');
    }

    public function getRemainingAttempts(string $username): int
    {
        $ip = Request::ip() ?? '0.0.0.0';
        $lockoutSecs = config('qat.login_lockout_minutes') * 60;
        $threshold = now()->subSeconds($lockoutSecs);
        $attempts = LoginAttempt::where('username', $username)
            ->where('ip_address', $ip)
            ->where('attempted_at', '>', $threshold)
            ->where('success', false)
            ->count();
        return max(0, config('qat.login_max_attempts') - $attempts);
    }

    public function getLockoutRemaining(string $username): int
    {
        $ip = Request::ip() ?? '0.0.0.0';
        $last = LoginAttempt::where('username', $username)
            ->where('ip_address', $ip)
            ->where('success', false)
            ->orderByDesc('attempted_at')
            ->first();
        if (! $last) return 0;
        $lockoutSecs = config('qat.login_lockout_minutes') * 60;
        return max(0, $lockoutSecs - (time() - $last->attempted_at->timestamp));
    }

    private function recordLoginAttempt(string $username, bool $success): void
    {
        LoginAttempt::create([
            'username'     => $username,
            'ip_address'   => Request::ip() ?? '0.0.0.0',
            'success'      => $success,
            'attempted_at' => now(),
        ]);
    }

    private function clearFailedAttempts(string $username): void
    {
        $ip = Request::ip() ?? '0.0.0.0';
        $lockoutSecs = config('qat.login_lockout_minutes') * 60;
        $threshold = now()->subSeconds($lockoutSecs);
        LoginAttempt::where('username', $username)
            ->where('ip_address', $ip)
            ->where('success', false)
            ->where('attempted_at', '>', $threshold)
            ->delete();
    }

    private function logActivity(string $action, string $module, ?int $recordId = null, ?string $details = null): void
    {
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'details'    => $details,
            'ip_address' => Request::ip(),
        ]);
    }
}
