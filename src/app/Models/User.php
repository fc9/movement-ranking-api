<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'user';
    
    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'id'   => 'integer',
        'name' => 'string'
    ];

    public $timestamps = false;

    public function personalRecords(): HasMany
    {
        return $this->hasMany(PersonalRecord::class, 'user_id');
    }

    public function getPersonalRecordsForMovement(int $movementId): HasMany
    {
        return $this->personalRecords()->where('movement_id', $movementId);
    }
}

