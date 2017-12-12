<?php 
namespace App\Controllers\Admin\Import;

use App\Controllers\Controller As Controller;
use App\Models\Admin\Flexiplan AS FlexiPlan;
use App\Models\Admin\FlexiplanOption;

Class ImportOptionsController extends Controller{
    public function index($request, $response){
        if(!empty($_SESSION['uid'])){            
            
            
            return $this->view->render($response, 'import/importOptions.twig');
        }
        else{
            return $this->response->withRedirect($this->router->pathFor('login'));
        }
    }

    public function postimportOptions($request, $response){
        $error = [];
        if(!empty($_FILES['import_plan_options']) 
            && $_FILES['import_plan_options']['error'] == '0' 
            && $_FILES['import_plan_options']['size'] > 0 
            && $_FILES['import_plan_options']['type'] == 'text/csv'){
                
                $record_heading = ['optiontype','optionvalue'];

                $file = fopen($_FILES['import_plan_options']['tmp_name'],"r");
                
                $records = [];
                $c = 0;
                while($row = fgetcsv($file)){
                    if($c < 1){
                        $temp = [];
                        foreach($row AS $r){
                            $temp[] = strtolower(str_replace(" ",'',$r));
                        }
                        if($temp !== $record_heading){
                            $error['file_pattern_missmatch'] = ERROR_MESSAGE['invalid_csv_columns'];
                            break;
                        }
                    }
                    $records[] = $row;
                    $c++;
                }
                
                if(empty($error)){
                    $map = [];
                    $record_heading = $records[0];
                    foreach($record_heading AS $rk=>$rh){
                        $map[$rk] = str_replace(" ","",strtolower($rh));
                    }
            
                    array_shift($records);
                    
                    if(!empty($records) && is_array($records)){                        
                        FlexiplanOption::truncate();
                        foreach($records AS $record){
                            $k = FlexiplanOption::create([
                                'option_type'=>$record[array_search('optiontype',$map)],
                                'option_value'=> $record[array_search('optionvalue',$map)]
                            ]);
                        }
                        $this->flash->addMessage('success', 'Plan options imported successfully');
                        /* Updating last sync time */
                        $this->updateLastSyncTime();
                        
                    }
                    return $this->response->withRedirect($this->router->pathFor('importOptions'));
                }
                else{
                    $this->flash->addMessage('danger', implode(', ',$error));
                    return $this->response->withRedirect($this->router->pathFor('importOptions'));
                }               
        }
        else{            
            return $this->response->withRedirect($this->router->pathFor('importOptins'));
        }
    }
}