<?php

namespace laranex\LaravelMyanmarNRC\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public $table = 'nrc_states';

    protected $fillable = ['code', 'code_mm', 'name', 'name_mm'];

    public function nrcTownships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Township::class);
    }
}
