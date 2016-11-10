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

use ONGR\TranslationsBundle\Service\LoadersContainer;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Import translations file.
 */
class FileImport
{
    /**
     * @var LoadersContainer
     */
    private $loadersContainer;

    /**
     * @param LoadersContainer $loadersContainer
     */
    public function __construct(LoadersContainer $loadersContainer)
    {
        $this->loadersContainer = $loadersContainer;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return array
     */
    public function import(SplFileInfo $file)
    {
        list($domain, $locale, $extension) = explode('.', $file->getFilename());

        $translations = [];

        if ($this->loadersContainer->has($extension)) {
            /** @var MessageCatalogue $messageCatalogue */
            $messageCatalogue = $this->loadersContainer->get($extension)->load($file, $locale, $domain);
            $domainMessages = $messageCatalogue->all($domain);

            if (!empty($domainMessages)) {
                $path = substr(pathinfo($file->getPathname(), PATHINFO_DIRNAME), strlen(getcwd()) + 1);
                foreach ($domainMessages as $key => $content) {
                    $translations[$domain][$key]['messages'][$locale] = $content;
                    $translations[$domain][$key]['path'] = $path;
                    $translations[$domain][$key]['format'] = $file->getExtension();
                }
            }
        }

        return $translations;
    }
}
