<?php

namespace App\Http\Requests\Realisasi;

use App\Models\RealisasiFisik;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/** Payload: fisik[1..12] (persen kumulatif, boleh kosong). Monoton terhadap bulan terisi sebelumnya (docs/04). */
class UpdateRealisasiFisikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', [RealisasiFisik::class, $this->route('subKegiatan')]) ?? false;
    }

    public function rules(): array
    {
        return [
            'fisik' => ['required', 'array'],
            'fisik.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'fisik.required' => 'Isi minimal satu bulan',
            'fisik.*.numeric' => 'Persen harus angka',
            'fisik.*.min' => 'Persen minimal 0',
            'fisik.*.max' => 'Persen maksimal 100',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $sebelum = null;
            $bulanSebelum = null;
            foreach ($this->persenPerBulan() as $bulan => $persen) {
                if ($persen === null) {
                    continue;
                }
                if ($sebelum !== null && $persen < $sebelum) {
                    $v->errors()->add("fisik.{$bulan}", "Fisik bulan {$bulan} ({$persen} %) tidak boleh menurun dari bulan {$bulanSebelum} ({$sebelum} %)");
                }
                $sebelum = $persen;
                $bulanSebelum = $bulan;
            }
        });
    }

    /** @return array<int, float|null> bulan 1..12 => persen|null */
    public function persenPerBulan(): array
    {
        $masuk = $this->input('fisik', []);
        $hasil = [];
        foreach (range(1, 12) as $b) {
            $nilai = $masuk[$b] ?? null;
            $hasil[$b] = ($nilai === null || $nilai === '') ? null : round((float) $nilai, 2);
        }

        return $hasil;
    }
}
