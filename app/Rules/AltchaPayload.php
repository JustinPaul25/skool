<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AltchaPayload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('captcha.altcha.enabled')) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail(__('Please complete the security verification.'));

            return;
        }

        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            $fail(__('Security verification failed. Please try again.'));

            return;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($decoded, true);
        if (! is_array($data)) {
            $fail(__('Security verification failed. Please try again.'));

            return;
        }

        $algorithm = (string) ($data['algorithm'] ?? '');
        $challenge = (string) ($data['challenge'] ?? '');
        $salt = (string) ($data['salt'] ?? '');
        $signature = (string) ($data['signature'] ?? '');
        $number = $data['number'] ?? null;

        if ($algorithm !== 'SHA-256' || $challenge === '' || $salt === '' || $signature === '' || ! is_numeric($number)) {
            $fail(__('Security verification failed. Please try again.'));

            return;
        }

        parse_str((string) parse_url($salt, PHP_URL_QUERY), $saltParams);
        $expires = $saltParams['expires'] ?? null;
        if (is_numeric($expires) && (int) $expires < now()->timestamp) {
            $fail(__('Security verification expired. Please retry.'));

            return;
        }

        $computedChallenge = hash('sha256', $salt.(string) $number);
        if (! hash_equals($computedChallenge, $challenge)) {
            $fail(__('Security verification failed. Please try again.'));

            return;
        }

        $expectedSignature = hash_hmac('sha256', $challenge, (string) config('captcha.altcha.hmac_key'));
        if (! hash_equals($expectedSignature, $signature)) {
            $fail(__('Security verification failed. Please try again.'));
        }
    }
}
