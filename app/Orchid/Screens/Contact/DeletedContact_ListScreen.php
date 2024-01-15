<?php

namespace App\Orchid\Screens\Contact;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class DeletedContact_ListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'model' => 
                Contact::onlyTrashed()->filters()
                    ->defaultSort('name', 'asc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Deleted Contacts';
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
            Layout::table('model',[
                TD::make('id', '#')->render(fn ($target, object $loop) => $loop->iteration + (request('page') > 0 ? (request('page') - 1) * $target->getPerPage() : 0)),
                TD::make('type')->filter()->sort(),
                TD::make('name'),
                TD::make('email'),
                TD::make('phone'),
                TD::make('Actions')
                ->canSee(Auth::user()->hasAnyAccess(['platform.systems.editor','platform.contacts.editor']))
                ->width('10px')
                ->render(
                    fn ($target) =>
                    $this->getTableActions($target)
                        ->alignCenter()
                        ->autoWidth()
                        ->render()
                ),
            ]),
        ];
    }

    /**
     * @param Model $model
     *
     * @return Group
     */
    private function getTableActions($target): Group
    {
        return Group::make([

            DropDown::make()
                ->icon('three-dots-vertical')
                ->list([
                    // Link::make(__('View'))
                    //     ->icon('eye')
                    //     // ->canSee($this->can('view'))
                    //     ->route('platform.sales.customers.view', $target),

                    Link::make(__('Edit'))
                        ->icon('pencil')
                        // ->canSee($this->can('update'))
                        ->route('platform.contacts.edit', $target),

                    // Button::make(__('Delete'))
                    //     ->icon('bs.trash3')
                    //     ->confirm(__('Once the contact is deleted, all of its resources and data will be permanently deleted. Before deleting your contact, please download any data or information that you wish to retain.'))
                    //     ->method('remove', [
                    //         'id' => $target->id,
                    //     ])
                    //     ->canSee(!$target->trashed()),

                    Button::make(__('Restore'))
                        ->icon('bs.recycle')
                        ->confirm(__('Selected contact to be retore.'))
                        ->method('restore', [
                            'id' => $target->id,
                        ])
                        ->canSee($target->trashed()),
                    
                    // 
                ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request): void
    {
        $contactToRestore = Contact::withTrashed()->findOrFail($request->get('id'));
        $contactToRestore->billingAddress()->restore();
        $contactToRestore->shippingAddress()->restore();
        $contactToRestore->restore();

        Toast::info(__('Contact was restored'));
    }
}
