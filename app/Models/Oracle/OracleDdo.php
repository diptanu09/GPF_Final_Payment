<?php

namespace App\Models\Oracle;

class OracleDdo extends ReadOnlyOracleModel
{
    protected $table = 'VLCS.STATE_DDO';
    protected $primaryKey = 'DDO_CODE';
    public $incrementing = false;
    protected $keyType = 'string';
}
