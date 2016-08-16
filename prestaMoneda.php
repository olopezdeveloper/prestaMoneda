<?php
/**
* 2007-2015 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class prestaMoneda extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'prestaMoneda';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'basc';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('prestaMoneda');
        $this->description = $this->l('blockes de contacto para parte inferior de cada pagina ROANJA');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if(parent::install() && $this->registerHook('displayProductPriceBlock') && $this->registerHook('displayHeader')){
            $res=$this->createTable();

            return (bool)$res;
        }
        return false;

    }
    protected function createTable()
    {

        $res = (bool)Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'currency_product` (
                `id_product` int(10) unsigned NOT NULL,
                `id_currency` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_product`, `id_currency`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;

        ');
        return $res;
    }
    public function uninstall()
    {
        return parent::uninstall();
    }
      
    
    
    public function hookDisplayProductPriceBlock($data)
    {
        
        $type =$data["type"];
        if (isset($data['product']) && is_array($data['product'])) {
            if(!empty($data['product']['price'])){
                $id_product =$data["product"]["id_product"];
            }
        }elseif (isset($data['product']) && is_object($data['product'])) {
            if(!empty($data['product']->price)){
                $id_product =$data["product"]->specificPrice['id_product'];
            }
        }

         ####ONLY THIS TYPE HOOK###
        if (($type!='before_price') && ($type!='old_price') && ($type!='price'))
            return;

        
        ###ONLY IF PRODUCT DEFINED###
        if(!isset($id_product))
            return;

        $currencyProduct=$this->getCurrencyByProduct($id_product);
        $currency=$currencyProduct['id_currency']?:$this->context->currency->id;
        Product::setCurrencyProduct($currency);
        
        $class='rj-money';
        if ($type=='before_price' || $type=='price'){
            $class.=' price';
            if ($type=='price')
                $class.=' price-datail-product';

            $price=Product::getPriceStatic($id_product);

        }
        if ($type=='old_price'){
            $class.=' old-price';
            $price =Product::getPriceStatic($id_product,true,null,6,null,false,false);
        }
        

        
        $this->context->smarty->assign(array(
            'price'         => $price,
            'currencyP'     => $currency,
            'class'         => $class,
            'price_tax_exc' => $price
        ));
        return  $this->display( __FILE__, 'views/templates/front/blockRoanja.tpl' );
    }



    public function getCurrencyByProduct($id_product)
    {
        $sql = 'SELECT id_currency FROM '._DB_PREFIX_.'currency_product as cp where cp.id_product='.$id_product;
        return Db::getInstance()->getRow($sql);
    }
    public function hookDisplayHeader() 
    {
        $this->context->controller->addCSS($this->_path.'css/front.css', 'all');
        //$this->context->controller->addJS($this->_path.'js/funciones.js');
    }



}

?>
