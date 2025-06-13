<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $id
 * @property mixed $name
 * @method static find(int $id)
 * @method static where(string $string, string $name)
 */
class Movement extends Model
{
    protected $table = 'movement';
    
    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string'
    ];

    public $timestamps = false;

    public function personalRecords(): HasMany
    {
        return $this->hasMany(PersonalRecord::class, 'movement_id');
    }

    public function getPersonalRecordsOrderedByValue(): HasMany
    {
        return $this->personalRecords()
            ->orderBy('value', 'desc')
            ->orderBy('date');
    }
}

