<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\like\ManageLikeAction;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for reactions.
 * 
 * @author		2020-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.reactions
 */
class ManageReactionClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete'];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = ['delete'];
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $objects, ClipboardAction $action) {
		$item = parent::execute($objects, $action);
		
		if ($item === null) {
			return null;
		}
		
		// handle actions
		switch ($action->actionName) {
			case 'delete':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.wcf.reaction.delete.confirmMessage', [
					'count' => $item->getCount()
				]));
				break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return ManageLikeAction::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'com.uz.wcf.reaction';
	}
	
	/**
	 * Returns the ids of the reactions that can be deleted.
	 */
	public function validateDelete() {
		$objectIDs = [];
		
		// permission
		if (!WCF::getSession()->getPermission('admin.user.canDeleteReactions')) {
			return $objectIDs;
		}
		
		foreach ($this->objects as $reaction) {
			$objectIDs[] = $reaction->likeID;
		}
		
		return $objectIDs;
	}
}
