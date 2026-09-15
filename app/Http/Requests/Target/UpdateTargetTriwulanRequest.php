<?php

namespace App\Http\Requests\Target;

use App\Models\TargetTriwulan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 4 baris target kumulatif. Validasi lintas-baris (monoton, TW4 = pagu / 100 %) di withValidator (docs/14).
 * Payload: target[1..4][keuangan], target[1..4][fisik].
 */
class UpdateTargetTriwulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', [TargetTriwulan::class, $this->route('subKegiatan')]) ?? false;
    }

    public function rules(): array
    {
        return [
            'target' => ['required', 'array', 'size:4'],
            'target.*.keuangan' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'target.*.fisik' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'target.required' => 'Empat baris target wajib diisi',
            'target.size' => 'Empat baris target wajib diisi',
            'target.*.keuangan.required' => 'Target keuangan wajib diisi',
            'target.*.keuangan.integer' => 'Target keuangan harus angka bulat',
            'target.*.fisik.required' => 'Target fisik wajib diisi',
            'target.*.fisik.max' => 'Target fisik maksimal 100 %',
        ];
    }

    protected function prepareForValidation(): void
    {
        $target = $this->input('target', []);
        if (is_array($target)) {
            foreach ($target as $tw => $baris) {
                if (is_array($baris) && array_key_exists('keuangan', $baris)) {
                    $target[$tw]['keuangan'] = parseRupiah($baris['keuangan']);
                }
            }
            $this->merge(['target' => $target]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $pagu = (int) $this->route('subKegiatan')->pagu;
            $t = $this->input('target');

            foreach ([1, 2, 3, 4] as $tw) {
                if (! isset($t[$tw])) {
                    $v->errors()->add('target', "Baris TW{$tw} tidak ada");

                    return;
                }
            }
            foreach ([2, 3, 4] as $tw) {
                if ((int) $t[$tw]['keuangan'] < (int) $t[$tw - 1]['keuangan']) {
                    $v->errors()->add("target.{$tw}.keuangan", "Target keuangan TW{$tw} tidak boleh lebih kecil dari TW".($tw - 1));
                }
                if ((float) $t[$tw]['fisik'] < (float) $t[$tw - 1]['fisik']) {
                    $v->errors()->add("target.{$tw}.fisik", "Target fisik TW{$tw} tidak boleh lebih kecil dari TW".($tw - 1));
                }
            }
            if ((int) $t[4]['keuangan'] !== $pagu) {
                $v->errors()->add('target.4.keuangan', 'Target keuangan TW4 harus sama dengan pagu '.rupiah($pagu));
            }
            if (abs((float) $t[4]['fisik'] - 100) > 0.001) {
                $v->errors()->add('target.4.fisik', 'Target fisik TW4 harus 100 %');
            }
        });
    }

    /** @return array<int, array{triwulan: int, target_keuangan: int, target_fisik: float}> */
    public function baris(): array
    {
        return collect($this->validated('target'))
            ->map(fn (array $b, $tw) => ['triwulan' => (int) $tw, 'target_keuangan' => (int) $b['keuangan'], 'target_fisik' => round((float) $b['fisik'], 2)])
            ->sortBy('triwulan')->values()->all();
    }
}
