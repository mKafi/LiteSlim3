<?php 
namespace App\Controllers\Admin;

use Illuminate\Pagination\Paginator;
use App\Controllers\Controller AS Controller;
use App\Models\Admin\Auth\UserModel AS User;


Class UserController extends Controller{    
    function index($request, $response, $args){        
        if(!empty($_SESSION['uid'])){  
            $users = User::paginate(50, ['*'], 'page', $request->getParam('page'))->toArray(); 
            if(!empty($users['data']) && is_array($users['data'])){
                $roles = $this->getUserRoles();
                foreach($users['data'] AS $key => $user){
                    $users['data'][$key]['role'] = $roles[$user['rid']];
                }
            }
            return $this->view->render($response, 'pages/user/users.twig', ['data' => $users]);
        }
        else{            
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }
    }

    public function edit($request, $response, $args){
        if(!empty($_SESSION['uid'])){              
            $userInfo = User::where(["id"=>$request->getParam("id")])->first()->toArray();
            $roles = $this->getUserRoles();
            if(!empty($roles) && is_array($roles)){
                $userInfo['roles'] = $roles;
            }
            return $this->view->render($response, 'pages/user/userEdit.twig', ['data' => $userInfo]);
        }
        else{            
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }        
    }

    public function editPost($request, $response, $args){
        $postVal = $request->getParams();
        if(!empty($request->getParam('userId'))){            
            if(empty($request->getParam('password'))){
                unset($postVal['password']);
            }
            else{
                $postVal['password'] = password_hash($request->getParam('password'), PASSWORD_DEFAULT);
            }
            unset($postVal['userId']);
            $flag = User::where(['id'=>$request->getParam('userId')])->update($postVal);
            if($flag){
                $this->flash->addMessage('danger', SUCCESS_MESSAGE['success_user_save']);
            }
            else{
                $this->flash->addMessage('danger', ERROR_MESSAGE['failed_user_save']);
            }
            return $this->response->withRedirect($this->router->pathFor('users'));
        }
    }

    public function create($request, $response, $args){
        if(!empty($_SESSION['uid'])){              
            $userInfo = [];
            $roles = $this->getUserRoles();
            if(!empty($roles) && is_array($roles)){
                $userInfo['roles'] = $roles;
            }
            return $this->view->render($response, 'pages/user/userEdit.twig', ['data' => $userInfo]);
        }
        else{            
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }  
    }

    public function createPost($request, $response, $args){
        $postVal = $request->getParams();
        $postVal['password'] = password_hash($request->getParam('password'), PASSWORD_DEFAULT);        
        $flag = User::create($postVal);
        if($flag){
            $this->flash->addMessage('danger', SUCCESS_MESSAGE['success_user_save']);
        }
        else{
            $this->flash->addMessage('danger', ERROR_MESSAGE['failed_user_save']);
        }
        return $this->response->withRedirect($this->router->pathFor('users'));
    }
}