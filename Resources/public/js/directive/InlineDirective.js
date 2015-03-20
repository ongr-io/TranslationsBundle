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
    .directive('inline', ['$http', 'asset', function ($http, $asset) {
        return {
            restrict: "A",
            scope: { translation: "=" },
            templateUrl: $asset.getLink('template/inline.html'),
            link: function(scope, element, attr) {

                var inputElement = angular.element(element[0].children[1].children[1])[0];

                scope.message = scope.translation.messages[attr.locale];
                
                scope.value = null;
                
                scope.error = null;
                
                scope.acting = false;
                
                if (attr.locale != undefined && attr.locale != '') {
                    if (scope.message) {
                        scope.value = scope.message.message;
                    }
                    
                    scope.field = 'messages';
                }
                
                element.addClass('inline-edit');
                
                scope.tryActEmpty = function() {
                    if (scope.value == null || scope.value == '') {
                        scope.acting = true;
                        scope.value = 'Empty field.';
                        element.parent().addClass('bg-danger');
                        
                        return true;
                    }
                    
                    return false;
                }

                scope.tryActEmpty();
                
                scope.suspendEmpty = function () {
                    element.parent().removeClass('bg-danger');
                    scope.acting = false;
                }

                /**
                 * Appears input field
                 */
                scope.edit = function() {
                    if (scope.acting) {
                        scope.value = '';
                    }
                    
                    scope.oldValue = scope.value;
                    element.addClass('active');
                    inputElement.focus();
                };

                /**
                 * Closes input field.
                 */
                scope.close = function() {
                    if (scope.error !== 0 || scope.error === null) {
                        scope.value = scope.oldValue;
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
                            scope.error = 0;
                            scope.httpSave();
                        })
                        .error(function() {
                            scope.error = 1;
                        });
                };
                
                scope.httpValidate = function() {
                    return $http.post(
                        Routing.generate('ongr_translations_api_check'),
                        {
                            message: scope.value,
                            locale: attr.locale,
                        }
                    )
                }
                
                scope.httpSave = function() {
                    $http.post(
                        Routing.generate('ongr_translations_api_edit'),
                        {
                            id: scope.translation.id,
                            name: 'messages',
                            properties: {
                                message: scope.value,
                                locale: attr.locale,
                                status: 'dirty'
                            },
                            findBy: {
                                locale: attr.locale
                            }
                        }
                    ).success(function(){
                            if (!scope.tryActEmpty()) {
                                scope.suspendEmpty();
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
            }
        }
    }]);
