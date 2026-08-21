<?php

namespace App\Services\Attendance;

use App\Models\Lecture;
use Illuminate\Support\Str;

/**
 * Time-based rotating attendance codes (RFC 4226 style) used to prove that a
 * student and a lecturer were physically together when an assessment took
 * place.
 *
 * The lecturer's screen shows a code derived from their own secret plus the
 * current 30-second window; the student relays it back (by scanning the QR,
 * which is just a URL carrying the code, or by typing the digits). Because
 * the code is derived rather than stored, a screenshot is worthless after
 * ~60 seconds, and the lecturer's `link_token` — which is a long-lived
 * bearer credential for the public scoring flow — is never exposed.
 */
class AttendanceCodeService
{
    /** Length of one rotation window, in seconds. */
    public const PERIOD = 30;

    private const DIGITS = 6;

    /** Windows of clock skew accepted on either side of "now". */
    private const SKEW = 1;

    public function generate(Lecture $lecture, ?int $timestamp = null): string
    {
        return $this->derive($this->secretFor($lecture), $this->counter($timestamp));
    }

    /**
     * True when $code matches the lecturer's code for the current window
     * (or an adjacent one, to tolerate clock drift between devices).
     */
    public function verify(Lecture $lecture, string $code): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $secret = $this->secretFor($lecture);
        $counter = $this->counter();

        for ($offset = -self::SKEW; $offset <= self::SKEW; $offset++) {
            if (hash_equals($this->derive($secret, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /** Seconds until the current code rotates. */
    public function secondsRemaining(): int
    {
        return self::PERIOD - (time() % self::PERIOD);
    }

    /**
     * Deep link encoded in the QR. Pointing at a URL (rather than the bare
     * code) means the phone's built-in camera can handle the scan and open
     * the app directly — no in-app scanner or camera permission needed.
     */
    public function deepLink(string $code): string
    {
        return rtrim((string) env('APP_URL'), '/')
            . '/blu/dashboard-student/scoring/confirm?c=' . $code;
    }

    /**
     * Per-lecturer secret. `link_token` is generated on demand for lecturers
     * that don't have one yet (the public scoring flow does the same), and is
     * combined with the app key so a leaked token alone cannot be used to
     * predict future codes.
     */
    private function secretFor(Lecture $lecture): string
    {
        if (!$lecture->link_token) {
            $lecture->link_token = Str::random(17);
            $lecture->save();
        }

        return $lecture->link_token . '|' . config('app.key');
    }

    private function counter(?int $timestamp = null): int
    {
        return intdiv($timestamp ?? time(), self::PERIOD);
    }

    private function derive(string $secret, int $counter): string
    {
        $hash = hash_hmac('sha256', (string) $counter, $secret, true);

        // Dynamic truncation: the low nibble of the last byte picks a 4-byte
        // window, whose top bit is masked off to keep the value positive.
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad(
            (string) ($binary % (10 ** self::DIGITS)),
            self::DIGITS,
            '0',
            STR_PAD_LEFT
        );
    }
}
