@isset($key)
    @livewire($name, $params, key($key))
@else
    @livewire($name, $params)
@endisset

{{-- @push('scripts')
    @livewireScripts
@endpush

@push('stylesheets')
    @livewireStyles
@endpush --}}