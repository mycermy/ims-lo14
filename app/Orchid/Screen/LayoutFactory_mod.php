<?php
namespace App\Orchid\Screen;

use App\Orchid\Screen\Layouts\Columns_mod_2col;
use App\Orchid\Screen\Layouts\Metric_mod;
use App\Orchid\Screen\Layouts\Table_mod;
use Orchid\Screen\LayoutFactory;
use Orchid\Screen\Layouts\TabMenu;

class LayoutFactory_mod extends LayoutFactory
{
    /**
     * @param array $labels
     *
     * @return Metric
     */
    public static function metrics(array $labels): Metric_mod
    {
        return new Metric_mod($labels);
    }

    public static function table(string $target, array $columns): Table_mod
    {
        return new class($target, $columns) extends Table_mod
        {
            /**
             * @var array
             */
            protected $columns;

            public function __construct(string $target, array $columns)
            {
                $this->target = $target;
                $this->columns = $columns;
            }

            public function columns(): array
            {
                return $this->columns;
            }
        };
    }

    /**
     * @param array $layouts
     *
     * @return Columns
     */
    public static function columns_2col(array $layouts): Columns_mod_2col
    {
        return new class($layouts) extends Columns_mod_2col
        {
        };
    }

    public static function tabmenu(array $menus): TabMenu
    {
        return new class($menus) extends TabMenu
        {
            /**
             * @var Field[]
             */
            protected $menus;

            /**
             *  constructor.
             */
            public function __construct(array $menus = [])
            {
                $this->menus = $menus;
            }

            public function navigations(): array
            {
                return $this->menus;
            }
        };
    }
}
