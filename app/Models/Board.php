<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = [
        'name',
        'organization_id'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function columns()
    {
        return $this->hasMany(BoardColumn::class)->orderBy('order');
    }
}
