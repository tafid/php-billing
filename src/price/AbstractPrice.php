<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\price;

use hiqdev\php\billing\action\ActionInterface;
use hiqdev\php\billing\charge\ChargeModifier;
use hiqdev\php\billing\EntityInterface;
use hiqdev\php\billing\plan\PlanInterface;
use hiqdev\php\billing\target\TargetInterface;
use hiqdev\php\billing\type\TypeInterface;
use hiqdev\php\units\QuantityInterface;
use Money\Money;

/**
 * Price.
 * @see PriceInterface
 * By default Price is applicable when same target and same type as Action.
 * But it can be different e.g. same price for all targets when certain type.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
abstract class AbstractPrice implements PriceInterface, EntityInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var TypeInterface
     */
    protected $type;

    /**
     * @var TargetInterface
     */
    protected $target;

    /**
     * @var PlanInterface
     */
    protected $plan;

    public function __construct(
                            $id,
        TypeInterface       $type,
        TargetInterface     $target,
        PlanInterface       $plan = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->target = $target;
        $this->plan = $plan;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlan(): ?PlanInterface
    {
        return $this->plan;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPlan()
    {
        return $this->plan !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlan(PlanInterface $plan)
    {
        if ($this->hasPlan()) {
            throw new \Exception('cannot reassign plan for price');
        }
        $this->plan = $plan;
    }

    /**
     * {@inheritdoc}
     * Default sum calculation method: sum = price * usage.
     */
    public function calculateSum(QuantityInterface $quantity): ?Money
    {
        $usage = $this->calculateUsage($quantity);
        if ($usage === null) {
            return null;
        }

        $price = $this->calculatePrice($quantity);
        if ($price === null) {
            return null;
        }

        /// TODO add configurable rounding mode later
        return $price->multiply($usage->getQuantity(), Money::ROUND_UP);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'target' => $this->target,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(ActionInterface $action): bool
    {
        /* sorry, debugging facility
         * var_dump([
            'action.target'     => $action->getTarget(),
            'this.target'       => $this->getTarget(),
            'action.type'       => $action->getType(),
            'this.type'         => $this->getType(),
            'target matches'    => $action->getTarget()->matches($this->getTarget()),
            'type matches'      => $action->getType()->matches($this->getType()),
        ]);*/
        return $action->getTarget()->matches($this->getTarget()) &&
               $action->getType()->matches($this->getType());
    }

    /** {@inheritdoc} */
    public function calculateCharges(ActionInterface $action): array
    {
        $charge = $action->calculateCharge($this);
        if ($this instanceof ChargeModifier) {
            $charges = $this->modifyCharge($charge, $action);
        } else {
            $charges = $charge ? [$charge] : [];
        }

        if ($action->isFinished()) {
            foreach ($charges as $charge) {
                $charge->setFinished();
            }
        }

        return $charges;
    }
}
