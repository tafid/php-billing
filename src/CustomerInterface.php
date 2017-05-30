<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing;

/**
 * Customer interface.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
interface CustomerInterface extends TargetInterface
{
    /**
     * Returns client login.
     * @return string
     */
    public function getLogin();
}