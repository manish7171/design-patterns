<?php

class Product1
{
  public $parts = [];

  public function listParts(): void
  {
    echo "Product parts: " . implode(', ', $this->parts) . "\n\n";
  }
}

interface Builder
{
  public function productPartA(): void;
  public function productPartB(): void;
  public function productPartC(): void;
}

class ConcreteBuilder1 implements Builder
{
  public function __construct()
  {
    $this->reset();
  }

  public function reset(): void
  {
    $this->product = new Product1();
  }

  /**
     * All production steps work with the same product instance.
     */
  public function productPartA(): void
  {
    $this->product->parts[] = "PartA1";
  }

  public function productPartB(): void
  {
    $this->product->parts[] = "PartB1";
  }

  public function productPartC(): void
  {
    $this->product->parts[] = "PartC1";
  }
  public function getProduct(): Product1
  {
    $result = $this->product;
    $this->reset();

    return $result;
  }
}

class Director
{
  private $builder;

  public function setBuilder(Builder $builder): void
  {
    $this->builder = $builder;

  }

  public function buildMinimalViableProduct(): void
  {
    $this->builder->productPartA();
  }

  public function buildFullFeaturedProduct(): void
  {
    $this->builder->productPartA();
    $this->builder->productPartB();
    $this->builder->productPartC();
  }

}

function clientCode(Director $director):void
{
  $builder = new ConcreteBuilder1();
  $director->setBuilder($builder);

  echo "Standard basic product:\n";
  $director->buildMinimalViableProduct();
  $builder->getProduct()->listParts();

  echo "Standard full featured product:\n";
  $director->buildFullFeaturedProduct();
  $builder->getProduct()->listParts();

  // Remember, the Builder pattern can be used without a Director class.
  echo "Custom product:\n";
  $builder->productPartA();
  $builder->productPartC();
  $builder->getProduct()->listParts();
}

$director = new Director();
clientCode($director);

