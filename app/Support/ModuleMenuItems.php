<?php

namespace App\Support;

/**
 * Menu entries that optional modules add at request time instead of storing
 * them in v_menu_items. A module registers its entries from its service
 * provider, which only boots while the module is enabled; apply() then adds
 * the entries the user has permission for to the menu shared with the pages.
 */
class ModuleMenuItems
{
    /** Links that identify the Advanced menu in every menu and language. */
    protected const ADVANCED_LINKS = ['/default-settings', '/menus', '/modules'];

    /** @var array<int, array{title: string, link: string, permission: ?string}> */
    protected array $advanced = [];

    /**
     * Add an entry to the Advanced menu for users with $permission. The title
     * is plain English and goes through the shared translation catalog.
     */
    public function addToAdvanced(string $title, string $link, ?string $permission = null): void
    {
        $this->advanced[] = ['title' => $title, 'link' => $link, 'permission' => $permission];
    }

    /**
     * The session menu with the user's module entries added. Works on copies
     * so the menu stored in the session never changes.
     */
    public function apply($menu)
    {
        $entries = collect($this->advanced)
            ->filter(fn (array $item) => ! $item['permission'] || userCheckPermission($item['permission']))
            ->map(fn (array $item) => (object) [
                'menu_item_uuid' => 'module:'.$item['link'],
                'menu_item_title' => __($item['title']),
                'menu_item_link' => $item['link'],
                'menu_item_icon' => null,
                'menu_item_order' => null,
            ])
            ->values();

        if (empty($menu)) {
            $menu = $this->buildFallbackMenu();
        }

        $menu = collect($menu)->map(fn ($top) => clone $top);

        // Add Contact Center navigation menu
        $contactCenterChildren = collect([
            (object) [
                'menu_item_uuid' => 'module:/contact-center/supervisor-command-center',
                'menu_item_title' => __('Supervisor Command Center'),
                'menu_item_link' => '/contact-center/supervisor-command-center',
                'menu_item_icon' => null,
                'menu_item_order' => 1,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/agent-desktop',
                'menu_item_title' => __('Agent Desktop'),
                'menu_item_link' => '/contact-center/agent-desktop',
                'menu_item_icon' => null,
                'menu_item_order' => 2,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/qa-evaluation',
                'menu_item_title' => __('QA & Coaching'),
                'menu_item_link' => '/contact-center/qa-evaluation',
                'menu_item_icon' => null,
                'menu_item_order' => 3,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/workforce-management',
                'menu_item_title' => __('Workforce Management (WFM)'),
                'menu_item_link' => '/contact-center/workforce-management',
                'menu_item_icon' => null,
                'menu_item_order' => 4,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/campaigns',
                'menu_item_title' => __('Campaign & Dialer Studio'),
                'menu_item_link' => '/contact-center/campaigns',
                'menu_item_icon' => null,
                'menu_item_order' => 5,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/routing',
                'menu_item_title' => __('ACD & Skills Routing'),
                'menu_item_link' => '/contact-center/routing',
                'menu_item_icon' => null,
                'menu_item_order' => 6,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/analytics',
                'menu_item_title' => __('Analytics & SLA Reports'),
                'menu_item_link' => '/contact-center/analytics',
                'menu_item_icon' => null,
                'menu_item_order' => 7,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/customer-360',
                'menu_item_title' => __('Customer 360 & Tickets'),
                'menu_item_link' => '/contact-center/customer-360',
                'menu_item_icon' => null,
                'menu_item_order' => 8,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/omnichannel',
                'menu_item_title' => __('Omnichannel Messaging Hub'),
                'menu_item_link' => '/contact-center/omnichannel',
                'menu_item_icon' => null,
                'menu_item_order' => 9,
            ],
            (object) [
                'menu_item_uuid' => 'module:/contact-center/ivr-studio',
                'menu_item_title' => __('Inbound IVR Journey Studio'),
                'menu_item_link' => '/contact-center/ivr-studio',
                'menu_item_icon' => null,
                'menu_item_order' => 10,
            ],
        ]);

        if (! $menu->contains(fn ($top) => ($top->menu_item_title ?? '') === __('Contact Center') || ($top->menu_item_title ?? '') === 'Contact Center')) {
            $menu->push((object) [
                'menu_item_uuid' => 'module:contact-center',
                'menu_item_title' => __('Contact Center'),
                'menu_item_link' => null,
                'menu_item_icon' => null,
                'menu_item_order' => 50,
                'child_menu' => $contactCenterChildren,
            ]);
        }

        // Add Telecom Cloud navigation menu
        $telecomPlatformChildren = collect([
            (object) [
                'menu_item_uuid' => 'module:/telecom/routing-intelligence',
                'menu_item_title' => __('Telecom Routing & LCR'),
                'menu_item_link' => '/telecom/routing-intelligence',
                'menu_item_icon' => null,
                'menu_item_order' => 1,
            ],
            (object) [
                'menu_item_uuid' => 'module:/telecom/stir-shaken',
                'menu_item_title' => __('STIR/SHAKEN Caller Identity'),
                'menu_item_link' => '/telecom/stir-shaken',
                'menu_item_icon' => null,
                'menu_item_order' => 2,
            ],
            (object) [
                'menu_item_uuid' => 'module:/carrier/infrastructure',
                'menu_item_title' => __('Carrier Multi-Node Infrastructure'),
                'menu_item_link' => '/carrier/infrastructure',
                'menu_item_icon' => null,
                'menu_item_order' => 3,
            ],
            (object) [
                'menu_item_uuid' => 'module:/billing/financial-studio',
                'menu_item_title' => __('Financial Rating & Ledger'),
                'menu_item_link' => '/billing/financial-studio',
                'menu_item_icon' => null,
                'menu_item_order' => 4,
            ],
            (object) [
                'menu_item_uuid' => 'module:/messaging/sms-voice-studio',
                'menu_item_title' => __('SMS & Voice Messaging Studio'),
                'menu_item_link' => '/messaging/sms-voice-studio',
                'menu_item_icon' => null,
                'menu_item_order' => 5,
            ],
            (object) [
                'menu_item_uuid' => 'module:/ai/voice-automation',
                'menu_item_title' => __('AI Voice & Neural Agents'),
                'menu_item_link' => '/ai/voice-automation',
                'menu_item_icon' => null,
                'menu_item_order' => 6,
            ],
            (object) [
                'menu_item_uuid' => 'module:/security/fraud-compliance-studio',
                'menu_item_title' => __('Security & Fraud Radar'),
                'menu_item_link' => '/security/fraud-compliance-studio',
                'menu_item_icon' => null,
                'menu_item_order' => 7,
            ],
            (object) [
                'menu_item_uuid' => 'module:/analytics/operations-intelligence',
                'menu_item_title' => __('Operations Intelligence & BI'),
                'menu_item_link' => '/analytics/operations-intelligence',
                'menu_item_icon' => null,
                'menu_item_order' => 8,
            ],
            (object) [
                'menu_item_uuid' => 'module:/api-ecosystem/developer-portal',
                'menu_item_title' => __('Developer Portal & HMAC Webhooks'),
                'menu_item_link' => '/api-ecosystem/developer-portal',
                'menu_item_icon' => null,
                'menu_item_order' => 9,
            ],
            (object) [
                'menu_item_uuid' => 'module:/pbx/advanced-operations',
                'menu_item_title' => __('PBX Fleet & E911 Governance'),
                'menu_item_link' => '/pbx/advanced-operations',
                'menu_item_icon' => null,
                'menu_item_order' => 10,
            ],
            (object) [
                'menu_item_uuid' => 'module:/system/release-readiness',
                'menu_item_title' => __('Release Readiness Hub'),
                'menu_item_link' => '/system/release-readiness',
                'menu_item_icon' => null,
                'menu_item_order' => 11,
            ],
            (object) [
                'menu_item_uuid' => 'module:/reseller/whitelabel-studio',
                'menu_item_title' => __('Reseller & White-Label Studio'),
                'menu_item_link' => '/reseller/whitelabel-studio',
                'menu_item_icon' => null,
                'menu_item_order' => 12,
            ],
        ]);

        if (! $menu->contains(fn ($top) => ($top->menu_item_title ?? '') === __('Telecom Cloud') || ($top->menu_item_title ?? '') === 'Telecom Cloud')) {
            $menu->push((object) [
                'menu_item_uuid' => 'module:telecom-cloud',
                'menu_item_title' => __('Telecom Cloud'),
                'menu_item_link' => null,
                'menu_item_icon' => null,
                'menu_item_order' => 51,
                'child_menu' => $telecomPlatformChildren,
            ]);
        }

        // Reseller & Multi-tier SaaS studio navigation
        $resellerChildren = collect([
            (object) [
                'menu_item_uuid' => 'module:/reseller/whitelabel-brand',
                'menu_item_title' => __('White-Label & Brand Identity'),
                'menu_item_link' => '/reseller/whitelabel-studio?tab=brand',
                'menu_item_icon' => null,
                'menu_item_order' => 1,
            ],
            (object) [
                'menu_item_uuid' => 'module:/reseller/whitelabel-login',
                'menu_item_title' => __('Custom Login Page Studio'),
                'menu_item_link' => '/reseller/whitelabel-studio?tab=login',
                'menu_item_icon' => null,
                'menu_item_order' => 2,
            ],
            (object) [
                'menu_item_uuid' => 'module:/reseller/whitelabel-smtp',
                'menu_item_title' => __('Custom Dedicated SMTP & Email'),
                'menu_item_link' => '/reseller/whitelabel-studio?tab=smtp',
                'menu_item_icon' => null,
                'menu_item_order' => 3,
            ],
            (object) [
                'menu_item_uuid' => 'module:/reseller/whitelabel-sms',
                'menu_item_title' => __('SMS Sender ID & Footers'),
                'menu_item_link' => '/reseller/whitelabel-studio?tab=sms',
                'menu_item_icon' => null,
                'menu_item_order' => 4,
            ],
            (object) [
                'menu_item_uuid' => 'module:/reseller/whitelabel-tenants',
                'menu_item_title' => __('Sub-Resellers & Tenant Quotas'),
                'menu_item_link' => '/reseller/whitelabel-studio?tab=tenants',
                'menu_item_icon' => null,
                'menu_item_order' => 5,
            ],
        ]);

        if (! $menu->contains(fn ($top) => ($top->menu_item_title ?? '') === __('Reseller Studio') || ($top->menu_item_title ?? '') === 'Reseller Studio')) {
            $menu->push((object) [
                'menu_item_uuid' => 'module:reseller-studio',
                'menu_item_title' => __('Reseller Studio'),
                'menu_item_link' => null,
                'menu_item_icon' => null,
                'menu_item_order' => 52,
                'child_menu' => $resellerChildren,
            ]);
        }

        // Master Superadmin Platform Owner Control Suite
        if (function_exists('isSuperAdmin') && isSuperAdmin()) {
            $superadminChildren = collect([
                (object) [
                    'menu_item_uuid' => 'module:/superadmin/control-suite?tab=fleet',
                    'menu_item_title' => __('Fleet & Ghost Impersonate'),
                    'menu_item_link' => '/superadmin/control-suite?tab=fleet',
                    'menu_item_icon' => null,
                    'menu_item_order' => 1,
                ],
                (object) [
                    'menu_item_uuid' => 'module:/superadmin/control-suite?tab=warehouse',
                    'menu_item_title' => __('Global DID Warehouse'),
                    'menu_item_link' => '/superadmin/control-suite?tab=warehouse',
                    'menu_item_icon' => null,
                    'menu_item_order' => 2,
                ],
                (object) [
                    'menu_item_uuid' => 'module:/superadmin/control-suite?tab=trunks',
                    'menu_item_title' => __('Carrier Gateways & SIP Pools'),
                    'menu_item_link' => '/superadmin/control-suite?tab=trunks',
                    'menu_item_icon' => null,
                    'menu_item_order' => 3,
                ],
                (object) [
                    'menu_item_uuid' => 'module:/superadmin/control-suite?tab=packages',
                    'menu_item_title' => __('SaaS Packages & Entitlements'),
                    'menu_item_link' => '/superadmin/control-suite?tab=packages',
                    'menu_item_icon' => null,
                    'menu_item_order' => 4,
                ],
                (object) [
                    'menu_item_uuid' => 'module:/superadmin/control-suite?tab=panic',
                    'menu_item_title' => __('Panic Switches & Fraud Radar'),
                    'menu_item_link' => '/superadmin/control-suite?tab=panic',
                    'menu_item_icon' => null,
                    'menu_item_order' => 5,
                ],
                (object) [
                    'menu_item_uuid' => 'module:/superadmin/control-suite?tab=audit',
                    'menu_item_title' => __('Cross-Tenant Audit Trail'),
                    'menu_item_link' => '/superadmin/control-suite?tab=audit',
                    'menu_item_icon' => null,
                    'menu_item_order' => 6,
                ],
            ]);

            if (! $menu->contains(fn ($top) => ($top->menu_item_title ?? '') === __('Superadmin Suite') || ($top->menu_item_title ?? '') === 'Superadmin Suite')) {
                $menu->push((object) [
                    'menu_item_uuid' => 'module:superadmin-suite',
                    'menu_item_title' => __('Superadmin Suite'),
                    'menu_item_link' => null,
                    'menu_item_icon' => null,
                    'menu_item_order' => 53,
                    'child_menu' => $superadminChildren,
                ]);
            }
        }

        if ($entries->isEmpty()) {
            return $menu;
        }
        $advanced = $menu->first(fn ($top) => collect($top->child_menu ?? [])
            ->contains(fn ($child) => in_array($child->menu_item_link, self::ADVANCED_LINKS, true)));

        if (! $advanced) {
            // No Advanced menu for this user: give the entries their own menu.
            return $menu->push((object) [
                'menu_item_uuid' => 'module:menu',
                'menu_item_title' => $entries->count() === 1 ? $entries->first()->menu_item_title : __('Advanced'),
                'menu_item_link' => null,
                'menu_item_icon' => null,
                'menu_item_order' => null,
                'child_menu' => $entries,
            ]);
        }

        // Skip links an administrator already added to the menu by hand.
        $existing = collect($advanced->child_menu)->pluck('menu_item_link')->all();
        $advanced->child_menu = collect($advanced->child_menu)
            ->concat($entries->reject(fn ($entry) => in_array($entry->menu_item_link, $existing, true)))
            ->sortBy(fn ($child) => mb_strtolower((string) $child->menu_item_title))
            ->values();

        return $menu;
    }

