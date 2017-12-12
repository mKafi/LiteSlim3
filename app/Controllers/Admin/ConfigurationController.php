<?php 
namespace App\Controllers\Admin;

use App\Controllers\Controller AS Controller;

use App\Models\Admin\SettingsModel AS Settings;

Class ConfigurationController extends Controller{        
    public function index($request, $response){ 
        if(!empty($_SESSION['uid'])){            
            $records = Settings::where(['status'=>'1'])->paginate(4)->toArray();
            
            $settings_vals = [];
            
            foreach($records['data'] AS $rec){
                $settings_vals[$rec['settings_group']][] = [
                    'field_title' => $rec['title'],
                    'field_name' => $rec['name'],
                    'data_type' => $rec['type'],
                    'value_options' => !empty($rec['value_options']) ? unserialize($rec['value_options']) : '',
                    'value' => $rec['value']
                ];
            }

            // echo '<pre>'; print_r($settings_vals); echo '</pre>';

            return $this->view->render($response, 'pages/configurations.twig', ['records' => $settings_vals]);
        }
        else{
            return $this->response->withRedirect($this->router->pathFor('login'));
        }
    }

    public function post_configurations($request, $response){
        if(!empty($request->getParams())){            
            foreach($request->getParams() AS $k => $v){
                Settings::where(['name'=>$k])->update(['value'=>$v]);
            }
            $this->flash->addMessage('success', 'Configuration updated successfully');            
        }
        return $this->response->withRedirect($this->router->pathFor('config'));
    }
}