<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


//require_once(dirname(__FILE__) . '/../../../config/config.inc.php');

//require_once(dirname(__FILE__) . '/../../../init.php');

require_once(dirname(__FILE__).'/ProductImporterOne.php');

use PrestaShop\PrestaShop\Adapter\Import\ImportDataFormatter;


class AccessoryImporterOne extends ProductImporterOne
{
    protected $product; //row source
    protected $image_basepath;

    public function setProductSource(&$p)
    {
        if (empty($p)) {
            throw new Exception("No Product Source");
        }

        $this->product = $p;
    }

    public function setImageBasePath($path)
    {
        if (Tools::substr($path, Tools::strlen($path) - 1) != '/') {
            $path .= '/';
        }
        $this->image_basepath = $path;
    }

    public function getIdAccessory($id,$partial_model,$model){
        

        $counter = Db::getInstance()->ExecuteS(
            'SELECT id_product  FROM `' .
            _DB_PREFIX_ .
            'product` WHERE supplier_reference LIKE "' .
            (string)$partial_model.'%" and supplier_reference != "'.(string)$model.'" and id_product = '.$id
        );

        return $counter;

    }

    public function checkAttributeExist($id1,$id2){
        $counter = Db::getInstance()->ExecuteS(
            'SELECT count(*) as cnt FROM `' .
            _DB_PREFIX_ .
            'accessory` WHERE id_product_1 = '.$id1.' and id_product_2 = '.$id2
        );

        
      

        return $counter[0]['cnt'];
    }

    public function InsertAccessory($id,$partial_model,$model){
        $prod_id = self::ifExistId($id);
        $toadd_id = self::getIdAccessory($prod_id,$partial_model,$model);

        foreach($toadd_id as $key => $value){
            if(self::checkAttributeExist($prod_id,$value['id_product']) == 0){
                Db::getInstance()->execute(
                    "INSERT INTO "._DB_PREFIX_."accessory ( `id_product_1`,  `id_product_2`) VALUES ( '".$prod_id."', '".$value['id_product']."' )"
                );
            }
        }
    }

    public function isCombination($partial_model,$model){
        

        $counter = Db::getInstance()->ExecuteS(
            'SELECT count(*) as cnt FROM `' .
            _DB_PREFIX_ .
            'product` WHERE supplier_reference LIKE "' .
            (string)$partial_model.'%" and supplier_reference != "'.(string)$model.'"'
        );

        
        if(empty($counter)){
            return 0;
        }

        return $counter[0]['cnt'];

    }

    private function combination($id_product, $arr, $lang_id){
        $id_product_attribute = Combination::getIdByReference($id_product, $arr['code']);
        $comb = new Combination($id_product_attribute);
        if($id_product_attribute){
            StockAvailable::setQuantity($id_product, $id_product_attribute, $arr['quantity']);
        } else {
    
            $id_atribute = array();
            if($arr['size_group_id']){
                $id_attribute_size = attribute_id($arr['size_group_id'], $arr['sizes'], $lang_id);
                $id_attribute[] = $id_attribute_size;
            }
            if($arr['color_group_id']){
                $id_attribute_color = attribute_id($arr['color_group_id'], $arr['color'], $lang_id);
                $id_attribute[] = $id_attribute_color;
            }

            if($arr['1style_group_id']){
                $id_attribute_1style = attribute_id($arr['1style_group_id'], $arr['val'], $lang_id);
                $id_attribute[] = $id_attribute_1style;
            }
            if(count($id_attribute)){
                $comb->quantity = $arr['quantity'];
                $comb->id_product = $id_product;
                $comb->reference = $arr['code'];
                $comb->add();
                $comb->setAttributes($id_attribute);
            }
            if (isset($arr['supplyer']) && $arr['supplyer'] == 'Supplyer') {
                
                $id_product_attribute = Combination::getIdByReference($id_product, $arr['code']);
                $comb = new Combination($id_product_attribute);
                
                if ($arr['combination_image'] != '') {
                        $combination_image = $arr['combination_image'][0];
                        product_img($id_product, $combination_image);
                }
    
                if($id_product_attribute){
                    StockAvailable::setQuantity($id_product, $id_product_attribute, $arr['quantity']);
                    
                    $comb->default_on = 1;
                    $comb->update();
                }
            }
        }
    }

