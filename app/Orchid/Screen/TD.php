<?php

declare(strict_types=1);

namespace App\Orchid\Screen;

use Orchid\Screen\Cell;
use Orchid\Screen\Field;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\NumberRange;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\Concerns\ComplexFieldConcern;
use Orchid\Screen\TD as ScreenTD;

class TD extends ScreenTD //Cell
{
    /**
     * Builds content for the column.
     *
     * @param Repository|Model $repository
     *
     * @return Factory|View
     */
    public function buildTd($repository, ?object $loop = null)
    {
        $value = $this->render ? $this->handler($repository, $loop) : $repository->getContent($this->name);

        return view('partials.layouts.td', [
            'align'   => $this->align,
            'value'   => $value,
            'render'  => $this->render,
            'slug'    => $this->sluggable(),
            'width'   => is_numeric($this->width) ? $this->width.'px' : $this->width,
            'style'   => $this->style,
            'class'   => $this->class,
            'colspan' => $this->colspan,
        ]);
    }

    /**
     * Builds an item menu for show/hiden column.
     *
     * @return Factory|View|null
     */
    public function buildItemMenu()
    {
        if (! $this->isAllowUserHidden()) {
            return;
        }

        return view('platform::partials.layouts.selectedTd', [
            'title'         => $this->title,
            'slug'          => $this->sluggable(),
            'defaultHidden' => var_export($this->defaultHidden, true),
        ]);
    }
}
