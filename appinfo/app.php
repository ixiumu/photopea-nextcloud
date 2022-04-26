<?php

namespace OCA\Files_Photopea\AppInfo;

use OC\Security\CSP\ContentSecurityPolicy;

$eventDispatcher = \OC::$server->getEventDispatcher();

if (\OC::$server->getUserSession()->isLoggedIn()) {
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function() {
        \OCP\Util::addScript('files_photopea', 'photopea');
    });
}

$cspManager = \OC::$server->getContentSecurityPolicyManager();
$csp = new ContentSecurityPolicy();
$csp->addAllowedChildSrcDomain("'self'");
$csp->addAllowedFrameDomain("data:");
$cspManager->addDefaultPolicy($csp);

$app = new Application();
$app->registerProvider();
