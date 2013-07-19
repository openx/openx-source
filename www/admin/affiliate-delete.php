<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

// Require the initialisation file
require_once '../../init.php';

// Required files
require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-zones.inc.php';
require_once MAX_PATH . '/lib/OA/Central/AdNetworks.php';

// Register input variables
phpAds_registerGlobal ('returnurl');

// Initialise Ad  Networks
$oAdNetworks = new OA_Central_AdNetworks();

// Security check
OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER);

/*-------------------------------------------------------*/
/* Main code                                             */
/*-------------------------------------------------------*/

if (!empty($affiliateid)) {
    $ids = explode(',', $affiliateid);
    while (list(,$affiliateid) = each($ids)) {

        // Security check
        OA_Permission::enforceAccessToObject('affiliates', $affiliateid);

        $doAffiliates = OA_Dal::factoryDO('affiliates');
        $doAffiliates->affiliateid = $affiliateid;
        if ($doAffiliates->get($affiliateid)) {
            $aAffiliate = $doAffiliates->toArray();
        }

        // User unsubscribed from adnetworks
        $oacWebsiteId = $doAffiliates->as_website_id;
        $aPublisher = array(
            array(
                    'id'            => $affiliateid,
                    'an_website_id' => $oacWebsiteId,
                )
            );
        $oAdNetworks->unsubscribeWebsites($aPublisher);

        $doAffiliates->delete();
    }

    // Queue confirmation message
    $translation = new OX_Translation ();

    if (count($ids) == 1) {
        $translated_message = $translation->translate ( $GLOBALS['strWebsiteHasBeenDeleted'], array(
            htmlspecialchars($aAffiliate['name'])
        ));
    } else {
        $translated_message = $translation->translate ( $GLOBALS['strWebsitesHaveBeenDeleted']);
    }

    OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);
}

if (empty($returnurl))
    $returnurl = 'website-index.php';

Header("Location: ".$returnurl);

?>