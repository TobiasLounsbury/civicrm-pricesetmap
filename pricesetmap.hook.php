<?php
/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 11/4/14
 * Time: 6:01 PM
 */

class CRM_PriceSetMap_hook {

    static $_nullObject = NULL;

    /**
     * Allows a hook to alter the params of the relationship created
     *
     * @param int $wid: The ID of the Workflow for which details are being saved
     * @param array $data:
     * @return mixed
     */


    static function beforeRelationshipCreate($formName, &$form, &$relationship) {
        return CRM_Utils_Hook::singleton()->invoke(3, $formName, $form, $relationship,
            self::$_nullObject, self::$_nullObject, self::$_nullObject,
            'pricesetmap_beforeRelationshipCreate'
        );
    }
}