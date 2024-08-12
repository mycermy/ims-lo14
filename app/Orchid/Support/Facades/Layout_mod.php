<?php
namespace App\Orchid\Support\Facades;

use Orchid\Screen\LayoutFactory;
use Orchid\Support\Facades\Layout;
use App\Orchid\Screen\LayoutFactory_mod;

class Layout_mod extends Layout
{
    /**
     * Initiate a mock expectation on the facade.
     *
     * @return mixed
     */
    protected static function getFacadeAccessor()
    {
        return LayoutFactory_mod::class;
    }
}
