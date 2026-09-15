<?php

namespace App\Http\Requests\Laporan;

use App\Queries\LaporanFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Filter bersama 4 laporan (P4 docs/06); semua peran boleh (docs/05). */
class FilterLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer'],
            'triwulan' => ['nullable', 'integer', 'between:1,4'],
            'bidang' => ['nullable', 'integer', Rule::exists('bidang', 'id')],
            'sumber_dana' => ['nullable', 'integer', Rule::exists('sumber_dana', 'id')],
            'sub_kegiatan' => ['nullable', 'integer', Rule::exists('sub_kegiatan', 'id')],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'export' => ['nullable', 'in:xlsx,pdf'],
            'halaman' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function filter(): ?LaporanFilter
    {
        return LaporanFilter::dari($this->validated());
    }

    public function export(): ?string
    {
        return $this->validated('export');
    }
}
