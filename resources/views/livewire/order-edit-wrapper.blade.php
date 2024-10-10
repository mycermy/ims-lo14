@push('scripts')
    @livewireScripts
@endpush

@push('stylesheets')
    @livewireStyles
@endpush

<div>
    @livewire('order-edit-component', ['order' => $order])
</div>