<?php
/**
 * Nextcloud - passman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Sander Brand <brantje@gmail.com>
 * @copyright Sander Brand 2016
 */

namespace OCA\Passman\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;

use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUserManager;
use OCP\IUser;

use OCA\Passman\Service\VaultService;
use OCA\Passman\Service\CredentialService;
use OCA\Passman\Service\UserService;


class ShareController extends ApiController {
	private $userId;
	private $activityService;
	private $groupManager;
	private $userManager;

	private $limit = 50;
	private $offset = 0;

	private $result = [];

	public function __construct($AppName,
								IRequest $request,
								IUser $UserId,
								IGroupManager $groupManager,
								IUserManager $userManager,
								ActivityService $activityService
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->activityService = $activityService;
	}


	public function searchUsers($search) {
		$users = array();
		$usersTmp = $this->userManager->searchDisplayName($search, $this->limit, $this->offset);

		foreach ($usersTmp as $user) {
			$users[] = array(
				'text' => $user->getDisplayName(),
				'uid' => $user->getUID(),
				'type' => 'user'
			);
		}
		$this->result = array_merge($this->result, $users);
	}

	public function searchGroups($search){

		$groups = array();
		$groupsTmp = $this->groupManager->search($search, $this->limit, $this->offset);


		foreach ($groupsTmp as $group) {
			$groups[] = array(
				'text' => $group->getGID(),
				'uid' => $group->getGID(),
				'type' => 'group'
			);
		}


		$this->result = array_merge($this->result, $groups);
	}

	/**
	 * @NoAdminRequired
	 */
	public function search($search) {
		$this->searchUsers($search);
		$this->searchGroups($search);

		return new JSONResponse($this->result);
	}


	public function share($credential){

		$link = '';
		$this->activityService->add(
			'item_shared', array($credential->label, $this->userId),
			'', array(),
			$link, $this->userId, Activity::TYPE_ITEM_ACTION);
	}

}