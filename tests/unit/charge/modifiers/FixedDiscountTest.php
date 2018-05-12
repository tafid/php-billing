<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\tests\unit\charge\FixedDiscountTest;

use hiqdev\php\billing\charge\modifiers\FixedDiscount;
use Money\Money;

/**
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class FixedDiscountTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testCreateAbsolute()
    {
        $dis = new FixedDiscount(Money::USD(10));
        $this->assertTrue($dis->isAbsolute());
        $this->assertFalse($dis->isRelative());
    }

    public function testCreateRelative()
    {
        $dis = new FixedDiscount(2);
        $this->assertTrue($dis->isRelative());
        $this->assertFalse($dis->isAbsolute());
    }
}
