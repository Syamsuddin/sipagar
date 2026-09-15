{{-- <x-form-input name label type value placeholder icon> — error → .input-error + .pw-error.show (docs/26) --}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'placeholder' => '', 'icon' => null])
@php $ada = $errors->has($name); @endphp
<div>
    @if ($label)<label class="form-label" for="{{ $name }}">{{ $label }}</label>@endif
    <div class="relative">
        <input {{ $attributes->merge(['class' => 'form-input'.($ada ? ' input-error' : '').($icon ? ' !pl-[42px]' : '')]) }} type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" @if ($type !== 'password') value="{{ old($name, $value) }}" @endif>
        @if ($icon)<i class="fa-solid {{ $icon }} absolute left-[14px] top-1/2 -translate-y-1/2 text-[var(--fg-muted)] text-[0.85rem] pointer-events-none"></i>@endif
    </div>
    @error($name)<div class="pw-error show"><i class="fa-solid fa-circle-xmark"></i> <span>{{ $message }}</span></div>@enderror
</div>
