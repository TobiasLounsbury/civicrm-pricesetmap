<?php

/**
 * PriceSetMapDetail.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_price_set_map_detail_get_spec(&$spec) {

}

/**
 * PriceSetMapDetail.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_price_set_map_detail_get($params) {

    $values = array();

    if (array_key_exists("page_id", $params) && $params['page_id']) {
        $where = " WHERE `page_id` = %0";
        $values[0] = array($params['page_id'], "Int");
    } else {
        $where = "";
    }

    $sql = "SELECT * FROM `civicrm_pricesetmap_detail` $where";
    $dao =& CRM_Core_DAO::executeQuery($sql, $values);

    if ($dao) {
        $returnValues = array();
        while ($dao->fetch()) {
            $row = array();
            $row['id'] = $dao->id;
            $row['page_id'] = $dao->page_id;
            $row['type'] = $dao->type;
            $row['field_id'] = $dao->field_id;
            $row['field_value'] = $dao->field_value;
            $row['relationship_type'] = $dao->relationship_type;
            $row['related_contact_id'] = $dao->related_contact_id;
            $row['relationship_start'] = $dao->relationship_start;
            $row['relationship_end'] = $dao->relationship_end;
            $row['relationship_date_match_membership'] = $dao->relationship_date_match_membership;
            $row['custom_data_id'] = $dao->custom_data_id;
            $row['custom_data_format'] = $dao->custom_data_format;
            $row['notes'] = $dao->notes;
            $returnValues[] = $row;
        }
        return civicrm_api3_create_success($returnValues, $params, 'PriceSetMapDetail', 'get');
    } else {
        return civicrm_api3_create_success(array(), $params, 'PriceSetMapDetail', 'get');
    }



   // throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);

}

