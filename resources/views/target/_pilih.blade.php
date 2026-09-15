<div class="mb-5">
    <label class="form-label" for="pilih-sub">Sub Kegiatan</label>
    <select class="form-input" id="pilih-sub" x-data @change="if ($event.target.value) window.location = '{{ url('/target') }}/' + $event.target.value">
        <option value="">-- Pilih sub kegiatan --</option>
        @foreach ($pilihan as $sk)
            <option value="{{ $sk->id }}" @selected($terpilih && $terpilih->id === $sk->id)>{{ $sk->kode }} — {{ $sk->nama }}</option>
        @endforeach
    </select>
</div>
