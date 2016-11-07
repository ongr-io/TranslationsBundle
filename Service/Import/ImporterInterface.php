<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Service\Import;

use Symfony\Component\Finder\SplFileInfo;

/**
 * This interface represents file data importer.
 */
interface ImporterInterface
{
    /**
     * Impoort the given file and return the number of inserted translations.
     *
     * @param SplFileInfo $file
     *
     * @return array
     */
    public function import(SplFileInfo $file);
}
