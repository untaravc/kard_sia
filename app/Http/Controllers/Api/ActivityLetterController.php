<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityLetter;
use App\Models\Lecture;
use App\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Undangan and Surat Keterangan for referat / seminar kasus activities.
 *
 * These replace the older /sadmin/pdf/* letters in two ways: the signature
 * block carries a QR code instead of a scanned signature image, and every
 * letter is registered in `activity_letters` the first time it is printed.
 * Scanning the QR opens the public route below, which re-renders the exact
 * same document, so a paper copy can always be checked against the system.
 * The first print also mails the signatory, since from their point of view
 * that is the moment their name goes onto a document.
 */
class ActivityLetterController extends Controller
{
    /** Activity categories that may produce these letters. */
    private const LETTER_CATEGORIES = [7, 8];

    private $kps = [
        'name'    => 'Dr.Med. dr. Putrika Prastuti Ratna Gharini, SpJP(K)',
        'nip'     => '197305271999032001',
        'gol'     => 'Penata Tingkat I / III D',
        'jabatan' => 'Ketua Program Studi Jantung dan Pembuluh Darah',
        'email'   => 'vyvy1777@gmail.com',
    ];

    /**
     * Print view, opened from /blu/activities. Registers the letter on the
     * first call and auto-opens the browser print dialog.
     */
    public function print(Request $request, $activity_id, $type)
    {
        $type = $this->normalizeType($type);
        if (!$type) {
            return response('Jenis surat tidak dikenal.', 404);
        }

        $activity = $this->findLetterActivity($activity_id);
        if (!$activity) {
            return response('Kategori aktifitas bukan referat/lapsus.', 404);
        }

        $letter = $this->registerPrint($request, $activity, $type);

        return view($this->viewFor($type), $this->letterData($activity, $letter, true, $request));
    }

    /**
     * Public verification view reached by scanning the QR code. No auth, no
     * print dialog: the same letter, rendered read-only.
     */
    public function viewPublic(Request $request, $token)
    {
        $letter = ActivityLetter::where('token', $token)->first();
        if (!$letter) {
            return response('Surat tidak ditemukan.', 404);
        }

        $activity = $this->findLetterActivity($letter->activity_id);
        if (!$activity) {
            return response('Aktifitas surat ini sudah tidak tersedia.', 404);
        }

        return view($this->viewFor($letter->type), $this->letterData($activity, $letter, false, $request));
    }

    /**
     * Fetch or create the letter row, then count the print. The signatory is
     * mailed only once, on the print that created the row.
     */
    private function registerPrint(Request $request, Activity $activity, $type)
    {
        $letter = ActivityLetter::where('activity_id', $activity->id)
            ->where('type', $type)
            ->first();

        $isFirstPrint = $letter === null;

        if ($isFirstPrint) {
            $letter = new ActivityLetter([
                'activity_id'     => $activity->id,
                'type'            => $type,
                'number'          => $this->letterNumber($activity),
                'token'           => $this->uniqueToken(),
                'signer_name'     => $this->kps['name'],
                'signer_nip'      => $this->kps['nip'],
                'signer_email'    => $this->kps['email'],
                'printed_by_type' => $this->authType($request),
                'printed_by_id'   => $this->authId($request),
                'print_count'     => 0,
                'first_printed_at' => now(),
            ]);
        }

        $letter->print_count = (int) $letter->print_count + 1;
        $letter->last_printed_at = now();
        $letter->save();

        if ($isFirstPrint) {
            $this->notifySigner($activity, $letter);
        }

        return $letter;
    }

    /**
     * Tell the signatory a letter now carries their name. Delivery problems
     * must not stop the print, so failures are swallowed after being logged
     * in mail_logs by sendAndLogMail().
     */
    private function notifySigner(Activity $activity, ActivityLetter $letter)
    {
        $to = $this->kps['email'];
        if (!$to) {
            return;
        }

        $subject = 'Pemberitahuan: Anda baru saja menandatangani ' . $letter->typeLabel();
        $data = [
            'kps'      => $this->kps,
            'activity' => $activity,
            'letter'   => $letter,
            'link'     => $letter->publicUrl(),
        ];

        try {
            $this->sendAndLogMail([
                'name'   => $this->kps['name'],
                'email'  => $to,
                'origin' => config('mail.from.address') ?: env('MAIL_USERNAME'),
                'title'  => $subject,
                'label'  => 'activity_letter_signed',
            ], 'mails.letters.activity_letter_signed', $data, function ($message) use ($to, $subject) {
                $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME');
                $fromName = config('mail.from.name') ?: config('app.name');

                if ($fromAddress) {
                    $message->from($fromAddress, $fromName);
                }

                $message->to($to, $this->kps['name'])->subject($subject);
            });

            $letter->notified_at = now();
            $letter->save();
        } catch (\Throwable $e) {
            // Already recorded as a failed mail_log; the letter still prints.
        }
    }

