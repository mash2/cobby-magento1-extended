<?php

class Cobby_Extended_Model_Observer extends Mage_Core_Model_Abstract
{
    private $_customAttribute = 'dummy_attribute'; //dummy attribute

    public function cobbyCatalogProductAttributeExportAfter($observer)
    {
        $event = $observer->getEvent();
        $attribute = $event->getAttribute();
        if($attribute->getAttributeCode() == $this->_customAttribute) {
            $transport = $event->getTransport();
            $data = $transport->getData();
            $data['is_user_defined'] = 0; //simulate system attribute

            $transport->setData($data);
        }
    }

    public function cobbyCatalogProductAttributeOptionsExportAfter($observer)
    {
        if($observer->getEvent()->getAttribute()->getAttributeCode() == $this->_customAttribute) {
            $transport = $observer->getEvent()->getTransport();
            $options = $transport->getOptions();

            //custom data for attribute options, can be loaded from other tables or models
            $items = array(
                array('id'=> 1, 'title'=> 'title 1'),
                array('id'=> 2, 'title'=> 'title 2')
            );

            $stores = Mage::app()->getStores(true);
            foreach($stores as $storeId => $store) {
                foreach ($items as $item) {
                    $options[] = array(
                        'store_id' => $storeId,
                        'value' => $item['id'],
                        'label' => $item['title'],
                        'use_default' => $storeId > Mage_Core_Model_App::ADMIN_STORE_ID
                    );
                }
            }

            $transport->setOptions($options);
        }
    }

    public function cobbyCatalogProductExportAfter($observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $rows = $transport->getRows();
        $result = array();

        foreach ($rows as $row) {
            $sku = $row['_sku'];
            $productId = $row['_entity_id'];
            foreach ($row['_attributes'] as $storeId => $storeValues) {
                if (key_exists($this->_customAttribute, $row['_attributes'][$storeId])) {

                    $row['_attributes'][$storeId][$this->_customAttribute] = 'changed during export'; // value changed during export
                }
            }
            $result[] = $row;
        }
        $transport->setRows($result);
    }

    public function cobbyCatalogProductImportAfter($observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $rows = $transport->getRows();
        $result = array();
        foreach ($rows as $row) {
            $productId = $row['_id'];
            if($productId && key_exists($this->_customAttribute, $row)) {
                $value = $row[$this->_customAttribute];
                //value can be saved in a different attribute or table
            }

            $result[] = $row;
        }
        $transport->setRows($result);
    }
}
