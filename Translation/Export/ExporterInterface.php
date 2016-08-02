<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Translation\Export;

use Symfony\Component\Translation\MessageCatalogue;

/**
 * This interface should be implemented by file exporters.
 */
interface ExporterInterface
{
    /**
     * Export translations in to the given file.
     *
     * @param string           $file
     * @param MessageCatalogue $translations
     * @param string           $domain
     *
     * @return bool
     */
    public function export($file, MessageCatalogue $translations, $domain);
}
