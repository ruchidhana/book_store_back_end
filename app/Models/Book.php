<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['isbn','title','description','price','cover_image'];

    use HasFactory;

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
