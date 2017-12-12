<?php 
$app->post('/foo',function($request, $response, $args){
    $post = $_POST;
    echo '<pre>'; print_r($post); echo '</pre>';
    return 'test post';
    die();
});

$app->get('/test_route', function($request, $response, $args=''){
    $vars = [];
    if($args){
        $vars[] = ['arg'=>$args,'test'=>'Test'];
    }
    else{
        $vars[] = ['test1'=>'Test One','test2'=>'Test2'];
    }
    return json_encode(array('test1'=>'Test One','test2'=>'Test2'));
});

/* Generic pages A */
$app->get('/','HomeController:index')->setName('home');
$app->get('/logout','SignupController:logout')->setName('logout');
$app->get('/signup','SignupController:index')->setName('signup');
$app->post('/signup','SignupController:postSignup')->setName('postSignup');
$app->get('/signin','SignupController:signin')->setName('signin');
$app->post('/signin','SignupController:postSignin')->setName('postSignin'); 
$app->get('/plans','Plans:index')->setName('plans');
$app->get('/options','Plans:options')->setName('options');
$app->get('/configuration','ConfigurationController:index')->setName('config');
$app->post('/configuration','ConfigurationController:post_configurations')->setName('config-post');
$app->post('/settings-post','ImportController:settings_post');
/* Generic pages Z */

/* Reports A */
$app->get('/customers','CustomerController:index')->setName('customers');
$app->get('/purchased','CustomerController:purchased')->setName('purchased');
/* Reports Z */

/* Misc links A */
$app->get('/toolbox','ToolBox:get_environment'); 
$app->get('/import-plan-options','ImportController:import_flexiplan_options')->setName('import.options');
$app->get('/post-plan-options','ImportController:post_plan_options')->setName('options.post'); 
/* Misc links Z */

/* Plan and Options importing A */
$app->get('/import/plans','ImportPlansController:index')->setName('importPlans');
$app->post('/import/plans','ImportPlansController:postImportPlans')->setName('importPlanPost');
$app->get('/import/options','ImportOptionsController:index')->setName('importOptions');
$app->post('/import/options','ImportOptionsController:postimportOptions')->setName('importOptionsPost');
/* Plan and Options importing Z */

/* User routes A */
$app->get('/users','UserController:index')->setName('users'); 
$app->get('/user','UserController:edit')->setName('user.edit'); 
$app->post('/user','UserController:editPost')->setName('user.edit.submit');
$app->get('/user/create','UserController:create')->setName('user.create'); 
$app->post('/user/create','UserController:createPost')->setName('user.create.submit'); 
/* User routes Z */

/* API Route (Grouped routes) A */
$app->group('/api',function(){
    $this->map(['GET', 'DELETE', 'PATCH', 'PUT'], '', function ($request, $response, $args) {
        $response->write('In API root');
    });
    
    /* all route setup for API version v1 */
    $this->group('/v1',function(){
        $this->post('/get_pin','SettingsController:get_pin');
        $this->post('/verify_pin','SettingsController:verify_pin');
        $this->get('/get_settings','SettingsController:get_settings');        
        $this->post('/get_plan_list','SettingsController:get_plan_list');
        $this->post('/get_plans','SettingsController:get_plans');
        $this->post('/verify_me','SettingsController:verify_me');   
        $this->post('/save_token','SettingsController:save_token');
        
        $this->post('/get_latest_version','SettingsController:get_latest_version');  
        $this->post('/purchase_plan','SettingsController:purchase_plan');
        $this->post('/get_free_data','SettingsController:get_free_data');

        $this->post('/get_purchase_history','SettingsController:get_purchase_history');
        $this->post('/share_on_facebook','SettingsController:share_on_facebook');
        $this->post('/save_recharge_result','SettingsController:save_recharge_result');
        $this->post('/get_success_page_contents','SettingsController:get_success_page_contents');

        $this->post('/get_plan_options','SettingsController:get_plan_options');
          
        $this->post('/get_otp','OtpController:index');
        $this->post('/subscribe_plan','SubscribePlanController:index');

        $this->post('/check_3g_login','SettingsController:check_3g_login');

        $this->post('/myphone_number','SettingsController:get_my_phone');

        $this->post('/get_recharge_url','SettingsController:get_recharge_url');
    });    
});
/* API Route (Grouped routes) Z */

