<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortlinkVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortlink_id', 'ip', 'hits', 'first_seen', 'last_seen', 'is_bot', 'country', 'city', 'asn', 'org'
    ];

    protected $casts = [
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
        'is_bot' => 'boolean',
        'hits' => 'integer',
    ];
}
