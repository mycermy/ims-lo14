<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Get Started')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index'))
                ->divider(),

            Menu::make('Products & Services')
                ->icon('bs.card-list')
                ->list([
                    Menu::make('Product Categories')->route('platform.products.categories'),
                    Menu::make('Product List')->route('platform.products'),
                    Menu::make('Stock Adjustments')->route('platform.products.stockadjustments'),
                ])
                ->permission('platform.products.index')
                ->title('Products Management Module')
                ->divider(),

            Menu::make('Purchases')
                ->icon('bs.card-list')
                ->list([
                    Menu::make('Bills')->route('platform.purchases'),
                    Menu::make('Purchase Payments')->route('platform.purchasepayments'),
                    Menu::make('Purchase Returns')->route('platform.purchasereturns'),
                ])
                ->permission('platform.purchases.index')
                ->title('Purchases Management Module')
                ->divider(),
                
            Menu::make('Contacts')
                ->icon('bs.card-list')
                ->permission('platform.contacts.index')
                ->route('platform.contacts')
                ->title('CRM Module'),
                
            Menu::make('Addresses')
                ->icon('bs.card-list')
                ->permission('platform.contacts.index')
                ->route('platform.contacts.addresses')
                ->divider(),
                
            Menu::make('Recycle Bin')
                ->icon('bs.trash')
                ->list([
                    Menu::make('Contacts')->route('platform.deleted.contacts'),
                    Menu::make('Products & Services')->route('platform.deleted.products'),
                    // Menu::make('Product Categories')->route('platform.deleted.products.categories'),
                ])
                ->permission('platform.rbin.index')
                ->divider(),
                
            // Menu::make('Sample Screen')
            //     ->icon('bs.collection')
            //     ->route('platform.example')
            //     ->badge(fn () => 6),

            // Menu::make('Form Elements')
            //     ->icon('bs.card-list')
            //     ->route('platform.example.fields')
            //     ->active('*/examples/form/*'),

            // Menu::make('Overview Layouts')
            //     ->icon('bs.window-sidebar')
            //     ->route('platform.example.layouts'),

            // Menu::make('Grid System')
            //     ->icon('bs.columns-gap')
            //     ->route('platform.example.grid'),

            // Menu::make('Charts')
            //     ->icon('bs.bar-chart')
            //     ->route('platform.example.charts'),

            // Menu::make('Cards')
            //     ->icon('bs.card-text')
            //     ->route('platform.example.cards')
            //     ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users'))
                ->addPermission('platform.systems.editor', __('Editor')),

            ItemPermission::group(__('Products'))
                ->addPermission('platform.products.index', __('Main'))
                ->addPermission('platform.products.inventories', __('Inventories'))
                ->addPermission('platform.products.categories', __('Categories'))
                ->addPermission('platform.products.editor', __('Editor')),

            ItemPermission::group(__('Purchases'))
                ->addPermission('platform.purchases.index', __('Main'))
                ->addPermission('platform.purchases.editor', __('Editor')),

            ItemPermission::group(__('Contacts'))
                ->addPermission('platform.contacts.index', __('Main'))
                ->addPermission('platform.contacts.editor', __('Editor')),

            ItemPermission::group(__('Recycle Bin'))
                ->addPermission('platform.rbin.index', __('Main'))
                ->addPermission('platform.rbin.editor', __('Editor'))
                ->addPermission('platform.rbin.delete', __('Delete'))
                ->addPermission('platform.rbin.delete.contact', __('Delete Contact'))
                ->addPermission('platform.rbin.delete.product', __('Delete Product/Service'))
                ->addPermission('platform.rbin.restore', __('Restore'))
                ->addPermission('platform.rbin.restore.contact', __('Restore Contact'))
                ->addPermission('platform.rbin.restore.product', __('Restore Product/Service')),
            
            // ---
        ];
    }
}
