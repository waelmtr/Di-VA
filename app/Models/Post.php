<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class Post  extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;


    protected $connection = 'mongodb';
    protected $collection = 'posts';
    use HasFactory;
    protected $casts = [
        'upvotes_user_id' => 'array',
        'downvotes_user_id' => 'array',
        'interest_id' => 'array',
        'comments' => 'array',
    ];
    protected $fillable = [
        'contanet',
        'photo',
        'upvotes_user_id',
        'downvotes_user_id',
        'comments',
        'user_id',
        'interest_id',
        'is_prometed',
    ];
}