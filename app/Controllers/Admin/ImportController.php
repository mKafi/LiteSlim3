<?php 
namespace App\Controllers\Admin;

use Illuminate\Database\Query\Builder as Builder;
use App\Models\User;
use App\Models\Admin\Flexiplan AS FlexiPlan;
use App\Models\Admin\FlexiplanOption;
use App\Models\Admin\Settings;

Class ImportController {
    
    /*
    Setting insert model 
    */
    public function settings_post(){        
        $flag = Settings::create([
            'name'=>'site_title',
            'value' => 'FlexiPlan'
        ]);
    }
}