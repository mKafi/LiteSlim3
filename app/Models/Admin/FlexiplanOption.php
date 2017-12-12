<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class FlexiplanOption extends Model{
    protected $table = 'flexiplanOptions';

    protected $fillable = ['option_type','option_value'];
}