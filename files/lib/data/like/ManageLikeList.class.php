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

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ObjectTypeCacheBuilder;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Represents a list of manageable reactions.
 */
class ManageLikeList extends LikeList
{
    /**
     * @inheritDoc
     */
    public $className = Like::class;

    /**
     * @inheritDoc
     */
    public $decoratorClassName = ManageLike::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'like_table.time DESC';

    /**
     * filter
     */
    protected $receiver;

    /**
     * Creates a new ManageLikeList object.
     */
    public function __construct($guest = null, $objectTypeID = null, $reactionTypeID = null, $receiver = null, $user = null)
    {
        parent::__construct();

        $this->guest = $guest;
        $this->objectTypeID = $objectTypeID;
        $this->reactionTypeID = $reactionTypeID;
        $this->receiver = $receiver;
        $this->user = $user;

        $this->sqlSelects = 'user_table.username AS receiver, user_table2.username AS user, like_table.objectTypeID AS type, like_table.objectID AS title, like_table.objectID AS url';
        $this->sqlJoins = " LEFT JOIN wcf" . WCF_N . "_user user_table ON (user_table.userID = like_table.objectUserID)
                            LEFT JOIN wcf" . WCF_N . "_user user_table2 ON (user_table2.userID = like_table.userID)";
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        $userIDs = [];
        $likeGroups = [];
        foreach ($this->objects as $like) {
            $userIDs[] = $like->userID;

            if (!isset($likeGroups[$like->objectTypeID])) {
                $objectType = ObjectTypeCache::getInstance()->getObjectType($like->objectTypeID);
                $likeGroups[$like->objectTypeID] = [
                    'provider' => $objectType->getProcessor(),
                    'objects' => [],
                ];
            }

            $likeGroups[$like->objectTypeID]['objects'][] = $like;
        }

        // set user profiles
        if (!empty($userIDs)) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs(\array_unique($userIDs));
        }

        // parse like
        foreach ($likeGroups as $likeData) {
            if ($likeData['provider'] instanceof IViewableLikeProvider) {
                $likeData['provider']->prepare($likeData['objects']);
            }
        }

        // validate permissions
        foreach ($this->objects as $index => $like) {
            if (!$like->isAccessible()) {
                unset($this->objects[$index]);
            }
        }
        $this->indexToObject = \array_keys($this->objects);
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $conditions = new PreparedStatementConditionBuilder();
        if (!empty($this->objectTypeID)) {
            $conditions->add("like_table.objectTypeID = ?", [$this->objectTypeID]);
        }
        if (!empty($this->reactionTypeID)) {
            $conditions->add("like_table.reactionTypeID = ?", [$this->reactionTypeID]);
        }

        if (!empty($this->guest)) {
            $conditions->add("like_table.objectUserID IS NULL");
        } else {
            if (!empty($this->receiver)) {
                $conditions->add("user_table.username LIKE ?", ['%' . $this->receiver . '%']);
            }
            if (!empty($this->user)) {
                $conditions->add("user_table2.username LIKE ?", ['%' . $this->user . '%']);
            }
        }

        $sql = "SELECT COUNT(*)    FROM wcf" . WCF_N . "_like like_table 
                LEFT JOIN wcf" . WCF_N . "_user user_table ON (user_table.userID = like_table.objectUserID)
                LEFT JOIN wcf" . WCF_N . "_user user_table2 ON (user_table2.userID = like_table.userID)
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * Returns a list of available reaction types.
     */
    public function getAvailableReactionsTypes()
    {
        $reactionTypes = ReactionTypeCacheBuilder::getInstance()->getData();
        $reactionTypeIDs = [];
        $sql = "SELECT        DISTINCT reactionTypeID
                FROM        wcf" . WCF_N . "_like
                ORDER BY    reactionTypeID";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if ($row['reactionTypeID'] && isset($reactionTypes[$row['reactionTypeID']])) {
                $reactionTypeIDs[$row['reactionTypeID']] = $reactionTypes[$row['reactionTypeID']]->renderIcon();
            }
        }

        return $reactionTypeIDs;
    }

    /**
     * Returns a list of available object types.
     */
    public function getAvailableObjectTypes()
    {
        $objectTypes = ObjectTypeCacheBuilder::getInstance()->getData()['objectTypes'];

        $objectTypeIDs = [];
        $sql = "SELECT        DISTINCT objectTypeID
                FROM        wcf" . WCF_N . "_like
                ORDER BY    objectTypeID";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if ($row['objectTypeID'] && isset($objectTypes[$row['objectTypeID']])) {
                $temp = 'wcf.like.objectType.' . $objectTypes[$row['objectTypeID']]->objectType;
                if (WCF::getLanguage()->get($temp) === $temp) {
                    $temp = 'wcf.reactions.manage.object.unknown';
                }
                $objectTypeIDs[$row['objectTypeID']] = $temp;
            }
        }

        return $objectTypeIDs;
    }
}
