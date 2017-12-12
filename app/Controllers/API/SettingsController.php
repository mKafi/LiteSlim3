<?php 
namespace App\Controllers\API;

use Illuminate\Database\Query\Builder as Builder;

use App\Controllers\Controller AS Controller;
use App\Controllers\API\OtpController AS OtpController;
use App\Controllers\Admin\PortWalletController AS PortWallet;

use App\Models\User;
use App\Models\Admin\Flexiplan AS FlexiPlan;
use App\Models\Admin\FlexiplanOption AS FlexiPlanOption;
use App\Models\Admin\SmsPrice AS SmsPrice;
use App\Models\Admin\SettingsModel AS Settings;
use App\Models\Admin\PurchasePlansModel;
use App\Models\Admin\GetOtpModel;
use App\Models\Admin\CustomerModel;
use App\Models\Admin\RechargeUrlsModel;

Class SettingsController extends Controller{
    public function index($request, $response, $args){
        $vars = [];

        $vars['version_code'] = 12;
        $vars['app_title'] = 'FlexiPlan';

        return json_encode($vars);
    }

    public function get_pin($request, $response, $args){
        $output = [];
        $error = [];                
        if(empty($_POST['phone'])){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }
        
        if(empty($error)){
            $getOtp = new OtpController;   
            
            $info = [
                'msisdn' => $request->getParam('phone'),
                'return_response' => TRUE
            ];

            
            $result = $getOtp->index($info);
            if($result['status'] === 'success'){
                $expTime = date( 'Y-m-d H:i:s', strtotime( '+' . PIN_VALIDITY . ' minutes'));
                $saveData = array( 
                    'Pin' => array(
                        'phone'       => $_POST['phone'],
                        'pin'         => trim( $result['remarks'] ),
                        'expiry_date' => $expTime,
                        'status'      => UNVERIFIED,
                        'try_attemp' => $result['try_attemp'],
                        'imei' => $this->get_imei_number(),
                    ) 
                );
                
                $file = fopen('../storage/customer/customer_pins/cst_'.$request->getParam('phone').'.txt',"w+");
                $flag = fwrite($file,json_encode($saveData));
                fclose($file);
                if($flag){                    
                    GetOtpModel::create([
                        'msisdn' => $request->getParam('phone'),
                        'pin' => trim($result['remarks']),
                        'expiry_date' => $expTime,
                        'pin_status' => UNVERIFIED
                    ]);
                    $output['request'] = ['phone' => $request->getParam('phone')];
                    $output['message'] = (ENVIRONMENT == 'live') ? ('A PIN has been sent to ' . $request->getParam('phone') ) : "Your Verification PIN is {$result['remarks']}.";
                    $output['status_code'] = STATUS_OK;
                }
                else{
                    $error['pin_save_error'] = ERROR_MESSAGE['pin_save_error'];
                }
            }
        }
        if(!empty($error)){
            $output['error'] = $error;
        }        
        return json_encode($output);
    }


    public function verify_pin($request, $response, $args){                                       
        $output = [];
        $error = [];  
        $result = []; 
        $pin_info = [];     
        // $toolbox = new Toolbox;        
        if(empty($request->getParam('phone'))){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }
        if(empty($request->getParam('pin'))){
            $error[] = ERROR_MESSAGE['pin_empty'];
        }
        if(!empty($request->getParam('pin')) && is_numeric($request->getParam('pin')) && strlen($request->getParam('pin') < 4)) {           
            $error[] = ERROR_MESSAGE['pin_invalid'];
        }
        
        if(!empty($request->getParam('pin')) && is_numeric($request->getParam('pin'))){
            $file_path = '../storage/customer/customer_pins/cst_'.$request->getParam('phone').'.txt';
            if(file_exists($file_path)){
                $pin_info = json_decode(file_get_contents($file_path));
            }
            
            if(empty($pin_info)){
                $error[] = ERROR_MESSAGE['pin_not_found'];
            }
            
            if(!empty($pin_info->Pin) && $pin_info->Pin->phone == $request->getParam('phone') && $pin_info->Pin->pin == $request->getParam('pin')){ 
                $pin_time = strtotime($pin_info->Pin->expiry_date);
                if(time() > $pin_time){
                    $error[] = ERROR_MESSAGE['pin_invalid'];
                }
                $flag = unlink($file_path);
                if($flag == FALSE){
                    $error[] = ERROR_MESSAGE['pin_verification_failed'];
                }

                $user_info = [
                    'msisdn' => $request->getParam('phone'),
                    'imei' => $this->get_imei_number(),
                    'status' => '1'
                ];
                
                $logInFile = FALSE;
                $flag = FALSE;
                $id = CustomerModel::where(['msisdn' => $request->getParam('phone')])->get()->first();
                if(!$id){
                    $flag = CustomerModel::create($user_info);
                    $logInFile = TRUE;
                }
                if($flag){
                    $user_info['updated_at'] = $flag->updated_at;
                    $user_info['created_at'] = $flag->created_at;
                    $user_info['id'] = $flag->id;
                }

                if($logInFile){
                    $this->logInFile('../storage/customer/customer_registered/',implode('|',$user_info),'registered-customer_'.date("Y-m-d").'.txt','a');
                }
            }
            else if(!empty($pin_info->Pin) && $pin_info->Pin->phone == $request->getParam('phone') && $pin_info->Pin->pin != $request->getParam('pin')){
                if(!empty($pin_info->Pin->try_attemp) && $pin_info->Pin->try_attemp >= 2){
                    $error[] = ERROR_MESSAGE['try_limit_over'];
                    unlink($file_path);
                    
                    
                }
                else{
                    $pin_info->Pin->try_attemp += 1;
                    $error[] = ERROR_MESSAGE['pin_invalid'];
                    @file_put_contents($file_path, json_encode($pin_info));
                }
            }            
        }                
        if(empty($error)){            
            if($pin_info->Pin->pin){                    
                $output['request'] = ['phone'=>$_POST['phone']];
                $output['message'] = "Thank you for verification. Enjoy the Flexiplan application";
                $output['status_code'] = STATUS_OK;
            }            
        }
        
        if(!empty($error)){
            $output['error'] = $error;
        }        
        return json_encode($output);
    }

    
    public function get_settings($request, $response){        
        $error = [];                
        $output = [];
        $results = [
            ['type'=>'first_installation_offer', 'value'=>'3409','status'=>'1'],
            ['type'=>'facebook_share_offer', 'value'=>'','status'=>'0'],
            ['type'=>'first_installation_offer', 'value'=>'','status'=>'0'],
        ];
        $output['request'] = [];
        $output['result'] = $results;                   
        $output['message'] = 'Settings provided successfully';
        $output['status_code'] = STATUS_OK;
        if(!empty($error)){
            $output['error'] = $error;
        }
        return json_encode($output);
    }

    
    public function get_plan_list($request, $response, $args){
        $error = [];
        $output = [];
        if(empty($request->getParams()['last_sync_time'])){
            $error[] = ERROR_MESSAGE['empty_sync_time'];
        }

        if(!empty($error)){
            $output['error'] = $error;
        }
        else{
            $req = [
                'last_sync_time' => $request->getParams()['last_sync_time']
            ];
            $results = [            
                'insert'=>[], 
                'update'=>[],
                'delete'=>[],
                'last_sync_time'=>date("Y-m-d").' '.$request->getParams()['last_sync_time']
            ];
            $output['request'] = $req;
            $output['result'] = $results;  
            $output['message'] = 'Flexi Plan list provided successfully.';
            $output['status_code'] = STATUS_OK; 
            $output['terms_url'] = 'https://flexiplan.grameenphone.com/flexiplan_test/terms_conditions';
            $output['offers_url'] = 'https://flexiplan.grameenphone.com/flexiplan_test/offers';
        }
        return json_encode($output);
    }       
        
    
    public function get_plans($request, $response, $args){                
        $output = [];
        $error = [];
        if(empty($request->getParam('last_sync_time'))){
            $error[] = ERROR_MESSAGE['empty_sync_time'];
        }
                   
        if(!empty($request->getParam('last_sync_time')) && strtotime($request->getParam('last_sync_time')) > $this->last_sync_time){            
            $error[] = ERROR_MESSAGE['sync_already_done'];
        }
        
        if(!empty($error)){
            $output['error'] = $error;
        }
        else{
            $req = [
                'last_sync_time' => $request->getParam('last_sync_time')
            ];
            
            $results = $this->get_current_plans($this->last_sync_time);
            $sms = $this->get_sms_prices();            
            $output['request'] = $req;
            $output['result'] = $results; 
            $output['sms'] = $sms; 
            $output['flexiplanOptions'] = $this->get_plan_options();       
            $output['message'] = 'Flexi Plan list provided successfully.';
            $output['status_code'] = '200'; 
            $output['terms_url'] = 'https://flexiplan.grameenphone.com/flexiplan_test/terms_conditions';
            $output['offers_url'] = 'https://flexiplan.grameenphone.com/flexiplan_test/offers';
        }
        
        return json_encode($output);
    }
    
    
    public function verify_me($request, $response, $args){        
        $output = [];
        $error = [];
        if(empty($request->getParam('phone'))){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }        
        $req = [
            'phone' => $request->getParam('phone')
        ];
        
        $results = [];
        $output['request'] = $req;
        $output['result'] = $results;        
        $output['message'] = 'Please verify your phone number.';
        $output['status_code'] = '403'; 
        $output['update'] = '0';
        $output['force'] = '0';        
        if(!empty($error)){
            $output['error'] = $error;
        }
        
        return json_encode($output);        
    } 


    public function save_token($request, $response, $args){
        $output = [];
        $error = [];
        if(empty($request->getParams()['phone'])){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }
        if(empty($request->getParams()['token'])){
            $error[] = ERROR_MESSAGE['token_empty'];
        }        
        /* token saving */
        $tokenData = array( 
            'Token' => array(
                'customer_id' => '2345', 
                'token'       => $request->getParam('token'),
                'platform'    => 'android', 
                'is_manual'   => 0, 
                'id' => 'ID001',
            ) 
        );

        $file = fopen('../storage/customer/customer_tokens/ctok_'.$request->getParams()['phone'].'.txt',"w+");
        $flag = fwrite($file,json_encode($tokenData));
        fclose($file);
        if($flag){            
            $output['message'] = (ENVIRONMENT == 'live') ? ('Token saved successfully and it will be used for push notification.') : 'Token saved successfully';        }
        else{
            $output['message'] = 'Failed to save Token data. Please try again.';
        }
        $req = [
            'phone' => $request->getParams('phone'),
            'token' => $request->getParams('token')
        ];
        $results = [];
        $output['request'] = $req;
        $output['result'] = $results;        
        $output['status_code'] = '403';         
        if(!empty($error)){
            $output['error'] = $error;
        }
        return json_encode($output); 
    }


    public function get_latest_version($request, $response, $args){
        $versionNumber = 0;
        $latestVersions = '12|4|2';        
        list( $android, $iOS, $windows ) = explode( '|', trim( $latestVersions ) );
        switch( $this->platform ) {
            case 'android':
            case 'Android':
            case 'ANDROID':
                $versionNumber = $android;

            case 'ios':
            case 'iOS':
            case 'IOS':
                $versionNumber = $iOS;

            case 'windows':
            case 'Windows':
            case 'WINDOWS':
                $versionNumber = $windows;

            default:
                $versionNumber = 0;
        }

        //** sending static version 12. NEED TO REMOVE */
        $versionNumber = 12;
            
        $this->output['request'] = [];
        $this->output['result'] = $versionNumber;        
        $this->output['message'] = 'Latest version is: '.$versionNumber;
        $this->output['status_code'] = '200';
        
        return json_encode($this->output); 
    }


    public function purchase_plan($request, $response, $args){
        $output = [];
        $error = [];
        $op_flag = FALSE;
        $server_resp = [];   
        $msg = '';     
        switch(ENVIRONMENT){
            case 'production':
                /* TEMP BLOCK FOR LIVE OPERATION. NEED TO RELEASE FOR PRODUCTION */
                /*    
                try{
                    $url = $this->getESBBaseURL( 'campaign_activation' ) . '&msisdn=88' . trim( $this->input['my_number'] ) . '&keyword=campaignid:' . trim( $plan['FlexiPlan']['campaign_code'] );
                    if ( $this->input['my_number'] != $this->input['purchase_number'] ) {
                        $url .= '&remarks=referral:88' . trim( $this->input['purchase_number'] );
                    }
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, $url );
                    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/xml' ) );
                    curl_setopt( $ch, CURLOPT_USERPWD, ESB_USERNAME . ':' . ESB_PASSWORD );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
                    $response = curl_exec( $ch );
                    curl_close( $ch );
                    if ( empty( $response ) ) {
                        throw new Exception( 'ESB is not responding. Please try again later.', STATUS_SERVER_ERROR );
                    }
                    
                    $xml = new SimpleXMLElement( $response );
                    $response = json_decode( json_encode( $xml ), TRUE );
                    if ( !empty( $response[0] ) && trim( $response[0] ) == 'Unauthorized access denied!' ) {
                        throw new Exception( 'ESB is not responding. Please try again later.', STATUS_SERVER_ERROR );
                    }
                    
                    if ( trim( $response['remarks'] ) == 'Insufficient balance' ) {
                        $recharge = TRUE;
                    }
                }
                catch (Exception $e){
                    $error['server_exception'] = $e;
                }

                */
            break; 


            case 'development':
                $purchase_for = Settings::where(['name'=>'purchase_for'])->get(['value'])->first()->toArray();

                switch($purchase_for['value']){
                    case 'insufficient_balance':
                        $server_resp = array(
                            'recharge' => TRUE,
                            'ticketid' => 0,
                            'status'   => STATUS_OK,
                            'status_for' => 'insufficient_balance',
                            'remarks'  => 'Insufficient balance',
                            'heading' => 'Insufficient balance',
                            'body' => 'You do not have enough balance to buy the plan. Press OK to recharge online'
                        );
                    break;

                    case 'success':
                        $server_resp = array(
                            'ticketid' => 0,
                            'status'   => STATUS_OK,
                            'status_for' => 'success',
                            'remarks'  => 'Successfully forwarded',                            
                        );
                    break;

                    default:
                        $server_resp = array(
                            'ticketid' => 0,
                            'status'   => 'Abnormally terminated',
                            'status_for' => 'Abnormally terminated',
                            'remarks'  => 'Invalid request for',
                        );
                }
                $op_flag = TRUE;
            break; 
            default:

        }
        

        if($op_flag){
            $planInfo = [
                'net_type' => $request->getParam('net_type'),
                'data' => $request->getParam('data'),
                'voice' => $request->getParam('voice'),
                'validity' => $request->getParam('validity'),
                'sms' => $request->getParam('sms'),
                'flexi_plan_price' => $request->getParam('flexi_plan_price'),
                'discount' => $request->getParam('discount'),
            ];

            $output = [
                'customer_id' => '99',
                'version_code'    => 'xx',                
                'msisdn'       => $request->getParam('phone'),
                'platform' => $request->getParam('platform'),
                'imei' => $request->getParam('imei'),
                'purchase_number' => 'FP00054',
                'transaction_id'  => trim($server_resp['ticketid'] ),
                'remarks'         => trim($server_resp['remarks'] ),
                'status'          => trim($server_resp['status_for']),
            ];
            
            $output = array_merge($output, $planInfo);
            
            $flag = PurchasePlansModel::create($output);
            if($flag['id']){
                $file = fopen('../storage/PurchaseHistory/purchase_history_'.date("Y-m-d").'.txt',"a");
                $flag = fwrite($file,json_encode($flag));
                fclose($file);
                if($output['status'] == 'success'){
                    $msg = SUCCESS_MESSAGE['plan_purchase'];
                }
                else if($output['status'] == 'insufficient_balance'){
                    $msg = ERROR_MESSAGE['insufficient_balance'];
                }
                else {
                    $msg = ERROR_MESSAGE['abnormal_termination'];
                }
                
            }
        }
        return json_encode($server_resp);
        
        /*    
        $output['request'] = [];
        $output['result'] = [];        
        
        $output['status_code'] = '200';
        $output['update'] = 1;
        $output['force'] = 1;
        
        $output['free'] = '';
        $output['free_plan_id'] = '';
        $output['recharge_url'] = '';
        $output['message'] = 'Thank you for purchasing a flexiplan.';
        */
         
    }


    public function get_free_data($request, $response, $args){
        $output = [];
        $error = [];
        if(empty($request->getParams()['phone'])){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }        
        if(empty($request->getParams()['plan_id'])){
            $error[] = ERROR_MESSAGE['plan_id_empty'];
        }        
        
        // $tokenData = array( 
        //     'Token' => array(
        //         'customer_id' => '2345', // $customer['Customer']['id'],
        //         'token'       => $request->getParams()['token'],
        //         'platform'    => 'android', //$this->platform,
        //         'is_manual'   => 0, //NO,
        //         'id' => 'ID001',
        //     ) 
        // );

        if(empty($error)){
            $req = [
                'phone' => $request->getParams()['phone'],
                'plan_id' => $request->getParams()['plan_id']            
            ];
            $results = [];
            $output['request'] = $req;
            $output['result'] = [];        
            $output['message'] = 'Getting free data';
            $output['status_code'] = '200';
        }
        
        if(!empty($error)){
            $output['error'] = $error;
        }
        return json_encode($output); 
    }
    
    
    public function get_purchase_history($request, $response){
        $output = [];
        $error = [];
        if(empty($request->getParams()['phone'])){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }        
              
        
        // $tokenData = array( 
        //     'Token' => array(
        //         'customer_id' => '2345', // $customer['Customer']['id'],
        //         'token'       => $request->getParams()['token'],
        //         'platform'    => 'android', //$this->platform,
        //         'is_manual'   => 0, //NO,
        //         'id' => 'ID001',
        //     ) 
        // );

        if(empty($error)){
            
            $req = [
                'phone' => $request->getParams()['phone']
            ];
            
            $results = [];
            $output['request'] = $req;

            $res = PurchasePlansModel::where(['msisdn'=>$request->getParam('phone')])->get()->toArray();
            if(!empty($res) && is_array($res)){
                foreach($res AS $r){
                    $results[] = [
                        'date' => $r['created_at'],
                        'transaction_id' => $r['transaction_id'],
                        'platform' => $r['platform'],
                        'imei' => $r['imei'],
                        'net_type' => $r['net_type'],
                        'data' => $r['data'],                        
                        'voice' => $r['voice'],
                        'validity' => $r['validity'],
                        'sms' => $r['sms'],
                        'price' => $r['flexi_plan_price'],
                        'discount' => $r['discount'],
                    ];
                }
                
                $output['status_code'] = STATUS_OK;
                $output['result'] = $results;        
            }
        }
        
        if(!empty($error)){
            $output['error'] = $error;
        }
        return json_encode($output); 
    }


    public function share_on_facebook($request, $response){
        $output = [];
        $error = [];
        if(empty($request->getParams()['phone'])){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }        
         
        if(empty($error)){
            $req = [
                'phone' => $request->getParams()['phone']
            ];
            $results = [];
            $output['request'] = $req;
            $output['result'] = $results;        
            $output['message'] = 'Share on facebook message';
            $output['status_code'] = '200';
        }
        
        if(!empty($error)){
            $output['error'] = $error;
        }
        return json_encode($output); 
    }


    public function save_recharge_result($request, $response){
        $output = [];
        $error = [];        
        if(empty($request->getParams()['phone'])){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }        
        if(empty($request->getParams()['amount'])){
            $error[] = ERROR_MESSAGE['amount_empty'];
        }   
        if(empty($request->getParams()['status'])){
            $error[] = ERROR_MESSAGE['status_empty'];
        }
        
        // $tokenData = array( 
        //     'Token' => array(
        //         'customer_id' => '2345', // $customer['Customer']['id'],
        //         'token'       => $request->getParams()['token'],
        //         'platform'    => 'android', //$this->platform,
        //         'is_manual'   => 0, //NO,
        //         'id' => 'ID001',
        //     ) 
        // );

        if(empty($error)){
            $req = [
                'phone' => $request->getParams()['phone'],
                'amount' => $request->getParams()['amount'],
                'status' => $request->getParams()['status'],
            ];
            $results = [];
            $output['request'] = $req;
            $output['result'] = $results;        
            $output['message'] = 'Save recharge result message';
            $output['status_code'] = '200';
        }
        
        if(!empty($error)){
            $output['error'] = $error;
        }
        return json_encode($output); 
    }

    
    public function get_success_page_contents($request, $response){
        $error = [];
                
        // $tokenData = array( 
        //     'Token' => array(
        //         'customer_id' => '2345', // $customer['Customer']['id'],
        //         'token'       => $request->getParams()['token'],
        //         'platform'    => 'android', //$this->platform,
        //         'is_manual'   => 0, //NO,
        //         'id' => 'ID001',
        //     ) 
        // );

        if(empty($error)){
            $req = [];
            $results = [
                'recommended_apps' => [
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/comoyo.png',
                        'link' => 'https://play.google.com/store/apps/details?id=com.vopium.comoyo&hl=en',
                        'status' => '1'
                    ],
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/musicapp.png',
                        'link' => 'https://play.google.com/store/apps/details?id=co.gpmusic&hl=en',
                        'status' => '1'
                    ],
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/wowbox.png',
                        'link' => 'https://play.google.com/store/apps/details?id=com.telenor.ads&hl=en',
                        'status' => '1'
                    ]
                ],
                'recommended_offers' => [
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/slider1.png',
                        'link' => '',
                        'status' => '1'
                    ],
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/slider2.png',
                        'link' => '',
                        'status' => '1'
                    ],
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/slider3.png',
                        'link' => '',
                        'status' => '1'
                    ]
                ],
                'bottom_advertisements' => [
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/bottom1.png',
                        'link' => 'http://www.grameenphone.com/personal/offers/htc-one-a9-44990tk',
                        'status' => '1'
                    ],
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/bottom2.png',
                        'link' => 'http://www.grameenphone.com/personal/offers/galaxy-s-7-edge',
                        'status' => '1'
                    ],
                    [
                        'file' => 'http://flexiplantest.grameenphone.com/files/no_offer_bg.png',
                        'link' => '',
                        'status' => '1'
                    ]
                ]        
            ];
            $this->output['request'] = $req;
            $this->output['result'] = $results;        
            $this->output['message'] = 'Get success page content';
            $this->output['status_code'] = '200';
        }
        
        if(!empty($error)){
            $this->output['error'] = $error;
        }
        return json_encode($this->output); 
    }


    public function check_3g_login($request, $response){
        $login_3g = Settings::where(['name'=>'check_3g_login'])->get(['value'])->first()->toArray();
        
        $resp = [
        	'allow_3g'=>$login_3g['value']        	
        ];
        $temp = $this->get_my_phone('','');
        
        $own_msisdn = (array)json_decode($temp);
        if(!empty($own_msisdn) && is_array($own_msisdn)){
        	$resp = array_merge($resp,$own_msisdn);
        }
        return !empty($login_3g['value']) ? json_encode($resp) : json_encode(array('allow_3g'=>'undefined'));         
    }


    public function get_my_phone($request, $response){
        $digits = 8;
        $gen = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
        return  json_encode([
            'msisdn'=>'017'.$gen,
            'status' => STATUS_OK
        ]); 
    }
    
    public function get_recharge_url($request, $response){
        $output = [];
        $error = [];
        if(empty($request->getParam('phone'))){
            $error[] = ERROR_MESSAGE['phone_empty'];
        }
        
        if(empty($request->getParam('amount'))){
            $error[] = ERROR_MESSAGE['recharge_amount_empty'];
        }
        else if(!is_numeric($request->getParam('amount'))){
            $error[] = ERROR_MESSAGE['invalid_recharge_amount'];
        }

        if(empty($error)){
            $params = [
                'amount'=>$request->getParam('amount'),
                'mobile'=>$request->getParam('phone'),
            ];
            $pwr = new PortWallet;
            
            $pwResponse = $pwr->index($params);
            $pwResponse = json_decode($pwResponse);
            if(!empty($pwResponse->url)){
                $output['recharge_url'] = $pwResponse->url;

                $info = [
                    'recharge_url' => $output['recharge_url'],
                    'msisdn' => $request->getParam('phone'),
                    'amount' => $request->getParam('amount')
                ];
                
                /* NO LONGER REQUIRED ANY LOG */
                /*
                $flag = RechargeUrlsModel::create($info);
                if($flag){
                    $this->logInFile('../storage/customer/recharge_url_history/', implode('|',$info), 'purchae-history_'.date("Y-m-d").'.txt' , 'a');
                }
                */
                
            }
        }
        $output['error'] = $error;
        return json_encode($output);        
    }




    /*
    API methods ended here 
    */


    public function verify_gp_number($number){        
        $flag = TRUE;
        if ( strlen($number) != 11 || substr( $number, 0, 3 ) != '017' ) {
            $flag = FALSE;
        }
        return $flag;
    }

    /**
     * Set device Platform
     */
    private function setPlatform() {
        
    }

    
    public function get_plan_options(){
        $options = []; 
        $results = FlexiplanOption::all();   
        foreach($results AS $result){
            $options[] = [
                'option_type' => $result->option_type,
                'option_value' => $result->option_value,
            ];
        }        
        return $options;        
    }


    function get_current_plans($last_sync_time){        
        $plans = FlexiPlan::get()->toArray();
        $results = [];
        foreach($plans AS $plan){                        
            
            if($plan['updated_at'] > $last_sync_time){
                continue;
            }
            $group = '';
            switch($plan['status']){
                case '0':
                    $group = 'delete';
                break;

                case '1':
                    $group = 'insert';
                break;

                case '2':
                    $group = 'update';
                break;

                default:

            }
            $results[$group][$plan['price_type'].'_'.$plan['net_type'].'_'.$plan['validity']][] = [
                'data_from' => $plan['mb_start'],
                'data_to' => $plan['mb_end'],
                'voice_from' => $plan['voice_start'],
                'voice_to' => $plan['voice_end'],
                'price' => $plan['price'],
                'market_price' => $plan['market_price']
            ];
        }        
        return $results;
        
        
        /* this will not execute */
        
        $info = [];
        $type = ['appm','appmb'];
        $net = ['onnet','anynet'];
        $validity = ['1','7','15','30'];
        $mb_range = ['0-3', '4-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39', '40-44', '45-49', '50-59', '60-69', '70-79', '80-89', '90-99', '100-149', '150-199', '200-249', '250-299', '300-349', '350-399', '400-449', '450-499', '500-599', '600-699', '700-799', '800-899', '900-1023', '1024-1535', '1536-2047', '2048-2559', '2560-3071', '3072-4095', '4096-5119', '5120-6143', '6144-7167', '7168-8191', '8192-9215', '9216-10239', '10240-15359', '15360-20479', '20480'];
        $munite_range = ['0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-39', '40-49', '50-59', '60-69', '70-79', '80-89', '90-99', '100-149', '150-199', '200-249', '250-299', '300-349', '350-399', '400-449', '450-499', '500-599', '600-699', '700-799', '800-899', '900-999', '1000-1499', '1500-1999', '2000'];
        
        $type = ['appm','appmb'];
        $net = ['onnet','anynet'];
        $validity = ['1','7'];
        $mb_range = ['0-3', '4-9'];
        $munite_range = ['0-4', '5-9'];
        
        
        $price = [0.25,0.45,0.36, 0.85];
        
        foreach($type AS $t){
            foreach($net AS $n){
                foreach($validity AS $v){
                    foreach($mb_range AS $mb){                     
                        $str = $t.', '.$n.', '.$v.', ';
                        $temp = explode('-',$mb);
                        // $info[] = $temp;
                        if(isset($temp[0]) && $temp[0] != NULL){
                            $df = $temp[0];                            
                        }
                        else{
                            $df = 'inv';  
                        }
                        
                        if(isset($temp[1]) && $temp[1] != NULL){
                            $dt = $temp[1];                            
                        }
                        else{
                            $dt = 'inv'; 
                        }
                        // $info[] = $str;
                        foreach($munite_range AS $mr){
                            $str2 = $str.', ';
                            $temp2 = explode('-',$mr);                            
                            if(isset($temp2[0]) && $temp2[0] != NULL){
                                $vf = $temp2[0];                            
                            }
                            else{
                                $vf = 'inv';  
                            }
                            
                            if(isset($temp2[1]) && $temp2[1] != NULL){
                                $vt = $temp2[1];                            
                            }
                            else{
                                $vt = 'inv'; 
                            }
                            $pp = $price[array_rand($price)];
                            
                            $ar = [                                
                                'data_from' => $df,
                                'data_to' => $dt,
                                'voice_from' => $vf,
                                'voice_to' => $vt,
                                'price' => $pp,
                                'market_price' => ($pp+1)
                            ];
                            $info[$t.'_'.$n.'_'.$v][] = $ar;
                        }
                    }
                }
            }
        }
        return $info;
        
    }


    public function get_sms_prices(){
        $sms = [];
        $results = SmsPrice::where(['status'=>'1'])->get()->toArray();        
        foreach($results AS $record){            
            $sms[] = [
                'sms_count' => $record['sms_count'],
                'price' => $record['price'], 
                'price_per_sms' => $record['price_per_sms'],                
                'market_price' => $record['market_price'],
                'market_price_per_sms' => $record['market_price_per_sms'],
            ];
        }
        return $sms;
    }

    /* getting IMEI number */
    public function get_imei_number(){
        /* hardcoded device info. */
        $imei = 'IME0010009EVL';
        

        return $imei;
    }
    
};