<?php 

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

Class GetOtpModel extends Model{
    protected $table = 'get_otps';

    protected $fillable = [
        'msisdn',
        'pin',
        'expiry_date',
        'pin_status',
    ];
}