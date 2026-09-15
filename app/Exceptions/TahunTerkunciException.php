<?php

namespace App\Exceptions;

use App\Models\TahunAnggaran;
use RuntimeException;

/** → HTTP 423 (docs/14); dirender di bootstrap/app.php. */
class TahunTerkunciException extends RuntimeException
{
    public function __construct(public readonly TahunAnggaran $tahunAnggaran)
    {
        parent::__construct("Tahun anggaran {$tahunAnggaran->tahun} telah dikunci");
    }
}
