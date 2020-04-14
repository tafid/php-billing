<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\tools;

use DateTimeImmutable;
use Money\Currency;
use hiqdev\php\units\Quantity;
use hiqdev\php\units\Unit;
use Money\Parser\DecimalMoneyParser;
use Money\Currencies\ISOCurrencies;
use hiqdev\php\billing\Exception\UnknownEntityException;
use hiqdev\php\billing\target\TargetCollection;

/**
 * Generalized entity factory.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Factory
{
    private $entities = [];

    private $factories = [];

    protected $moneyParser;

    public function __construct(array $factories)
    {
        $this->factories = $factories;
        $this->moneyParser = new DecimalMoneyParser(new ISOCurrencies());
    }

    public function getMoney($data)
    {
        return $this->get('money', $data);
    }

    public function getSums($data)
    {
        $res = [];
        foreach ($data as $key => $value) {
            $res[$key] = $value*100;
        }

        return $res;
    }

    public function parseMoney($str)
    {
        [$amount, $currency] = explode(' ', $str);

        return [
            'amount' => $amount,
            'currency' => $currency,
        ];
    }

    public function createMoney($data)
    {
        return $this->moneyParser->parse($data['amount'], $data['currency']);
    }

    public function getCurrency($data)
    {
        return new Currency($data);
    }

    public function getQuantity($data)
    {
        return $this->get('quantity', $data);
    }

    public function parseQuantity($str)
    {
        [$quantity, $unit] = explode(' ', $str);

        return [
            'quantity' => $quantity,
            'unit' => $unit,
        ];
    }

    public function createQuantity($data)
    {
        return Quantity::create($data['unit'], $data['quantity']);
    }

    public function getUnit($data)
    {
        return $this->get('unit', $data);
    }

    public function createUnit($data)
    {
        return Unit::create($data['name']);
    }

    public function getType($data)
    {
        return $this->get('type', $data);
    }

    public function getTime($data)
    {
        return $this->get('time', $data);
    }

    public function createTime($data)
    {
        return new DateTimeImmutable($data['time']);
    }

    public function getTargets($data)
    {
        return $this->get('targets', $data);
    }

    public function createTargets($data)
    {
        $targets = [];
        foreach ($data as $one) {
            $targets[] = $this->getTarget($one);
        }

        return new TargetCollection($targets);
    }

    public function getTarget($data)
    {
        return $this->get('target', $data);
    }

    public function getPlan($data)
    {
        return $this->get('plan', $data);
    }

    public function getSale($data)
    {
        return $this->get('sale', $data);
    }

    public function getCustomer($data)
    {
        return $this->get('customer', $data);
    }

    public function get(string $entity, $data)
    {
        if (is_scalar($data)) {
            $data = $this->parse($entity, $data);
        }

        $keys = $this->extractKeys($entity, $data);

        $res = $this->find($entity, $keys) ?: $this->create($entity, $data);

        foreach ($keys as $key) {
            $this->entities[$entity][$key] = $res;
        }

        return $res;
    }

    public function parse(string $entity, $str)
    {
        $method = $this->getMethod($entity, 'parse');

        return $method ? $this->{$method}($str) : $this->parseByUnique($entity, $str);
    }

    public function parseByUnique(string $entity, $str, $delimiter = ':')
    {
        $keys = $this->getEntityUniqueKeys($entity);
        if (count($keys) === 1) {
            return [reset($keys) => $str];
        }
        $parts = explode($delimiter, $str, count($keys));
        if (count($parts) === count($keys)) {
            $res = array_combine($keys, $parts);
        } else {
            $res = [];
        }
        $res['id'] = $str;

        return $res;
    }

    public function find(string $entity, array $keys)
    {
        foreach ($keys as $key) {
            if (!empty($this->entities[$entity][$key])) {
                return $this->entities[$entity][$key];
            }
        }

        return null;
    }

    public function create(string $entity, $data)
    {
        $method = $this->getMethod($entity, 'create');
        if ($method) {
            return $this->{$method}($data);
        }

        if (empty($this->factories[$entity])) {
            throw new FactoryNotFoundException($entity);
        }

        $factory = $this->factories[$entity];

        return $factory->create($this->createDto($entity, $data));
    }

    public function createDto(string $entity, array $data)
    {
        $class = $this->getDtoClass($entity);
        $dto = new $class();

        foreach ($data as $key => $value) {
            $dto->{$key} = $this->prepareValue($entity, $key, $value);
        }

        return $dto;
    }

    public function getDtoClass(string $entity)
    {
        return $this->getEntityClass($entity) . 'CreationDto';
    }

    public function prepareValue($entity, $key, $value)
    {
        if (is_object($value)) {
            return $value;
        }
        $method = $this->getPrepareMethod($entity, $key);

        return $method ? $this->{$method}($value) : $value;
    }

    private function getMethod(string $entity, string $op)
    {
        $method = $op . ucfirst($entity);

        return method_exists($this, $method) ? $method : null;
    }

    private $prepareMethods = [
        'bill'      => 'getBill',
        'charge'    => 'getCharge',
        'currency'  => 'getCurrency',
        'customer'  => 'getCustomer',
        'plan'      => 'getPlan',
        'prepaid'   => 'getQuantity',
        'price'     => 'getMoney',
        'quantity'  => 'getQuantity',
        'sale'      => 'getSale',
        'seller'    => 'getCustomer',
        'sum'       => 'getMoney',
        'sums'      => 'getSums',
        'target'    => 'getTarget',
        'targets'   => 'getTargets',
        'time'      => 'getTime',
        'type'      => 'getType',
        'unit'      => 'getUnit',
    ];

    private function getPrepareMethod(string $entity, string $key)
    {
        if ($entity === 'target' && $key === 'type') {
            return null;
        }
        return $this->prepareMethods[$key] ?? null;
    }

    public function getEntityClass(string $entity)
    {
        $parts = explode('\\', __NAMESPACE__);
        array_pop($parts);
        $parts[] = $entity;
        $parts[] = ucfirst($entity);

        return implode('\\', $parts);
    }

    public function extractKeys(string $entity, $data)
    {
        $id = $data['id'] ?? null;
        $unique = $this->extractUnique($entity, $data);

        return array_filter(['id' => $id, 'unique' => $unique]);
    }

    public function extractUnique(string $entity, $data)
    {
        $keys = $this->getEntityUniqueKeys($entity);
        if (empty($keys)) {
            return null;
        }

        $values = [];
        foreach ($keys as $key) {
            if (empty($data[$key])) {
                return null;
            }
            $values[$key] = $data[$key];
        }

        return implode(' ', $values);
    }


    private $uniqueKeys = [
        'action'    => [],
        'bill'      => [],
        'customer'  => ['login'],
        'money'     => ['amount', 'currency'],
        'plan'      => ['name', 'seller'],
        'price'     => [],
        'quantity'  => ['quantity', 'unit'],
        'sale'      => [],
        'targets'   => [],
        'target'    => ['type', 'name'],
        'time'      => ['time'],
        'type'      => ['name'],
        'unit'      => ['name'],
    ];

    public function getEntityUniqueKeys(string $entity): array
    {
        $keys = $this->uniqueKeys[$entity] ?? null;

        if (is_null($keys)) {
            throw new UnknownEntityException($entity);
        }

        return $keys;
    }
}
