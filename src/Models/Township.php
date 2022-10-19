<?php

namespace laranex\LaravelMyanmarNRC\Models;

use Illuminate\Database\Eloquent\Model;

class Township extends Model
{
    public $table = 'nrc_townships';

    protected $fillable = ['nrc_state_id', 'code', 'code_mm', 'name', 'name_mm'];

    public function nrcTownship(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
