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
    .controller('list', ['$scope', '$http', 'DATA', 'LOCALES', function($scope, $http, DATA, LOCALES) {
        $scope.translations = DATA;
        $scope.locales = LOCALES;
    }]);
