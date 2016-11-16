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

use ONGR\TranslationsBundle\Document\Message;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Import translations file.
 */
class FileImport
{
    /**
     * @var ParameterBag
     */
    private $loadersContainer;

    /**
     * @param ParameterBag $loadersContainer
     */
    public function __construct(ParameterBag $loadersContainer)
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
                $date = (new \DateTime())->format(\DateTime::ISO8601);

                foreach ($domainMessages as $key => $content) {
                    $id = sha1($domain.$key);
                    $message = [
                        'locale' => $locale,
                        'message' => $content,
                        'status' => Message::FRESH,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ];
                    $translations[$id]['_id'] = $id;
                    $translations[$id]['key'] = $key;
                    $translations[$id]['domain'] = $domain;
                    $translations[$id]['path'] = $path;
                    $translations[$id]['format'] = $file->getExtension();
                    $translations[$id]['messages'][$locale] = $message;
                }
            }
        }

        return $translations;
    }
}
