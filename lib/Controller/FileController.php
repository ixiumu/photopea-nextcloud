<?php
namespace OCA\Photopea\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Http\Client\IClientService;
use OCP\Files\IRootFolder;
use OCA\Files\Helper;
use OCP\IL10N;
use OCP\IURLGenerator;

class FileController extends Controller {
	
	/** @var UserId */
	private $UserId;

	/** @var IRootStorage */
	private $storage;

	/** @var IClientService */
	private $clientService;

	/** @var IL10N */
	private $l10n;

	/** @var IURLGenerator */
	protected $urlGenerator;

	public function __construct($AppName,
								IRequest $request,
								IRootFolder $storage,
								IClientService $clientService,
								IL10N $l10n,
								IURLGenerator $urlGenerator,
								$UserId){
		parent::__construct($AppName, $request);
		$this->UserId = $UserId;
		$this->storage = $storage;
		$this->clientService = $clientService;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @param string $path
	 */
	public function get(string $path) {
		return new RedirectResponse(\OC::$WEBROOT . "/remote.php/webdav/" . $path);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @param string $name
	 * @param string $dir
	 */
	public function create(string $name, string $dir) {
		
		if (empty($name) || substr($name , -4) !== ".psd") {
			return ["error" => "File name not found"];
		}

		$userFolder = $this->storage->getUserFolder($this->UserId);

		if ($userFolder instanceof File) {
			return ["error" => $this->l10n->t("You don't have enough permission")];
		}

		$folder = $userFolder->get($dir);

		if ($folder === null) {
			return ["error" => $this->l10n->t("The required folder was not found")];
		}
		if (!($folder->isCreatable() && $folder->isUpdateable())) {
			return ["message" => $this->l10n->t("You don't have enough permission")];
		}

		try {
			$template = file_get_contents(dirname(__DIR__) . "/../sources/images/pea.psd");

			if (\version_compare(\implode(".", \OCP\Util::getVersion()), "19", "<")) {
				$file = $folder->newFile($name);

				$file->putContent($template);
			} else {
				$file = $folder->newFile($name, $template);
			}
		} catch (NotPermittedException $e) {
			return ["error" => "Can't create file"];
		}

		$fileInfo = $file->getFileInfo();

		$result = Helper::formatFileInfo($fileInfo);
		return $result;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 */
	public function save() {
		$userFolder = $this->storage->getUserFolder($this->UserId);

		if ($userFolder instanceof File) {
			return ["message" => $this->l10n->t("You don't have enough permission")];
		}

		$peaFolder = "Photopea";

		try {

			$fi = fopen("php://input", "rb");
			$p = JSON_decode(fread($fi, 2000));

			if (!empty($p->source)) {
				$purl = \OC::$WEBROOT . '/apps/photopea/d/';

				if (substr($p->source , 0 , 4) === "http" || substr($p->source , 0 , strlen($purl)) === $purl) {
					$path = substr($p->source, strripos($p->source, $purl) + strlen($purl));
				} else {
					$source = explode(",", $p->source);

					if ($source[0] === "local" && count($source) === 3 && $source[2]) {
						if (!$userFolder->nodeExists($peaFolder)) {
							$userFolder->newFolder($peaFolder);
						}

						$path = $peaFolder ."/" . $source[2];
					}
				}

				$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

				if (empty($path) || strpos($path,".") === false || !in_array($ext, 
				["psd","ai","xcf","sketch","xd","fig","pxd","cdr","svg","eps","pdf","pdn","wmf","emf","png","jpg","gif","webp","ico","bmp",
				"ppm","pgm","pbm","tiff","dds","iff","tga","dng","nef","cr2","arw","raf","gpr","3fr","fff"])) {
					fclose($fi);
					return ["message"=> $this->l10n->t("File format error")];
				}

				if (!$userFolder->nodeExists($path)) {
					$userFolder->newFile($path);
				}
				$file = $userFolder->get($path);
	
				// the id can be accessed by $file->getId();
				$file->putContent($fi);
				//fclose($fi);
				return ["message"=> $this->l10n->t("Successfully saved")];
			}

		} catch(\OCP\Files\NotPermittedException $e) {
			//throw new StorageException("Cant write to file");
			fclose($fi);
		}

		return ["message"=> $this->l10n->t("Cant write to file")];
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 * 
	 * @param string $path
	 */
	public function static(string $path) {

		$path = str_replace(array("'",'#','=','`','$','%','&',';','..'), '', $path);
		$path = rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $path), '/');

		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		if (empty($path) || strpos($path,".") === false || !in_array($ext, 
		["json","zip","csh","wasm","psd","woff","woff2","otf","ttf","webp"])) {
			return ["message"=> $this->l10n->t("File format error")];
		}

		$contentType = [
			"json" => "application/json",
			"zip" => "application/zip",
			"csh" => "application/x-csh",
			"wasm" => "application/wasm",
			"psd" => "application/x-photoshop",
			"woff" => "application/font-woff",
			"woff2" => "application/font-woff2",
			"otf" => "application/x-font-opentype",
			"ttf" => "application/x-font-ttf",
			"webp" => "image/webp",
		];

		$content = file_get_contents(dirname(__DIR__) . "/../sources/" . $path);
		return new DataDownloadResponse($content, "", $contentType[$ext]);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @param string $path
	 */
	public function fonts(string $path) {
		$client = $this->clientService->newClient();
		$response = $client->get("https://ixiumu.github.io/photopea/rsrc/fonts/" . $path);
		return new DataDownloadResponse($response->getBody(), "", "application/font");
	}
}
