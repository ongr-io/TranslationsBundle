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
    .controller('list', ['$scope', '$http', 'tag', 'DATA', 'LOCALES',
        function($scope, $http, $tag, DATA, LOCALES) {
            
            $scope.translations = DATA;
    
            $scope.locales = LOCALES;
            
            $scope.tag = $tag;
                
            $scope.barVisible = false;
            
            $scope.current = -1;
            
            $scope.barTranslation = {};
            
            $scope.transColWidth = (70 / Object.keys(LOCALES).length)+'px';

            /**
             * Selects translation row.
             * 
             * @param {string} $index
             * @param Event  $event
             */
            $scope.selectCurrent = function($index, $event) {
                if (!$event.target.hasAttribute('ng-click') && !$scope.isInput($event.target)) {
                    $scope.current = $index;
                    $scope.updateTranslationBar();
                    $scope.openBar();
                }
            }

            /**
             * Opens top translation bar.
             */
            $scope.openBar = function() {
                $scope.barVisible = true;
            }

            /**
             * Closes to translation bar.
             */
            $scope.closeBar = function() {
                $scope.barVisible = false;
                $scope.current = -1;
            }

            /**
             * Updates translation used in bar.
             */
            $scope.updateTranslationBar = function ()
            {
                $scope.barTranslation = $scope.translations[$scope.current];
            }

            /**
             * Compares if current index is opened.
             * 
             * @param {string} $index
             * 
             * @returns {boolean}
             */
            $scope.isCurrent = function($index) {
                return $scope.current === $index;
            }

            /**
             * Selects next row.
             */
            $scope.selectNext = function() {
                if (!$scope.barVisible) {
                    $scope.openBar();
                }
                
                if (DATA.length - 2 >= $scope.current) {
                    $scope.current++;
                    $scope.updateTranslationBar();
                }
            }

            /**
             * Selects prev row.
             */
            $scope.selectPrev = function() {
                if (!$scope.barVisible) {
                    $scope.openBar();
                }
                
                if ($scope.current > 0) {
                    $scope.current--;
                    $scope.updateTranslationBar();
                }
            }

            /**
             * Extra shortcuts for better ux.
             *
             * @param Event e
             */
            $scope.keyPress = function(e) {
                if (!$scope.isInput(e.target)) {
                    e.preventDefault();
                    switch(e.keyCode) {
                        case 40: // down
                            $scope.selectNext();
                            break;
                        case 38: // up
                            $scope.selectPrev();
                            break;
                        case 27: //esc
                            $scope.closeBar();
                            break;
                    }
                }
            }

            /**
             * Checks if target is input element.
             * 
             * @param {} $target
             * 
             * @returns {boolean}
             */
            $scope.isInput = function(target) {
                inputs = ['INPUT', 'TEXTAREA', 'A', 'BUTTON'];
                
                for (var key in inputs) {
                    if (inputs[key] === target.tagName) {
                        return true;
                    }
                }
                
                return false;
            }

            /**
             * Returns class for label based on status.
             *
             * @param {string} status
             * 
             * @returns {string}
             */
            $scope.getLabelClass = function(status) {
                switch (status) {
                    case 'dirty':
                        return 'label-danger';
                    case 'fresh':
                        return 'label-success';
                    default:
                        return 'label-default';
                }
            }
    }]);
