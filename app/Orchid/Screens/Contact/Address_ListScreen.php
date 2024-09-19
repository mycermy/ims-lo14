<?php

namespace App\Orchid\Screens\Contact;

use App\Models\Contact\Address;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Address_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => Address::filters()->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Address_ListScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('model', [
                TD::make('id', '#')->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('contact_id', 'CID')->filter()->sort(),
                TD::make('type')->filter()->sort(),
                TD::make('name'),
                TD::make('phone'),
                TD::make('address_street_1', 'Address 1'),
                TD::make('address_street_2', 'Address 2'),
                TD::make('city'),
                TD::make('state'),
                TD::make('zip'),
                TD::make('fax'),
                TD::make('updated_by', 'Updated By')->render(fn($target) => $target->updatedBy->name ?? null),
            ]),
        ];
    }
}
