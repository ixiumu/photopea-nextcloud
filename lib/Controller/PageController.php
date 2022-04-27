<?php

namespace OCA\Photopea\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\INavigationManager;
use OCP\IRequest;
use OCP\IURLGenerator;

class PageController extends Controller {

	/** @var IConfig */
	protected $config;

	/** @var INavigationManager */
	protected $navigationManager;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/** @var IL10N */
	protected $l10n;

	/**
	 * SiteController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param INavigationManager $navigationManager
	 * @param IURLGenerator $urlGenerator
	 * @param IL10N $l10n
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								INavigationManager $navigationManager,
								IURLGenerator $urlGenerator,
								IL10N $l10n) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->navigationManager = $navigationManager;
		$this->urlGenerator = $urlGenerator;
		$this->l10n = $l10n;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $name
	 * @param string $dir
	 * @return TemplateResponse|RedirectResponse
	 */
	public function index(string $name = '', string $dir = '') {
		//$this->navigationManager->setActiveEntry('photopea');

		$api = \OC::$WEBROOT . '/apps/photopea/api';

		if (empty($name) || empty($dir)) {
			$config = '%7B"files":%5B%5D,"resources":%5B%5D,"server":%7B"version":1,"url":"'.$api.'","formats":%5B"PSD"%5D%7D%7D';
		} else {
			$file = \OC::$WEBROOT . '/apps/photopea/d' . str_replace('//', '/', $dir.'/'.$name);

			$config = urlencode('{"files":["'.$file.'"],"resources":[],"server":{"version":1,"url":"'.$api.'","formats":["PSD"]}}');
			// fix %20
			$config = str_replace('+', '%20', $config);
		}

		$response = new TemplateResponse('photopea', 'frame', [
			'url' => $this->urlGenerator->linkTo('photopea', 'sources/index.html').'#'.$config,
			'name' => $name,
		], 'user');

		$policy = new ContentSecurityPolicy();
		$policy->addAllowedChildSrcDomain('*');
		$policy->addAllowedFrameDomain('*');
		$response->setContentSecurityPolicy($policy);

		return $response;
	}

}
