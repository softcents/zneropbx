<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxRecordingLegalHold extends Model
{
    use HasFactory;

    protected $table = 'pbx_recording_legal_holds';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'case_number',
        'case_title',
        'recording_identifier',
        'caller_destination',
        'call_date',
        'custodian_name',
        'legal_officer',
        'reason_notes',
        'status',
        'tamper_checksum_sha256',
    ];

    protected $casts = [
        'call_date' => 'datetime',
    ];
}
