<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfToJsonFormat extends Model
{
    use HasFactory;

    protected $table = 'pdf_json_format';
    protected $primaryKey  = 'id';

    protected $guarded = [];
    public $timestamps = false;
}
