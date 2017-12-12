<?php 

define( 'ENVIRONMENT', 'development' ); /* development, test, production */
define( 'ENVIRONMENT_LIVE', 'test' ); /* test, production */

define( 'PROJECT_TITLE', 'GP Flexi Plan' );
define( 'PROJECT_NAME', 'gp_flexi_plan' );

define( 'APP_UPDATE_BONUS', TRUE );

define( 'DB_HOST_LIVE', '10.10.22.245' );
define( 'DB_NAME_LIVE', 'flexiplan' );
define( 'DB_USERNAME_LIVE', 'root' );
define( 'DB_PASSWORD_LIVE', 'flexidb@1234' );

/* PortWallet App Key */
define( 'PORTWALLET_APP_KEY', '74239e3d26a8e1578d9a8f15ce6ed0b9' );

/* GP ESB API settings */
define( 'ESB_PRODUCTION_URL', 'http://192.168.206.49:3240' );
define( 'ESB_TEST_URL', 'http://192.168.206.152:6240' );

switch(ENVIRONMENT){
    case 'production':
        define('ESB_URL', ESB_PRODUCTION_URL);
    break;
    case 'test':
        define('ESB_URL', ESB_TEST_URL);
    break; 
    default:
        define('ESB_URL', '');
}

define( 'ESB_USERNAME', 'flxplnapp' );
define( 'ESB_PASSWORD', 'flxp!n@ESB123!' );
define( 'ESB_TRANSACTION_ID_LENGTH', 20 );
define( 'PIN_VALIDITY', 60 ); /* minutes */
define( 'UNVERIFIED', 'Not Verified' ); 
define( 'STATUS_CODE_SUCCESS', '200' ); 

define( 'STATUS_OK', 200 );
define( 'STATUS_LOGIN_SUCCESSFUL', 201 );
define( 'STATUS_UNAUTHORIZED', 401 );
define( 'STATUS_FORBIDDEN', 403 );
define( 'STATUS_NOT_FOUND', 404 );
define( 'STATUS_INPUT_UNACCEPTABLE', 406 );
define( 'STATUS_INSUFFICIENT_BALANCE', 407 );
define( 'STATUS_GIFT_UNAVAILABLE', 408 );
define( 'STATUS_DB_UPDATE_AVAILABLE', 409 );
define( 'STATUS_SERVER_ERROR', 500 );

$success_message = [
    'plan_purchase' => 'You have purchased plan successfully',
    'success_user_save' => 'User information saved successfully',
];
define('SUCCESS_MESSAGE',$success_message);

$error_message = [
    'abnormal_termination' => 'Abnormal termination',
    'phone_empty' => 'Provide valid phone number',
    'invalid_phone' => 'Provide valid phone number',
    'access_denied' => 'Access Denied',
    'pin_save_error' => 'Verification PIN generation process failed. Please try again.',
    'empty_sync_time' => 'Sync time is empty',
    'token_empty' => 'Token is empty',
    'plan_id_empty' => 'Plan is empty',
    'amount_empty' => 'Amount is empty',
    'status_empty' => 'Status is empty',
    'pin_invalid' => 'Pin invalid',
    'pin_empty' => 'Pin invalid',
    'pin_verification_failed' => 'Pin verification process failed. Please try again.',
    'pin_not_found' => 'Pin not found at database. Start again',
    'invalid_csv_columns' => 'Invalid CSV file column pattern provided.',
    'esb_not_responding' => 'ESB is not responding. Please try again later',
    'pin_generation_faild' => 'PIN generation process failed. Please try again',
    'sync_already_done' => 'Sync already done',
    'try_limit_over' => 'You have tried more then 3 times and this PIN is no longer available. Restart the process',
    'recharge_amount_empty' => 'Recharge amount should not empaty',
    'invalid_recharge_amount' => 'Invalid recharge amount',
    'insufficient_balance' => 'Insufficient balance to purchase this plan',
    'failed_user_save' => 'Failed to save user information',
];
define('ERROR_MESSAGE',$error_message);