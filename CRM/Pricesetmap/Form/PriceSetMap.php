<?php

require_once 'CRM/Core/Form.php';
require_once 'CRM/Pricesetmap/BAO/PriceSetMap.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pricesetmap_Form_PriceSetMap extends CRM_Contribute_Form_ContributionPage {
    function buildQuickForm() {

        //crm-relationship-form-block-is_permission_a_b

        $this->addElement(
            'checkbox',
            'pricesetmap_active',
            ts('Enable PriceSet Map?')
        );

        $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_contribution_page', $this->_id, 3, 1);
        $ShowMatchMembership = true;
        if (!$priceSetId) {
            $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_contribution_page', $this->_id, 2, 1);
            $ShowMatchMembership = false;
        }



        if ($this->_id && $priceSetId) {

            $result = civicrm_api3("PriceSet", "get", array(
                'sequential' => 1,
                'id' => $priceSetId,
                'api.PriceField.get' => array(
                    'html_type' => array(
                        'IN' => array(
                            "select",
                            "checkbox",
                            "radio")
                    ),
                    'api.PriceFieldValue.get' => array(),
                ),
            ));
            //TODO: Add error checking
            $priceSetTitle = $result['values'][0]['title'];
            $fields = array();
            $values = array();
            foreach ($result['values'][0]['api.PriceField.get']['values'] as $field) {
                $values[$field['id']] = array();
                foreach($field['api.PriceFieldValue.get']['values'] as $value) {
                    $values[$field['id']][$value['id']] = $value['label'];
                }
                $fields[$field['id']] = $field['label'];
            }

            $this->addElement('select', "PriceFields", ts('Price Field'), $fields);

            //TODO: Load all the details we already have in the database
            $result = civicrm_api3("PriceSetMapDetail", "get", array(
                "sequential" => 1,
                "page_id" => $this->_id,
            ));
            $details = $result['values'];

            //Get the details of the various extra pieces.
            foreach($details as &$row) {
                if ($row['type'] == "Relationship") {

                    //Fetch the Contact Name
                    $result = civicrm_api3('Contact', 'get', array(
                        'sequential' => 1,
                        'return' => "display_name",
                        'id' => $row['related_contact_id'],
                    ));
                    if ($result['is_error'] == 0) {
                        $row['related_contact_name'] = $result['values'][0]['display_name'];
                    } else {
                        $row['related_contact_name'] = "";
                    }

                    //Fetch the relationship name (For the appropriate direction)

                    $rParts = explode("_", $row['relationship_type'], 2);
                    $result = civicrm_api3('RelationshipType', 'get', array(
                        'sequential' => 1,
                        'return' => "label_a_b,label_b_a",
                        'id' => $rParts[0],
                    ));

                    if ($result['is_error'] == 0) {
                        $row['relationship_type_name'] = $result['values'][0]['label_'.$rParts[1]];
                    } else {
                        $row['relationship_type_name'] = "";
                    }



                } else if ($row['type'] == "Custom") {

                }

                //Fetch the Price Field Name
                $result = civicrm_api3('PriceField', 'get', array(
                    'sequential' => 1,
                    'return' => "label",
                    'id' => $row['field_id'],
                ));
                if ($result['is_error'] == 0) {
                    $row['field_name'] = $result['values'][0]['label'];
                } else {
                    $row['field_name'] = "";
                }

                //Fetch the label for the value if we need it
                if($row['field_value']) {
                    $result = civicrm_api3('PriceFieldValue', 'get', array(
                        'sequential' => 1,
                        'return' => "label",
                        'id' => $row['field_value'],
                    ));
                    if ($result['is_error'] == 0) {
                        $row['field_value_name'] = $result['values'][0]['label'];
                    } else {
                        $row['field_value_name'] = "";
                    }
                }

            }


            $this->assign('details', $details);
            $this->assign('fields', json_encode($fields));
            $this->assign('values', json_encode($values));
            $this->assign('Price', $priceSetTitle);
            $this->assign('ShowMatchMembership', $ShowMatchMembership);
        } else {
            $this->assign('Price', false);
        }



        $this->assign('PageID', $this->_id);
        // export form elements
        $this->assign('elementNames', $this->getRenderableElementNames());
        parent::buildQuickForm();
    }

    function setDefaultValues() {
        $origID = null;
        $defaults = array();


        $MapSettings = CRM_PriceSetMap_BAO_PriceSetMap::getMapSettings($this->_id);

        if ($MapSettings && array_key_exists("is_active", $MapSettings)) {
            $defaults['pricesetmap_active'] = CRM_Utils_Array::value('is_active', $MapSettings);
        } else {
            $defaults['pricesetmap_active'] = 0;
        }

        return $defaults;
    }

    function postProcess() {
        $values = $this->exportValues();
        if($values['pricesetmap_active']) {

        }
        parent::postProcess();
    }

    /**
     * Get the fields/elements defined in this form.
     *
     * @return array (string)
     */
    function getRenderableElementNames() {
        // The _elements list includes some items which should not be
        // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
        // items don't have labels.  We'll identify renderable by filtering on
        // the 'label'.
        $elementNames = array();
        foreach ($this->_elements as $element) {
            $label = $element->getLabel();
            if (!empty($label)) {
                $elementNames[] = $element->getName();
            }
        }
        return $elementNames;
    }
}
