<?php

class CRM_PriceSetMap_BAO_PriceSetMap {

    static function getMapSettings($PageID) {
        $result = civicrm_api3('PriceSetMap', 'Get', array(
            'sequential' => 1,
            'page_id' => $PageID
        ));

        if ($result['is_error'] || $result['count'] == 0) {
            return false;
        }

        return $result['values'][0];
    }



}