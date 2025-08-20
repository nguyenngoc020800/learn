<?php

interface Strategy {
    public function name () : string;
    public function apply (array $order) : bool;
    public function calc (array &$ctx, array $order);
}

class BaseCalcConcreteStrategy implements Strategy {
    public function name () : string {return 'baseCalc';}
    public function apply (array $order) : bool {
        return true;
    }
    public function calc(array &$ctx, array $order)
    {
        foreach (($order['items'] ?? []) as $item) {
            $ctx['amount'] += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            $ctx['total'] = $ctx['amount'];
        }
    }

}

class TaxConcreteStrategy implements Strategy {
    public $taxList;
    public $defaultTax;
    public function __construct(array $taxList = [], int $defaultTax = 10)
    {
        $this->taxList = $taxList;
        $this->defaultTax = $defaultTax;
    }
    public function name () : string {return 'tax';}
    public function apply (array $order) : bool {
        return !$order['nonTax'] === true;
    }
    public function calc(array &$ctx, array $order)
    {
        $rate = $this->taxList[$order['country']] ?? $this->defaultTax;
        $amount = (int)($ctx['amount'] ?? 0) * $rate;
        $ctx[$this->name()] = [
            'rate' => $rate,
            'amount' => $amount
        ];
        $ctx['total'] += $amount;
    }

}

class DiscountConcreteStrategy implements Strategy {
    public $discountList;
    public function __construct(array $discountList = [])
    {
        $this->discountList = $discountList;
    }
    public function name () : string {return 'discount';}
    public function apply (array $order) : bool {return ($this->discountList[$order['membership']] ?? 0) !== 0;}
    public function calc(array &$ctx, array $order)
    {
        $rate = $this->discountList[$order['membership']];
        $amount = (int)($ctx['amount'] ?? 0) * $rate;
        $ctx[$this->name()] = [
            'rate' => $rate,
            'amount' => -($amount)
        ];
        $ctx['total'] -= +$amount;
    }
}

class ShippingFeeConcreteStrategy implements Strategy {
    public $shoppingFeeList;
    public $defaultFee;
    public function __construct(array $shoppingFeeList = [], int $defaultFee = 30000)
    {
        $this->shoppingFeeList = $shoppingFeeList;
        $this->defaultFee = $defaultFee;
    }
    public function name () : string {return 'shoppingFee';}
    public function apply (array $order) : bool {return !$order['freeShip'];}
    public function calc(array &$ctx, array $order)
    {
        $amount = $this->shoppingFeeList[$order['shippingType']] ?? $this->defaultFee;
        $ctx[$this->name()] = [
            'type' => $order['shippingType'],
            'amount' => $amount
        ];
        $ctx['total'] += +$amount;
    }
}

final class PricingEnginer {
    public $strategies;
    public function __construct(array $strategies = []) {
        $this->strategies = $strategies;
    }
    public function checkout (array $order): array
    {
        $ctx = [
            'amount' => 0,
            'total' => 0
        ];

        foreach ($this->strategies as $strategy) {
            if ($strategy instanceof Strategy && $strategy->apply($order)) {
                $strategy->calc($ctx, $order);
            }
        }

        return $ctx;
    }
}

$taxList = [
    'VN' => 10,
    'USA' => 7,
    'JA' => 8
];

$discountList = [
    'G' => 10,
    'S' => 5,
    'C' => 3
];

$shippingFeeList = [
    'STD' => 30000,
    'F' => 50000,
    'SF' => 70000
];

$order = [
    "items" => [
        ["name"=> "Laptop", "price"=> 20000000, "qty"=> 1],
        ["name"=> "Mouse", "price"=> 500000, "qty"=> 2]
    ],
    "country"=> "VN",
    "membership"=> "G",
    "shippingType"=> "F"
];

$engine = new PricingEnginer([
    new BaseCalcConcreteStrategy(),
    new TaxConcreteStrategy($taxList),
    new DiscountConcreteStrategy($discountList),
    new ShippingFeeConcreteStrategy($shippingFeeList)
]);

var_dump($engine->checkout($order));


