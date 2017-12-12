<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class RechargeUrlsModel extends Model{
    protected $table = 'recharge_urls';

    protected $fillable = [
        'recharge_url',
        'msisdn',
        'amount'
    ];
}