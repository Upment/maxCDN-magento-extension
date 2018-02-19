<?php

require_once MAGENTO_ROOT . '/lib/autoload.php';

class Upment_FlushMaxCDN_Model_Observer
{
    const CDN_CACHE_TYPE = 'cdn_cache';

    /**
     * @param Varien_Event_Observer $observer
     */
    public function clearCDNCache(Varien_Event_Observer $observer)
    {
        $this->clearCache();
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function clearCDNCacheType(Varien_Event_Observer $observer)
    {
        if ($observer->getEvent()->getType() === Upment_FlushMaxCDN_Model_Observer::CDN_CACHE_TYPE) {
            $this->clearCache();
        }
    }

    /**
     * call CDN api
     */
    public function clearCache()
    {
        $api = new MaxCDN($this->getConfig("alias"), $this->getConfig("consumer_key"), $this->getConfig("consumer_secret"));
        $zoneId = $this->getConfig("zone_id");

        $purge_api_call = $api->delete("/zones/pull.json/$zoneId/cache");
        $purge_json = json_decode($purge_api_call);
        if (array_key_exists("code", $purge_json)) {
            if ($purge_json->code == 200 || $purge_json->code == 201) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('flushcdn/data')->__("Zone ID [{$zoneId}] Cache was purged")
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('flushcdn/data')->__("Zone ID [{$zoneId}] Cache wasn't purged")
                );
            }
        }
    }

    /**
     * @param $field
     * @return string
     */
    public function getConfig($field)
    {
        $fieldValue = '';
        $value = Mage::getStoreConfig('flush_cdn/settings/' . $field);
        if ($value) {
            $fieldValue = $value;
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('flushcdn/data')->__("'{$field}' wasn't defined at the system configuration!")
            );
        }

        return $fieldValue;
    }
}
