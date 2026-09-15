{{-- <x-form-textarea name label rows value placeholder> --}}
@props(['name', 'label' => null, 'rows' => 3, 'value' => null, 'placeholder' => ''])
@php $ada = $errors->has($name); @endphp
<div>
    @if ($label)<label class="form-label" for="{{ $name }}">{{ $label }}</label>@endif
    <textarea {{ $attributes->merge(['class' => 'form-input resize-y'.($ada ? ' input-error' : '')]) }} id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}">{{ old($name, $value) }}</textarea>
    @error($name)<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
</div>
