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

class ImageDiagnosticsController extends ImageCore
{

    /**
     * Return the directory list from the given $path using php glob function.
     *
     * @param string $path
     *
     * @return array
     */
    public static function getDirectoriesWithGlob($path)
    {
        $directoryList = glob($path . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
        array_walk($directoryList,
            function (&$absolutePath, $key) {
                $absolutePath = substr($absolutePath, strrpos($absolutePath, '/') + 1);
            }
        );

        return $directoryList;
    }

    /**
     * Returns ids of product images existing in img dir
     *
     * @return array
     */
    public static function getExistingImagesInDir()
    {
        $existingImagesInFileSystem = array();

        $directories =  ImageDiagnosticsController::getDirectoriesWithGlob(_PS_PROD_IMG_DIR_);
        foreach ($directories as $directory) {
            $imagesInDir =  Tools::scandir(_PS_PROD_IMG_DIR_.$directory, 'jpg');
            foreach ($imagesInDir as $image) {
                if ($directory.'.jpg' == $image) {
                    $existingImagesInFileSystem[] = $directory;
                }
            }
        }

        return $existingImagesInFileSystem;
    }


    public static function getImagesThatNotExistInFilesystem()
    {
        $ImagesThatNotExist = array();
        $allImages = parent::getAllImages();

        foreach ($allImages as $image) {
            $imageFolderPath = parent::getImgFolderStatic($image['id_image']);
            if (!file_exists(_PS_PROD_IMG_DIR_.$imageFolderPath.$image['id_image'].'.jpg')) {
                $ImagesThatNotExist[] = $image;
            }
        }
        return $ImagesThatNotExist;
    }

    public static function fixMissingProductImagesInFilesystem()
    {
        $imagesThatNotExist = ImageDiagnosticsController::getImagesThatNotExistInFilesystem();
        $imagesObjects = array();
        foreach ($imagesThatNotExist as $image) {
            $imagesObjects[] = new ImageCore($image['id_image']);
        }

        foreach ($imagesObjects as $imageObject) {
            try {
                $imageObject->delete();
            } catch (PrestaShopException $e) {
                $e->displayMessage();
            }
        }

        return true;
    }

    public static function getImagesWithNoRecordsInDatabase()
    {
        $existingImagesInFileSystem = ImageDiagnosticsController::getExistingImagesInDir();
        $imagesNotExistingInDatabase = array();

        foreach ($existingImagesInFileSystem as $image) {
            $result = ImageDiagnosticsController::countImagesInDatabaseById($image);
            if ($result == 0) {
                $imagesNotExistingInDatabase['id_image'] = (int)$image;
            }
        }

        return $imagesNotExistingInDatabase;
    }

    public static function countImagesInDatabaseById($idImage)
    {
        $result = Db::getInstance()->getRow('
		SELECT COUNT(`id_image`) AS total
		FROM `' . _DB_PREFIX_ . 'image`
		WHERE `id_image` = ' . (int) $idImage);

        return $result['total'];
    }
}
