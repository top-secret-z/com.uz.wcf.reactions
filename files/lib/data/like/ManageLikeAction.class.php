<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wcf\data\like;

use Exception;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\like\object\LikeObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Provides methods for manageable reactions.
 */
class ManageLikeAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = LikeEditor::class;

    /**
     * Validates the 'delete' action.
     */
    public function validateDelete()
    {
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
    public function delete()
    {
        foreach ($this->getObjects() as $likeEditor) {
            try {
                $like = new Like($likeEditor->likeID);
                if (!$like->likeID) {
                    continue;
                }

                $objectType = ObjectTypeCache::getInstance()->getObjectType($like->objectTypeID);
                if ($objectType !== null) {
                    $processor = $objectType->getProcessor();
                    $likeableObject = $processor->getObjectByID($like->objectID);
                    $likeableObject->setObjectType($objectType);

                    ReactionHandler::getInstance()->revertReact($like, $likeableObject, LikeObject::getLikeObject($objectType->objectTypeID, $like->objectID), UserRuntimeCache::getInstance()->getObject($like->userID));
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }
}
