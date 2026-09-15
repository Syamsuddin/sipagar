<?php

namespace App\Exceptions;

use RuntimeException;

/** → 422 "Melebihi sisa! Sisa: Rp …" (docs/14); dirender di bootstrap/app.php. */
class MelebihiSisaPaguException extends RuntimeException
{
    public function __construct(public readonly int $sisa)
    {
        parent::__construct('Melebihi sisa! Sisa: '.rupiah($sisa));
    }
}
