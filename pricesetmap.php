<?php

require_once 'pricesetmap.civix.php';
require_once 'pricesetmap.hook.php';


function pricesetmap_civicrm_buildForm( $formName, &$form ) {
    //TODO: make the tab show up no matter how we snap into a contribution page
    $PagesToTrap = array(
      "CRM_Contribute_Form_ContributionPage_Settings",
      "CRM_Contribute_Form_ContributionPage_Amount",
      "CRM_Member_Form_MembershipBlock",
      "CRM_Contribute_Form_ContributionPage_ThankYou",
      "CRM_Friend_Form_Contribute",
      "CRM_Contribute_Form_ContributionPage_Custom",
      "CRM_Contribute_Form_ContributionPage_Premium",
      "CRM_Contribute_Form_ContributionPage_Widget",
      "CRM_PCP_Form_Contribute",
      "CRM_Pricesetmap_Form_PriceSetMap",
      "CRM_Pricesetcustomvalues_Form_CustomValues",
    );
    if (in_array($formName, $PagesToTrap)) {
        $tabs = $form->get('tabHeader');
        $formId = $form->get('id');
        if($tabs) {
            $qfKey = $form->get('qfKey');
            $PriceSetID = $form->getVar('_priceSetID');
            $valid = ($PriceSetID);
            if ($formName == "CRM_Pricesetmap_Form_PriceSetMap") {
                $current = true;
                CRM_Core_Resources::singleton()->addSetting(array('tabSettings' => array('active' => 'pricesetmap')));
            } else {
                $current = false;
                $qfKey = null;
            }
            $tabs['pricesetmap'] = array(
              "title" => "PriceSet Map",
              "link" => CRM_Utils_System::url("civicrm/admin/contribute/pricesetmap", "action=update&id={$formId}"),
              "valid" => $valid,
              "active" => true,
              "current" => $current,
              "qfKey" => $qfKey
            );
            $form->set('tabHeader', $tabs);
            $form->assign_by_ref('tabHeader', $tabs);
        }
    }

}


/**
 * Implmentation of hook_civicrm_post
 *
 * This hook is called after a db write on some core objects.
 * pre and post hooks are useful for developers building more
 * complex applications and need to perform operations before
 * CiviCRM takes action
 *
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 *
 * @param $op - operation being performed with CiviCRM object.
 * @param $objectName
 * @param $objectId - the unique identifier for the object. tagID in case of EntityTag
 * @param $objectRef - the reference to the object if available. For case of EntityTag it is an array of (entityTable, entityIDs)
 *
 */
function pricesetmap_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
    //$objectName == "Membership";
    //if ($op == "create" && $objectName == "LineItem") {
    //$objectRef->price_field_id
    //$objectRef->price_field_value_id
    //$objectRef->qty > 1

    //}
}

/**
 * Implmentation of hook_civicrm_postProcess
 *
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postProcess
 *
 * @param $formName - the name of the form
 * @param $form - reference to the form object
 *
 */
