{{-- <x-user-badge nama> — avatar = huruf pertama nama --}}
@props(['nama'])
<div class="user-badge"><div class="avatar">{{ mb_substr($nama, 0, 1) }}</div><span>{{ $nama }}</span></div>
