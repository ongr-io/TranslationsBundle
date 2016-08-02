<?php

namespace ONGR\TranslationsBundle\Translation\Export;

use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class XlfExport for dumping translations to yml file.
 */
class XlfExport implements ExporterInterface
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
    public function export($file, MessageCatalogue $translations, $domain)
    {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'xlf') {
            $xlfDumper = new XliffFileDumper();
            try {
                $xlfContent = $xlfDumper->formatCatalogue($translations, $domain);
                if ($xlfContent !== false) {
                    return (file_put_contents($file, $xlfContent) !== false);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

}