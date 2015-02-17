<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Translation\Import;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Import translations file.
 */
class FileImport
{
    /**
     * @var array
     */
    private $translations = [];

    /**
     * @var array
     */
    private $loaders;

    /**
     * @param array $loaders
     */
    public function __construct(array $loaders = [])
    {
        $this->loaders = $loaders;
    }

    /**
     * Impoort the given file and return the number of inserted translations.
     *
     * @param SplFileInfo $file
     *
     * @return array
     */
    public function import(SplFileInfo $file)
    {
        list($domain, $locale, $extension) = explode('.', $file->getFilename());

        if (isset($this->loaders[$extension])) {
            /** @var MessageCatalogue $messageCatalogue */
            $messageCatalogue = $this->loaders[$extension]->load($file, $locale, $domain);

            foreach ($messageCatalogue->all($domain) as $key => $content) {
                $this->translations[$domain][$locale][$key] = $content;
            }

            return $this->translations;
        }
    }
}
