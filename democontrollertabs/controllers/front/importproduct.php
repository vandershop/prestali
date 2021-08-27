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


class DemoControllerTabsImportproductModuleFrontController extends ModuleFrontController {

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
        $data = Tools::getValue('super_attributes');
        $id = Tools::getValue('id');
        $qty = Tools::getValue('qty');
        $name = Tools::getValue('name');
        $meta_keywords = Tools::getValue('meta_keywords');
        $meta_title = Tools::getValue('meta_title');
        $meta_description = Tools::getValue('meta_description');
        $description_url = Tools::getValue('description_url');

        $opts = array('http' => array(
            'method' => "GET",
            'Timeout' => 5,
            'header' => "User-Agent Mozilla/5.0 (Windows NT 6.1; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0\r\n"
            . "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n"
            . "Accept-Encoding:gzip, deflate\r\n"
            . "Accept-Language:cs,en-us;q=0.7,en;q=0.3\r\n"
            . "Connection:keep-alive\r\n"
            . "Host:your.domain.com\r\n"
            ));
        $context = stream_context_create($opts);
        $description = file_get_contents('https://'.$description_url, FALSE, $context);

        //$description =  file_get_contents('https://'.$description_url);
        

        
        
        //print($description_url);
        //print($description);
        //exit();

        $price = Tools::getValue('price');
        $image_thumbnails = Tools::getValue('image_thumbnails');

        //print_r($image_thumbnails);

        if (isset($data)) {

            $accessroyImport = new AccessoryImporterOne();
            $objectProduct = new stdClass();
            
            //p('Importing product '.$objectProduct->codice);
            $objectProduct->id =  $accessroyImport->ifExistReference($id);
            if($objectProduct->id != 0){
                $response = $callback . '(' . json_encode([
                    'success' => true,
                    'message' => 'Product Successfully Imported.',
                    'product_id' => $id
                ]) . ')';
            }
            $objectProduct->active =  1;
            $objectProduct->reference = $id;
            $objectProduct->name = substr($name,0,128);
            $objectProduct->meta_keywords = substr($meta_keywords,0,128);
            $objectProduct->price = (float)$price;
            //echo gettype($objectProduct->price);
            $objectProduct->street_price = $objectProduct->price + ($objectProduct->price * 0.80);
            $objectProduct->description = $meta_description;
           
            $objectProduct->quantity = $qty;
            $objectProduct->url_image = 'https://'.$image_thumbnails[0];

            //handle multiple category association
            //$cat = [$objectProduct->categoria];
           

            //$local_categories = $accessroyImport->getLocalCategoryId($cat);

            //$db_cat = $accessroyImport->getLocalCategoryRealId($objectProduct->id, $local_categories);

            $objectProduct->id_category[] = 1133;              
            
            //$objectProduct->local_category = $local_categories[0];

            $objectProduct->meta_description = substr($meta_description,0,160);
            $objectProduct->meta_title = substr($meta_title,0,70);
            
            $objectProduct->short_description = substr($objectProduct->description,0,160); 
            //$objectProduct->version = $objectProduct->last_update;
            //$manuf = strip_tags($objectProduct->manufacturer);
            //$objectProduct->id_manufactuter = $accessroyImport->getManufacturerId($manuf);
            //$objectProduct->manufactuter = '';

           
            
           

            //specific price
            //$accessroyImport->disableSpecialPriceProduct($objectProduct->product_id);
            
            //accessroyImport->enableOrAddSpecialPriceProduct($objectProduct->product_id,$objectProduct->price,$objectProduct->spe_1_price_default,$objectProduct->spe_1_date_start_default,$objectProduct->spe_1_date_end_default);
           
            $accessroyImport->setProductSource($objectProduct);
          //print_r($objectProduct);
           $objectProduct->ean13 = '';
            $accessroyImport->save();


            /*foreach ($data as $attributeData) {


                
                foreach($attributeData['value'] as $val){
                    $accessroyImport->insertAttributeValueProd('aliexpress', $val['name'],$accessroyImport->getId(),'+',$objectProduct->price,$qty);
                    //$id_attr = $accessroyImport->insertAttributeValue('aliexpress', $val['name'],$type);
    
                   
                }
    
               
            }*/

            

            
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