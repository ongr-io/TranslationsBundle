<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Service\Export;

use Symfony\Component\Yaml\Dumper;

/**
 * Class YmlExport for dumping translations to yml file.
 */
class YmlExport implements ExporterInterface
{
    /**
     * Export translations in to the given file.
     *
     * @param string $file
     * @param array  $translations
     *
     * @return bool
     */
    public function export($file, $translations)
    {
        $bytes = false;

        if (pathinfo($file, PATHINFO_EXTENSION) === 'yml') {
            $ymlDumper = new Dumper();
            $ymlContent = '';
            $ymlContent .= $ymlDumper->dump($translations, 10);
            $bytes = file_put_contents($file, $ymlContent);
        }

        return ($bytes !== false);
    }
}
