<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\TranslationsBundle\Translation;

use Symfony\Component\Translation\Interval;
use Symfony\Component\Translation\PluralizationRules;

/**
 * Class used to check if plural translation is valid.
 */
class TranslationChecker
{
    /**
     * Checks if translation is valid.
     *
     * @param string $message Message to check.
     * @param string $locale  Locale used for.
     *
     * @return bool
     */
    public static function check($message, $locale)
    {
        $parts = explode('|', $message);
        $explicitRules = [];
        $standardRules = [];
        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match(
                '/^(?P<interval>' . Interval::getIntervalRegexp() . ')\s*(?P<message> . *?)$/x',
                $part,
                $matches
            )) {
                $explicitRules[$matches['interval']] = $matches['message'];
            } elseif (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } else {
                $standardRules[] = $part;
            }
        }

        if (count($parts) !== 1 && count($parts) !== count($explicitRules)) {
            for ($count = 0; $count < 200; $count++) {
                $position = PluralizationRules::get($count, $locale);

                if (!isset($standardRules[$position])) {
                    return false;
                }
            }
        }

        return true;
    }
}
