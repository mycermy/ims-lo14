<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasLocalDates {

    /**
     * Localize a date to users timezone 
     * 
     * @param null $dateField
     * @return Carbon
     */
    public function localize($dateField = null)
    {
        $dateValue = is_null($this->{$dateField}) ? Carbon::now() : $this->{$dateField};
        return $this->inUsersTimezone($dateValue);
    }

    /**
     * Change timezone of a carbon date 
     * 
     * @param $dateValue
     * @return Carbon
     */
    private function inUsersTimezone($dateValue): Carbon
    {
        $timezone = optional(auth()->user())->timezone ?? config('app.timezone');
        return $this->asDateTime($dateValue)->timezone($timezone);
    }
}