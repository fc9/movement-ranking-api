<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalRecord extends Model
{
    protected $table = 'personal_record';
    
    protected $fillable = [
        'user_id',
        'movement_id',
        'value',
        'date'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'movement_id' => 'integer',
        'value' => 'float',
        'date' => 'datetime'
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class, 'movement_id');
    }
}

