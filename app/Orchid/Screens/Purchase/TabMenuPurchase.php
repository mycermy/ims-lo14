<?php

namespace App\Orchid\Screens\Purchase;

use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Layouts\TabMenu;

class TabMenuPurchase extends TabMenu
{
    /**
     * @var Field[]
     */
    protected $purchase;

    /**
     *  constructor.
     */
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }
    /**
     * Get the menu elements to be displayed.
     *
     * @return Menu[]
     */
    protected function navigations(): iterable
    {
        return [
            Menu::make('Purchase Details')
                ->route('platform.purchases.view', $this->purchase),

            Menu::make('Payments')
                ->route('platform.purchases.payments', $this->purchase),

            Menu::make('Returns')
                ->route('platform.purchases.returns', $this->purchase),
        ];
    }
}
