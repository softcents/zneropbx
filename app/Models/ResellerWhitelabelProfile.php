<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResellerWhitelabelProfile extends Model
{
    use HasFactory;

    protected $table = 'reseller_whitelabel_profiles';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'parent_reseller_uuid',
        'reseller_tier',
        'company_name',
        'custom_domain_hostname',
        'logo_url',
        'favicon_url',
        'login_heading',
        'login_subheading',
        'login_accent_color',
        'login_bg_image_url',
        'support_email',
        'support_phone',
        'copyright_text',
        'terms_url',
        'privacy_url',
        'smtp_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
        'sms_sender_id',
        'sms_footer_text',
        'allocated_extensions',
        'allocated_did_numbers',
        'credit_balance',
        'markup_pct',
        'is_active',
    ];

    protected $casts = [
        'smtp_enabled' => 'boolean',
        'is_active' => 'boolean',
        'allocated_extensions' => 'integer',
        'allocated_did_numbers' => 'integer',
        'credit_balance' => 'float',
        'markup_pct' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }

    public function parentReseller()
    {
        return $this->belongsTo(self::class, 'parent_reseller_uuid', 'uuid');
    }

    public function subResellers()
    {
        return $this->hasMany(self::class, 'parent_reseller_uuid', 'uuid');
    }
}
