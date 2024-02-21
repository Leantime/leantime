<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode;

use MyCLabs\Enum\Enum;

/**
 * @method static LabelAlignment LEFT()
 * @method static LabelAlignment CENTER()
 * @method static LabelAlignment RIGHT()
 *
 * @extends Enum<string>
 * @psalm-immutable
 */
class LabelAlignment extends Enum
{
    const LEFT = 'left';
    const CENTER = 'center';
    const RIGHT = 'right';
}
