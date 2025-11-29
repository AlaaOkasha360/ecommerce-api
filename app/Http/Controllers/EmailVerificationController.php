<?php

namespace App\Http\Controllers;

use App\HttpResponses;
use App\Models\User;
use App\Notifications\VerifyEmailApi;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    use HttpResponses;

    public function emailVerify(Request $request, $id, $hash)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return $this->success([], "Email already verified.");
        }

        // Verify the hash matches
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->error(null, 'Invalid verification link.', 403);
        }

        // Verify the signature and expiration
        if (!$request->hasValidSignature()) {
            return $this->error(null, 'Verification link expired or invalid.', 403);
        }

        // Mark as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->success([], "Email verified successfully.");
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $this->error(null, 'Email already verified.', 400);
        }

        $user->notify(new VerifyEmailApi());

        return $this->success([], 'Verification email sent.');
    }
}
