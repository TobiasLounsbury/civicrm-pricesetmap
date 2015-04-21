<?php

/**
 * PriceSetMapDetail.Update API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_price_set_map_detail_update_spec(&$spec) {
  $spec['page_id']['api.required'] = 1;
  $spec['type']['api.required'] = 1;
  $spec['field_id']['api.required'] = 1;
}

/**
 * PriceSetMapDetail.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_price_set_map_detail_update($params) {

    if ($params['type'] == "Relationship") {

        $req_fields = array("page_id", "type", "field_id", "relationship_type", "related_contact", "field_value");
        foreach($req_fields as $field) {
            if (!array_key_exists($field, $params) || !$params[$field]) {
                throw new API_Exception('Missing required field "{$field}"', 2);
            }
        }

        //Now, we have all the required fields, lets Proceed
        $sql = "INSERT INTO `civicrm_pricesetmap_detail` (`page_id`, `type`, `field_id`,`relationship_type`, `related_contact_id`, `relationship_start`, `field_value`, `relationship_end`, `notes`, `relationship_date_match_membership`) VALUES (%1, %2, %3, %4, %5, %6, %7, %8, %9, %10)";
        $start_date = (array_key_exists("start_date", $params)) ? $params['start_date'] : "";
        $end_date = (array_key_exists("end_date", $params)) ? $params['end_date'] : "";
        $notes = (array_key_exists("end_date", $params)) ? $params['end_date'] : "";
        $relationship_match = (array_key_exists("relationship_date_match_membership", $params)) ? $params['relationship_date_match_membership'] : "0";
        $values = array(
            1 => array($params['page_id'], "Int"),
            2 => array($params['type'], "String"),
            3 => array($params['field_id'], "Int"),
            4 => array($params['relationship_type'], "String"),
            5 => array($params['related_contact'], "Int"),
            6 => array($start_date, "String"),
            7 => array($params['field_value'], "Int"),
            8 => array($end_date, "String"),
            9 => array($notes, "String"),
            10 => array($relationship_match , "Int")
        );

        $dao =& CRM_Core_DAO::executeQuery($sql, $values);

        if (!dao) {
            throw new API_Exception('Could not Create Row', 2);
        }

        //Return the ID so we can create delete button
        $row_id = CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM `civicrm_pricesetmap_detail`');

        return civicrm_api3_create_success($row_id, $params, 'PriceSetMapDetail', 'update');
    }

    // ALTERNATIVE: $returnValues = array(); // OK, success
    // ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
    //return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');

    //throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);

}

