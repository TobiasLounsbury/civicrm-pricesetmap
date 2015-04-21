<?php

/**
 * PriceSetMap.Toggle API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_price_set_map_toggle_spec(&$spec) {
    $spec['status']['api.required'] = 1;
    $spec['page_id']['api.required'] = 1;
}

/**
 * PriceSetMap.Toggle API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_price_set_map_toggle($params) {
    if (array_key_exists('page_id', $params) && $params['page_id']) {
        if (array_key_exists('status', $params) && !is_null($params['status'])) {

            $result = civicrm_api3('PriceSetMap', 'Get', array(
                'sequential' => 1,
                'page_id' => $params['page_id'],
            ));

            $values = array(0 => array($params['status'], "Int"), 1 => array($params['page_id'], "Int"));
            if ($result['is_error'] || $result['count'] < 1) {
                $update = true;
                $sql = "INSERT INTO `civicrm_pricesetmap` (`id`, `is_active`, `page_id`) VALUES(0, %0, %1)";
            } else {
                $update = false;
                $sql = "UPDATE `civicrm_pricesetmap`  SET `is_active` = %0 WHERE `page_id` = %1";
            }
            $dao =& CRM_Core_DAO::executeQuery($sql, $values);

            if ($dao) {
                $returnValues = array(true);
            } else {
                $returnValues = array(false);
            }


            return civicrm_api3_create_success($returnValues, $params, 'PriceSetMap', 'Toggle');
        } else {
            throw new API_Exception("ERROR: Missing Required Field 'status'", 13);
        }
    } else {
        throw new API_Exception("ERROR: Missing Required Field 'page_id'", 12);
    }
}

