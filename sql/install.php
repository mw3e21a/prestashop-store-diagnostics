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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'StoreDiagnostics` (
    `id_StoreDiagnostics` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_StoreDiagnostics`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
