<?php


use Symfony\Component\HttpFoundation\Response;

class DemoControllerTabsSecurityModuleFrontController extends ModuleFrontController {

     /** @var bool */
    public $ajax;

    public $auth = false;



     public function initContent()
     {
        $this->ajax = 1;
 
         
        $callback = Tools::getValue('callback');

        $response = $callback . '(' . json_encode(['success' => true, 'message' => 'Url Validate']) . ')';
        //$response->headers->set('Content-Type', 'application/javascript');

        //$json = Tools::jsonEncode($response);
        $this->ajaxDie($response);
    
     }
 

}