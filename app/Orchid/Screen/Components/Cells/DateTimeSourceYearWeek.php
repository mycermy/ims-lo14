<?php

namespace App\Orchid\Screen\Components\Cells;

use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\View\Component;

class DateTimeSourceYearWeek extends Component
{
    /**
     * Create a new component instance.
     *
     * @param float                     $value
     * @param string                    $format
     * @param \DateTimeZone|string|null $tz
     */
    public function __construct(
        protected mixed $value,
        protected string $format = 'W\'y',
        protected DateTimeZone|null|string $tz = null,
    ) {
    }

    /**
     * Get the view/contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return 'ww' . Carbon::parse($this->parseYearWeek($this->value), $this->tz)->translatedFormat($this->format);
        // return Carbon::parse($this->parseYearWeek($this->value), $this->tz)->diffForHumans();
    }

    function parseYearWeek($yearWeek) {
        $year = substr($yearWeek, 0, 4);
        $week = substr($yearWeek, 5);
    
        // Carbon uses weeks starting from 0, so we subtract 1
        $week = $week - 1;
    
        // Create a date corresponding to the 1st day of the year
        $date = Carbon::createFromDate($year, 1, 1);
    
        // Add the number of weeks
        $date->addWeeks($week);
    
        // Get the start of the week
        $date->startOfWeek();
    
        return $date;
    }
}
