<?php 
namespace App\Controllers\Admin;

use Illuminate\Pagination\Paginator;
use App\Controllers\Controller AS Controller;
use App\Models\Admin\Flexiplan AS FlexiPlan;
use App\Models\Admin\FlexiplanOption AS FlexiPlanOptions;

Class Plans extends Controller{    
    function index($request, $response, $args){        
        if(!empty($_SESSION['uid'])){  
            $plans = FlexiPlan::where(['status'=>'1'])->paginate(10, ['*'], 'page', $request->getParam('page'))->toArray();            
            $data = ['data' => $plans];            
            return $this->view->render($response, 'pages/plans.twig', $data);
        }
        else{            
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }
    }

    public function options($request, $response, $args){
        if(!empty($_SESSION['uid'])){  
            $options = FlexiPlanOptions::where(['status'=>'1'])->paginate(10, ['*'], 'page', $request->getParam('page'))->toArray();            
            $data = ['data' => $options];            
            return $this->view->render($response, 'pages/options.twig', $data);
        }
        else{            
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }
    }
}