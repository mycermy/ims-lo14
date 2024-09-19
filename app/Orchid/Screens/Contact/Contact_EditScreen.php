<?php

namespace App\Orchid\Screens\Contact;

use App\Models\Contact\Address;
use App\Models\Contact\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Contact_EditScreen extends Screen
{
    public ?Contact $contact = null;
    public ?Address $billingAdr = null;
    public ?Address $shippingAdr = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Contact $contact): iterable
    {
        // dd(Address::billingAddressByContact($contact->id)->first());
        return [
            'contact' => $contact,
            'billingAdr' => $contact->billingAddress()->first(),
            // 'billingAdr' => Address::billingAddressbyContact($contact->id)->first(),
            'shippingAdr' => $contact->shippingAddress()->first(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->contact->exists ? 'Edit ' . $this->contact->name : 'Create Contact';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->canSee(!$this->contact->exists)
                ->method('store'),

            Link::make(__('Cancel'))
                ->icon('bs.x-circle')
                ->route('platform.contacts'),
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
            Layout::block(Layout::rows([
                Switcher::make('contact.enabled')
                    ->sendTrueOrFalse()
                    ->title('Contact Enable')
                    ->canSee($this->contact->exists)
                    ->horizontal(),

                Select::make('contact.type')
                    ->options([
                        Contact::TYPE_CUSTOMER => 'Customer',
                        Contact::TYPE_EMPLOYEE => 'Employee',
                        Contact::TYPE_VENDOR   => 'Vendor',
                    ])
                    ->empty()
                    ->title('Contact Type')
                    ->required()
                    ->horizontal(),
            
                Input::make('contact.name')
                    ->title('Display Name')
                    ->required()
                    ->horizontal(),
            
                Input::make('contact.contact_name')
                    ->title('Primary Contact Name')
                    ->horizontal(),
                
                Input::make('contact.email')
                    ->title('Email')
                    ->required()
                    ->horizontal(),
                
                Input::make('contact.phone')
                    ->title('Phone Number')
                    ->required()
                    ->horizontal(), 

            ]))
            ->title('Contact Information')
            ->description('Contact Details Information')
            ->commands(
                Button::make(__('Update'))
                    ->type(Color::BASIC)
                    ->icon('bs.check-circle')
                    ->canSee($this->contact->exists)
                    ->method('update')
            ),

            Layout::accordion([
                'Billing Information' => Layout::block(Layout::rows([
                    Input::make('billingAdr.address_street_1')
                        ->title('Street 1')->horizontal(),
                    Input::make('billingAdr.address_street_2')
                        ->title('Street 2')->horizontal(),
                    Input::make('billingAdr.city')
                        ->title('City')->horizontal(),
                    Input::make('billingAdr.state')
                        ->title('State')->horizontal(),
                    Input::make('billingAdr.zip')
                        ->title('Zip Code')->horizontal(),
                    Input::make('billingAdr.fax')
                        ->title('Fax Number')->horizontal(),
                    Input::make('billingAdr.contact_id')->type('hidden'),
                    //
                ]))->title('Billing Address')->commands(
                    Button::make(__('Update'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee(!empty($this->billingAdr))
                        ->method('update'),
                ),

                'Shipping Information' => Layout::block(Layout::rows([
                    Input::make('shippingAdr.address_street_1')
                        ->title('Street 1')->horizontal(),
                    Input::make('shippingAdr.address_street_2')
                        ->title('Street 2')->horizontal(),
                    Input::make('shippingAdr.city')
                        ->title('City')->horizontal(),
                    Input::make('shippingAdr.state')
                        ->title('State')->horizontal(),
                    Input::make('shippingAdr.zip')
                        ->title('Zip Code')->horizontal(),
                    Input::make('shippingAdr.fax')
                        ->title('Fax Number')->horizontal(),
                    //
                ]))->title('Shipping Address')->commands([
                    Button::make(__('Update'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee(!empty($this->shippingAdr))
                        ->method('update'),
                        
                    Button::make(__('Add Shipping Address'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee(empty($this->shippingAdr) && $this->contact->exists)
                        ->method('storeShippingAddress')
                ]),
            ]),
        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Contact $contact)
    {
        $request->validate([
            'contact.email' => [
                'required',
                Rule::unique(Contact::class,'email')
            ]
        ]);

        $createdBy = auth()->id();
        
        $contact->fill($request->get('contact'));
        $contact->fill(['created_by' => $createdBy]);

        $contact->save();

        
        $billAdr = new Address();
        $billAdr->fill($request->get('billingAdr'));
        $billAdr->fill([
            'contact_id' => $contact->id,
            'type' => Address::TYPE_BILLING,
            'name' => $request->input('contact.name'),
            'phone' => $request->input('contact.phone'),
            'created_by' => $createdBy,
        ]);
        $billAdr->save();


        $shippingAdr = $request->get('shippingAdr');
        if(empty(array_filter($shippingAdr))) {
            //dd('All values in the shippingAdr array are empty or null');
        } else {
            // Save to database
            $shipAdr = new Address();
            $shipAdr->fill($shippingAdr);
            $shipAdr->fill([
                'contact_id' => $contact->id,
                'type' => Address::TYPE_SHIPPING,
                'name' => $request->input('contact.name'),
                'phone' => $request->input('contact.phone'),
                'created_by' => $createdBy,
            ]);
            $shipAdr->save();
        }


        Toast::info(__('Contact was saved.'));

        return redirect()->route('platform.contacts');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeShippingAddress(Request $request, Address $shipAdr)
    {
        $createdBy = auth()->id();

        $shipAdr->fill($request->get('shippingAdr'));
        $shipAdr->fill([
            'contact_id' => $this->contact->id,
            // 'contact_id' => $request->input('billingAdr.contact_id'),
            'type' => Address::TYPE_SHIPPING,
            'name' => $request->input('contact.name'),
            'phone' => $request->input('contact.phone'),
            'created_by' => $createdBy,
        ]);
        $shipAdr->save();


        Toast::info(__('Shipping address was saved.'));

        return redirect()->route('platform.contacts');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Contact $contact)
    {
        $updatedBy = auth()->id();

        $contact->fill($request->get('contact'));
        $contact->fill(['updated_by' => $updatedBy]);

        $contact->save();

        $billAdr = $contact->billingAddress()->first();
        $billAdr->fill([
            'name' => $request->input('contact.name'),
            'phone' => $request->input('contact.phone'),
            'updated_by' => $updatedBy,
        ]);
        $billAdr->update($request->get('billingAdr'));

        $shipAdr = $contact->shippingAddress()->first();
        if (empty($shipAdr)) {
            # code...
        } else {
            # code...
            $shipAdr->fill([
                'name' => $request->input('contact.name'),
                'phone' => $request->input('contact.phone'),
                'updated_by' => $updatedBy,
            ]);
            $shipAdr->update($request->get('shippingAdr'));
        }
        

        Toast::info(__('Contact was updated.'));

        return redirect()->route('platform.contacts');
    }
}
