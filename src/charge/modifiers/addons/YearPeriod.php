<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\charge\modifiers\addons;

use DateTimeImmutable;

/**
 * Year Period addon.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class YearPeriod extends Period
{
    public function countPeriodsPassed(DateTimeImmutable $since, DateTimeImmutable $time): float
    {
        $diff = $time->diff($since);

        return $diff->y / $this->value;
    }
}
