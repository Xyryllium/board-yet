<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'column_id',
        'title',
        'description',
        'order',
    ];

    public function column()
    {
        return $this->belongsTo(BoardColumn::class, 'column_id');
    }
}
