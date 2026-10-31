<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterAgentDisposition extends Model
{
    use HasFactory, \App\Models\Traits\TraitUuid;

    protected $table = 'contact_center_agent_dispositions';
    protected $primaryKey = 'disposition_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'disposition_uuid',
        'domain_uuid',
        'agent_uuid',
        'xml_cdr_uuid',
        'caller_number',
        'disposition_code',
        'customer_notes',
        'follow_up_at',
        'sentiment',
        'created_by',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }

    public function cdr()
    {
        return $this->belongsTo(CDR::class, 'xml_cdr_uuid', 'xml_cdr_uuid');
    }
}
