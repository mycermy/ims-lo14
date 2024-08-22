@component($typeForm, get_defined_vars())
    <div data-controller="select"
        data-select-placeholder="{{$attributes['placeholder'] ?? ''}}"
        data-select-allow-empty="{{ $allowEmpty }}"
        data-select-message-notfound="{{ __('No results found') }}"
        data-select-allow-add="{{ var_export($allowAdd, true) }}"
        data-select-message-add="{{ __('Add') }}"
    >
        <select {{ $attributes }}>
            @foreach ($optgroups as $category)
                <optgroup label="{{ $category->text }}">
                    @foreach($options as $key => $option)
                        @if ($option->category_id === $category->id)
                            <option value="{{ $key }}"
                                    @isset($value)
                                        @if (is_array($value) && in_array($key, $value)) selected
                                        @elseif (isset($value[$key]) && $value[$key] == $option) selected
                                        @elseif ($key == $value) selected
                                        @endif
                                    @endisset
                            >{{ $option }}</option>
                        @endif
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
@endcomponent
