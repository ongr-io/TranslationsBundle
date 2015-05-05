/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('directive.inline', [])
    .directive('inline', ['$http', 'asset', 'STATUS', '$modal',
        function ($http, $asset, STATUS, $modal) {
        return {
            restrict: "A",
            scope: {
                translation: "="
            },
            templateUrl: $asset.getLink('template/inline.html'),
            link: function(scope, element, attr) {

                var inputElement = angular.element(element[0].children[1].children[1])[0];

                element.addClass('inline-edit');

                /**
                 * @type {}
                 */
                scope.message = scope.translation.messages[attr.locale] ? scope.translation.messages[attr.locale] : {message: null};

                /**
                 * @type {int}
                 */
                scope.error = null;

                /**
                 * @type {boolean}
                 */
                scope.acting = false;

                /**
                 * Acts as empty value if it is null.
                 *
                 * @returns {boolean}
                 */
                scope.tryActEmpty = function() {
                    if (scope.message.message == null || scope.message.message == '') {
                        scope.acting = true;
                        scope.message.message = 'Empty field.';
                        element.parent().addClass('bg-danger');

                        return true;
                    }

                    return false;
                }

                scope.tryActEmpty();

                /**
                 * Suspends empty message.
                 */
                scope.suspendEmpty = function () {
                    element.parent().removeClass('bg-danger');
                    scope.acting = false;
                }

                /**
                 * Appears input field
                 */
                scope.edit = function() {
                    if (scope.acting) {
                        scope.message.message = '';
                    }

                    scope.oldValue = scope.message.message;
                    element.addClass('active');
                    inputElement.focus();
                };

                /**
                 * Closes input field.
                 */
                scope.close = function() {
                    if (scope.error !== 0 || scope.error === null) {
                        scope.message.message = scope.oldValue;
                    }

                    scope.error = null;
                    element.removeClass('active');
                    scope.tryActEmpty();
                };

                /**
                 * Saves values with ajax request.
                 */
                scope.save = function($event) {
                    scope
                        .httpValidate()
                        .success(function() {
                            scope.httpSave();
                            scope.error = 0;
                        })
                        .error(function() {
                            scope.error = 1;
                        });
                };

                /**
                 * Validates translation message through http.
                 *
                 * @returns {Promise}
                 */
                scope.httpValidate = function() {
                    return $http.post(
                        Routing.generate('ongr_translations_api_check'),
                        {
                            message: scope.message.message,
                            locale: attr.locale,
                        }
                    )
                }

                /**
                 * Saves translation throught http request if it's valid.
                 */
                scope.httpSave = function() {
                    $http.post(
                        Routing.generate('ongr_translations_api_edit'),
                        {
                            id: scope.translation.id,
                            name: 'messages',
                            properties: {
                                message: scope.message.message,
                                locale: attr.locale,
                                status: STATUS.changed
                            },
                            findBy: {
                                locale: attr.locale
                            }
                        }
                    ).success(function(){
                            if (!scope.tryActEmpty()) {
                                scope.suspendEmpty();
                                scope.message.status = STATUS.changed;
                            }
                        });
                }

                /**
                 * Extra shortcuts for better ux.
                 *
                 * @param e Event
                 */
                scope.keyPress = function(e) {
                    switch(e.keyCode) {
                        case 13: // Enter.
                            scope.save();
                            break;
                        case 27: // Esc.
                            scope.close();
                            break;
                    }
                }

                /**
                 * Returns locale, key, domain of the message.
                 *
                 * @returns {*[]|*}
                 */
                scope.getMessageParams = function() {
                    locale = scope.$parent.locale;
                    key = scope.translation.key;
                    domain = scope.translation.domain;
                    arr = [key, locale, domain];

                    return arr
                };

                /**
                 * Opens up modal for history.
                 */
                scope.history = function() {
                    $modal.open({
                        controller: 'history',
                        templateUrl: $asset.getLink('template/historyModal.html'),
                        resolve: {
                            history: function () {
                                return scope.getMessageParams();
                            }
                        }
                    });
                }
            }
        }
    }]);
