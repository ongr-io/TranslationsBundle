/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('ongr.translations', [
        'ui.bootstrap',
        'ngTagsInput',
        'controller.list',
        'controller.export',
        'controller.history',
        'directive.inline',
        'service.tag',
        'util.asset'
    ])
    .constant('DATA', translations)
    .constant('LOCALES', locales)
    .constant('STATUS', {
        changed: 'dirty',
        unchanged: 'fresh'
    });
