<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class RolesModel extends Model{
    protected $table = 'roles';

    protected $fillable = ['name','title','status'];

}