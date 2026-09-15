{{-- <x-form-select name label> <option>…</option> </x-form-select> --}}
@props(['name', 'label' => null])
@php $ada = $errors->has($name); @endphp
<div>
    @if ($label)<label class="form-label" for="{{ $name }}">{{ $label }}</label>@endif
    <select {{ $attributes->merge(['class' => 'form-input'.($ada ? ' input-error' : '')]) }} id="{{ $name }}" name="{{ $name }}">{{ $slot }}</select>
    @error($name)<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
</div>
