<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class SettingsModel extends Model{
    protected $table = 'settings';

    protected $fillable = ['name','value'];
}