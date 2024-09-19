<?php

namespace App\Orchid\Screens\Sales;

use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Layouts\TabMenu;

class TabMenuOrder extends TabMenu
{
    /**
     * @var Field[]
     */
    protected $parameter;

    /**
     *  constructor.
     */
    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }
    /**
     * Get the menu elements to be displayed.
     *
     * @return Menu[]
     */
    protected function navigations(): iterable
    {
        return [
            Menu::make('Order Details')
                ->route('platform.orders.view', $this->parameter),

            Menu::make('Payments')
                ->route('platform.orders.payments', $this->parameter),

            Menu::make('Returns')
                ->route('platform.orders.returns', $this->parameter),
        ];
    }
}
