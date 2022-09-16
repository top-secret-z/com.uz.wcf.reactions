<?php
namespace wcf\page;
use wcf\data\like\ManageLikeList;
use wcf\system\clipboard\ClipboardHandler;
use wcf\util\StringUtil;
use wcf\system\WCF;

/**
 * Shows the reaction page.
 * 
 * @author		2020-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.reactions
 */
class ManageReactionsPage extends SortablePage{
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'com.uz.wcf.Reactions';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_LIKE'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canViewReactions'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 20;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ManageLikeList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['likeID' ,'time', 'reactionTypeID', 'objectTypeID', 'receiver', 'user'];
	
	/**
	 * search
	 */
	public $guest = 0;
	public $reactionTypeID = 0;
	public $objectTypeID = 0;
	public $receiver = '';
	public $user = '';
	
	public $availableReactionsTypes;
	public $availableObjectTypes;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->objectTypeID = $this->reactionTypeID = $this->guest = 0;
		if (!empty($_REQUEST['objectTypeID'])) $this->objectTypeID = intval($_REQUEST['objectTypeID']);
		if (!empty($_REQUEST['reactionTypeID'])) $this->reactionTypeID = intval($_REQUEST['reactionTypeID']);
		if (!empty($_REQUEST['guest'])) $this->guest = intval($_REQUEST['guest']);
		if (!empty($_REQUEST['receiver'])) $this->receiver = StringUtil::trim($_REQUEST['receiver']);
		if (!empty($_REQUEST['user'])) $this->user = StringUtil::trim($_REQUEST['user']);
		
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new ManageLikeList($this->guest, $this->objectTypeID, $this->reactionTypeID, $this->receiver, $this->user);
		
		// reaction reaction and object types
		$this->availableReactionsTypes = $this->objectList->getAvailableReactionsTypes();
		$this->availableObjectTypes = $this->objectList->getAvailableObjectTypes();
		
		// search
		if ($this->objectTypeID) $this->objectList->getConditionBuilder()->add('like_table.objectTypeID = ?', [$this->objectTypeID]);
		if ($this->reactionTypeID) $this->objectList->getConditionBuilder()->add('like_table.reactionTypeID = ?', [$this->reactionTypeID]);
		if ($this->guest) {
			$this->objectList->getConditionBuilder()->add('like_table.objectUserID IS NULL');
		}
		else {
			if ($this->receiver) $this->objectList->getConditionBuilder()->add('user_table.username LIKE ?', ['%' . $this->receiver . '%']);
			if ($this->user) $this->objectList->getConditionBuilder()->add('user_table2.username LIKE ?', ['%' . $this->user . '%']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.wcf.reaction')),
				'availableObjectTypes' => $this->availableObjectTypes,
				'availableReactionsTypes' => $this->availableReactionsTypes,
				'guest' => $this->guest,
				'objectTypeID' => $this->objectTypeID,
				'reactionTypeID' => $this->reactionTypeID,
				'receiver' => $this->receiver,
				'user' => $this->user
		]);
	}
}
