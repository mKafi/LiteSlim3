<?php 

namespace App\Controllers\API;

use App\Controllers\Controller AS Controller;

Class OtpController extends Controller{
    public function index($info){ 
        $result = [];
        if(ENVIRONMENT == 'development'){
            $result = [
                'ticketid' => 'myob-123456',
                'status'   => 'success',
                'remarks'  => $this->generateRandomString(4,2),
            ];
        }
        else{
            $opt_url = ESB_URL."/GenerateOTP/GenerateOTP_GPCRM_PS?";
            $opt_url .= "ChannelName=flxpln";
            $opt_url .= "&SessionID=".time();
            $opt_url .= "&service=abcd";
            $opt_url .= "&OTPLength=4";
            $opt_url .= "&MSISDN=".$info['msisdn'];
            $opt_url .= "&ShortCode=FlxPlan_PIN";
            if(!empty($info['return_response'])){
                $opt_url .= "&isResponseRequired=1";
            }
            
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $opt_url);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/xml' ) );
            curl_setopt( $ch, CURLOPT_USERPWD, ESB_USERNAME . ':' . ESB_PASSWORD );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
            $response = curl_exec( $ch );
            curl_close( $ch );
            
            $otp_response = (array)simplexml_load_string($response);
            if(!empty($otp_response) && is_array($otp_response)){
                $result = [
                    'ticketid' => $otp_response['ticketid'],
                    'status'   => $otp_response['status'],
                    'remarks'  => $otp_response['remarks'],
                ];
            }
        }
        $result['try_attemp'] = 0;
        return $result;
    }

    /**
     * Generate Transaction ID to communicate with ESB
     * @param string $service_id
     * @return string
     */
    public function generateTransactionId( $service_id ) {
        $transactionId = time() . $this->generateRandomString( 9, 7 );        
        return $transactionId;
    }

    /**
     * Generate Random String
     *
     * @param interger $length
     *
     * @return string
     */
    public function generateRandomString( $length = 29, $type = 1 ) {
        switch( $type ) {
            case 2:
                $characters = '0123456789';
                break;
            case 3:
                $characters = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 4:
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 5:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 6:
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 7:
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $randomString = '';
        for( $i = 0; $i < $length; $i++ ) {
            $randomString .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
        }

        return $randomString;
    }
}