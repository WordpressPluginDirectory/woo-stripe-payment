<?php

namespace PaymentPlugins\Stripe\Packages;

use PaymentPlugins\Stripe\Container\Container;

abstract class AbstractPackage {

	public $id;

	protected $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public abstract function is_active();

	public abstract function register();

	public abstract function initialize();
}