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

        if (! $menu) {
            return $menu;
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
}
