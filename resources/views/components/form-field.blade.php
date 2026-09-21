@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false, // adds the HTML required attribute + asterisk
    'star' => false,     // asterisk only (server-side required, e.g. fields that can be hidden)
    'optional' => false,
    'placeholder' => '',
    'autocomplete' => null,
])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required || $star)<span class="text-red-500" aria-hidden="true">*</span>@elseif($optional)<span class="text-gray-400 font-normal">(Optional)</span>@endif
    </label>
    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}" @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif @required($required)
           class="w-full border rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ $errors->has($name) ? 'border-red-400' : 'border-gray-200' }}">
    @error($name)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
