/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('controller.export', [])
    .controller('export', ['$scope', '$modalInstance', '$http', 'locales', 'STATUS',
        function ($scope, $modalInstance, $http, locales, STATUS) {

            /**
             * @type {Array}
             */
            $scope.data = [];

            /**
             * @type {Array}
             */
            $scope.locales = locales;

            /**
             * @type {{default: string, ok: string, error: string}}
             */
            $scope.feedMessages = {
                default: 'No changes detected.',
                ok: 'Translations successfully exported.',
                error: 'Error occurred while exporting translations.',
            };

            /**
             * @type {string}
             */
            $scope.feed = 'default';

            /**
             * @type {object}
             */
            var ladda = null;

            setTimeout(function() {
                ladda = Ladda.create(document.querySelector('.ladda-button'));

                $scope.$watch('data', function(newValue, oldValue) {
                    newValue.length > 0 ? ladda.enable() : ladda.disable();
                });
            }, 50);

            /**
             * Returns feedback message. 
             *
             * @returns {string}
             */
            $scope.getFeedMessage = function() {
                return $scope.feedMessages[$scope.feed];
            }

            /**
             * Returns class name to use on dom element in which message is put.
             * 
             * @returns {string}
             */
            $scope.getFeedClass = function() {
                switch ($scope.feed) {
                    case 'ok':
                        return 'alert-success';
                    case 'error':
                        return 'alert-danger';
                    default:
                        return 'alert-warning';
                }
            }

            /**
             * Retrieves translatons though post http request.
             * 
             * @returns {Promise}
             */
            $scope.httpTranslations = function () {
                return $http.post(
                    Routing.generate('ongr_translations_api_get'),
                    {
                        name: 'messages',
                        findBy: {
                            status: STATUS.changed
                        }
                    }
                )
            }

            /**
             * Normalizes and sets translations into scope.
             */
            $scope.getNormalizedTranslations = function() {
                $scope.httpTranslations()
                    .success(function(data) {
                        for (transKey in data) {
                            for (msgKey in data[transKey].messages) {
                                if (data[transKey].messages[msgKey].status === STATUS.changed) {
                                    $scope.data.push(
                                        {
                                            domain: data[transKey].domain,
                                            key: data[transKey].key,
                                            locale: data[transKey].messages[msgKey].locale,
                                            message: data[transKey].messages[msgKey].message
                                        }
                                    );
                                }
                                
                            }
                        }
                    });
            }

            $scope.getNormalizedTranslations();

            /**
             * Exports translations through http.
             */
            $scope.export = function () {
                ladda.start();
                
                console.log(ladda);
                
                $http.post(Routing.generate('ongr_translations_api_export'), {})
                    .success(function () {
                        ladda.stop();
                        $scope.data = [];
                        $scope.feed = 'ok';
                    })
                    .error(function () {
                        ladda.stop();
                        $scope.feed = 'error';
                    });
            }

            /**
             * Closes modal.
             */
            $scope.close = function () {
                $modalInstance.dismiss('cancel');
                $scope.feed = $scope.feedMessages.default;
            }
    }]);
