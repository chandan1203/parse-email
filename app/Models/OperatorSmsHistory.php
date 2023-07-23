<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorSmsHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'service_id',
        'bu',
        'type',
        'service_name',
        'total_sub_base',
        'activation',
        'renewal_count',
        'deactivation',
        'ppu_success_count',
        'total_success_count'
    ];
}