    public function insertAttributeValue($group_name, $value,$type='radio'){
        $group_id = self::getIdGroup($group_name);

        $id_shop = (int)Context::getContext()->shop->id;
        $language_id = (int)Context::getContext()->language->id;
       
        if($group_id == 0){
            $newGroup = new AttributeGroup();
            $newGroup->name[$language_id] = $group_name;
            $newGroup->public_name[$language_id] = $group_name;
            $newGroup->group_type = $type;
            $newGroup->add();
            $group_id = $newGroup->id;
        }


            

            
        
           

            //public static function isAttribute($idAttributeGroup, $name, $idLang)
            $id_attr = null;
            if( ! Attribute::isAttribute($group_id, $value, $language_id)){
                $newAttribute = new Attribute();
                $newAttribute->name[$language_id] = addslashes($value);
                $newAttribute->id_attribute_group = $group_id;
                $newAttribute->add();
                $id_attr =$newAttribute->id;
            }else{
                $attributes = Attribute::getAttributes($language_id,true);
               
                foreach($attributes as $key => $val){
                    //print_r($val); exit();
                    if($val['name'] == $value && $val['attribute_group'] == $group_name){
                        $id_attr = $val['id_attribute'];
                    }
                }

                if($id_attr == null){
                    exit('attribute not found');
                }
            }

            return $id_attr;
    }

    public function insertAttributeValueProd($group_name, $value,$product_id,$sign,$price,$qty){
        $group_id = self::getIdGroup($group_name);
        $id_shop = (int)Context::getContext()->shop->id;
        $language_id = (int)Context::getContext()->language->id;
       


            $product = new \Product($product_id);
        
           

            //public static function isAttribute($idAttributeGroup, $name, $idLang)
            $id_attr = [];
            
            $attributes = Attribute::getAttributes($language_id,true);
            foreach($value as $v){
                foreach($attributes as $key => $val){
                
                    if($val['name'] == $v && $val['attribute_group'] == $group_name){
                        $id_attr[] = $val['id_attribute'];
                       
                        
                    }
                }
            }
            

            //print_r($id_attr);
        
            

            $default_on = self::getDefaultOn($product_id);

            /*
            @param float $price Additional price
            * @param float $weight Additional weight
            * @param float $unit_impact
            * @param float $ecotax Additional ecotax
            * @param int $quantity
            * @param int[] $id_images Image ids
            * @param string $reference Reference
            * @param int $id_supplier Supplier identifier
            * @param string $ean13
            * @param bool $default Is default attribute for product
            * @param string $location
            * @param string $upc
            * @param int $minimal_quantity
            * @param string $isbn
            * @param int|null $low_stock_threshold Low stock for mail alert
            * @param bool $low_stock_alert Low stock mail alert activated
            * @param string|null $mpn */
            $id_prod_attr = null;
            if($product->productAttributeExists($id_attr)){
                //print('skip product attribute add'.$value);
                $all_attributes = $product->getAttributesGroups($language_id);
                foreach($all_attributes as $key => $val){
                    if(in_array($val['id_attribute'],$id_attr)){
                        $id_prod_attr = $val['id_product_attribute'];
                    }
                }

                if($id_prod_attr == null){
                    exit('attribute not found');
                }
            }else{
                $id_prod_attr = $product->addProductAttribute(
                    $sign.$price,
                    0,
                    0,
                    0,
                    $qty, //quantity
                    0,
                    0,
                    0,
                    '',
                    $default_on,
                    '',
                    '',
                    1,
                    '',
                    0,
                    0);
            
            }


            //getAttributeCombinaisons
            $exits = false;
            $all_combinations = $product->getAttributeCombinaisons($language_id);
            foreach($all_combinations as $key => $val){
                if($id_prod_attr == $val['id_product_attribute'] && in_array($val['id_attribute'],$id_attr)){
                    $exits = true;
                }
            }

            if(!$exits && $id_prod_attr != null && $id_attr != null){
                $product->addAttributeCombinaison($id_prod_attr, $id_attr);
            }


            
             
            return;
           

           

       

    }

