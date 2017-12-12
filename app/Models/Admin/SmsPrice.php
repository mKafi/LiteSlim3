<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class SmsPrice extends Model{
    protected $table = 'sms_prices';
    protected $fillable = ['sms_start','sms_end','price','price_per_sms','market_price','market_price_per_sms'];
}