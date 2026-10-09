<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $code
 * @property string $code_my
 * @property string $name
 * @property string $name_my
 */
class State extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nrc_states';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['code', 'code_my', 'name', 'name_my'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'int',
        'code' => 'int',
    ];

    /**
     * The townships that belong to this state or region.
     *
     * @return HasMany<Township, $this>
     */
    public function townships(): HasMany
    {
        return $this->hasMany(Township::class, 'nrc_state_id');
    }
}
