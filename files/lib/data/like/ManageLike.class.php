<?php
namespace wcf\data\like;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Provides methods for manageable reactions.
 *
 * @author		2020-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.reactions
 */
class ManageLike extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Like::class;
	
	/**
	 * data
	 */
	protected $description = '';
	protected $isAccessible = false;
	protected $title = '';
	protected $userProfile;
	protected $receiverUserProfile;
	
	/**
	 * description of the object type displayed in the list of likes
	 */
	protected $objectTypeDescription;
	
	/**
	 * Marks this like as accessible for current user.
	 */
	public function setIsAccessible() {
		$this->isAccessible = true;
	}
	
	/**
	 * Returns true if like is accessible by current user.
	 *
	 * @return	boolean
	 */
	public function isAccessible() {
		return $this->isAccessible;
	}
	
	/**
	 * Sets user profile.
	 */
	public function setUserProfile(UserProfile $userProfile) {
		$this->userProfile = $userProfile;
	}
	
	/**
	 * Returns user profile.
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns the receivers user profile.
	 */
	public function getReceiverUserProfile() {
		if ($this->receiverUserProfile === null) {
			$this->receiverUserProfile = UserProfileRuntimeCache::getInstance()->getObject($this->objectUserID);
		}
		
		return $this->receiverUserProfile;
	}
	
	/**
	 * Like description.
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Like title.
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the object type name.
	 */
	public function getObjectTypeName() {
		return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->objectType;
	}
	
	/**
	 * Description of the object type displayed in the list of likes.
	 */
	public function setObjectTypeDescription($name) {
		$this->objectTypeDescription = $name;
	}
	public function getObjectTypeDescription() {
		if ($this->objectTypeDescription !== null) {
			return $this->objectTypeDescription;
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.like.objectType.' . $this->getObjectTypeName());
	}
}
