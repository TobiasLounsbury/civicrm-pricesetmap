<?php

/**
 * PriceSetMap.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_price_set_map_get_spec(&$spec) {
    $spec['page_id']['api.required'] = 1;
}

/**
 * PriceSetMap.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_price_set_map_get($params) {
    if (array_key_exists('page_id', $params) && $params['page_id']) {

        $result = civicrm_api3('ContributionPage', 'get', array(
            'sequential' => 1,
            'id' => $params['page_id'],
        ));

        if ($result['is_error'] || $result['count'] < 1) {
            throw new API_Exception('ERROR: Page cannot be found', 14);
        }

        $vals = array(array($params['page_id'], 'Int'));
        $dao =& CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_pricesetmap` WHERE `page_id` = %0", $vals);
        if ($dao->fetch()) {
            $returnValues = array((array) $dao);
        } else {
            $returnValues = array();
        }

        return civicrm_api3_create_success($returnValues, $params, 'PriceSetMap', 'Get');
    } else {
        throw new API_Exception('ERROR: Missing required param "page_id"', 12);
    }
}

