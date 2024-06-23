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

class Contact_ListScreen extends Screen
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
                // Contact::withTrashed()->filters()
                // Contact::onlyTrashed()->filters()
                Contact::filters()
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
        return 'All Contacts';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.contacts.create'),

            ModalToggle::make('Add Customer')
                ->modal('xpressAddModal')
                ->method('store')
                ->parameters([
                    'contactType' => Contact::TYPE_CUSTOMER
                ])
                ->icon('bs.window'),

            ModalToggle::make('Add Vendor')
                ->modal('xpressAddModal')
                ->method('store')
                ->parameters([
                    'contactType' => Contact::TYPE_VENDOR
                ])
                ->icon('bs.window'),
        ];
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
                // TD::make('updated_by', 'Updated By')->render(fn($target) => $target->updatedBy->name ?? null),
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

            Layout::modal('xpressAddModal', Layout::rows([
                Input::make('contact.name')
                    ->title('Display Name')
                    ->required()
                    ->horizontal(),
                
                Input::make('contact.email')
                    ->title('Email')
                    ->required()
                    ->horizontal(),
                
                Input::make('contact.phone')
                    ->title('Phone Number')
                    ->required()
                    ->horizontal(), 
            ]))->title('Create new contact.'),
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

                    Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Once the contact is deleted, all of its resources and data will be permanently deleted. Before deleting your contact, please download any data or information that you wish to retain.'))
                        ->method('remove', [
                            'id' => $target->id,
                        ])
                        ->canSee(!$target->trashed()),

                    // Button::make(__('Restore'))
                    //     ->icon('bs.recycle')
                    //     ->confirm(__('Once the contact is deleted, all of its resources and data will be permanently deleted. Before deleting your contact, please download any data or information that you wish to retain.'))
                    //     ->method('restore', [
                    //         'id' => $target->id,
                    //     ])
                    //     ->canSee($target->trashed()),
                    
                    // 
                ]),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Contact $contact, $contactType)
    {
        $request->validate([
            'contact.email' => [
                'required',
                Rule::unique(Contact::class,'email')
            ]
        ]);

        $createdBy = auth()->id();
        
        $contact->fill($request->get('contact'));
        $contact->fill([
            'type' => $contactType,
            'created_by' => $createdBy,
        ]);

        $contact->save();

        
        $billAdr = new Address();
        $billAdr->fill([
            'contact_id' => $contact->id,
            'type' => Address::TYPE_BILLING,
            'name' => $request->input('contact.name'),
            'phone' => $request->input('contact.phone'),
            'created_by' => $createdBy,
        ]);
        $billAdr->save();


        Toast::info(__('Contact [' . $contactType . '] was saved.'));

        return redirect()->route('platform.contacts');
    }

    public function remove(Request $request): void
    {
        $contactToRemove = Contact::findOrFail($request->get('id'));
        $contactToRemove->billingAddress()->delete();
        $contactToRemove->shippingAddress()->delete();
        $contactToRemove->delete();

        Toast::info(__('Contact was removed'));
    }

    // public function restore(Request $request): void
    // {
    //     $contactToRestore = Contact::withTrashed()->findOrFail($request->get('id'));
    //     $contactToRestore->billingAddress()->restore();
    //     $contactToRestore->shippingAddress()->restore();
    //     $contactToRestore->restore();

    //     Toast::info(__('Contact was restored'));
    // }
}
