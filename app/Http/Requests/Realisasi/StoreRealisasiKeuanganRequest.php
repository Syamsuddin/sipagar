<?php

namespace App\Http\Requests\Realisasi;

use App\Models\RealisasiKeuangan;
use App\Models\SubKegiatan;
use App\Services\SerapanCalculator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRealisasiKeuanganRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sk = SubKegiatan::find($this->integer('sub_kegiatan_id'));

        return $sk !== null && ($this->user()?->can('create', [RealisasiKeuangan::class, $sk]) ?? false);
    }

    public function rules(): array
    {
        return [
            'sub_kegiatan_id' => ['required', 'integer', Rule::exists('sub_kegiatan', 'id')->whereNull('deleted_at')],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:9999999999999'],
            'uraian' => ['required', 'string', 'max:500'],
            'no_sp2d' => ['nullable', 'string', 'max:50'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'sub_kegiatan_id.required' => 'Pilih sub kegiatan',
            'tanggal.required' => 'Tanggal wajib diisi',
            'tanggal.date_format' => 'Format tanggal tidak valid',
            'jumlah.required' => 'Jumlah wajib diisi',
            'jumlah.integer' => 'Jumlah harus angka bulat',
            'jumlah.min' => 'Jumlah harus lebih dari 0',
            'uraian.required' => 'Uraian wajib diisi',
            'lampiran.mimes' => 'Lampiran harus pdf, jpg, atau png',
            'lampiran.mimetypes' => 'Lampiran harus pdf, jpg, atau png',
            'lampiran.max' => 'Lampiran maksimal 2 MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('jumlah')) {
            $this->merge(['jumlah' => parseRupiah($this->input('jumlah'))]);
        }
    }

    /** Lintas-baris (docs/14): tanggal dalam tahun anggaran & jumlah ≤ sisa. Service mengecek ulang dalam transaksi. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $sk = $this->subKegiatan();
            $tahun = $sk->tahunAnggaran()->tahun;
            if ((int) substr($this->input('tanggal'), 0, 4) !== $tahun) {
                $v->errors()->add('tanggal', "Tanggal harus dalam tahun anggaran {$tahun}");
            }

            $k = app(SerapanCalculator::class);
            $sisa = $k->sisa($sk->pagu, $k->realisasi($sk) - $this->jumlahLama());
            if ((int) $this->input('jumlah') > $sisa) {
                $v->errors()->add('jumlah', 'Melebihi sisa! Sisa: '.rupiah($sisa));
            }
        });
    }

    public function subKegiatan(): SubKegiatan
    {
        return SubKegiatan::findOrFail($this->integer('sub_kegiatan_id'));
    }

    /** Untuk update: jumlah transaksi ini sendiri tidak dihitung sebagai terpakai. */
    protected function jumlahLama(): int
    {
        return 0;
    }
}
