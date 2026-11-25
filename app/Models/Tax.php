<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = ['tax_name', 'tax_value', 'status'];

    protected function casts(): array
    {
        return ['tax_value' => 'decimal:4'];
    }
}
