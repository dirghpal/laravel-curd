<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken($data['device_name'] ?? 'api-client')->plainTextToken;

        return $this->respondSuccess([
            'user' => $user,
            'token' => $token,
        ], 'Registration successful.', 201, ['token_type' => 'Bearer']);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return $this->respondError('Invalid credentials.', 401);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api-client')->plainTextToken;

        return $this->respondSuccess(
            ['token' => $token],
            'Authentication successful.',
            200,
            ['token_type' => 'Bearer']
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        auth('sanctum')->forgetUser();

        return $this->respondSuccess(null, 'Successfully logged out.');
    }

    public function me(Request $request)
    {
        return $this->respondSuccess($request->user(), 'Authenticated user retrieved successfully.');
    }

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->respondSuccess(null, 'Email address is already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->respondSuccess(null, 'Verification email sent.');
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->respondError('Invalid verification link.', 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->respondSuccess(null, 'Email address verified successfully. You can now use the API.');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return $this->respondSuccess(null, 'If the email address exists, a password reset link has been sent.');
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->respondError(__($status), 422);
        }

        return $this->respondSuccess(null, 'Password reset successfully. Please log in again.');
    }

    public function tokens(Request $request)
    {
        $currentTokenId = $request->user()->currentAccessToken()?->id;

        $tokens = $request->user()->tokens()
            ->latest()
            ->get(['id', 'name', 'last_used_at', 'created_at', 'expires_at'])
            ->map(fn ($token) => [
                'id' => $token->id,
                'device_name' => $token->name,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                'expires_at' => $token->expires_at,
                'is_current' => $token->id === $currentTokenId,
            ]);

        return $this->respondSuccess($tokens, 'Active devices retrieved successfully.');
    }

    public function revokeToken(Request $request, int $token)
    {
        $accessToken = $request->user()->tokens()->find($token);

        if (! $accessToken) {
            return $this->respondError('Device token not found.', 404);
        }

        $accessToken->delete();

        return $this->respondSuccess(null, 'Device logged out successfully.');
    }
}
