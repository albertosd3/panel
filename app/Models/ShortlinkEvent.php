<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortlinkEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortlink_id','ip','country','city','asn','org','device','platform','browser','referrer','is_bot','clicked_at'
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'clicked_at' => 'datetime',
    ];
}
