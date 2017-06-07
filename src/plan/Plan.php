<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\plan;

use hiqdev\php\billing\action\ActionInterface;
use hiqdev\php\billing\customer\CustomerInterface;
use hiqdev\php\billing\price\PriceInterface;
use hiqdev\php\billing\target\TargetInterface;

/**
 * Tariff Plan.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Plan implements PlanInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Plan|null
     * XXX not sure to implement
     */
    protected $parent;

    /**
     * @var CustomerInterface
     */
    protected $seller;

    /**
     * @var PriceInterface[]
     */
    protected $prices = [];

    /**
     * @param PriceInterface[] $prices
     */
    public function __construct(
                            $id,
                            $name,
        CustomerInterface   $seller = null,
        array               $prices = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->seller = $seller;
        $this->prices = $prices;
    }

    /**
     * @return PriceInterface[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Calculate charges for given action.
     * @param ActionInterface $action
     * @return Charge[]
     */
    public function calculateCharges(ActionInterface $action)
    {
        $charges = [];
        foreach ($this->prices as $price) {
            $charge = $action->calculateCharge($price);
            if ($charge !== null) {
                $charges[] = $charge;
            }
        }

        return $charges;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
