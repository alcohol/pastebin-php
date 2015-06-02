<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

class Bundle extends BaseBundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new Extension();
    }
}
