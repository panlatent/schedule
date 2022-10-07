/*
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

(function($) {
    /** global: Craft */
    /** global: Garnish */
    var Notifications = Garnish.Base.extend(
        {
            init: function() {
                this.$selectType = $('#notification-type');
                this.$settings = $('#notification-settings');
                this.notificationId = $('#content input[name=id]').val();
                this.editing = this.$settings.data('editing');
                let _this = this;
                
                if (this.$selectType.length) {
                    this.$selectType.change(function () {
                        _this.reloadNotificationsSettings();
                    });
                    if (!this.editing) {
                       this.reloadNotificationsSettings(); 
                    }
                }
            },

            reloadNotificationsSettings: function () {
                this.$settings.html('');
                let _this = this;
                if (!this.$selectType.val()) {
                    return;
                }
                let data = {
                    handle: this.$selectType.val(),
                    action: 'schedule/notifications/settings'
                };
                if ($('#content input[name=id]').val()) {
                    data.id = $('#content input[name=id]').val()
                }
                Craft.postActionRequest('/', data, function(response, textStatus, jqXHR) {
                    if (textStatus === 'success') {
                        _this.$settings.html(response.settings);
                        Craft.initUiElements(_this.$settings);
                        Craft.appendHeadHtml(response.headHtml);
                        Craft.appendFootHtml(response.footHtml);
                    }
                });
            }
        });


    Garnish.$doc.ready(function() {
        new Notifications();
    });
})(jQuery);
