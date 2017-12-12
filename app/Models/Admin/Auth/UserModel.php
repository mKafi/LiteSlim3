<?php 

namespace App\Models\Admin\Auth;

use Illuminate\Database\Eloquent\Model;

Class UserModel extends Model{
    protected $table = 'users';

    protected $fillable = ['rid','nick','name','email','phone','password'];
}