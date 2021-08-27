<?php

//namespace PrestaShop\Module\DemoControllerTabs\controllers\front;


/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/




use Symfony\Component\HttpFoundation\Response;

//require_once(dirname(__FILE__).'/Repositories/AliExpressAttributeRepository.php');

require_once(dirname(__FILE__).'/Repositories/AccessoryImporterOne.php');
require_once(dirname(__FILE__).'/Repositories/ProductImporterOne.php');
require_once(dirname(__FILE__).'/Repositories/ps_category_lang.php');
//use Repositories\AliExpressAttributeRepository as AliExpressAttributeRepository ;


class DemoControllerTabsImportvariationModuleFrontController extends ModuleFrontController {

     /** @var bool */
    public $ajax;

    public $auth = false;

    


    


     public function postProcess()
     {
        $this->ajax = 1;

        

        //$data = Tools::jsonDecode(Tools::file_get_contents('php://input'), true);

        //$callback = $data['callback'];
        
        $callback = Tools::getValue('callback');
        //$token = $data['token'];

        /*if ($action_token != Tools::getAdminToken('democontrollertabs')) {
         die('Invalid token');
        }*/

        //$data = $data['super_attributes'];
        $data = Tools::getValue('custom_option');
        $id = Tools::getValue('product_id');
        

        //print_r($image_thumbnails);

        if (isset($data)) {

            $accessroyImport = new AccessoryImporterOne();
            $objectProduct = new stdClass();
            
            //p('Importing product '.$objectProduct->codice);
            $objectProduct->id =  $accessroyImport->ifExistReference($id);
           
            //print( $objectProduct->id );
            //print_r($data);

            if(is_array($data[0])){
                foreach ($data as $attributeData) {


                
                    //public function insertAttributeValueProd($group_name, $value,$product_id,$sign,$price,$qty){
    
                    $accessroyImport->insertAttributeValueProd('aliexpress', explode('+',$attributeData['text']),$objectProduct->id,'+',$attributeData['price'] ,$attributeData['qty']);
                        //$id_attr = $accessroyImport->insertAttributeValue('aliexpress', $val['name'],$type);
        
                       
                    
        
                   
                }
            }else{
                $accessroyImport->insertAttributeValueProd('aliexpress', explode('+',$data['text']),$objectProduct->id,'+',$data['price']  ,$data['qty']);

            }
            

            

            
            //$result = true;
            $response = $callback . '(' . json_encode([
                'success' => true,
                'message' => 'Product Successfully Imported.',
                'product_id' => $id
            ]) . ')';
        } else {
            $response = $callback . '(' . json_encode([
                'success' => false,
                'message' => 'No data provided',
               
            ]) . ')';
        }

        //$response->headers->set('Access-Control-Allow-Origin: *');
        //$response->headers->set('Access-Control-Allow-Methods: GET, POST');

        //return $response->json();
        $this->ajaxDie($response);
    
     }


  

}