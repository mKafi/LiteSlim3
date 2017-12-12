<?php 
namespace App\Controllers\Admin;

use App\Controllers\Controller AS Controller;

use Illuminate\Pagination\Paginator;
use App\Models\Admin\CustomerModel AS Customers;
use App\Models\Admin\PurchasePlansModel AS Purchases;

Class CustomerController extends Controller{    
    public function index($request, $response){
        if(!empty($_SESSION['uid'])){                        
            $vars = ['page_title' => 'Customer'];
            $vars['data'] = Customers::get()->toArray();
            return $this->view->render($response, 'pages/customer.twig', $vars);  
        }
        else{
            return $this->response->withRedirect('signin');
        }
    }

    public function purchased($request, $response){
        if(!empty($_SESSION['uid'])){                        
            $vars = ['page_title' => 'Purchased'];
            $vars['data'] = Purchases::get()->toArray();            
            return $this->view->render($response, 'pages/purchased.twig', $vars);  
        }
        else{
            return $this->response->withRedirect('signin');
        }
    }
}