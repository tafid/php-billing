<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\sale;

use DateTime;
use hiqdev\php\billing\customer\CustomerInterface;
use hiqdev\php\billing\plan\PlanInterface;
use hiqdev\php\billing\target\TargetInterface;

/**
 * Sale.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Sale implements SaleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var TargetInterface
     */
    protected $target;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var PlanInterface
     */
    protected $plan;

    /**
     * @var DateTime
     */
    protected $time;

    public function __construct(
                            $id,
        TargetInterface     $target,
        CustomerInterface   $customer,
        PlanInterface       $plan,
        DateTime            $time
    ) {
        $this->id = $id;
        $this->target = $target;
        $this->customer = $customer;
        $this->plan = $plan;
        $this->time = $time;
    }
}