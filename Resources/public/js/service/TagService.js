/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('service.tag', [])
    .service('tag', ['$http', function($http) {
        
        /**
         * Sends https request to remove tag.
         * 
         * @param string id  Translation document id.
         * @param Object tag Tag object.
         */
        this.remove = function(id, tag) {
            $http.post(
                Routing.generate('ongr_translations_api_delete'),
                {
                    id: id,
                    name: 'tags',
                    properties: {
                        name: tag.text
                    }
                }
            );
        }

        /**
         * Sends http request to add tag.
         * 
         * @param string id  Translation document id.
         * @param Object tag Tag object
         */
        this.add = function(id, tag) {
            $http.post(
                Routing.generate('ongr_translations_api_add'),
                {
                    id: id,
                    name: 'tags',
                    properties: {
                        name: tag.text
                    }
                }
            );
        }
    }]);
