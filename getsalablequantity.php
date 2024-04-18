<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

require '../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$appState = $objectManager->get('\Magento\Framework\App\State');
$appState->setAreaCode('frontend');


$productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
$collection = $productCollectionFactory->create();
$collection->addAttributeToSelect('*');

$csv = "SKU,Salable Quantity,Main Quantity\n";

foreach ($collection as $productData) {

    $productId = $productData->getId();


    $productSku = $productData->getSku();
//    $qty = $productData->getQty();

    $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
    $salableQtyData = $StockState->execute(trim($productSku));

    $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
    $qty = $StockState->getStockQty($productId, 0);


    $salableQty = $salableQtyData[0]['qty'];


    if($salableQty !=$qty){
        $csv .= "$productSku,$salableQty,$qty\n";
        echo $salableQty."\n";
        echo $qty."\n";
        echo $productId."\n";
    }


}

$filename = 'product_quantities.csv';
file_put_contents($filename, $csv);


die;

?>