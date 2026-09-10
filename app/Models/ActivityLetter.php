<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One signed letter (undangan / surat keterangan) issued for an activity.
 *
 * The row is created the first time somebody prints the letter and is what
 * makes the printed copy verifiable: its `token` is the only thing encoded
 * in the QR code stamped where a scanned signature used to sit, and the
 * public route resolves that token back to the very same document.
 */
class ActivityLetter extends Model
{
    public const TYPE_UNDANGAN = 'undangan';
    public const TYPE_SK = 'sk';

    protected $fillable = [
        'activity_id',
        'type',
        'number',
        'token',
        'signer_name',
        'signer_nip',
        'signer_email',
        'printed_by_type',
        'printed_by_id',
        'print_count',
        'first_printed_at',
        'last_printed_at',
        'notified_at',
    ];

    protected $dates = [
        'first_printed_at',
        'last_printed_at',
        'notified_at',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /** Public URL the QR code points at. */
    public function publicUrl()
    {
        return rtrim((string) config('app.url'), '/') . '/surat-kegiatan/' . $this->token;
    }

    public function typeLabel()
    {
        return $this->type === self::TYPE_SK ? 'Surat Keterangan' : 'Undangan';
    }
}
