<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Util;

class HashUtils
{
    /**
     * @param int $length
     * @return string
     */
    public function generate($length = 4)
    {
        return bin2hex(file_get_contents('/dev/urandom', null, null, 0, $length / 2));
    }

    /**
     * @param string $known
     * @param string $input
     * @return bool
     */
    public function compare($known, $input)
    {
        $known = (string) $known;
        $input = (string) $input;

        if (function_exists('hash_equals')) {
            return hash_equals($known, $input);
        }

        $known_length = strlen($known);
        $input_length = strlen($input);

        $known .= $input;

        $result = $known_length - $input_length;

        for ($i = 0; $i < $input_length; $i++) {
            $result |= (ord($known[$i]) ^ ord($input[$i]));
        }

        return 0 === $result;
    }
}
