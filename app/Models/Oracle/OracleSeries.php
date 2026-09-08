<?php

namespace App\Models\Oracle;

class OracleSeries extends ReadOnlyOracleModel
{
    protected $table = 'VLCS.MM_GPF_SERIES';
    protected $primaryKey = 'SERIES_ID';
    public $incrementing = false;
    protected $keyType = 'string';
}
