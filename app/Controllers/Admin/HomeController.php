<?php 
namespace App\Controllers\Admin;

use App\Controllers\Controller AS Controller;

Class HomeController extends Controller{    
    public function index($request, $response){
        
        if(!empty($_SESSION['uid'])){ 
            $vars = ['user' => $this->userLogged, 'page_title' => 'Dashboard'];
            return $this->view->render($response, 'home.twig', $vars);  
        }
        else{
            return $this->response->withRedirect('signin');
        }
    }
}