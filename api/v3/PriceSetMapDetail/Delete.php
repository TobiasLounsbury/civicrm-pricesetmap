<?php

/**
 * PriceSetMapDetail.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_price_set_map_detail_delete_spec(&$spec) {
  $spec['id']['api.required'] = 1;
}

/**
 * PriceSetMapDetail.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_price_set_map_detail_delete($params) {
    if (array_key_exists("id", $params) && $params['id']) {
        $sql = "DELETE FROM `civicrm_pricesetmap_detail` WHERE `id` = %1";
        $values = array(1 => array($params['id'], "Int"));
        $dao =& CRM_Core_DAO::executeQuery($sql, $values);
        if ($dao) {
            return civicrm_api3_create_success(1, $params, 'NewEntity', 'NewAction');
        } else {
            throw new API_Exception('There was an error deleting the detail item', 24);
        }
    } else {
        throw new API_Exception('Missing parameter `id`"', 13);
    }
}

