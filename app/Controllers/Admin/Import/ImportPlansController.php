<?php 

namespace App\Controllers\Admin\Import;

use App\Controllers\Controller As Controller;
use App\Models\Admin\Flexiplan AS FlexiPlan;
//use App\Models\Admin\FlexiplanOption;

Class ImportPlansController extends Controller{
    
    public function index($request, $response){
        if(!empty($_SESSION['uid'])){            
            return $this->view->render($response, 'import/importPlans.twig');
        }
        else{            
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('login'));
        }
    }

    public function postImportPlans($request, $response){
        if(!empty($_SESSION['uid'])){            
            /*
            Array
            (
                [0] => Price Type
                [1] => Net Type
                [2] => Validity
                [3] => MB Start
                [4] => MB End
                [5] => Voice Start
                [6] => Voice End
                [7] => Price
                [8] => Market Price
            )
            */
            $error = [];
            if(!empty($_FILES['import_plans']) 
                && $_FILES['import_plans']['error'] == '0' 
                && $_FILES['import_plans']['size'] > 0 
                && $_FILES['import_plans']['type'] == 'text/csv'){
                    
                    $record_heading = ['pricetype','nettype','validity','mbstart','mbend','voicestart','voiceend','price','marketprice'];

                    $file = fopen($_FILES['import_plans']['tmp_name'],"r");
                    
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
                    fclose($file);

                    if(empty($error) && count($records) > 2){
                        $map = [];
                        $record_heading = $records[0];
                        foreach($record_heading AS $rk=>$rh){
                            $map[$rk] = str_replace(" ","",strtolower($rh));
                        }
                
                        array_shift($records);
                        if(!empty($records) && is_array($records)){
                            // FlexiPlan::truncate();
                            $excludeding_ids = [];
                            foreach($records AS $record){
                                $plan = FlexiPlan::where([
                                    'price_type'=>$record[array_search('pricetype',$map)],
                                    'net_type'=> $record[array_search('nettype',$map)],
                                    'validity'=>$record[array_search('validity',$map)],
                                    'mb_start'=>$record[array_search('mbstart',$map)],
                                    'mb_end'=> empty($record[array_search('mbend',$map)]) ? '-1' : $record[array_search('mbend',$map)],
                                    'voice_start'=>$record[array_search('voicestart',$map)],
                                    'voice_end'=> empty($record[array_search('voiceend',$map)]) ? '-1' : $record[array_search('voiceend',$map)]
                                ])->first();
                                
                                if(!empty($plan->id)){
                                    $plan->status = '2';
                                    $plan->save();
                                    $excludeding_ids[] = $plan->id;
                                }
                                else{
                                    $k = FlexiPlan::create([
                                        'price_type'=>$record[array_search('pricetype',$map)],
                                        'net_type'=> $record[array_search('nettype',$map)],
                                        'validity'=>$record[array_search('validity',$map)],
                                        'mb_start'=>$record[array_search('mbstart',$map)],
                                        'mb_end'=> empty($record[array_search('mbend',$map)]) ? '-1' : $record[array_search('mbend',$map)],
                                        'voice_start'=>$record[array_search('voicestart',$map)],
                                        'voice_end'=> empty($record[array_search('voiceend',$map)]) ? '-1' : $record[array_search('voiceend',$map)],
                                        'price'=>$record[array_search('price',$map)],
                                        'market_price'=>$record[array_search('marketprice',$map)],
                                    ]);
                                    $excludeding_ids[] = $k->id;
                                } 

                            }
                            $flag = FlexiPlan::whereNotIn('id', $excludeding_ids)->update(['status'=>'0']);   
                            $this->flash->addMessage('success', 'Plan list updated successfully');
                        }
                        
                        /* Updating last sync time */
                        $this->updateLastSyncTime();
                    }
                    else{                    
                        $this->flash->addMessage('danger', implode(', ',$error));
                    }
            }           
            return $this->response->withRedirect($this->router->pathFor('importPlans'));
        }
        else{
            $this->flash->addMessage('danger', 'Login to access');
            return $this->response->withRedirect($this->router->pathFor('signin'));
        }

    }
}