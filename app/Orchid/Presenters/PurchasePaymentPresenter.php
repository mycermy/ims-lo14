<?php

namespace App\Orchid\Presenters;

use Orchid\Screen\Contracts\Personable;
use Orchid\Support\Presenter;

class PurchasePaymentPresenter extends Presenter implements Personable
{
    /**
     * Returns the title for this presenter, which is displayed in the UI as the main heading.
     */
    public function title(): string
    {
        return $this->entity->reference;
    }

    /**
     * Returns the subtitle for this presenter, which provides additional context about the user.
     */
    public function subTitle(): string
    {
        return $this->entity->note;
    }

    /**
     * Returns the URL for this presenter, which is used to link to the user's edit page.
     */
    public function url(): string
    {
        return route('platform.purchases.payments', $this->entity->purchase);
    }

    /**
     * Returns the URL for the user's Gravatar image, or a default image if one is not found.
     */
    public function image(): ?string
    {
        return false;
    }
}
