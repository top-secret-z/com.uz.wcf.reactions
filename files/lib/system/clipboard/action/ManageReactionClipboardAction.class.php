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
namespace wcf\system\clipboard\action;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\like\ManageLikeAction;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for reactions.
 */
class ManageReactionClipboardAction extends AbstractClipboardAction
{
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
    public function execute(array $objects, ClipboardAction $action)
    {
        $item = parent::execute($objects, $action);

        if ($item === null) {
            return null;
        }

        // handle actions
        switch ($action->actionName) {
            case 'delete':
                $item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.wcf.reaction.delete.confirmMessage', [
                    'count' => $item->getCount(),
                ]));
                break;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return ManageLikeAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.uz.wcf.reaction';
    }

    /**
     * Returns the ids of the reactions that can be deleted.
     */
    public function validateDelete()
    {
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
