<?php

namespace App\Models\Oracle;

class OracleTreasury extends ReadOnlyOracleModel
{
    protected $table = 'VLCS.STATE_TREASURY';
    protected $primaryKey = 'TRES_CODE';
    public $incrementing = false;
    protected $keyType = 'string';
}
