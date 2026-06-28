<?php

namespace PaymentPlugins\Stripe\Client;

/**
 * Gateway class that abstracts all API calls to Stripe.
 *
 * @since   4.0.0
 * @author  Payment Plugins
 * @package PaymentPlugins\Stripe\Client
 *
 * @property \Stripe\Service\AccountLinkService                        $accountLinks
 * @property \Stripe\Service\AccountService                            $accounts
 * @property \Stripe\Service\AccountSessionService                     $accountSessions
 * @property \Stripe\Service\ApplePayDomainService                     $applePayDomains
 * @property \Stripe\Service\ApplicationFeeService                     $applicationFees
 * @property \Stripe\Service\BalanceService                            $balance
 * @property \Stripe\Service\BalanceTransactionService                 $balanceTransactions
 * @property \Stripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \Stripe\Service\ChargeService                             $charges
 * @property \Stripe\Service\Checkout\CheckoutServiceFactory           $checkout
 * @property \Stripe\Service\CountrySpecService                        $countrySpecs
 * @property \Stripe\Service\CouponService                             $coupons
 * @property \Stripe\Service\CreditNoteService                         $creditNotes
 * @property \Stripe\Service\CustomerService                           $customers
 * @property \Stripe\Service\DisputeService                            $disputes
 * @property \Stripe\Service\EphemeralKeyService                       $ephemeralKeys
 * @property \Stripe\Service\EventService                              $events
 * @property \Stripe\Service\ExchangeRateService                       $exchangeRates
 * @property \Stripe\Service\FileLinkService                           $fileLinks
 * @property \Stripe\Service\FileService                               $files
 * @property \Stripe\Service\InvoiceItemService                        $invoiceItems
 * @property \Stripe\Service\InvoiceService                            $invoices
 * @property \Stripe\Service\Issuing\IssuingServiceFactory             $issuing
 * @property \Stripe\Service\MandateService                            $mandates
 * @property \Stripe\Service\OrderReturnService                        $orderReturns
 * @property \Stripe\Service\OrderService                              $orders
 * @property \Stripe\Service\PaymentIntentService                      $paymentIntents
 * @property \Stripe\Service\PaymentMethodService                      $paymentMethods
 * @property \Stripe\Service\PaymentMethodDomainService                $paymentMethodDomains
 * @property \Stripe\Service\PayoutService                             $payouts
 * @property \Stripe\Service\PlanService                               $plans
 * @property \Stripe\Service\PriceService                              $prices
 * @property \Stripe\Service\ProductService                            $products
 * @property \Stripe\Service\Radar\RadarServiceFactory                 $radar
 * @property \Stripe\Service\RefundService                             $refunds
 * @property \Stripe\Service\Reporting\ReportingServiceFactory         $reporting
 * @property \Stripe\Service\ReviewService                             $reviews
 * @property \Stripe\Service\SetupIntentService                        $setupIntents
 * @property \Stripe\Service\Sigma\SigmaServiceFactory                 $sigma
 * @property \Stripe\Service\SkuService                                $skus
 * @property \Stripe\Service\SourceService                             $sources
 * @property \Stripe\Service\SubscriptionItemService                   $subscriptionItems
 * @property \Stripe\Service\SubscriptionScheduleService               $subscriptionSchedules
 * @property \Stripe\Service\SubscriptionService                       $subscriptions
 * @property \Stripe\Service\TaxRateService                            $taxRates
 * @property \Stripe\Service\Terminal\TerminalServiceFactory           $terminal
 * @property \Stripe\Service\TokenService                              $tokens
 * @property \Stripe\Service\TopupService                              $topups
 * @property \Stripe\Service\TransferService                           $transfers
 * @property \Stripe\Service\WebhookEndpointService                    $webhookEndpoints
 * @property \Stripe\Service\PaymentMethodConfigurationService         $paymentMethodConfigurations
 */
class StripeClient {

	/**
	 * @var \Stripe\StripeClient
	 */
	private $stripe_client;

	/**
	 * @var string
	 */
	private $secret_key;

	/**
	 * @var string
	 */
	private $mode;

	/**
	 * @param string $mode
	 * @param string $secret_key
	 * @param array  $config
	 */
	public function __construct( string $mode = '', string $secret_key = '', array $config = [] ) {
		$this->mode          = $mode;
		$this->secret_key    = $secret_key;
		$this->stripe_client = new \Stripe\StripeClient( array_merge( $this->get_client_config(), $config ) );
		\Stripe\Stripe::setAppInfo( 'WordPress woo-stripe-payment', \stripe_wc()->version(), 'https://wordpress.org/plugins/woo-stripe-payment/', 'pp_partner_FdPtriN2Q7JLOe' );
	}

	/**
	 * @param string $key
	 *
	 * @return ClientOperation
	 */
	public function __get( string $key ): ClientOperation {
		return new ClientOperation( $this->stripe_client, $key, $this->secret_key, $this->mode );
	}

	public function __isset( string $key ): bool {
		$client_operation = new ClientOperation( $this->stripe_client, $key, $this->secret_key, $this->mode );

		return $client_operation->has_property( $key );
	}

	/**
	 * @param string|\WC_Order|\Stripe\ApiResource $mode
	 *
	 * @return $this
	 * @since 4.0.0
	 */
	public function mode( $mode ): self {
		if ( $mode instanceof \WC_Order ) {
			$this->mode = \wc_stripe_order_mode( $mode );
		} elseif ( $mode instanceof \Stripe\ApiResource ) {
			if ( isset( $mode->livemode ) ) {
				$this->mode = $mode->livemode ? 'live' : 'test';
			}
		} else {
			$this->mode = $mode;
		}

		return $this;
	}

	/**
	 * @param string $mode
	 *
	 * @since 4.0.0
	 */
	public function set_mode( string $mode ): void {
		$this->mode = $mode;
	}

	protected function get_client_config(): array {
		return \apply_filters( 'wc_stripe_client_config_params', [ 'stripe_version' => wc_stripe_get_container()->get( 'API_VERSION' ) ], $this );
	}

	public function is_connected(): bool {
		$key = wc_Stripe_get_secret_key( $this->mode );

		return ! empty( $key );
	}

}