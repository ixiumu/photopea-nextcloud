<?php

namespace OCA\Photopea\AppInfo;

use OC\Files\Type\Detection;
use OCP\AppFramework\App;

class Application extends App {
    const APPNAME = 'photopea';

	public function __construct(array $urlParams = array()) {
		parent::__construct(self::APPNAME, $urlParams);
    }

	public function registerProvider() {
		$container = $this->getContainer();

		// Register mimetypes
		/** @var Detection $detector */
		$detector = $container->query(\OCP\Files\IMimeTypeDetector::class);
		$detector->getAllMappings();
		$detector->registerType('psd','application/x-photoshop');
	}
}