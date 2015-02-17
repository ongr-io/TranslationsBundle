/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('controller.list', [])
    .controller('list', ['$scope', '$http', 'DATA', function($scope, $http, DATA) {
        $scope.translations = DATA;
    }])
    .directive('editable', ['$http', function($http) {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, elm, attrs) {
                /**
                 * Input blur event.
                 */
                elm.on('blur', function() {
                    //elm.val(scope.oldValue);
                    elm.attr('disabled', true);
                });

                /**
                 * Enables input field on double click.
                 */
                scope.edit = function() {
                    elm.attr('disabled', false);
                    scope.oldValue = elm.val();
                };

                /**
                 * Disables input field.
                 */
                scope.close = function() {
                    scope.value = scope.oldValue;
                    elm.val(scope.value);
                    elm.attr('disabled', true);
                };

                /**
                 * Save new value.
                 */
                scope.save = function() {

                    $http({
                        method:"POST",
                        url: Routing.generate('ongr_translations_translation_edit', {'id' : scope.trans.id} ),
                        data: {
                            translation: {
                                field: attrs.ngModel.substr(attrs.ngModel.lastIndexOf('.') + 1),
                                value: elm.val()
                            }
                        }
                    });
                };

                /**
                 * Extra shortcuts for better user experience
                 *
                 * @param e Event
                 */
                scope.keyPress = function(e) {
                    switch(e.keyCode) {
                        case 13: //enter
                            scope.save();
                            elm.attr('disabled', true);
                            break;
                        case 27: //esc
                            scope.close();
                            break;
                    }
                }
            }
        };
    }]);
