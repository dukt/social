var Social = {};

Social.LoginAccountsPane = Craft.AdminTable.extend(
    {
        init: function(settings) {
            this.base(settings);
            this.setSettings(settings, Social.LoginAccountsPane.defaults);
        },

        confirmDeleteItem: function($row) {
            var name = this.getItemName($row);
            return confirm(Craft.t('app', this.settings.confirmDeleteMessage, {name: name}));
        },

        handleDeleteBtnClick: function(event) {
            if (this.settings.minItems && this.totalItems <= this.settings.minItems) {
                // Sorry pal.
                return;
            }

            var $row = $(event.target).closest('tr');

            var $target = $(event.target);

            if($target.hasClass('disconnect')) {
                if (this.confirmDeleteItem($row)) {
                    this.disconnectItem($row);
                }
            } else {
                if (this.confirmDeleteItem($row)) {
                    this.deleteItem($row);
                }
            }
        },

        deleteItem: function($row) {
            var data = {
                id: this.getItemId($row)
            };

            Craft.postActionRequest(this.settings.deleteAction, data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    this.handleDeleteItemResponse(response, $row);
                    Craft.cp.displayNotice(Craft.t('social', 'Disconnected from {name}.', {name: this.getItemName($row)}));
                }
            }, this));
        },

        disconnectItem: function($row) {
            var data = {
                provider: this.getItemProvider($row)
            };

            Craft.postActionRequest(this.settings.disconnectAction, data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    this.handleDeleteItemResponse(response, $row);
                    Craft.cp.displayNotice(Craft.t('social', 'Disconnected from {name}.', {name: this.getItemName($row)}));
                }
            }, this));
        },

        handleDeleteItemResponse: function(response, $row) {
            var providerHandle = this.getItemProvider($row);
            var $deleteBtn = $row.find('.delete');
            var $td = $deleteBtn.parent();

            $deleteBtn.remove();

            var $connectBtn = $('<div class="btn small submit disabled">'+Craft.t('social', 'Connect')+'</div>');

            if(this.getItemConnectEnabled($row)) {
                $connectBtn = $('<a class="btn small submit" href="'+Craft.getActionUrl('social/login-accounts/login', {provider: providerHandle})+'">'+Craft.t('social', 'Connect')+'</a>');
            }

            $connectBtn.appendTo($td);
        },

        getItemProvider: function($row) {
            return $row.attr(this.settings.providerAttribute);
        },

        getItemConnectEnabled: function($row) {
            return $row.attr(this.settings.connectEnabledAttribute);
        },
    },
    {
        defaults: {
            providerAttribute: 'data-provider',
            connectEnabledAttribute: 'data-connect-enabled',
            deleteAction: 'social/login-accounts/delete-login-account',
            disconnectAction: 'social/login-accounts/disconnect-login-account',
            confirmDeleteMessage: Craft.t('social', 'Are you sure you want to disconnect “{name}”?'),
            confirmDisconnectMessage: Craft.t('social', 'Are you sure you want to disconnect “{name}”?'),
        }
    });