    /**
     * Fallback to construct top-level menu hierarchy from v_menu_items
     * if session menu is empty or uninitialized.
     */
    public function buildFallbackMenu()
    {
        try {
            $menuUuid = \Illuminate\Support\Facades\Session::get('user.menu_uuid');
            if (! $menuUuid) {
                $menuUuid = \Illuminate\Support\Facades\DB::table('v_menus')->where('menu_name', 'default')->value('menu_uuid')
                    ?? \Illuminate\Support\Facades\DB::table('v_menus')->value('menu_uuid');
            }

            if (! $menuUuid) {
                return collect([]);
            }

            $groupUuids = \Illuminate\Support\Facades\Session::get('user.group_uuids', []);

            $topQuery = \Illuminate\Support\Facades\DB::table('v_menu_items')
                ->where('v_menu_items.menu_uuid', '=', $menuUuid)
                ->whereNull('v_menu_items.menu_item_parent_uuid');

            if (! empty($groupUuids)) {
                $topQuery->join('v_menu_item_groups', 'v_menu_item_groups.menu_item_uuid', '=', 'v_menu_items.menu_item_uuid')
                    ->whereIn('v_menu_item_groups.group_uuid', $groupUuids)
                    ->distinct();
            }

            $topItems = $topQuery->orderBy('v_menu_items.menu_item_order')
                ->get([
                    'v_menu_items.menu_item_uuid',
                    'v_menu_items.menu_item_title',
                    'v_menu_items.menu_item_link',
                    'v_menu_items.menu_item_icon',
                    'v_menu_items.menu_item_order',
                ]);

            foreach ($topItems as $item) {
                $childQuery = \Illuminate\Support\Facades\DB::table('v_menu_items')
                    ->where('v_menu_items.menu_uuid', '=', $menuUuid)
                    ->where('v_menu_items.menu_item_parent_uuid', '=', $item->menu_item_uuid);

                if (! empty($groupUuids)) {
                    $childQuery->join('v_menu_item_groups', 'v_menu_item_groups.menu_item_uuid', '=', 'v_menu_items.menu_item_uuid')
                        ->whereIn('v_menu_item_groups.group_uuid', $groupUuids)
                        ->distinct();
                }

                $item->child_menu = $childQuery->orderBy('v_menu_items.menu_item_order')
                    ->orderBy('v_menu_items.menu_item_title')
                    ->get([
                        'v_menu_items.menu_item_uuid',
                        'v_menu_items.menu_item_title',
                        'v_menu_items.menu_item_link',
                        'v_menu_items.menu_item_icon',
                        'v_menu_items.menu_item_order',
                    ]);
            }

            if ($topItems->isNotEmpty()) {
                \Illuminate\Support\Facades\Session::put('menu', $topItems);
            }

            return $topItems;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ModuleMenuItems fallback build failed: '.$e->getMessage());
            return collect([]);
        }
    }
}
