<?php
namespace OCA\Files_Photopea\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Http\Client\IClientService;
use OCP\Files\IRootFolder;

class FileController extends Controller {
	
	/** @var UserId */
	private $UserId;

    /** @var IRootStorage */
    private $storage;

    /** @var IClientService */
    private $clientService;

	public function __construct($AppName, IRequest $request, IRootFolder $storage, IClientService $clientService, $UserId){
		parent::__construct($AppName, $request);
		$this->UserId = $UserId;
		$this->storage = $storage;
		$this->clientService = $clientService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * 
	 * @param string $path
	 */
	public function get(string $path) {
		return new RedirectResponse('/remote.php/webdav/' . $path);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $path
	 */
	public function put(string $path) {

		$userFolder = $this->storage->getUserFolder($this->UserId);

		// check if file exists and write to it if possible
		try {
			try {
				$file = $userFolder->get($path);
			} catch(\OCP\Files\NotFoundException $e) {
				$userFolder->touch($path);
				$file = $userFolder->get($path);
			}

			// the id can be accessed by $file->getId();
			$fi = fopen("php://input", "rb");
			$p = JSON_decode(fread($fi, 2000));
			$file->putContent($fi);
			//fclose($fi);

		} catch(\OCP\Files\NotPermittedException $e) {
			// you have to create this exception by yourself ;)
			//throw new StorageException('Cant write to file');
			return array('message'=> 'Cant write to file');
		}

		return $file->getId() ? array('message'=> 'Successfully saved') : array('message'=> 'Failed to save file');

	}

	// /**
	//  * @NoAdminRequired
	//  * @NoCSRFRequired
	//  * 
	//  * @param string $path
	//  */
	// public function fonts(string $path) {
	// 	$client = $this->clientService->newClient();
	// 	$response = $client->get('https://ixiumu.github.io/photopea/rsrc/fonts/' . $path);
	// 	return new DataDownloadResponse($response->getBody(), '', 'application/font');
	// }
}
