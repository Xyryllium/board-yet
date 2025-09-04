<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

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
