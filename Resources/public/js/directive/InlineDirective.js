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

                scope.field = attr.field;
                element.addClass('inline-edit');

                if (scope.translation[scope.field] === null) {
                    scope.value = 'Empty message!';
                    element.parent().addClass('bg-danger')
                } else {
                    scope.value = scope.translation[scope.field];
                }

                /**
                 * Appears input field
                 */
                scope.edit = function() {
                    scope.oldValue = scope.value;
                    element.addClass('active');
                    inputElement.focus();
                };

                /**
                 * Closes input field.
                 */
                scope.close = function() {
                    scope.value = scope.oldValue;
                    element.removeClass('active');
                };

                /**
                 * Saves values with ajax request.
                 */
                scope.save = function() {
                    element.removeClass('active');

                    var requestUrl = Routing.generate('ongr_translations_translation_edit',
                        {
                            id: scope.translation.id
                        }
                    );

                    $http({
                        method:"POST",
                        url: requestUrl,
                        data: {
                            value: scope.value,
                            field: scope.field
                        }

                    }).success(function(){
                        element.parent().removeClass('bg-danger')
                    });
                };

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