    private function getPosition($id_group){
        $counter = Db::getInstance()->ExecuteS(
            'SELECT MAX(position) as cnt FROM `' .
            _DB_PREFIX_ .
            'attribute` WHERE id_attribute_group = '.(int)$id_group
        ); 

        
        if($counter[0]['cnt'] == NULL){
            return 0;
        }

        return $counter[0]['cnt'] + 1 ;

        
    }
    
    private function getDefaultOn($id_product){
        $counter = Db::getInstance()->ExecuteS(
            'SELECT count(*) as cnt FROM `' .
            _DB_PREFIX_ .
            'product_attribute` WHERE id_product = "'.(int)$id_product.'" and default_on = 1'
        ); 

        
        

        if($counter[0]['cnt'] > 0){
            return 0;
        }else{
            return 1;
        }

        
    }

    private function getIdGroup($group_name){
        $language_id = (int)Context::getContext()->language->id;
        $counter = Db::getInstance()->ExecuteS(
            'SELECT id_attribute_group FROM `' .
            _DB_PREFIX_ .
            'attribute_group_lang` WHERE name  LIKE"'.(string)$group_name.'" and id_lang = '.(int)$language_id
        );

       

        
        if(empty($counter)){
            return 0;
        }

        return $counter[0]['id_attribute_group'];
    }

    public function getVersion($id_product){
        $version = Db::getInstance()->ExecuteS(
            'SELECT `version` FROM `' .
            _DB_PREFIX_ .
            'onestyle_product` WHERE id_product_external = ' .
            (int)$id_product
        );

        if(empty($version)){
            return 0;
        }

        return $version[0]['version'];

    }


    public function getIdProductAttribute(){
        $version = Db::getInstance()->ExecuteS(
            'SELECT MAX(id_product_attribute) as mx FROM `' .
            _DB_PREFIX_ .
            'product_attribute`'
        );

        if(empty($version)){
            return 1;
        }

        return $version[0]['mx'];

    }


    public function getIdAttribute(){
        $version = Db::getInstance()->ExecuteS(
            'SELECT MAX(id_attribute) as mx FROM `' .
            _DB_PREFIX_ .
            'attribute`'
        );

        if(empty($version)){
            return 1;
        }

        return $version[0]['mx'];

    }

    protected function getPrice()
    {
        return $this->product->price;
        $price_limit = (bool)Configuration::get('onestyle_price_limit');

        $price_overhead = Db::getInstance()->getValue(
            'SELECT profit FROM `' .
            _DB_PREFIX_ .
            'onestyle_category` WHERE id_category_external = ' .
            (int)$this->product->local_category
        );
        $product_price = $this->product->price + ($this->product->price * $price_overhead / 100);

        if ($product_price > $this->product->street_price && $price_limit) {
            return (float)$this->product->street_price;
        } else {
            return (float)$product_price;
        }
    }

    protected function getWholesalePrice()
    {
        $product_price = $this->product->price;
        return (float)$product_price;
    }

    protected function getWidth()
    {
        return (float)0;
    }

    protected function getHeight()
    {
        return (float)0;
    }
    protected function getDepth()
    {
        return (float)0;
    }
    
    protected function getWeight()
    {
        if(!is_numeric($this->product->weight)){
            return 0;
        }
        return (float)($this->product->weight / 1000);
    }
    
    protected function getShortDesciption()
    {
        $str_tmp = strip_tags($this->product->short_description);
        $str_tmp = str_replace("\r\n", "<br>", $str_tmp);

        return $this->product->short_description;
//      return $str_tmp;
    }
    
    protected function getDesciption()
    {
        return (string)$this->product->description;
    }
    
    protected function getMetaDescription()
    {
        $meta_description = (string)$this->product->meta_description;
        $meta_description = preg_replace('/[<>;=#{}]/ui', ' ', $meta_description);

        $meta_description = (Tools::strlen($meta_description) > 255) ? Tools::substr($meta_description, 0, 255) : $meta_description;

        return $meta_description;
    }