    /**
     * Everything both blade templates need. `$auto_print` separates the
     * printable copy from the QR verification view.
     */
    private function letterData(Activity $activity, ActivityLetter $letter, $autoPrint, Request $request)
    {
        $data = [
            'activity' => $activity,
            'number'   => $letter->number,
            'type'     => $activity->category == 7 ? 'Seminar Kasus' : 'Referat',
            'date'     => date_indo_str($activity->start_date),
            'day'      => $this->dayName($activity->start_date),
            'time'     => substr((string) $activity->start_date, 11, 5),
        ];

        if ($letter->type === ActivityLetter::TYPE_UNDANGAN) {
            $data['all_staff'] = $this->allStaff($activity, $request->get('dosen_lain'));
        } else {
            $data['pembimbing'] = $this->lecturesFrom($activity->lecture_pembimbing);
            $data['penguji'] = $this->lecturesFrom($activity->lecture_penguji);
        }

        return [
            'data'       => $data,
            'letter'     => $letter,
            'kps'        => $this->kps,
            'logo'       => $this->logoUgm(),
            'qr'         => $this->qrDataUri($letter->publicUrl()),
            'auto_print' => (bool) $autoPrint,
        ];
    }

    private function findLetterActivity($activityId)
    {
        return Activity::whereIn('category', self::LETTER_CATEGORIES)
            ->with('stase')
            ->where('id', $activityId)
            ->first();
    }

    private function normalizeType($type)
    {
        $type = strtolower((string) $type);

        if (in_array($type, ['undangan', 'invitation'], true)) {
            return ActivityLetter::TYPE_UNDANGAN;
        }

        if (in_array($type, ['sk', 'surat-keterangan', 'keterangan'], true)) {
            return ActivityLetter::TYPE_SK;
        }

        return null;
    }

    private function viewFor($type)
    {
        return $type === ActivityLetter::TYPE_SK
            ? 'templates.pdf.activity_sk'
            : 'templates.pdf.activity_undangan';
    }

    /** Same shape as the legacy letters: DDMM/UN1/FKKMK.2/JP.1/AK/YYYY. */
    private function letterNumber(Activity $activity)
    {
        $prefix = substr((string) $activity->start_date, 5, 2) . substr((string) $activity->start_date, 8, 2);

        return $prefix . '/UN1/FKKMK.2/JP.1/AK/' . date('Y');
    }

    private function uniqueToken()
    {
        do {
            $token = Str::lower(Str::random(24));
        } while (ActivityLetter::where('token', $token)->exists());

        return $token;
    }

    private function lecturesFrom($raw)
    {
        $ids = json_decode($raw ?: '[]', true);
        if (!is_array($ids) || !count($ids)) {
            return collect();
        }

        return Lecture::whereIn('id', $ids)->get();
    }

    /**
     * Invitation recipient list: the activity's own lecturers first (marked
     * as Pembimbing), then the department heads, then the remaining in-house
     * staff.
     */
    private function allStaff(Activity $activity, $dosenLain = null)
    {
        $ids = [];
        foreach (['lecture_pembimbing', 'lecture_penguji', 'lecture_pengampu'] as $field) {
            $decoded = json_decode($activity->{$field} ?: '[]', true);
            if (is_array($decoded)) {
                $ids = array_merge($ids, $decoded);
            }
        }
        $ids = array_values(array_unique($ids));

        $main = [];
        foreach (Lecture::whereIn('id', $ids)->pluck('name_alt')->toArray() as $name) {
            if ($name === 'Dosen Lainnya' && $dosenLain) {
                $main[] = $dosenLain . ' (Pembimbing)';
                continue;
            }
            $main[] = $name . ' (Pembimbing)';
        }

        $heads = [
            'Ketua Departemen Kardiologi dan Kedokteran Vaskular',
            'Ketua Program Studi Jantung dan Pembuluh Darah',
        ];

        $rest = Lecture::whereNotIn('id', $ids ?: [0])
            ->where('is_in_house', '=', 1)
            ->pluck('name_alt')
            ->toArray();

        return array_merge($main, $heads, $rest);
    }

    private function dayName($date)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $days[(int) date('w', strtotime($date))];
    }

    private function logoUgm()
    {
        $path = public_path('assets/images/logo-ugm.png');

        return file_exists($path) ? base64_encode(file_get_contents($path)) : '';
    }

    /** QR as a base64 SVG data URI so it also survives a PDF renderer. */
    private function qrDataUri($url)
    {
        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(110)
            ->margin(0)
            ->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function authType(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');

        return $payload
            ? (data_get($payload, 'log_as_auth_type') ?: data_get($payload, 'auth_type'))
            : null;
    }

    private function authId(Request $request)
    {
        $payload = $request->attributes->get('jwt_payload');

        return $payload
            ? (data_get($payload, 'log_as_auth_id') ?: data_get($payload, 'auth_id'))
            : null;
    }

    /**
     * Send a mail through a Blade view while recording the attempt in
     * mail_logs, including the SMTP conversation for later diagnosis.
     */
    private function sendAndLogMail(array $meta, $view, array $data, \Closure $buildMessage)
    {
        $mailLog = MailLog::create(array_merge($meta, [
            'data' => json_encode(['letter_id' => $data['letter']->id ?? null, 'link' => $data['link'] ?? null]),
            'status' => 'pending',
        ]));

        $swiftLogger = new \Swift_Plugins_Loggers_ArrayLogger();
        Mail::getSwiftMailer()->registerPlugin(new \Swift_Plugins_LoggerPlugin($swiftLogger));

        try {
            Mail::send($view, $data, $buildMessage);

            $mailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'response' => $swiftLogger->dump(),
            ]);
        } catch (\Throwable $e) {
            $mailLog->update([
                'status' => 'fail',
                'response' => $swiftLogger->dump() ?: $e->getMessage(),
            ]);

            throw $e;
        }

        return $mailLog;
    }
}
