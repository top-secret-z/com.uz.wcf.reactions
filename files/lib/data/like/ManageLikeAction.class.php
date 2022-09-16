<?php
namespace wcf\data\like;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\like\Like;
use wcf\data\like\object\LikeObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Provides methods for manageable reactions.
 *
 * @author		2020-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.reactions
 */
class ManageLikeAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = LikeEditor::class;
	
	/**
	 * Validates the 'delete' action.
	 */
	public function validateDelete() {
		// need permission
		if (!WCF::getSession()->getPermission('admin.user.canDeleteReactions')) {
			throw new PermissionDeniedException();
		}
		
		// need objects
		// if single selected like does not exist, an error message is thrown. Accept it.
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Executes the 'delete' action.
	 */
	public function delete() {
		foreach ($this->getObjects() as $likeEditor) {
			try {
				$like = new Like($likeEditor->likeID);
				if (!$like->likeID) continue;
				
				$objectType = ObjectTypeCache::getInstance()->getObjectType($like->objectTypeID);
				if ($objectType !== null) {
					$processor = $objectType->getProcessor();
					$likeableObject = $processor->getObjectByID($like->objectID);
					$likeableObject->setObjectType($objectType);
					
					ReactionHandler::getInstance()->revertReact($like, $likeableObject, LikeObject::getLikeObject($objectType->objectTypeID, $like->objectID), UserRuntimeCache::getInstance()->getObject($like->userID));
				}
			}
			catch (\Exception $e) {
				continue;
			}
		}
	}
}
