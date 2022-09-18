/**
 * Handles reaction deletion.
 * 
 * @author        2020-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.wcf.reactions
 */
define(['Ajax', 'Core', 'Dictionary', 'Dom/Util', 'EventHandler', 'Ui/Confirmation', 'Ui/Notification', 'WoltLabSuite/Core/Controller/Clipboard'],
    function (Ajax, Core, Dictionary, DomUtil, EventHandler, UiConfirmation, UiNotification, ControllerClipboard) {
    "use strict";

    var _reactions = new Dictionary();

    /**
     * @constructor
     */
    function UZManageReactionsInlineEditor(objectId, options) { this.init(objectId, options); }
    UZManageReactionsInlineEditor.prototype = {
        /**
         * Initializes the ACP inline editor for reactions.
         */
        init: function (objectId, options) {
            this._options = Core.extend({
                redirectUrl: ''
            }, options);

            if (objectId) {
                this._initReaction(null, objectId);
            }
            else {
                elBySelAll('.jsLikeRow', undefined, this._initReaction.bind(this));

                EventHandler.add('com.woltlab.wcf.clipboard', 'com.uz.wcf.reaction', this._clipboardAction.bind(this));
            }
        },

        /**
         * Reacts to executed clipboard actions.
         */
        _clipboardAction: function(actionData) {
            if (actionData.responseData !== null) {
                var triggerFunction;
                switch (actionData.data.actionName) {
                    case 'com.uz.wcf.reaction.delete':
                        triggerFunction = this._triggerDelete;
                        break;
                }

                if (triggerFunction) {
                    for (var i = 0, length = actionData.responseData.objectIDs.length; i < length; i++) {
                        triggerFunction(actionData.responseData.objectIDs[i]);
                    }

                    UiNotification.show();
                }
            }
        },

        /**
         * Initializes a reaction row element.
         */
        _initReaction: function (reaction, objectId) {
            if (!reaction && ~~objectId > 0) {
                reaction = undefined;
            }
            else {
                objectId = ~~elData(reaction, 'object-id');
            }

            var buttonDelete = elBySel('.jsButtonDelete', reaction);
            buttonDelete.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'delete'));

            _reactions.set(objectId, {
                buttons: {
                    delete: buttonDelete
                },
                element: reaction
            });
        },

        /**
         * Prompts a user to confirm the clicked action before executing it.
         */
        _prompt: function (objectId, actionName, event) {
            event.preventDefault();

            var reaction = _reactions.get(objectId);

            UiConfirmation.show({
                confirm: (function () { this._invoke(objectId, actionName) }).bind(this),
                message: elData(reaction.buttons[actionName], 'confirm-message-html'),
                messageIsHtml: true
            });
        },

        /**
         * Invokes the selected action.
         */
        _invoke: function (objectId, actionName) {
            Ajax.api(this, {
                actionName: actionName,
                objectIDs: [objectId]
            });
        },

        /**
         * Handles a reaction being deleted.
         */
        _triggerDelete: function(likeId) {
            var reaction = _reactions.get(likeId);
            if (reaction === undefined || !reaction) {
                return;
            }

            var tbody = reaction.element.parentNode;
            elRemove(reaction.element);

            if (elBySel('tr', tbody) === null) {
                window.location.reload();
            }
        },

        _ajaxSuccess: function (data) {
            var notificationCallback;

            switch (data.actionName) {
                case 'delete':
                    this._triggerDelete(data.objectIDs[0]);
                    break;
            }

            UiNotification.show(undefined, notificationCallback);
            ControllerClipboard.reload();
        },

        _ajaxSetup: function () {
            return {
                data: {
                    className: 'wcf\\data\\like\\ManageLikeAction'
                }
            }
        }
    };

    return UZManageReactionsInlineEditor;
});
