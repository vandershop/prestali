<?php

//namespace PrestaShop\Module\DemoControllerTabs\Controllers\front\Repositories;

//use Illuminate\Container\Container as App;
//use Webkul\Core\Eloquent\Repository;
//use Webkul\Attribute\Repositories\AttributeRepository;
//use Webkul\Attribute\Repositories\AttributeFamilyRepository;

require_once(dirname(__FILE__).'/ProductImporterOne.php');
require_once(dirname(__FILE__).'/AccessoryImporterOne.php');
require_once(dirname(__FILE__).'/ps_category_lang.php');

/**
 * AliExpress Attribute Repository
 *
 * @author    Jitendra Singh <jitendra@webkul.com>
 * @copyright 2018 Webkul Software Pvt Ltd (http://www.webkul.com)
 */
class AliExpressAttributeRepository //extends Repository
{
    /**
     * AttributeRepository object
     *
     * @var array
     */
    protected $attributeRepository;

    /**
     * AttributeFamilyRepository object
     *
     * @var array
     */
    protected $attributeFamilyRepository;

    /**
     * AliExpressAttributeOptionRepository object
     *
     * @var array
     */
    protected $aliExpressAttributeOptionRepository;

    /**
     * Create a new repository instance.
     *
     * @param Webkul\Attribute\Repositories\AttributeRepository                 $attributeRepository
     * @param Webkul\Attribute\Repositories\AttributeFamilyRepository           $attributeFamilyRepository
     * @param Webkul\Attribute\Repositories\AliExpressAttributeOptionRepository $aliExpressAttributeOptionRepository
     * @param Illuminate\Container\Container                                    $app
     * @return void
     */
    public function __construct(
        
    )
    {
        

        //parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Dropship\Contracts\AliExpressAttribute';
    }

    /**
     * Import Super Attributes
     *
     * @param array $superAttributes
     * @return array
     */
    public function importSuperAttributes($superAttributes)
    {
        $data = [];
        $accessroyImport = new AccessoryImporterOne();
        //$attributeFamily = $this->attributeFamilyRepository->find(core()->getConfigData('dropship.settings.product.default_attribute_family'));

        //$attributeGroup = $attributeFamily->attribute_groups()->first();

        //$groupAttributeCount = $attributeGroup->custom_attributes()->count();

        foreach ($superAttributes as $attributeData) {


            $type = 'color';
            //$aliExpressAttribute = $this->findOnebyField('ali_express_attribute_id', $attributeData['attr_id']);
            if (strtolower(substr($attributeData['title'], 0, -1)) != "color") {
                $type = 'radio';
            }
            foreach($attributeData['value'] as $val){
                $id_attr = $accessroyImport->insertAttributeValue('aliexpress', $val['name'],$type);

               
                $data[] = [
                    'id' => $id_attr,
                    'title' => substr($attributeData['title'], 0, -1) . ' '. $val['name'],
                    'status' => 1
                ];
            }

           
        }

        return $data;
    }

    /**
     * Returns attribute code by attribute title
     *
     * @param string $attributeTitle
     * @return mixed
     */
    public function getAttributeCodeByTitle($attributeTitle)
    {
        $attributeCode = substr($attributeTitle, 0, -1);

        $attributeCode = strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', $attributeCode)));

        return $attributeCode;
    }
}