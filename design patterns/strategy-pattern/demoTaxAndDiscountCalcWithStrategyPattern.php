<?php

interface Strategy {
    public function name () : string;
    public function apply (array $order) : bool;
    public function calc (array $order) : bool;
}

class TaxConcreteStrategy implements Strategy {
    public function name () {return 'tax'};
    public function apply () {

    }
}

class TaxConcreteStrategy implements Strategy {
    public function name () {return 'tax'};
    public function apply () {

    }
}

class DiscountConcreteStrategy implements Strategy {

}

class ShippingFeeConcreteStrategy implements Strategy {

}

final class PricingEnginer {
    public function __construct(array $strategys = []) {
        $this->strategys = $strategys;
    }
}

