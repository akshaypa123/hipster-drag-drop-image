<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = ['upload_id', 'filename', 'status', 'checksum'];

    public function images() {
        return $this->hasMany(Image::class);
    }
}