    protected function getEan13()
    {
        $ean13 = $this->product->ean13;
        if(isset($this->product->ean13) && strlen($this->product->ean13) == 13){
            return $this->product->ean13;
        }else{
            //echo "ean not valid:".$this->product->ean13;
            $ean13 = null;
        }
        if ($ean13 == "0000000000000") {
            $ean13 = null;
        }

        return $ean13;
    }

    protected function getReference()
    {
        return (string)$this->product->reference;
    }
    
    protected function getSupplierReference()
    {
        return (string)$this->product->reference;
    }
    
    protected function getMetaKeyword()
    {
        $meta_keywords = $this->product->meta_keywords;
        $meta_keywords = preg_replace('/[<>;=#{}]/ui', ' ', $meta_keywords);

        return (string)Tools::substr($meta_keywords, 0, 255);
    }

    protected function getMetaTitle()
    {
        $meta_title = (string)$this->product->meta_title;
        $meta_title = preg_replace('/[<>;=#{}]/ui', ' ', $meta_title);

        return $meta_title;
    }
    
    protected function getName()
    {
        $not_valid = array("#", "{", "}", "^", "<", ">", ";", "=");
        $name = str_replace($not_valid, '', (string)$this->product->name);
        if (empty($name)) {
            p((string)$this->product->name);
//          throw Exception("Accessory Import Exception : Blank Name");
        }
        return $name;
    }
    
    protected function getUnitPrice()
    {
        return (float) 0;
    }

    protected function getManufacturerName()
    {
        return (string)addslashes($this->product->manufactuter);
    }


