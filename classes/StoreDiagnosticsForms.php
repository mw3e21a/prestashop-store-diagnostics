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

class StoreDiagnosticsForms
{
    protected static $module = false;

    public static function init($module)
    {
        if (self::$module == false) {
            self::$module = $module;
        }

        return self::$module;
    }

    public static function getMissingProductImagesList()
    {
        return array(
            'ProductID' => array('title' => self::$module->l('Product ID', 'StoreDiagnosticsForms'), 'type' => 'text', 'orderby' => false),
            'Reference' => array('title' => self::$module->l('Reference', 'StoreDiagnosticsForms'), 'type' => 'text', 'orderby' => false),
            'ProductName' => array('title' => self::$module->l('Product Name', 'StoreDiagnosticsForms'), 'type' => 'text', 'orderby' => false),
        );
    }

    public static function getImagesWithNoRecordsInDatabaseList()
    {
        return array(
            'ImagePath' => array('title' => self::$module->l('Image Path', 'StoreDiagnosticsForms'),'action' => 'view',),
        );
    }

    public static function getMissingProductImagesListValues()
    {
        $imagesThatNotExistInFileSystem = ImageDiagnosticsController::getImagesThatNotExistInFilesystem();
        /** @var Product[] $products */
        $products = array();
        $productsDesc = array();

        foreach ($imagesThatNotExistInFileSystem as $image) {
            $products[] = new Product($image['id_product'], false, 1);
        }
        foreach ($products as $key => $product) {
            $productsDesc[$key]['ProductID'] = $product->id;
            $productsDesc[$key]['Reference'] = $product->reference;
            $productsDesc[$key]['ProductName'] =  $product->name;
        }
        return $productsDesc;
    }

    public static function getImagesWithNoRecordsInDatabaseListValues()
    {
        $images = array();
        $imagesWithNoRecordsInDatabase = ImageDiagnosticsController::getImagesWithNoRecordsInDatabase();

        foreach ($imagesWithNoRecordsInDatabase as $key => $image) {
            $imageFolderPath =  ImageDiagnosticsController::getImgFolderStatic($image);
            $images[$key]['ImagePath'] = _PS_PROD_IMG_DIR_.$imageFolderPath.$image.'.jpg';
        }

        return $images;
    }

    public static function getTasksListValues()
    {
        $tasks = array();

        $tasks[0]['overview'] ='78/100';
        $tasks[0]['MissingProductImages'] = count(ImageDiagnosticsController::getImagesThatNotExistInFilesystem());
        $tasks[0]['ImagesWithNoRecordsInDatabase'] =count(StoreDiagnosticsForms::getImagesWithNoRecordsInDatabaseListValues());
        $tasks[0]['ProductsWithoutAnyImage'] ='245';
        $tasks[0]['ProductsWithoutImageCover'] ='245';

        return $tasks;
    }

    public static function getTasksList()
    {
        return array(
            'overview' => array('title' => self::$module->l('Overview', 'StoreDiagnosticsForms'),
                'type' => 'text', 'orderby' => false),
            'MissingProductImages' => array('title' => self::$module->l('Missing product images', 'StoreDiagnosticsForms'),
                'type' => 'text', 'orderby' => false),
            'ImagesWithNoRecordsInDatabase' => array('title' => self::$module->l('Images with no records in database', 'StoreDiagnosticsForms'),
                'type' => 'text', 'orderby' => false),
            'ProductsWithoutAnyImage' => array('title' => self::$module->l('Products without any image', 'StoreDiagnosticsForms'),
                'type' => 'text', 'orderby' => false),
            'ProductsWithoutImageCover' => array('title' => self::$module->l('Products without image cover', 'StoreDiagnosticsForms'),
                'type' => 'text', 'orderby' => false),
        );
    }
}
