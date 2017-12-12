<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class CustomerModel extends Model{
    protected $table = 'customers';

    protected $fillable = [
        'msisdn',
        'imei',
        'status',
    ];
}