function pricesetmap_civicrm_postProcess( $formName, &$form ) {
    if ($formName == "CRM_Contribute_Form_Contribution_Confirm") {
        $PageId = $form->getVar("_id");
        $page = civicrm_api3("PriceSetMap", "get", array("sequential" => 1, "page_id" => $PageId));

        if ($page['is_error'] == 0 && $page['count'] > 0 && $page['values'][0]['is_active'] == 1) {
            // get the contribution status so that we can know if we should run this procedure.
            if ($form->_contributionID) {
                $contribution = civicrm_api3('Contribution', 'getsingle', array(
                  'sequential' => 1,
                  'id' => $form->_contributionID,
                ));
                $contributionstatus = $contribution['contribution_status_id'];
            }

            if ($contributionstatus == 1) {
                $pid = $form->getVar("_priceSetId");
                $memType = $form->_params['price_3'];
                $memtypeid = $form->_lineItem[$pid][$memType]['membership_type_id'];
                $membershipdetail = civicrm_api3('MembershipType', 'get', array(
                  'sequential' => 1,
                  'id' => $memtypeid,
                ));
                if (array_key_exists("membershipID", $form->_params)) {
                    $membershipID = $form->_params['membershipID'];
                }
                else {
                    $membershipID = NULL;
                }
                $membership = civicrm_api3('Membership', 'get', array(
                  'sequential' => 1,
                  'id' => $membershipID,
                ));
                $today = new DateTime();
                $thisyear = $today->format('Y');

                // get priceset membership info
              $pricesetmembership = civicrm_api3('PriceFieldValue', 'get', array(
                'sequential' => 1,
                'id' => $memType,
              ));

              $enddate = FALSE;
              if ($membershipdetail['values'][0]['fixed_period_rollover_day']>0){
                // if there's a rollover date then do the normal stuff
                $civirolldate = $membershipdetail['values'][0]['fixed_period_rollover_day'];
                $rolloverdate = new DateTime($thisyear . '-' . (strlen($civirolldate) == 4 ? substr($civirolldate, 0, 2) : substr($civirolldate, 0, 1)) . '-' . substr($civirolldate, -2));
                if (!is_null($membership['values'][0]['end_date'])){
                  $enddate = $membership['values'][0]['end_date'];
                }
              } else {
                // if there's not a rollover date it's a lifetime membership; manually calcuate an ending date
                $rolloverdate = new DateTime();
                $fiscalyearstart = new DateTime($thisyear."-10-01");
                if ($today > $fiscalyearstart){
                  // it's after the start of a mew fiscal year
                  if (!is_null($membership['values'][0]['end_date'])){
                    $enddate = ($thisyear+1)."-12-31";
                  }
                } else {
                  // otherwise set it to the end of this year
                  if (!is_null($membership['values'][0]['end_date'])) {
                    $enddate = $thisyear . "-12-31";
                  }
                }

              }if ($membership['is_error'] == 0 && $membership['count'] > 0) {
                if ($enddate !== FALSE){
                  $membershipend = new DateTime($enddate);
                }
                $membershipjoin = new DateTime($membership['values'][0]['join_date']);
                }

                if ($today->format("Y-m-d") == $membershipjoin->format("Y-m-d")) {
                    //set start date to today
                    $membershipstart = clone $membershipjoin;
                }
                else {
                  if ($membershipdetail['values'][0]['fixed_period_rollover_day']>0) {
                    // if there's a rollover date then do the normal stuff
                    // sets start date to preceding membership ending date
                    $membershipstart = clone $membershipend;
                    $intervalstring = "P" . ($membershipdetail['values'][0]['duration_interval']*$pricesetmembership['values'][0]['membership_num_terms']) . strtoupper(substr($membershipdetail['values'][0]['duration_unit'], 0, 1));
                    $membershipstart = $membershipstart->sub(new DateInterval($intervalstring));
                    // sets start date to day after preceding membership ending date.
                    $membershipstart = $membershipstart->add(new DateInterval('P1D'));
                  } else {
                    // if there's not a rollover date it's a lifetime membership; manually calcuate a starting date
                    // no starting or ended dates available.  Just set it to today (I guess)
                    $membershipstart = new DateTime();
                  }
                }
                $ContactId = $form->getVar("_contactID");
                $useForMember = $form->getVar("_useForMember");

                $maps = civicrm_api3("PriceSetMapDetail", "get", array(
                  "sequential" => 1,
                  "page_id" => $PageId
                ));

                //If we have nothing to do, don't do it.
                if ($maps['is_error'] == 1 || $maps['count'] == 0) {
                    return;
                }

                $m_start_date = FALSE;
                $m_end_date = FALSE;

                foreach ($maps['values'] as $map) {
                    if (array_key_exists($map['field_value'], $form->_lineItem[$pid])) {
                        if ($map['type'] == "Relationship") {
                            $start_date = $map['relationship_start'];
                            $end_date = $map['relationship_end'];
                            if ($map['relationship_date_match_membership'] && $useForMember && $membershipID) {
                                if (!$m_start_date) {
                                    if ($membership['is_error'] == 0 && $membership['count'] > 0) {
                                        $m_start_date = $membershipstart->format("Y-m-d");
                                        if (!is_null($membershipend)){
                                          $m_end_date = $membershipend->format("Y-m-d");
                                        }
                                    }
                                }

                                $start_date = $m_start_date;
                                $end_date = $m_end_date;
                            }

                            //Parse the relationship type
                            $rParts = explode("_", $map['relationship_type'], 2);
                            if ($rParts[1] == "a_b") {
                                $CA = $ContactId;
                                $CB = $map['related_contact_id'];
                            }
                            else {
                                $CA = $map['related_contact_id'];
                                $CB = $ContactId;
                            }


                            //Now let's create the relationship
                            $params = array(
                              'sequential' => 1,
                              'contact_id_a' => $CA,
                              'contact_id_b' => $CB,
                                //Print subscription relationship type
                              'relationship_type_id' => $rParts[0],
                              'start_date' => $start_date,
                              'end_date' => $end_date,
                            );

                            //invoke the before_relationship_create hook so other extensions can alter what we create
                            CRM_PriceSetMap_hook::beforeRelationshipCreate($formName, $form, $params);

                            //Create the relationship
                            $r = civicrm_api3('Relationship', 'create', $params);

                        }
                        elseif ($map['type'] == "Custom") {

                        }
                    }
                }
            }
        }
    }
}


function pricesetmap_civicrm_links( $op, $objectName, $objectId, &$links, &$mask, &$values ) {
    error_log("test");
    if ($op == "contributionpage.configure.actions") {
        $bit = 2 ^ (sizeof($links));
        $new = array(
          "name" => "Price Set Map",
          "title" => "Price Set Map",
          "url" => "civicrm/admin/contribute/pricesetmap",
          "qs" => "reset=1&action=update&id=".$objectId,
          "uniqueName" => "cvals",
            //"bit" => $bit
          "bit" => 10000
        );
        $links[] = $new;
    }
}

function pricesetmap_civicrm_tabset($tabsetName, &$tabs, $context) {
    error_log("test");
}

/**
 * Implmentation of hook_civicrm_custom:
 *
 * This hook is called AFTER the db write on a custom table
 *
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_custom
 *
 * @param $op - the type of operation being performed
 * @param $groupID - the custom group ID
 * @param $entityID - the entityID of the row in the custom table
 * @param $params - the parameters that were sent into the calling function
 *
 */
function pricesetmap_civicrm_custom( $op, $groupID, $entityID, &$params ) {
    //error_log("custom");
}



/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function pricesetmap_civicrm_config(&$config) {
    _pricesetmap_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function pricesetmap_civicrm_xmlMenu(&$files) {
    _pricesetmap_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function pricesetmap_civicrm_install() {
    return _pricesetmap_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function pricesetmap_civicrm_uninstall() {
    return _pricesetmap_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function pricesetmap_civicrm_enable() {
    return _pricesetmap_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function pricesetmap_civicrm_disable() {
    return _pricesetmap_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function pricesetmap_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
    return _pricesetmap_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function pricesetmap_civicrm_managed(&$entities) {
    return _pricesetmap_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function pricesetmap_civicrm_caseTypes(&$caseTypes) {
    _pricesetmap_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function pricesetmap_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
    _pricesetmap_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
