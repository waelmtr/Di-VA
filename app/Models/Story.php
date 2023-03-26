<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;


    protected $connection = 'mysql';
    protected $fillable = [
        'photo',
        'user_id',
        'user_name',
        'user_photo',
        'date_type'
    ];

    public function users()
    {

        return $this->belongsTo('App\Models\User');
    }
}