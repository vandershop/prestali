<?php

//namespace PrestaShop\Module\DemoControllerTabs\controllers\front;

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/


use Symfony\Component\HttpFoundation\Response;

require_once(dirname(__FILE__).'/Repositories/AliExpressAttributeRepository.php');


//use Repositories\AliExpressAttributeRepository as AliExpressAttributeRepository ;


class DemoControllerTabsImportsuperattributesModuleFrontController extends ModuleFrontController {

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

        if (isset($data)) {
            $aliExpressAttributeRepository = new aliExpressAttributeRepository();
            $result = $aliExpressAttributeRepository->importSuperAttributes($data);
            //$result = true;
            $response = $callback . '(' . json_encode([
                    'success' => true,
                    'data' => $result
                ]) . ')';
        } else {
            $response = $callback . '(' . json_encode([
                    'success' => false,
                    'message' => 'No attributes available.',
                ]) . ')';
        }

        //$response->headers->set('Access-Control-Allow-Origin: *');
        //$response->headers->set('Access-Control-Allow-Methods: GET, POST');

        //return $response->json();
        $this->ajaxDie($response);
    
     }


  

}