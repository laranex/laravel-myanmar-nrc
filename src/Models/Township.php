<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $nrc_state_id
 * @property string $code
 * @property string $code_mm
 * @property string $name
 * @property string $name_mm
 */
class Township extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nrc_townships';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['nrc_state_id', 'code', 'code_mm', 'name', 'name_mm'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'int',
        'nrc_state_id' => 'int',
    ];

    /**
     * The state or region this township belongs to.
     *
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'nrc_state_id');
    }
}
