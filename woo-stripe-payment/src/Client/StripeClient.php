<?php

namespace PaymentPlugins\Stripe\Client;

/**
 * Gateway class that abstracts all API calls to Stripe.
 *
 * @since   4.0.0
 * @author  Payment Plugins
 * @package PaymentPlugins\Stripe\Client
 *
 * @property \PaymentPlugins\Vendor\Stripe\Service\AccountLinkService                        $accountLinks
 * @property \PaymentPlugins\Vendor\Stripe\Service\AccountService                            $accounts
 * @property \PaymentPlugins\Vendor\Stripe\Service\AccountSessionService                     $accountSessions
 * @property \PaymentPlugins\Vendor\Stripe\Service\ApplePayDomainService                     $applePayDomains
 * @property \PaymentPlugins\Vendor\Stripe\Service\ApplicationFeeService                     $applicationFees
 * @property \PaymentPlugins\Vendor\Stripe\Service\BalanceService                            $balance
 * @property \PaymentPlugins\Vendor\Stripe\Service\BalanceTransactionService                 $balanceTransactions
 * @property \PaymentPlugins\Vendor\Stripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \PaymentPlugins\Vendor\Stripe\Service\ChargeService                             $charges
 * @property \PaymentPlugins\Vendor\Stripe\Service\Checkout\CheckoutServiceFactory           $checkout
 * @property \PaymentPlugins\Vendor\Stripe\Service\CountrySpecService                        $countrySpecs
 * @property \PaymentPlugins\Vendor\Stripe\Service\CouponService                             $coupons
 * @property \PaymentPlugins\Vendor\Stripe\Service\CreditNoteService                         $creditNotes
 * @property \PaymentPlugins\Vendor\Stripe\Service\CustomerService                           $customers
 * @property \PaymentPlugins\Vendor\Stripe\Service\DisputeService                            $disputes
 * @property \PaymentPlugins\Vendor\Stripe\Service\EphemeralKeyService                       $ephemeralKeys
 * @property \PaymentPlugins\Vendor\Stripe\Service\EventService                              $events
 * @property \PaymentPlugins\Vendor\Stripe\Service\ExchangeRateService                       $exchangeRates
 * @property \PaymentPlugins\Vendor\Stripe\Service\FileLinkService                           $fileLinks
 * @property \PaymentPlugins\Vendor\Stripe\Service\FileService                               $files
 * @property \PaymentPlugins\Vendor\Stripe\Service\InvoiceItemService                        $invoiceItems
 * @property \PaymentPlugins\Vendor\Stripe\Service\InvoiceService                            $invoices
 * @property \PaymentPlugins\Vendor\Stripe\Service\Issuing\IssuingServiceFactory             $issuing
 * @property \PaymentPlugins\Vendor\Stripe\Service\MandateService                            $mandates
 * @property \PaymentPlugins\Vendor\Stripe\Service\OrderReturnService                        $orderReturns
 * @property \PaymentPlugins\Vendor\Stripe\Service\OrderService                              $orders
 * @property \PaymentPlugins\Vendor\Stripe\Service\PaymentIntentService                      $paymentIntents
 * @property \PaymentPlugins\Vendor\Stripe\Service\PaymentMethodService                      $paymentMethods
 * @property \PaymentPlugins\Vendor\Stripe\Service\PaymentMethodDomainService                $paymentMethodDomains
 * @property \PaymentPlugins\Vendor\Stripe\Service\PayoutService                             $payouts
 * @property \PaymentPlugins\Vendor\Stripe\Service\PlanService                               $plans
 * @property \PaymentPlugins\Vendor\Stripe\Service\PriceService                              $prices
 * @property \PaymentPlugins\Vendor\Stripe\Service\ProductService                            $products
 * @property \PaymentPlugins\Vendor\Stripe\Service\Radar\RadarServiceFactory                 $radar
 * @property \PaymentPlugins\Vendor\Stripe\Service\RefundService                             $refunds
 * @property \PaymentPlugins\Vendor\Stripe\Service\Reporting\ReportingServiceFactory         $reporting
 * @property \PaymentPlugins\Vendor\Stripe\Service\ReviewService                             $reviews
 * @property \PaymentPlugins\Vendor\Stripe\Service\SetupIntentService                        $setupIntents
 * @property \PaymentPlugins\Vendor\Stripe\Service\Sigma\SigmaServiceFactory                 $sigma
 * @property \PaymentPlugins\Vendor\Stripe\Service\SkuService                                $skus
 * @property \PaymentPlugins\Vendor\Stripe\Service\SourceService                             $sources
 * @property \PaymentPlugins\Vendor\Stripe\Service\SubscriptionItemService                   $subscriptionItems
 * @property \PaymentPlugins\Vendor\Stripe\Service\SubscriptionScheduleService               $subscriptionSchedules
 * @property \PaymentPlugins\Vendor\Stripe\Service\SubscriptionService                       $subscriptions
 * @property \PaymentPlugins\Vendor\Stripe\Service\TaxRateService                            $taxRates
 * @property \PaymentPlugins\Vendor\Stripe\Service\Terminal\TerminalServiceFactory           $terminal
 * @property \PaymentPlugins\Vendor\Stripe\Service\TokenService                              $tokens
 * @property \PaymentPlugins\Vendor\Stripe\Service\TopupService                              $topups
 * @property \PaymentPlugins\Vendor\Stripe\Service\TransferService                           $transfers
 * @property \PaymentPlugins\Vendor\Stripe\Service\WebhookEndpointService                    $webhookEndpoints
 * @property \PaymentPlugins\Vendor\Stripe\Service\PaymentMethodConfigurationService         $paymentMethodConfigurations
 */
class StripeClient {

	/**
	 * @var \PaymentPlugins\Vendor\Stripe\StripeClient
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
		$this->stripe_client = new \PaymentPlugins\Vendor\Stripe\StripeClient( array_merge( $this->get_client_config(), $config ) );
		\PaymentPlugins\Vendor\Stripe\Stripe::setAppInfo( 'WordPress woo-stripe-payment', \stripe_wc()->version(), 'https://wordpress.org/plugins/woo-stripe-payment/', 'pp_partner_FdPtriN2Q7JLOe' );
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
	 * @param string|\WC_Order|\PaymentPlugins\Vendor\Stripe\ApiResource $mode
	 *
	 * @return $this
	 * @since 4.0.0
	 */
	public function mode( $mode ): self {
		if ( $mode instanceof \WC_Order ) {
			$this->mode = \wc_stripe_order_mode( $mode );
		} elseif ( $mode instanceof \PaymentPlugins\Vendor\Stripe\ApiResource ) {
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