<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\ResellerWhitelabelProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;

class WhiteLabelService
{
    /**
     * Resolve the active white-label branding profile for the current HTTP request.
     */
    public function current(): array
    {
        $profile = null;
        $host = request()->getHost();

        // 1. Try finding by custom domain hostname
        if ($host) {
            $profile = ResellerWhitelabelProfile::where('custom_domain_hostname', $host)
                ->where('is_active', true)
                ->first();
        }

        // 2. Try finding by domain_name in v_domains
        if (!$profile && $host) {
            $domain = Domain::where('domain_name', $host)->first();
            if ($domain) {
                $profile = ResellerWhitelabelProfile::where('domain_uuid', $domain->domain_uuid)
                    ->where('is_active', true)
                    ->first();
            }
        }

        // 3. If authenticated or session exists, check session domain
        if (!$profile) {
            $domainUuid = session('domain_uuid') ?? auth()->user()?->domain_uuid;
            if ($domainUuid) {
                $profile = ResellerWhitelabelProfile::where('domain_uuid', $domainUuid)
                    ->where('is_active', true)
                    ->first();
            }
        }

        // 4. If found profile has a parent reseller and missing fields, cascade
        if ($profile && $profile->parent_reseller_uuid) {
            $parent = $profile->parentReseller;
            if ($parent) {
                $profile->logo_url = $profile->logo_url ?: $parent->logo_url;
                $profile->favicon_url = $profile->favicon_url ?: $parent->favicon_url;
                $profile->support_email = $profile->support_email ?: $parent->support_email;
                $profile->support_phone = $profile->support_phone ?: $parent->support_phone;
                $profile->copyright_text = $profile->copyright_text ?: $parent->copyright_text;
                $profile->login_accent_color = $profile->login_accent_color ?: $parent->login_accent_color;
            }
        }

        // 5. Construct standardized response (fallback to defaults if no profile)
        return [
            'uuid' => $profile?->uuid,
            'is_whitelabel_active' => $profile !== null,
            'reseller_tier' => $profile?->reseller_tier ?? 'platform_root',
            'company_name' => $profile?->company_name ?? config('app.name', 'ZneroPBX'),
            'logo_url' => $profile?->logo_url ?: '/storage/logo.png',
            'favicon_url' => $profile?->favicon_url ?: '/storage/favicon-32x32.png',
            'login_heading' => $profile?->login_heading ?: 'Sign in to your account',
            'login_subheading' => $profile?->login_subheading ?: 'Enterprise Cloud Telephony & Contact Center',
            'login_accent_color' => $profile?->login_accent_color ?: '#4f46e5',
            'login_bg_image_url' => $profile?->login_bg_image_url,
            'support_email' => $profile?->support_email ?: 'support@zneropbx.com',
            'support_phone' => $profile?->support_phone ?: '+1 (800) 555-0199',
            'copyright_text' => $profile?->copyright_text ?: ('© ' . date('Y') . ' ' . config('app.name', 'ZneroPBX') . '. All rights reserved.'),
            'terms_url' => $profile?->terms_url ?: '#',
            'privacy_url' => $profile?->privacy_url ?: '#',
            'smtp_enabled' => (bool) ($profile?->smtp_enabled ?? false),
            'smtp_from_address' => $profile?->smtp_from_address,
            'smtp_from_name' => $profile?->smtp_from_name,
            'sms_sender_id' => $profile?->sms_sender_id ?: 'ZNERO_AUTH',
            'sms_footer_text' => $profile?->sms_footer_text,
            'allocated_extensions' => $profile?->allocated_extensions ?? 0,
            'allocated_did_numbers' => $profile?->allocated_did_numbers ?? 0,
            'credit_balance' => (float) ($profile?->credit_balance ?? 0.0),
            'markup_pct' => (float) ($profile?->markup_pct ?? 20.0),
        ];
    }

    /**
     * Get active SMTP configuration for a given domain (or its parent reseller).
     */
    public function getSmtpConfigForDomain(?string $domainUuid): ?array
    {
        if (!$domainUuid) return null;

        $profile = ResellerWhitelabelProfile::where('domain_uuid', $domainUuid)
            ->where('is_active', true)
            ->first();

        // If child profile doesn't have SMTP enabled, cascade to parent reseller
        if ((!$profile || !$profile->smtp_enabled) && $profile?->parent_reseller_uuid) {
            $parent = $profile->parentReseller;
            if ($parent && $parent->smtp_enabled) {
                $profile = $parent;
            }
        }

        if (!$profile || !$profile->smtp_enabled || empty($profile->smtp_host)) {
            return null;
        }

        return [
            'host' => $profile->smtp_host,
            'port' => $profile->smtp_port ?: 587,
            'username' => $profile->smtp_username,
            'password' => $profile->smtp_password,
            'encryption' => $profile->smtp_encryption ?: 'tls',
            'from_address' => $profile->smtp_from_address,
            'from_name' => $profile->smtp_from_name ?: $profile->company_name,
        ];
    }

    /**
     * Test live SMTP connection by sending a diagnostic test email.
     */
    public function testSmtpConnection(array $config, string $toEmail): array
    {
        try {
            $host = $config['host'];
            $port = (int) ($config['port'] ?? 587);
            $user = $config['username'] ?? '';
            $pass = $config['password'] ?? '';
            $scheme = ($config['encryption'] === 'ssl') ? 'smtps' : 'smtp';

            $dsn = new Dsn($scheme, $host, $user, $pass, $port);
            $factory = new EsmtpTransportFactory();
            $transport = $factory->create($dsn);
            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from(new \Symfony\Component\Mime\Address($config['from_address'], $config['from_name'] ?: 'White-Label PBX'))
                ->to($toEmail)
                ->subject('✓ White-Label SMTP Verification Test — ' . date('Y-m-d H:i:s'))
                ->html('<div style="font-family:sans-serif;padding:20px;border:1px solid #e5e7eb;border-radius:8px;">
                    <h2 style="color:#4f46e5;">White-Label SMTP Connection Verified</h2>
                    <p>Congratulations! Your dedicated white-label SMTP outgoing mail server is functioning properly with zero vendor exposure.</p>
                    <p style="font-size:12px;color:#6b7280;">Host: ' . htmlspecialchars($host) . ':' . $port . ' | From: ' . htmlspecialchars($config['from_address']) . '</p>
                </div>');

            $mailer->send($email);

            return [
                'success' => true,
                'message' => 'Test email successfully sent to ' . $toEmail . ' via ' . $host . ':' . $port,
            ];
        } catch (\Throwable $e) {
            Log::error('WhiteLabel SMTP test error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMTP verification failed: ' . $e->getMessage(),
            ];
        }
    }
}
