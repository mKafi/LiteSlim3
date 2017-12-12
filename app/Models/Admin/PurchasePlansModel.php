<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class PurchasePlansModel extends Model{
    protected $table = 'purchases';

    protected $fillable = [
        'customer_id',
        'version_code',
        'msisdn',
        'platform',
        'imei',
        'purchase_number',
        'transaction_id',
        'remarks',
        'status',
        'net_type',
        'data',
        'voice',
        'validity',
        'sms',
        'flexi_plan_price',
        'discount'
    ];
}