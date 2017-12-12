<?php 

namespace App\Controllers;

use App\Models\Admin\Settings AS Settings;
use App\Models\Admin\RolesModel AS Roles;
use App\Models\Admin\Auth\UserModel AS User;

Class Controller{
    public $output = array(
        'request'     => array(),
        'result'      => array(),
        'message'     => '',
        'status_code' => '200',
    );
    public $platform = NULL;
    
    protected $container; 
    public $last_sync_time;   
    
    public function __construct($container = NULL){        
        $this->container = $container;
        // unset($_SESSION);
        $this->getLastSyncTime();
        if(!empty($_SESSION['uid'])){
            $this->uid = !empty($_SESSION['uid']) ? $_SESSION['uid'] : '';
            $this->userLogged = User::where(['id'=>$this->uid])->first()->toArray();
        }
    }

    public function __get($property){
        if(!empty($this->container->{$property})){
            return $this->container->{$property};
        }
    }

    public function fpr($data){
        echo '<pre>'; print_r($data); echo '</pre>';
    }

    public function getLastSyncTime(){
        /*
        $last_sync_time = Settings::where('name','last_sync_time')->first();        
        if($last_sync_time->value){
            $this->last_sync_time = $last_sync_time->value;
        }
        */

        $last_sync_time = strtotime(file_get_contents('../app/config/last_sync_time.txt'));        
        if($last_sync_time){
            $this->last_sync_time = $last_sync_time;
        }
    }

    public function updateLastSyncTime(){
        /* updating last sync time */
        $file = fopen('../app/config/last_sync_time.txt',"w");
        fwrite($file,date("Y-m-d h:i:s", time()));
        fclose($file);
    }

    public function logInFile($path, $info, $fileName, $mode = NULL){
        $data_bytes = FALSE;       
        if(!empty($path) && !empty($info) && !empty($fileName)){
            if(empty($mode)) {
                $mode = "w";
            }
            $file = fopen($path.$fileName,$mode);
            $data_bytes = fwrite($file,$info.PHP_EOL);
            fclose($file);
        }
        return $data_bytes;
    }

    public function getUserRoles(){
        $info = [];
        $roles = Roles::get()->toArray();
        if(!empty($roles) && is_array($roles)){
            foreach($roles AS $role){
                $info[$role['id']] = $role['title'];
            }
        }
        if(!empty($info)){ 
            return $info;
        } 
        else { 
            return FALSE; 
        }
    }
}