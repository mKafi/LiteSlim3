<?php 

namespace App\Controllers\API;

use App\Controllers\Controller AS Controller;

Class SubscribePlanController extends Controller{
    public function __construct(){

    }

    public function index(){
        $opt_url = ESB_URL."/ManageProduct_FlexiPlan/ManageProductFlexiPlan_GPCRM_PS?";
        
        $opt_url .= "&OTPValue=";
        $opt_url .= "&msisdn=";
        $opt_url .= "&service=454";
        $opt_url .= "&ChannelName=";
        $opt_url .= "&ChannelPassword=test";
        $opt_url .= "&port=external";
        $opt_url .= "&serviceid=";
        $opt_url .= "&actioncode=subscription";
        $opt_url .= "&ProductId=FLEXI_PLAN";
        $opt_url .= "&data=";
        $opt_url .= "&dataUnit=";
        $opt_url .= "&voice=";
        $opt_url .= "&voiceUnit=";
        $opt_url .= "&voiceType=";
        $opt_url .= "&sms=";
        $opt_url .= "&validity=";
        $opt_url .= "&FlexiPriceWithTax=";
        $opt_url .= "&receiverNumber=";
        $opt_url .= "&flexiPrice=";
        $opt_url .= "&PriceSuppressionFlag=";
        $opt_url .= "&GreetingText=";
        echo $opt_url; 
        die();
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $opt_url);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/xml' ) );
        curl_setopt( $ch, CURLOPT_USERPWD, ESB_USERNAME . ':' . ESB_PASSWORD );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        $response = curl_exec( $ch );
        curl_close( $ch );
        
        echo '<pre>'; print_r($response); 

    }
}