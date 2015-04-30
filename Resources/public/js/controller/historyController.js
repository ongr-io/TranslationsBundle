/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('controller.history', [])
    .controller('history', ['$scope', '$modalInstance', '$http', 'STATUS', 'history',
        function ($scope, $modalInstance, $http, STATUS, history) {

            /**
             * @type {Array}
             */
            $scope.data = [];

            /**
             * @type {object}
             */
            var ladda = null;

            setTimeout(function() {
                ladda = Ladda.create(document.querySelector('.ladda-button'));
                ladda.start();

                $scope.$watch('data', function(newValue, oldValue) {
                    newValue.length > 0 ? ladda.enable() : ladda.disable();
                });
            }, 50);

            /**
             * Retrieves history though post http request.
             *
             * @returns {Promise}
             */
            $scope.httpTranslations = function () {
                return $http.post(
                    Routing.generate('ongr_translations_api_history'),
                    {
                        key: history[0],
                        locale: history[1],
                        domain: history[2]
                    }
                )
            }

            /**
             * Normalizes and sets translations into scope.
             */
            $scope.getNormalizedTranslations = function() {
                $scope.httpTranslations()
                    .success(function(data) {
                        for (messageKey in data) {
                            $scope.data.push(
                                {
                                    message: data[messageKey].message,
                                    created_at: data[messageKey].created_at
                                }
                            );
                        }
                    });
            }

            $scope.getNormalizedTranslations();

            /**
             * Closes modal.
             */
            $scope.close = function () {
                $modalInstance.dismiss('cancel');
            }
        }]);
