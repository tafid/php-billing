<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */
use Behat\Behat\Context\Context;
use hiqdev\php\billing\action\Action;
use hiqdev\php\billing\charge\Charge;
use hiqdev\php\billing\customer\Customer;
use hiqdev\php\billing\formula\FormulaEngine;
use hiqdev\php\billing\price\SinglePrice;
use hiqdev\php\billing\target\Target;
use hiqdev\php\billing\type\Type;
use hiqdev\php\units\Quantity;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    protected $engine;

    protected $customer;
    protected $price;
    protected $action;
    protected $charges;
    protected $date;

    /**
     * Initializes context.
     */
    public function __construct()
    {
        $this->customer = new Customer(null, 'somebody');
    }

    /**
     * @Given /(\S+) (\S+) price is ([0-9.]+) (\w+) per ([0-9.]+) (\w+)/
     */
    public function priceIs($target, $type, $sum, $currency, $amount, $unit)
    {
        $type = new Type(Type::ANY, $type);
        $target = new Target(Target::ANY, $target);
        $quantity = Quantity::create($unit, $amount);
        $sum = new Money($sum*100, new Currency($currency));
        $this->price = new SinglePrice(null, $type, $target, null, $quantity, $sum);
    }

    /**
     * @Given /action is (\S+) (\w+) ([0-9.]+) (\S+)/
     */
    public function actionIs($target, $type, $amount, $unit)
    {
        $type = new Type(Type::ANY, $type);
        $target = new Target(Target::ANY, $target);
        $quantity = Quantity::create($unit, $amount);
        $time = new DateTimeImmutable();
        $this->action = new Action(null, $type, $target, $quantity, $this->customer, $time);
    }

    /**
     * @Given /formula is (.+)/
     */
    public function formulaIs($formula)
    {
        $this->price->setFormula($this->getFormulaEngine()->build($formula));
    }

    protected function getFormulaEngine()
    {
        if ($this->engine === null) {
            $this->engine = new FormulaEngine();
        }

        return $this->engine;
    }

    /**
     * @When /date is ([0-9.-]+)/
     */
    public function dateIs($date)
    {
        $this->date = $date;
    }

    /**
     * @Then /(\w+) charge is (\S+)? ?([0-9.]+)? ?([A-Z]{3})?/
     */
    public function chargeIs($numeral, $type = null, $sum = null, $currency = null)
    {
        $no = $this->ensureNo($numeral);
        if ($no === 0) {
            $this->charges = $this->price->calculateCharges($this->action);
        }
        //var_dump($this->charges); die;
        $this->assertCharge($type, $sum, $currency, $this->charges[$no]);
    }

    public function assertCharge($type, $sum, $currency, $charge)
    {
        if (empty($type) && empty($sum) && empty($currency)) {
            return;
        }
        //var_dump($charge);die;
        Assert::assertInstanceOf(Charge::class, $charge);
        Assert::assertSame($type, $charge->getPrice()->getType()->getName());
        $money = new Money($sum*100, new Currency($currency));
        //Assert::assertEquals($money, $charge->getSum());
        //var_dump($this->formula);
        //var_dump($this->date);
    }

    protected $numerals = [
        'first'     => 1,
        'second'    => 2,
        'third'     => 3,
        'fourth'    => 4,
        'fifth'     => 5,
    ];

    public function ensureNo($numeral)
    {
        if (empty($this->numerals[$numeral])) {
            throw new Exception("wrong numeral '$numeral'");
        }

        return $this->numerals[$numeral] - 1;
    }
}
