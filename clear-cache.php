<?php
// Include Magento
require_once dirname(__FILE__).'/app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
// Set user admin session
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);
// Call Magento clean cache action
Mage::app()->cleanCache();
// Enable all cache types
$enable = array();
foreach(Mage::helper('core')->getCacheTypes() as $type => $label){
    $enable[$type] = 1;
}
Mage::app()->saveUseCache($enable);
// Refresh cache's
echo 'Refreshing cache...';
try {
    Mage::getSingleton('catalog/url')->refreshRewrites();
    echo 'Catalog Rewrites was refreshed successfully';
} catch ( Exception $e ) {
    echo 'Error in Catalog Rewrites: '.$e->getMessage();
}
// This one caused an error for me - you can try enable it
/*try {
    Mage::getSingleton('catalog/index')->rebuild();
    echo 'Catalog Index was rebuilt successfully';
} catch ( Exception $e ) {
    echo 'Error in Catalog Index: '.$e->getMessage();
}*/
try {
    $flag = Mage::getModel('catalogindex/catalog_index_flag')->loadSelf();
    if ( $flag->getState() == Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_RUNNING ) {
        $kill = Mage::getModel('catalogindex/catalog_index_kill_flag')->loadSelf();
        $kill->setFlagData($flag->getFlagData())->save();
    }
    $flag->setState(Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_QUEUED)->save();
    Mage::getSingleton('catalogindex/indexer')->plainReindex();
    echo 'Layered Navigation Indices was refreshed successfully';
} catch ( Exception $e ) {
    echo 'Error in Layered Navigation Indices: '.$e->getMessage();
}
try {
    Mage::getModel('catalog/product_image')->clearCache();
    echo 'Image cache was cleared successfully';
} catch ( Exception $e ) {
    echo 'Error in Image cache: '.$e->getMessage();
}
try {
    Mage::getSingleton('catalogsearch/fulltext')->rebuildIndex();
    echo 'Search Index was rebuilded successfully';
} catch ( Exception $e ) {
    echo 'Error in Search Index: '.$e->getMessage();
}
try {
    Mage::getSingleton('cataloginventory/stock_status')->rebuild();
    echo 'CatalogInventory Stock Status was rebuilded successfully';
} catch ( Exception $e ) {
    echo 'Error in CatalogInventory Stock Status: '.$e->getMessage();
}
try {
    Mage::getResourceModel('catalog/category_flat')->rebuild();
    echo 'Flat Catalog Category was rebuilt successfully';
} catch ( Exception $e ) {
    echo 'Error in Flat Catalog Category: '.$e->getMessage();
}
try {
    Mage::getResourceModel('catalog/product_flat_indexer')->rebuild();
    echo 'Flat Catalog Product was rebuilt successfully';
} catch ( Exception $e ) {
    echo 'Error in Flat Catalog Product: '.$e->getMessage();
}
echo 'Cache cleared';
// Rebuild indexes
echo 'Rebuilding indexes';
for ($i = 1; $i <= 9; $i++) {
    $process = Mage::getModel('index/process')->load($i);
    try {
        $process->reindexAll();
    } catch ( Exception $e ) {
        echo 'Error rebuilding index '.$i.': '.$e->getMessage();
    }
}
echo 'Indexes rebuilt';
echo 'Finished!';