    public function getManufacturerId($name){
        if(empty($name)){
            return 0;
        }
        

        $res = Db::getInstance()->ExecuteS(
            "SELECT  id_manufacturer AS id FROM "._DB_PREFIX_."manufacturer WHERE name = '".addslashes($name)."'"
        );
        if(empty($res)){
            Db::getInstance()->execute(
                "INSERT INTO "._DB_PREFIX_."manufacturer ( `name`,  `active`,`date_add`, `date_upd`) VALUES ( '".addslashes($name)."', 1 , CURRENT_TIMESTAMP(),  CURRENT_TIMESTAMP() )"
            );
            $res = Db::getInstance()->ExecuteS(
                "SELECT id_manufacturer AS id FROM "._DB_PREFIX_."manufacturer WHERE name = '".addslashes($name)."'"
            );
            if(empty($res)){
                throw Exception("Accessory Import Exception : No Manufacturer");
            }else{
                $id_shop = (int)Context::getContext()->shop->id;
                $language_id = (int)Context::getContext()->language->id;
                Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."manufacturer_shop  VALUES ( ".$res[0]['id'].", ".$id_shop.")");
                Db::getInstance()->execute(
                    "INSERT INTO "._DB_PREFIX_."manufacturer_lang (`id_manufacturer`, `id_lang`) VALUES ( ".$res[0]['id'].", ".$language_id.")"
                );
            }
            
        }
        return $res[0]['id'];
    }

    protected function getManufacturer()
    {
        //return 0;

        $name = $this->getManufacturerName();

        if(empty($name)){
            return 0;
        }


        $res = Db::getInstance()->ExecuteS(
            "SELECT id_manufacturer AS id FROM "._DB_PREFIX_."manufacturer WHERE name = '".(string)$this->getManufacturerName()."'"
        );
        if(empty($res)){
            //INSERT INTO `ps_manufacturer_lang` (`id_manufacturer`, `id_lang`, `description`, `short_description`, `meta_title`, `meta_keywords`, `meta_description`) VALUES ('9', '1', NULL, NULL, NULL, NULL, NULL);

            Db::getInstance()->execute(
                "INSERT INTO "._DB_PREFIX_."manufacturer ( `name`,  `active`,`date_add`, `date_upd`) VALUES (  '".(string)$this->getManufacturerName()."',  1 , CURRENT_TIMESTAMP(),  CURRENT_TIMESTAMP() )"
            );
            $res = Db::getInstance()->ExecuteS(
                "SELECT id_manufacturer AS id FROM "._DB_PREFIX_."manufacturer WHERE name = '".(string)$this->getManufacturerName()."'"
            );
            if(empty($res)){
                throw Exception("Accessory Import Exception : No Manufacturer");
            }else{
                $id_shop = (int)Context::getContext()->shop->id;
                $language_id = (int)Context::getContext()->language->id;
                Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."manufacturer_shop  VALUES ( ".$res[0]['id'].", ".$id_shop.")");
                Db::getInstance()->execute(
                    "INSERT INTO "._DB_PREFIX_."manufacturer_lang (`id_manufacturer`, `id_lang`) VALUES ( ".$res[0]['id'].", ".$language_id.")"
                );
            }
        }
        return $res[0]['id'];

    }
    
    protected function getQuantity()
    {
        return (string)$this->product->quantity;
    }

    protected function getImages()
    {
        $img_arr = array();
        $url = $this->product->url_image;
        $img_arr[]= $url;
        return $img_arr;
    }

    protected function getTags()
    {
        $meta_keywords = $this->product->meta_keywords;
        $meta_keywords = preg_replace('/[!<;>;?=+#"°{}_$%]/ui', ' ', $meta_keywords);

        $str_tags = str_replace(" ", ",", $meta_keywords);
        $tags = explode(",", $str_tags);
        $tags = array_merge(array_filter($tags));

        return (array)$tags;
    }
    
    protected function getCategoryDefault()
    {
        return 2;
        $default_category = Db::getInstance()->getValue(
            'SELECT id_category_ps
            FROM `'._DB_PREFIX_.'onestyle_category`
            WHERE id_category_external = '.(int)$this->product->local_category
        );
        if (empty($default_category)) {
            $default_category = Configuration::get('onestyle_default_category');
        }

        return $default_category;
    }



    public function getLocalCategoryId($localcategoryname){
        global $ps_category_lang;

        $local_ids = [];

       

        foreach($localcategoryname as $val){
            $found_key = array_search($val, array_column($ps_category_lang, 'name'));
            $id_category = $ps_category_lang[$found_key]['id_category'];
            
            if(!in_array($id_category,$local_ids)) {
                array_push($local_ids,$id_category);
            }

           

        }

        return $local_ids;
    }

    public function getLocalCategoryRealId($prod_id,$local_categories){
        global $ps_category_lang;

        $local_ids = [];

        $i = 1;
        $prod_id = $this->ifExistId($prod_id);

        foreach($local_categories as $val){
            $found_key = array_search($val, array_column($ps_category_lang, 'id_category'));
            $id_category = $ps_category_lang[$found_key]['id_category'];

            $default_category = Db::getInstance()->getValue(
                'SELECT id_category_ps
                FROM `'._DB_PREFIX_.'onestyle_category`
                WHERE id_category_external = '.(int)$id_category
            );

            if (empty($default_category)) {
                $default_category = Configuration::get('onestyle_default_category');
            }

            

            if(!in_array($default_category,$local_ids)){
                $local_ids[] = $default_category;

                

            }

            $count_cat = Db::getInstance()->getValue(
                'SELECT count(*) FROM `' .
                _DB_PREFIX_ .
                'category_product` WHERE id_product = ' .
                (int)$prod_id.' AND id_category = '.$default_category
            );



                $t_sql = 'INSERT INTO  `'._DB_PREFIX_.'category_product` (`id_product`, `id_category`, `position`)';
                $t_sql .= ' VALUES ';
                $t_sql .= '('.(int)$prod_id.', '.$default_category.', '.$i.')'; 
                try{
                    if($count_cat == 0){
                        Db::getInstance()->execute($t_sql);
                    }
                    
    
                }catch(Throwable $e){
                    echo 'cat already present';
                }
                $i += 1;

        }

        return $local_ids;
        
    }

    public function enableOrAddSpecialPriceProduct($prod_id,$price, $amount, $from, $to){
        $count_discount = Db::getInstance()->getValue(
            'SELECT count(*) FROM `' .
            _DB_PREFIX_ .
            'specific_price` WHERE id_product = ' .
            (int)$prod_id
        );

        

        $discount = (float)$price - (float) $amount;

       
        if(empty($from) || empty($to) || (int)$prod_id == 0 || $from == NULL || $to == NULL ){
            return;
        }

        if($count_discount == 0){
            $t_sql = 'INSERT INTO `'._DB_PREFIX_.'specific_price` (`id_product`, `from`, `to`, `reduction`,`reduction_type`,`price`,`from_quantity`)';
            $t_sql .= ' VALUES ';
            $t_sql .= '('.(int)$prod_id.', "'.$from.'", "'.$to.'","'.(float)$discount.'","amount",-1,1)';
            Db::getInstance()->execute($t_sql);
        }else if($count_discount == 1){
            $t_sql = 'UPDATE`'._DB_PREFIX_.'specific_price` SET `reduction` = "'.(float)$discount.'" , `from` = "'.$from.'" , `to` = "'.$to.'"  WHERE `id_product` = '.(int)$prod_id.';';
            Db::getInstance()->execute($t_sql);
        }
    }

    public function disableSpecialPriceProduct($prod_id){

       
        $t_sql = 'DELETE FROM `'._DB_PREFIX_.'specific_price`  WHERE `id_product` = '.(int)$prod_id.';';
        Db::getInstance()->execute($t_sql);
    }

    public function disableAllProductsCategory($macro_cat){

        //$cats = implode(',',$this->getSubCategory($macro_cat));

        $t_sql = 'UPDATE `'._DB_PREFIX_.'product` SET `active` = 0 WHERE `id_product` IN ( select id_product_ps from `'._DB_PREFIX_.'onestyle_product` );';
        Db::getInstance()->execute($t_sql);
    }

    private function getSubCategory($macro_cat){
        global $ps_category_lang;
        $to_return = [];
        foreach($ps_category_lang as $val){
            if($val['id_category'] == $macro_cat || $val['Cat1'] == $macro_cat  ){
                $to_return[] = $val['id_category'];
            }

            if(empty($to_return)){
                return Configuration::get('onestyle_default_category');
            }

            return $to_return;
        }
    }

    /**
    * This method check if current product already exist
    * you can implement any logic to check if item already exist.
    * only thing is that if you want to add item return 0 otherwise id_product
    * Here I am checking in a import table and return id_product if it is exist
    *
    *
    *
    */
    protected function ifExist()
    {
        $id_product=0;

        //first check in mapping table
        $sql = 'SELECT id_product_ps FROM '._DB_PREFIX_.'onestyle_product WHERE id_product_external = '.(int)$this->product->id;
        $res = Db::getInstance()->getRow($sql);
        if ($res) {
            $id_product = $res['id_product_ps'];
            if (!Product::existsInDatabase((int)($id_product), 'product')) {
                $t_sql = 'DELETE FROM '._DB_PREFIX_.'onestyle_product WHERE id_product_ps = '.(int)$id_product;
                Db::getInstance()->execute($t_sql);
                $id_product=0;
            }
        }

        return $id_product;
    }
    
    protected function ifExistId($productId)
    {
        $id_product=0;

        //first check in mapping table
        $sql = 'SELECT id_product_ps FROM '._DB_PREFIX_.'onestyle_product WHERE id_product_external = '.(int)$productId;
        $res = Db::getInstance()->getRow($sql);
        if ($res) {
            $id_product = $res['id_product_ps'];
            if (!Product::existsInDatabase((int)($id_product), 'product')) {
                $t_sql = 'DELETE FROM '._DB_PREFIX_.'onestyle_product WHERE id_product_ps = '.(int)$id_product;
                Db::getInstance()->execute($t_sql);
                $id_product=0;
            }
        }

        return $id_product;
    }
    /**
        this method will be called after item is added to database and it's id_product is generated
    **/
    protected function afterAdd()
    {
        
        parent::afterAdd();
    }

    public function getId(){
        return Db::getInstance()->Insert_ID();

    }


    public function ifExistReference($productId)
    {
        $id_product_ps=0;
        $id_product=0;

        

        

        if($id_product_ps == 0){
            $sql = 'SELECT id_product FROM '._DB_PREFIX_.'product WHERE reference = "'.$productId.'" or supplier_reference = "'.$productId.'"';
            $res = Db::getInstance()->getRow($sql);
            if ($res) {
               return $res['id_product'];          
            }

            
        }

        

        return $id_product;
    }
}
