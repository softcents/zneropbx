<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterCannedResponse extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_canned_responses';
    protected $primaryKey = 'canned_response_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'canned_response_uuid',
        'domain_uuid',
        'title',
        'shortcut',
        'content',
        'channel',
        'category',
    ];
}
