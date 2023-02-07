<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book_Authors extends Model
{
    use HasFactory;
    protected $fillable = ['book_id','author_id'];
    protected $table = 'book_authors';
}
