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

$csv = "Product Id,SKU,Salable Quantity,Main Quantity\n";

try {

    foreach ($collection as $productData) {
        $productType = $productData->getTypeId();
        if ($productType != 'bundle' && $productType != 'grouped' && $productType != 'configurable') {

            $productId = $productData->getId();


            $productSku = $productData->getSku();
//    $qty = $productData->getQty();

            $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
            $salableQtyData = $StockState->execute(trim($productSku));

            $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
            $qty = $StockState->getStockQty($productId, 0);
            $productStockObj = $objectManager->get('Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku')->execute($productSku);
            $quantity = "";
            foreach ($productStockObj as $sub_array) {
                if ($sub_array['source_code'] === 'Kirrawee') {
                    $quantity = $sub_array['quantity'];
                }
            }
            $salableQuantity = "";
            foreach ($salableQtyData as $sub_array) {
                if ($sub_array['stock_name'] === 'Kirrawee') {
                    $salableQuantity = $sub_array['qty'];
                }
            }

            if ($salableQuantity != $quantity) {


                echo "Product Id: " . $productId . "\n";
                echo "Salable Quantity: " . $salableQuantity . "\n";
                echo "Qty: " . $quantity . "\n";

//                $sourceItem2 = $objectManager->create('\Magento\InventoryApi\Api\Data\SourceItemInterface');
//                $sourceItem2->setSku("testttttttttttt");
//                $sourceItem2->setSourceCode('Kirrawee');
//                $sourceItem2->setQuantity(1000);
//                $sourceItem2->setStatus(1);
//                $sourceItemSave = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
//                $sourceItemSave->execute([$sourceItem2]);

                die;



//        $stockModel = $objectManager->get('Magento\CatalogInventory\Model\Stock\ItemFactory')->create();
//        $stockResource = $objectManager->get('Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
//        $stockResource->load($stockModel, 15,"product_id");
//        $stockModel->setQty(9);
//        $stockResource->save($stockModel);
                $csv .= "$productId,$productSku,$salableQuantity,$quantity\n";
            }


        }
    }

    $filename = 'product_quantities.csv';
    file_put_contents($filename, $csv);


}catch (Exception $exception){

    echo $productId."\n";

    echo $exception->getMessage();



die;


}

die;

?>