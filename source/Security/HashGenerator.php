<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Security;

final class HashGenerator
{
    /** @var string */
    private $secret;

    /**
     * @param string $secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Generates a hash for given paste id.
     *
     * @param string $paste_id
     *
     * @return string
     */
    public function generateHash($paste_id)
    {
        return hash('sha256', sprintf('%s.%s', $paste_id, $this->secret), false);
    }
}
