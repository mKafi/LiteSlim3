<?php 
namespace App\Controllers\Admin\Auth;

use App\Controllers\Controller AS Controller;
use App\Models\Admin\Auth\UserModel AS User;

Class SignupController extends Controller{    
    public function index($request, $response){        
        return $this->view->render($response, 'auth/signup.twig');  
    }

    public function logout(){
        unset($_SESSION['uid']);
        return $this->response->withRedirect($this->router->pathFor('signin'));
    }

    public function postSignup($request, $response){   
        $user = User::create([
            'rid' => '1',
            'nick' => 'kafi',
            'name' => 'M U Kafi',
            'email' => $request->getParams()['user_email'],
            'phone' => '01770168228',
            'password' => password_hash($request->getParam('user_password'), PASSWORD_DEFAULT),
        ]);
        if($user){
            return $this->response->withRedirect($this->router->pathFor('signup'));
        }
    }

    public function signin($request, $response){
        if(empty($_SESSION['uid'])){
            return $this->view->render($response, 'auth/signin.twig'); 
        }
        echo 'Already logged in';        
    }

    public function postSignin($request, $response){
        $user = User::where('email',$request->getParam('user_email'))->first();        
        if(password_verify($request->getParam('user_password'), $user->password)){
            $_SESSION['uid'] = $user->id;
            return $this->response->withRedirect($this->router->pathFor('home'));
        }
        else{
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }
    }

    
}