<?php
/**
 * NOTICE OF LICENSE
 *
 * This product is licensed for one customer to use on one installation (test stores and multishop included).
 * Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
 * whole or in part. Any other use of this module constitues a violation of the user agreement.
 *
 *
 * @author       Michał Wilczyński <mwilczynski0@gmail.com>
 * @copyright    Michał Wilczyński
 * @license      see above
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/controller/ImageDiagnosticsController.php');
require_once(dirname(__FILE__).'/classes/StoreDiagnosticsForms.php');

class storediagnostics extends Module
{
    protected $config_form = false;
    protected $_successes;
    protected $_warnings;

    public function __construct()
    {
        $this->name = 'storediagnostics';
        $this->tab = 'administration';
        $this->version = '0.1.0';
        $this->author = 'Michał Wilczyński';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Store Diagnostics');
        $this->description = $this->l('Store diagnostics tools for seo and images');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = null;
        StoreDiagnosticsForms::init($this);
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitStoreDiagnosticsModule')) == true) {
            $this->postProcess();
            $this->action();
        } elseif (Tools::isSubmit('storediagnosticsmissingproductimagesfix')) {
            $this->processMissingProductImages();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('form_errors', $this->_errors);
        $this->context->smarty->assign('form_warnings', $this->_warnings);
        $this->context->smarty->assign('form_successes', $this->_successes);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        if (Tools::isSubmit('storediagnosticsmissingproductimages')) {
            $title = $this->l('Missing images list');
            $backUrl = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name;
            $href = $this->context->link->getAdminLink('AdminModules', false)
                .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                .'&storediagnosticsmissingproductimagesfix=1&token='.Tools::getAdminTokenLite('AdminModules');

            $output = $output.$this->renderSpecificList(
                $title,
                $backUrl,
                $href,
                StoreDiagnosticsForms::getMissingProductImagesList(),
                StoreDiagnosticsForms::getMissingProductImagesListValues());
            $output = $output.$this->renderForm($backUrl, false);
        } elseif (Tools::isSubmit('storediagnosticsimageswithnorecordsindatabase')) {
            $title = $this->l('Images with no records in database');
            $backUrl = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name;
            $href = $this->context->link->getAdminLink('AdminModules', false)
                .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                .'&storediagnosticsimageswithnorecordsindatabasefix=1&token='.Tools::getAdminTokenLite('AdminModules');

            $output = $output.$this->renderSpecificList(
                $title,
                $backUrl,
                $href,
                StoreDiagnosticsForms::getImagesWithNoRecordsInDatabaseList(),
                StoreDiagnosticsForms::getImagesWithNoRecordsInDatabaseListValues());
            $output = $output.$this->renderForm($backUrl, false);
        } elseif (defined('_PS_HOST_MODE_') == false) {
            $output = $output.$this->renderTasksList();
            $output = $output.$this->renderForm();
        }

        return $output;
    }


    protected function renderTasksList()
    {
        $helper = new HelperList();

        $helper->title = $this->l('General shop condition and images Overview');
        $helper->table = $this->name;
        $helper->no_link = false;
        $helper->shopLinkType = '';
        $helper->actions = array('delete', 'view');
        $helper->identifier = 'id_StoreDiagnosticsImage';

        $values = StoreDiagnosticsForms::getTasksListValues();
        $helper->tpl_vars = array('show_filters' => true);

        $helper->toolbar_btn = array(
            'missingProductImagesButton' => array(
                'href' =>  $this->context->link->getAdminLink('AdminModules', false)
                    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                    .'&storediagnosticsmissingproductimages=1&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Repair missing product images'),
                'class' => 'icon-columns'
        ),
            'imagesWithNoRecordsInDatabaseButton' => array(
                'href' =>  $this->context->link->getAdminLink('AdminModules', false)
                    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                    .'&storediagnosticsimageswithnorecordsindatabase=1&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Repair images with no records in database'),
                'class' => 'icon-medkit'
            ),
            'productsWithoutAnyImageButton' => array(
                'href' =>  $this->context->link->getAdminLink('AdminModules', false)
                    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                    .'&storediagnosticsmissingproductimages=1&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Repair images without any image'),
                'class' => 'icon-th-large'
            ),
            'productsWithoutImageCover' => array(
                'href' =>  $this->context->link->getAdminLink('AdminModules', false)
                    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                    .'&storediagnosticsmissingproductimages=1&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Repair products with no image cover'),
                'class' => 'icon-qrcode'
            ),
            );
        return $helper->generateList($values, StoreDiagnosticsForms::getTasksList());
    }

    protected function renderSpecificList($title, $backUrl, $href, $list, $values)
    {
        $helper = new HelperList();

        $helper->title = $title;
        $helper->table = $this->name;
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_StoreDiagnosticsImage';

        $helper->tpl_vars = array('show_filters' => false);

        $helper->toolbar_btn = array(
            'back' => array(
                'href' => $backUrl,
                'desc' => $this->l('Back')),
            'repair' => array(
               'href' =>  $href,
                'desc' => $this->l('Repair'),
                'class' => 'icon-step-forward')
        );


        return $helper->generateList($values, $list);
    }


    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitStoreDiagnosticsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of main form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Clear all images in tmp dir'),
                        'name' => 'STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function action()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if ($key == 'STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR'
                && (Tools::getValue($key) == true)) {
                $this->processClearImgTmpDir();
            }
        }
    }

    public function processClearImgTmpDir()
    {
        ImageDiagnosticsController::clearTmpDir();
    }

    public function processMissingProductImages()
    {
        if (ImageDiagnosticsController::fixMissingProductImagesInFilesystem()) {
            $this->setSuccessMessage('Database records has been fully repaired');
        } else {
            $this->setWarningMessage('Something went wrong');
        }
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR' =>
                Configuration::get('STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        if (Tools::getValue('STOREDIAGNOSTICS_CLEAR_IMG_IN_TMP_DIR') == true) {
            $this->processClearImgTmpDir();
        }
    }

    protected function setSuccessMessage($message)
    {
        $this->_successes[] = $this->l($message);
        return true;
    }

    protected function setWarningMessage($message)
    {
        $this->_warnings[] = $this->l($message);
        return false;
    }
    protected function setErrorMessage($message)
    {
        $this->_errors[] = $this->l($message);
        return false;
    }


    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
