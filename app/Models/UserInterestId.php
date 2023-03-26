<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Interest;
use App\Models\User;

class UserInterestId extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'interest_id',
    ];
}