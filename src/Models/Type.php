<?php

namespace laranex\LaravelMyanmarNRC\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public $table = 'nrc_types';

    protected $fillable = ['code', 'code_mm', 'name', 'name_mm'];
}
