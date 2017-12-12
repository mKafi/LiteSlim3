<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class Flexiplan extends Model{
    protected $table = 'flexiplans';

    protected $fillable = ['price_type','net_type','validity','mb_start','mb_end','voice_start','voice_end','price','market_price','status'];
}