<?php 
namespace App\Controllers\Admin;

use App\Controllers\Controller AS Controller;

Class PortWalletController extends Controller{        
    public function index($values){ 
        $return_url = '';
        $cTime = time();
        $fields = [
          'transaction_id' => substr(md5($cTime),0,8),
          'source' =>'android'
        ];
        $fields = array_merge($fields,$values);
        
        $header = array( "Authorization: Basic " . base64_encode( PORTWALLET_APP_KEY . ":" . $cTime ), "Content-Type: application/x-www-form-urlencoded" );
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, "https://gp.portwallet.com/flexiplan-app/request.php" );
        curl_setopt( $ch, CURLOPT_POST, TRUE );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($fields) );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        $response = curl_exec( $ch );
        $response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        
        return $response;        
    }
}