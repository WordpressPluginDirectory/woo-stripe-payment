<?php

// Functions and constants

namespace {

}


namespace PaymentPlugins\Vendor {

    use BrianHenryIE\Strauss\Types\AutoloadAliasInterface;

    /**
     * @see AutoloadAliasInterface
     *
     * @phpstan-type ClassAliasArray array{'type':'class',isabstract:bool,classname:string,namespace?:string,extends:string,implements:array<string>}
     * @phpstan-type InterfaceAliasArray array{'type':'interface',interfacename:string,namespace?:string,extends:array<string>}
     * @phpstan-type TraitAliasArray array{'type':'trait',traitname:string,namespace?:string,use:array<string>}
     * @phpstan-type AutoloadAliasArray array<string,ClassAliasArray|InterfaceAliasArray|TraitAliasArray>
     */
    class AliasAutoloader
    {
        private string $includeFilePath;

        /**
         * @var AutoloadAliasArray
         */
        private array $autoloadAliases = array (
  'Stripe\\Account' => 
  array (
    'type' => 'class',
    'classname' => 'Account',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Account',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\AccountLink' => 
  array (
    'type' => 'class',
    'classname' => 'AccountLink',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\AccountLink',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\AccountSession' => 
  array (
    'type' => 'class',
    'classname' => 'AccountSession',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\AccountSession',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApiRequestor' => 
  array (
    'type' => 'class',
    'classname' => 'ApiRequestor',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ApiRequestor',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApiResource' => 
  array (
    'type' => 'class',
    'classname' => 'ApiResource',
    'isabstract' => true,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ApiResource',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApiResponse' => 
  array (
    'type' => 'class',
    'classname' => 'ApiResponse',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ApiResponse',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApplePayDomain' => 
  array (
    'type' => 'class',
    'classname' => 'ApplePayDomain',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ApplePayDomain',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Application' => 
  array (
    'type' => 'class',
    'classname' => 'Application',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Application',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApplicationFee' => 
  array (
    'type' => 'class',
    'classname' => 'ApplicationFee',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ApplicationFee',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApplicationFeeRefund' => 
  array (
    'type' => 'class',
    'classname' => 'ApplicationFeeRefund',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ApplicationFeeRefund',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Apps\\Secret' => 
  array (
    'type' => 'class',
    'classname' => 'Secret',
    'isabstract' => false,
    'namespace' => 'Stripe\\Apps',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Apps\\Secret',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Balance' => 
  array (
    'type' => 'class',
    'classname' => 'Balance',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Balance',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\BalanceSettings' => 
  array (
    'type' => 'class',
    'classname' => 'BalanceSettings',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\BalanceSettings',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\BalanceTransaction' => 
  array (
    'type' => 'class',
    'classname' => 'BalanceTransaction',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\BalanceTransaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\BankAccount' => 
  array (
    'type' => 'class',
    'classname' => 'BankAccount',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\BankAccount',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\BaseStripeClient' => 
  array (
    'type' => 'class',
    'classname' => 'BaseStripeClient',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\BaseStripeClient',
    'implements' => 
    array (
      0 => 'Stripe\\StripeClientInterface',
      1 => 'Stripe\\StripeStreamingClientInterface',
    ),
  ),
  'Stripe\\Billing\\Alert' => 
  array (
    'type' => 'class',
    'classname' => 'Alert',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\Alert',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\AlertTriggered' => 
  array (
    'type' => 'class',
    'classname' => 'AlertTriggered',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\AlertTriggered',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\CreditBalanceSummary' => 
  array (
    'type' => 'class',
    'classname' => 'CreditBalanceSummary',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\CreditBalanceSummary',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\CreditBalanceTransaction' => 
  array (
    'type' => 'class',
    'classname' => 'CreditBalanceTransaction',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\CreditBalanceTransaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\CreditGrant' => 
  array (
    'type' => 'class',
    'classname' => 'CreditGrant',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\CreditGrant',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\Meter' => 
  array (
    'type' => 'class',
    'classname' => 'Meter',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\Meter',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\MeterEvent' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\MeterEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\MeterEventAdjustment' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventAdjustment',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\MeterEventAdjustment',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Billing\\MeterEventSummary' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventSummary',
    'isabstract' => false,
    'namespace' => 'Stripe\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Billing\\MeterEventSummary',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\BillingPortal\\Configuration' => 
  array (
    'type' => 'class',
    'classname' => 'Configuration',
    'isabstract' => false,
    'namespace' => 'Stripe\\BillingPortal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\BillingPortal\\Configuration',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\BillingPortal\\Session' => 
  array (
    'type' => 'class',
    'classname' => 'Session',
    'isabstract' => false,
    'namespace' => 'Stripe\\BillingPortal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\BillingPortal\\Session',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Capability' => 
  array (
    'type' => 'class',
    'classname' => 'Capability',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Capability',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Card' => 
  array (
    'type' => 'class',
    'classname' => 'Card',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Card',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CashBalance' => 
  array (
    'type' => 'class',
    'classname' => 'CashBalance',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CashBalance',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Charge' => 
  array (
    'type' => 'class',
    'classname' => 'Charge',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Charge',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Checkout\\Session' => 
  array (
    'type' => 'class',
    'classname' => 'Session',
    'isabstract' => false,
    'namespace' => 'Stripe\\Checkout',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Checkout\\Session',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Climate\\Order' => 
  array (
    'type' => 'class',
    'classname' => 'Order',
    'isabstract' => false,
    'namespace' => 'Stripe\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Climate\\Order',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Climate\\Product' => 
  array (
    'type' => 'class',
    'classname' => 'Product',
    'isabstract' => false,
    'namespace' => 'Stripe\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Climate\\Product',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Climate\\Supplier' => 
  array (
    'type' => 'class',
    'classname' => 'Supplier',
    'isabstract' => false,
    'namespace' => 'Stripe\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Climate\\Supplier',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Collection' => 
  array (
    'type' => 'class',
    'classname' => 'Collection',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Collection',
    'implements' => 
    array (
      0 => 'Countable',
      1 => 'IteratorAggregate',
    ),
  ),
  'Stripe\\ConfirmationToken' => 
  array (
    'type' => 'class',
    'classname' => 'ConfirmationToken',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ConfirmationToken',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ConnectCollectionTransfer' => 
  array (
    'type' => 'class',
    'classname' => 'ConnectCollectionTransfer',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ConnectCollectionTransfer',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CountrySpec' => 
  array (
    'type' => 'class',
    'classname' => 'CountrySpec',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CountrySpec',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Coupon' => 
  array (
    'type' => 'class',
    'classname' => 'Coupon',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Coupon',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CreditNote' => 
  array (
    'type' => 'class',
    'classname' => 'CreditNote',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CreditNote',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CreditNoteLineItem' => 
  array (
    'type' => 'class',
    'classname' => 'CreditNoteLineItem',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CreditNoteLineItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Customer' => 
  array (
    'type' => 'class',
    'classname' => 'Customer',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Customer',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CustomerBalanceTransaction' => 
  array (
    'type' => 'class',
    'classname' => 'CustomerBalanceTransaction',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CustomerBalanceTransaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CustomerCashBalanceTransaction' => 
  array (
    'type' => 'class',
    'classname' => 'CustomerCashBalanceTransaction',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CustomerCashBalanceTransaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\CustomerSession' => 
  array (
    'type' => 'class',
    'classname' => 'CustomerSession',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\CustomerSession',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Discount' => 
  array (
    'type' => 'class',
    'classname' => 'Discount',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Discount',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Dispute' => 
  array (
    'type' => 'class',
    'classname' => 'Dispute',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Dispute',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Entitlements\\ActiveEntitlement' => 
  array (
    'type' => 'class',
    'classname' => 'ActiveEntitlement',
    'isabstract' => false,
    'namespace' => 'Stripe\\Entitlements',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Entitlements\\ActiveEntitlement',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Entitlements\\ActiveEntitlementSummary' => 
  array (
    'type' => 'class',
    'classname' => 'ActiveEntitlementSummary',
    'isabstract' => false,
    'namespace' => 'Stripe\\Entitlements',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Entitlements\\ActiveEntitlementSummary',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Entitlements\\Feature' => 
  array (
    'type' => 'class',
    'classname' => 'Feature',
    'isabstract' => false,
    'namespace' => 'Stripe\\Entitlements',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Entitlements\\Feature',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EphemeralKey' => 
  array (
    'type' => 'class',
    'classname' => 'EphemeralKey',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EphemeralKey',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ErrorObject' => 
  array (
    'type' => 'class',
    'classname' => 'ErrorObject',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ErrorObject',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Event' => 
  array (
    'type' => 'class',
    'classname' => 'Event',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Event',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V1BillingMeterErrorReportTriggeredEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V1BillingMeterErrorReportTriggeredEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V1BillingMeterErrorReportTriggeredEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V1BillingMeterNoMeterFoundEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V1BillingMeterNoMeterFoundEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V1BillingMeterNoMeterFoundEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountLinkReturnedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountLinkReturnedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountLinkReturnedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountPersonCreatedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonCreatedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountPersonCreatedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountPersonDeletedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonDeletedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountPersonDeletedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\EventData\\V2CoreAccountPersonUpdatedEventData' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonUpdatedEventData',
    'isabstract' => false,
    'namespace' => 'Stripe\\EventData',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\EventData\\V2CoreAccountPersonUpdatedEventData',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\UnknownEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'UnknownEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\UnknownEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V1BillingMeterErrorReportTriggeredEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V1BillingMeterErrorReportTriggeredEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V1BillingMeterErrorReportTriggeredEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V1BillingMeterErrorReportTriggeredEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V1BillingMeterErrorReportTriggeredEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V1BillingMeterErrorReportTriggeredEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V1BillingMeterNoMeterFoundEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V1BillingMeterNoMeterFoundEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V1BillingMeterNoMeterFoundEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V1BillingMeterNoMeterFoundEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V1BillingMeterNoMeterFoundEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V1BillingMeterNoMeterFoundEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountClosedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountClosedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountClosedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountClosedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountClosedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountClosedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountCreatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountCreatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountCreatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountCreatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountCreatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountCreatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationCustomerUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationMerchantUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationRecipientUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingDefaultsUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingDefaultsUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingDefaultsUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingDefaultsUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingDefaultsUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingDefaultsUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingFutureRequirementsUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingFutureRequirementsUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingFutureRequirementsUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingIdentityUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingIdentityUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingIdentityUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingIdentityUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingIdentityUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingIdentityUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingRequirementsUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingRequirementsUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingRequirementsUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountIncludingRequirementsUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountIncludingRequirementsUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountIncludingRequirementsUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountLinkReturnedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountLinkReturnedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountLinkReturnedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountLinkReturnedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountLinkReturnedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountLinkReturnedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountPersonCreatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonCreatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountPersonCreatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountPersonCreatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonCreatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountPersonCreatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountPersonDeletedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonDeletedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountPersonDeletedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountPersonDeletedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonDeletedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountPersonDeletedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountPersonUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountPersonUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountPersonUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountPersonUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountPersonUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountUpdatedEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountUpdatedEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountUpdatedEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreAccountUpdatedEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreAccountUpdatedEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreAccountUpdatedEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreEventDestinationPingEvent' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreEventDestinationPingEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreEventDestinationPingEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Events\\V2CoreEventDestinationPingEventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'V2CoreEventDestinationPingEventNotification',
    'isabstract' => false,
    'namespace' => 'Stripe\\Events',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Events\\V2CoreEventDestinationPingEventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\ApiConnectionException' => 
  array (
    'type' => 'class',
    'classname' => 'ApiConnectionException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\ApiConnectionException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\ApiErrorException' => 
  array (
    'type' => 'class',
    'classname' => 'ApiErrorException',
    'isabstract' => true,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\ApiErrorException',
    'implements' => 
    array (
      0 => 'Stripe\\Exception\\ExceptionInterface',
    ),
  ),
  'Stripe\\Exception\\AuthenticationException' => 
  array (
    'type' => 'class',
    'classname' => 'AuthenticationException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\AuthenticationException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\BadMethodCallException' => 
  array (
    'type' => 'class',
    'classname' => 'BadMethodCallException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\BadMethodCallException',
    'implements' => 
    array (
      0 => 'Stripe\\Exception\\ExceptionInterface',
    ),
  ),
  'Stripe\\Exception\\CardException' => 
  array (
    'type' => 'class',
    'classname' => 'CardException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\CardException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\IdempotencyException' => 
  array (
    'type' => 'class',
    'classname' => 'IdempotencyException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\IdempotencyException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\InvalidArgumentException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidArgumentException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\InvalidArgumentException',
    'implements' => 
    array (
      0 => 'Stripe\\Exception\\ExceptionInterface',
    ),
  ),
  'Stripe\\Exception\\InvalidRequestException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidRequestException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\InvalidRequestException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\InvalidClientException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidClientException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\InvalidClientException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\InvalidGrantException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidGrantException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\InvalidGrantException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\InvalidRequestException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidRequestException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\InvalidRequestException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\InvalidScopeException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidScopeException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\InvalidScopeException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\OAuthErrorException' => 
  array (
    'type' => 'class',
    'classname' => 'OAuthErrorException',
    'isabstract' => true,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\OAuthErrorException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\UnknownOAuthErrorException' => 
  array (
    'type' => 'class',
    'classname' => 'UnknownOAuthErrorException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\UnknownOAuthErrorException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\UnsupportedGrantTypeException' => 
  array (
    'type' => 'class',
    'classname' => 'UnsupportedGrantTypeException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\UnsupportedGrantTypeException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\OAuth\\UnsupportedResponseTypeException' => 
  array (
    'type' => 'class',
    'classname' => 'UnsupportedResponseTypeException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\UnsupportedResponseTypeException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\PermissionException' => 
  array (
    'type' => 'class',
    'classname' => 'PermissionException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\PermissionException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\RateLimitException' => 
  array (
    'type' => 'class',
    'classname' => 'RateLimitException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\RateLimitException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\SignatureVerificationException' => 
  array (
    'type' => 'class',
    'classname' => 'SignatureVerificationException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\SignatureVerificationException',
    'implements' => 
    array (
      0 => 'Stripe\\Exception\\ExceptionInterface',
    ),
  ),
  'Stripe\\Exception\\TemporarySessionExpiredException' => 
  array (
    'type' => 'class',
    'classname' => 'TemporarySessionExpiredException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\TemporarySessionExpiredException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Exception\\UnexpectedValueException' => 
  array (
    'type' => 'class',
    'classname' => 'UnexpectedValueException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\UnexpectedValueException',
    'implements' => 
    array (
      0 => 'Stripe\\Exception\\ExceptionInterface',
    ),
  ),
  'Stripe\\Exception\\UnknownApiErrorException' => 
  array (
    'type' => 'class',
    'classname' => 'UnknownApiErrorException',
    'isabstract' => false,
    'namespace' => 'Stripe\\Exception',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\UnknownApiErrorException',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ExchangeRate' => 
  array (
    'type' => 'class',
    'classname' => 'ExchangeRate',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ExchangeRate',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\File',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FileLink' => 
  array (
    'type' => 'class',
    'classname' => 'FileLink',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FileLink',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FinancialConnections\\Account' => 
  array (
    'type' => 'class',
    'classname' => 'Account',
    'isabstract' => false,
    'namespace' => 'Stripe\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FinancialConnections\\Account',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FinancialConnections\\AccountOwner' => 
  array (
    'type' => 'class',
    'classname' => 'AccountOwner',
    'isabstract' => false,
    'namespace' => 'Stripe\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FinancialConnections\\AccountOwner',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FinancialConnections\\AccountOwnership' => 
  array (
    'type' => 'class',
    'classname' => 'AccountOwnership',
    'isabstract' => false,
    'namespace' => 'Stripe\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FinancialConnections\\AccountOwnership',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FinancialConnections\\Session' => 
  array (
    'type' => 'class',
    'classname' => 'Session',
    'isabstract' => false,
    'namespace' => 'Stripe\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FinancialConnections\\Session',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FinancialConnections\\Transaction' => 
  array (
    'type' => 'class',
    'classname' => 'Transaction',
    'isabstract' => false,
    'namespace' => 'Stripe\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FinancialConnections\\Transaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Forwarding\\Request' => 
  array (
    'type' => 'class',
    'classname' => 'Request',
    'isabstract' => false,
    'namespace' => 'Stripe\\Forwarding',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Forwarding\\Request',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\FundingInstructions' => 
  array (
    'type' => 'class',
    'classname' => 'FundingInstructions',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\FundingInstructions',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\HttpClient\\CurlClient' => 
  array (
    'type' => 'class',
    'classname' => 'CurlClient',
    'isabstract' => false,
    'namespace' => 'Stripe\\HttpClient',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\HttpClient\\CurlClient',
    'implements' => 
    array (
      0 => 'Stripe\\HttpClient\\ClientInterface',
      1 => 'Stripe\\HttpClient\\StreamingClientInterface',
    ),
  ),
  'Stripe\\Identity\\VerificationReport' => 
  array (
    'type' => 'class',
    'classname' => 'VerificationReport',
    'isabstract' => false,
    'namespace' => 'Stripe\\Identity',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Identity\\VerificationReport',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Identity\\VerificationSession' => 
  array (
    'type' => 'class',
    'classname' => 'VerificationSession',
    'isabstract' => false,
    'namespace' => 'Stripe\\Identity',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Identity\\VerificationSession',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Invoice' => 
  array (
    'type' => 'class',
    'classname' => 'Invoice',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Invoice',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\InvoiceItem' => 
  array (
    'type' => 'class',
    'classname' => 'InvoiceItem',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\InvoiceItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\InvoiceLineItem' => 
  array (
    'type' => 'class',
    'classname' => 'InvoiceLineItem',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\InvoiceLineItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\InvoicePayment' => 
  array (
    'type' => 'class',
    'classname' => 'InvoicePayment',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\InvoicePayment',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\InvoiceRenderingTemplate' => 
  array (
    'type' => 'class',
    'classname' => 'InvoiceRenderingTemplate',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\InvoiceRenderingTemplate',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\Authorization' => 
  array (
    'type' => 'class',
    'classname' => 'Authorization',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\Authorization',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\Card' => 
  array (
    'type' => 'class',
    'classname' => 'Card',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\Card',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\CardDetails' => 
  array (
    'type' => 'class',
    'classname' => 'CardDetails',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\CardDetails',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\Cardholder' => 
  array (
    'type' => 'class',
    'classname' => 'Cardholder',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\Cardholder',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\Dispute' => 
  array (
    'type' => 'class',
    'classname' => 'Dispute',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\Dispute',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\PersonalizationDesign' => 
  array (
    'type' => 'class',
    'classname' => 'PersonalizationDesign',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\PersonalizationDesign',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\PhysicalBundle' => 
  array (
    'type' => 'class',
    'classname' => 'PhysicalBundle',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\PhysicalBundle',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\Token' => 
  array (
    'type' => 'class',
    'classname' => 'Token',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\Token',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Issuing\\Transaction' => 
  array (
    'type' => 'class',
    'classname' => 'Transaction',
    'isabstract' => false,
    'namespace' => 'Stripe\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Issuing\\Transaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\LineItem' => 
  array (
    'type' => 'class',
    'classname' => 'LineItem',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\LineItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\LoginLink' => 
  array (
    'type' => 'class',
    'classname' => 'LoginLink',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\LoginLink',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Mandate' => 
  array (
    'type' => 'class',
    'classname' => 'Mandate',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Mandate',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\OAuth' => 
  array (
    'type' => 'class',
    'classname' => 'OAuth',
    'isabstract' => true,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\OAuth',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\OAuthErrorObject' => 
  array (
    'type' => 'class',
    'classname' => 'OAuthErrorObject',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\OAuthErrorObject',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentAttemptRecord' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentAttemptRecord',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentAttemptRecord',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentIntent' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentIntent',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentIntent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentIntentAmountDetailsLineItem' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentIntentAmountDetailsLineItem',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentIntentAmountDetailsLineItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentLink' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentLink',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentLink',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentMethod' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentMethod',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentMethod',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentMethodConfiguration' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentMethodConfiguration',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentMethodConfiguration',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentMethodDomain' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentMethodDomain',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentMethodDomain',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PaymentRecord' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentRecord',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PaymentRecord',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Payout' => 
  array (
    'type' => 'class',
    'classname' => 'Payout',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Payout',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Person' => 
  array (
    'type' => 'class',
    'classname' => 'Person',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Person',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Plan' => 
  array (
    'type' => 'class',
    'classname' => 'Plan',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Plan',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Price' => 
  array (
    'type' => 'class',
    'classname' => 'Price',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Price',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Product' => 
  array (
    'type' => 'class',
    'classname' => 'Product',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Product',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ProductFeature' => 
  array (
    'type' => 'class',
    'classname' => 'ProductFeature',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ProductFeature',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\PromotionCode' => 
  array (
    'type' => 'class',
    'classname' => 'PromotionCode',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\PromotionCode',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Quote' => 
  array (
    'type' => 'class',
    'classname' => 'Quote',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Quote',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Radar\\EarlyFraudWarning' => 
  array (
    'type' => 'class',
    'classname' => 'EarlyFraudWarning',
    'isabstract' => false,
    'namespace' => 'Stripe\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Radar\\EarlyFraudWarning',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Radar\\PaymentEvaluation' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentEvaluation',
    'isabstract' => false,
    'namespace' => 'Stripe\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Radar\\PaymentEvaluation',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Radar\\ValueList' => 
  array (
    'type' => 'class',
    'classname' => 'ValueList',
    'isabstract' => false,
    'namespace' => 'Stripe\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Radar\\ValueList',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Radar\\ValueListItem' => 
  array (
    'type' => 'class',
    'classname' => 'ValueListItem',
    'isabstract' => false,
    'namespace' => 'Stripe\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Radar\\ValueListItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ReasonRequest' => 
  array (
    'type' => 'class',
    'classname' => 'ReasonRequest',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ReasonRequest',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Reason' => 
  array (
    'type' => 'class',
    'classname' => 'Reason',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Reason',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\RecipientTransfer' => 
  array (
    'type' => 'class',
    'classname' => 'RecipientTransfer',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\RecipientTransfer',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Refund' => 
  array (
    'type' => 'class',
    'classname' => 'Refund',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Refund',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\RelatedObject' => 
  array (
    'type' => 'class',
    'classname' => 'RelatedObject',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\RelatedObject',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Reporting\\ReportRun' => 
  array (
    'type' => 'class',
    'classname' => 'ReportRun',
    'isabstract' => false,
    'namespace' => 'Stripe\\Reporting',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Reporting\\ReportRun',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Reporting\\ReportType' => 
  array (
    'type' => 'class',
    'classname' => 'ReportType',
    'isabstract' => false,
    'namespace' => 'Stripe\\Reporting',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Reporting\\ReportType',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\RequestTelemetry' => 
  array (
    'type' => 'class',
    'classname' => 'RequestTelemetry',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\RequestTelemetry',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Reserve\\Hold' => 
  array (
    'type' => 'class',
    'classname' => 'Hold',
    'isabstract' => false,
    'namespace' => 'Stripe\\Reserve',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Reserve\\Hold',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Reserve\\Plan' => 
  array (
    'type' => 'class',
    'classname' => 'Plan',
    'isabstract' => false,
    'namespace' => 'Stripe\\Reserve',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Reserve\\Plan',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Reserve\\Release' => 
  array (
    'type' => 'class',
    'classname' => 'Release',
    'isabstract' => false,
    'namespace' => 'Stripe\\Reserve',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Reserve\\Release',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ReserveTransaction' => 
  array (
    'type' => 'class',
    'classname' => 'ReserveTransaction',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ReserveTransaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Review' => 
  array (
    'type' => 'class',
    'classname' => 'Review',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Review',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SearchResult' => 
  array (
    'type' => 'class',
    'classname' => 'SearchResult',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SearchResult',
    'implements' => 
    array (
      0 => 'Countable',
      1 => 'IteratorAggregate',
    ),
  ),
  'Stripe\\Service\\AbstractService' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractService',
    'isabstract' => true,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\AbstractService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\AbstractServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractServiceFactory',
    'isabstract' => true,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\AbstractServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\AccountLinkService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountLinkService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\AccountLinkService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\AccountService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\AccountService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\AccountSessionService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountSessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\AccountSessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ApplePayDomainService' => 
  array (
    'type' => 'class',
    'classname' => 'ApplePayDomainService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ApplePayDomainService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ApplicationFeeService' => 
  array (
    'type' => 'class',
    'classname' => 'ApplicationFeeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ApplicationFeeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Apps\\AppsServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'AppsServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Apps',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Apps\\AppsServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Apps\\SecretService' => 
  array (
    'type' => 'class',
    'classname' => 'SecretService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Apps',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Apps\\SecretService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\BalanceService' => 
  array (
    'type' => 'class',
    'classname' => 'BalanceService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\BalanceService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\BalanceSettingsService' => 
  array (
    'type' => 'class',
    'classname' => 'BalanceSettingsService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\BalanceSettingsService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\BalanceTransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'BalanceTransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\BalanceTransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\AlertService' => 
  array (
    'type' => 'class',
    'classname' => 'AlertService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\AlertService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\BillingServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'BillingServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\BillingServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\CreditBalanceSummaryService' => 
  array (
    'type' => 'class',
    'classname' => 'CreditBalanceSummaryService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\CreditBalanceSummaryService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\CreditBalanceTransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'CreditBalanceTransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\CreditBalanceTransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\CreditGrantService' => 
  array (
    'type' => 'class',
    'classname' => 'CreditGrantService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\CreditGrantService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\MeterEventAdjustmentService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventAdjustmentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\MeterEventAdjustmentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\MeterEventService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\MeterEventService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Billing\\MeterService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Billing\\MeterService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\BillingPortal\\BillingPortalServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'BillingPortalServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\BillingPortal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\BillingPortal\\BillingPortalServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\BillingPortal\\ConfigurationService' => 
  array (
    'type' => 'class',
    'classname' => 'ConfigurationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\BillingPortal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\BillingPortal\\ConfigurationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\BillingPortal\\SessionService' => 
  array (
    'type' => 'class',
    'classname' => 'SessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\BillingPortal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\BillingPortal\\SessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ChargeService' => 
  array (
    'type' => 'class',
    'classname' => 'ChargeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ChargeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Checkout\\CheckoutServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'CheckoutServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Checkout',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Checkout\\CheckoutServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Checkout\\SessionService' => 
  array (
    'type' => 'class',
    'classname' => 'SessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Checkout',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Checkout\\SessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Climate\\ClimateServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'ClimateServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Climate\\ClimateServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Climate\\OrderService' => 
  array (
    'type' => 'class',
    'classname' => 'OrderService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Climate\\OrderService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Climate\\ProductService' => 
  array (
    'type' => 'class',
    'classname' => 'ProductService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Climate\\ProductService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Climate\\SupplierService' => 
  array (
    'type' => 'class',
    'classname' => 'SupplierService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Climate',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Climate\\SupplierService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ConfirmationTokenService' => 
  array (
    'type' => 'class',
    'classname' => 'ConfirmationTokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ConfirmationTokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\CoreServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'CoreServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\CoreServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\CountrySpecService' => 
  array (
    'type' => 'class',
    'classname' => 'CountrySpecService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\CountrySpecService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\CouponService' => 
  array (
    'type' => 'class',
    'classname' => 'CouponService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\CouponService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\CreditNoteService' => 
  array (
    'type' => 'class',
    'classname' => 'CreditNoteService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\CreditNoteService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\CustomerService' => 
  array (
    'type' => 'class',
    'classname' => 'CustomerService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\CustomerService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\CustomerSessionService' => 
  array (
    'type' => 'class',
    'classname' => 'CustomerSessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\CustomerSessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\DisputeService' => 
  array (
    'type' => 'class',
    'classname' => 'DisputeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\DisputeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Entitlements\\ActiveEntitlementService' => 
  array (
    'type' => 'class',
    'classname' => 'ActiveEntitlementService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Entitlements',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Entitlements\\ActiveEntitlementService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Entitlements\\EntitlementsServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'EntitlementsServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Entitlements',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Entitlements\\EntitlementsServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Entitlements\\FeatureService' => 
  array (
    'type' => 'class',
    'classname' => 'FeatureService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Entitlements',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Entitlements\\FeatureService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\EphemeralKeyService' => 
  array (
    'type' => 'class',
    'classname' => 'EphemeralKeyService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\EphemeralKeyService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\EventService' => 
  array (
    'type' => 'class',
    'classname' => 'EventService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\EventService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ExchangeRateService' => 
  array (
    'type' => 'class',
    'classname' => 'ExchangeRateService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ExchangeRateService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\FileLinkService' => 
  array (
    'type' => 'class',
    'classname' => 'FileLinkService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\FileLinkService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\FileService' => 
  array (
    'type' => 'class',
    'classname' => 'FileService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\FileService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\FinancialConnections\\AccountService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\FinancialConnections\\AccountService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\FinancialConnections\\FinancialConnectionsServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'FinancialConnectionsServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\FinancialConnections\\FinancialConnectionsServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\FinancialConnections\\SessionService' => 
  array (
    'type' => 'class',
    'classname' => 'SessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\FinancialConnections\\SessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\FinancialConnections\\TransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\FinancialConnections',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\FinancialConnections\\TransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Forwarding\\ForwardingServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'ForwardingServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Forwarding',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Forwarding\\ForwardingServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Forwarding\\RequestService' => 
  array (
    'type' => 'class',
    'classname' => 'RequestService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Forwarding',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Forwarding\\RequestService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Identity\\IdentityServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'IdentityServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Identity',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Identity\\IdentityServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Identity\\VerificationReportService' => 
  array (
    'type' => 'class',
    'classname' => 'VerificationReportService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Identity',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Identity\\VerificationReportService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Identity\\VerificationSessionService' => 
  array (
    'type' => 'class',
    'classname' => 'VerificationSessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Identity',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Identity\\VerificationSessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\InvoiceItemService' => 
  array (
    'type' => 'class',
    'classname' => 'InvoiceItemService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\InvoiceItemService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\InvoicePaymentService' => 
  array (
    'type' => 'class',
    'classname' => 'InvoicePaymentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\InvoicePaymentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\InvoiceRenderingTemplateService' => 
  array (
    'type' => 'class',
    'classname' => 'InvoiceRenderingTemplateService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\InvoiceRenderingTemplateService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\InvoiceService' => 
  array (
    'type' => 'class',
    'classname' => 'InvoiceService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\InvoiceService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\AuthorizationService' => 
  array (
    'type' => 'class',
    'classname' => 'AuthorizationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\AuthorizationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\CardService' => 
  array (
    'type' => 'class',
    'classname' => 'CardService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\CardService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\CardholderService' => 
  array (
    'type' => 'class',
    'classname' => 'CardholderService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\CardholderService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\DisputeService' => 
  array (
    'type' => 'class',
    'classname' => 'DisputeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\DisputeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\IssuingServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'IssuingServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\IssuingServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\PersonalizationDesignService' => 
  array (
    'type' => 'class',
    'classname' => 'PersonalizationDesignService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\PersonalizationDesignService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\PhysicalBundleService' => 
  array (
    'type' => 'class',
    'classname' => 'PhysicalBundleService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\PhysicalBundleService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\TokenService' => 
  array (
    'type' => 'class',
    'classname' => 'TokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\TokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Issuing\\TransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Issuing\\TransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\MandateService' => 
  array (
    'type' => 'class',
    'classname' => 'MandateService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\MandateService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\OAuthService' => 
  array (
    'type' => 'class',
    'classname' => 'OAuthService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\OAuthService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentAttemptRecordService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentAttemptRecordService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentAttemptRecordService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentIntentService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentIntentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentIntentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentLinkService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentLinkService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentLinkService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentMethodConfigurationService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentMethodConfigurationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentMethodConfigurationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentMethodDomainService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentMethodDomainService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentMethodDomainService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentMethodService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentMethodService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentMethodService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PaymentRecordService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentRecordService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PaymentRecordService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PayoutService' => 
  array (
    'type' => 'class',
    'classname' => 'PayoutService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PayoutService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PlanService' => 
  array (
    'type' => 'class',
    'classname' => 'PlanService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PlanService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PriceService' => 
  array (
    'type' => 'class',
    'classname' => 'PriceService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PriceService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ProductService' => 
  array (
    'type' => 'class',
    'classname' => 'ProductService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ProductService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\PromotionCodeService' => 
  array (
    'type' => 'class',
    'classname' => 'PromotionCodeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\PromotionCodeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\QuoteService' => 
  array (
    'type' => 'class',
    'classname' => 'QuoteService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\QuoteService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Radar\\EarlyFraudWarningService' => 
  array (
    'type' => 'class',
    'classname' => 'EarlyFraudWarningService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Radar\\EarlyFraudWarningService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Radar\\PaymentEvaluationService' => 
  array (
    'type' => 'class',
    'classname' => 'PaymentEvaluationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Radar\\PaymentEvaluationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Radar\\RadarServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'RadarServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Radar\\RadarServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Radar\\ValueListItemService' => 
  array (
    'type' => 'class',
    'classname' => 'ValueListItemService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Radar\\ValueListItemService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Radar\\ValueListService' => 
  array (
    'type' => 'class',
    'classname' => 'ValueListService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Radar',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Radar\\ValueListService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\RefundService' => 
  array (
    'type' => 'class',
    'classname' => 'RefundService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\RefundService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Reporting\\ReportRunService' => 
  array (
    'type' => 'class',
    'classname' => 'ReportRunService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Reporting',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Reporting\\ReportRunService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Reporting\\ReportTypeService' => 
  array (
    'type' => 'class',
    'classname' => 'ReportTypeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Reporting',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Reporting\\ReportTypeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Reporting\\ReportingServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'ReportingServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Reporting',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Reporting\\ReportingServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ReviewService' => 
  array (
    'type' => 'class',
    'classname' => 'ReviewService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ReviewService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\SetupAttemptService' => 
  array (
    'type' => 'class',
    'classname' => 'SetupAttemptService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\SetupAttemptService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\SetupIntentService' => 
  array (
    'type' => 'class',
    'classname' => 'SetupIntentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\SetupIntentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\ShippingRateService' => 
  array (
    'type' => 'class',
    'classname' => 'ShippingRateService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ShippingRateService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Sigma\\ScheduledQueryRunService' => 
  array (
    'type' => 'class',
    'classname' => 'ScheduledQueryRunService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Sigma',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Sigma\\ScheduledQueryRunService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Sigma\\SigmaServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'SigmaServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Sigma',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Sigma\\SigmaServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\SourceService' => 
  array (
    'type' => 'class',
    'classname' => 'SourceService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\SourceService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\SubscriptionItemService' => 
  array (
    'type' => 'class',
    'classname' => 'SubscriptionItemService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\SubscriptionItemService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\SubscriptionScheduleService' => 
  array (
    'type' => 'class',
    'classname' => 'SubscriptionScheduleService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\SubscriptionScheduleService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\SubscriptionService' => 
  array (
    'type' => 'class',
    'classname' => 'SubscriptionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\SubscriptionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Tax\\AssociationService' => 
  array (
    'type' => 'class',
    'classname' => 'AssociationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Tax\\AssociationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Tax\\CalculationService' => 
  array (
    'type' => 'class',
    'classname' => 'CalculationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Tax\\CalculationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Tax\\RegistrationService' => 
  array (
    'type' => 'class',
    'classname' => 'RegistrationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Tax\\RegistrationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Tax\\SettingsService' => 
  array (
    'type' => 'class',
    'classname' => 'SettingsService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Tax\\SettingsService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Tax\\TaxServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'TaxServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Tax\\TaxServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Tax\\TransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Tax\\TransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TaxCodeService' => 
  array (
    'type' => 'class',
    'classname' => 'TaxCodeService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TaxCodeService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TaxIdService' => 
  array (
    'type' => 'class',
    'classname' => 'TaxIdService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TaxIdService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TaxRateService' => 
  array (
    'type' => 'class',
    'classname' => 'TaxRateService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TaxRateService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Terminal\\ConfigurationService' => 
  array (
    'type' => 'class',
    'classname' => 'ConfigurationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Terminal\\ConfigurationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Terminal\\ConnectionTokenService' => 
  array (
    'type' => 'class',
    'classname' => 'ConnectionTokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Terminal\\ConnectionTokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Terminal\\LocationService' => 
  array (
    'type' => 'class',
    'classname' => 'LocationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Terminal\\LocationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Terminal\\OnboardingLinkService' => 
  array (
    'type' => 'class',
    'classname' => 'OnboardingLinkService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Terminal\\OnboardingLinkService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Terminal\\ReaderService' => 
  array (
    'type' => 'class',
    'classname' => 'ReaderService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Terminal\\ReaderService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Terminal\\TerminalServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'TerminalServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Terminal\\TerminalServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\ConfirmationTokenService' => 
  array (
    'type' => 'class',
    'classname' => 'ConfirmationTokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\ConfirmationTokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\CustomerService' => 
  array (
    'type' => 'class',
    'classname' => 'CustomerService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\CustomerService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Issuing\\AuthorizationService' => 
  array (
    'type' => 'class',
    'classname' => 'AuthorizationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Issuing\\AuthorizationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Issuing\\CardService' => 
  array (
    'type' => 'class',
    'classname' => 'CardService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Issuing\\CardService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Issuing\\IssuingServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'IssuingServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Issuing\\IssuingServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Issuing\\PersonalizationDesignService' => 
  array (
    'type' => 'class',
    'classname' => 'PersonalizationDesignService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Issuing\\PersonalizationDesignService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Issuing\\TransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Issuing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Issuing\\TransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\RefundService' => 
  array (
    'type' => 'class',
    'classname' => 'RefundService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\RefundService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Terminal\\ReaderService' => 
  array (
    'type' => 'class',
    'classname' => 'ReaderService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Terminal\\ReaderService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Terminal\\TerminalServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'TerminalServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Terminal\\TerminalServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\TestClockService' => 
  array (
    'type' => 'class',
    'classname' => 'TestClockService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\TestClockService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\TestHelpersServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'TestHelpersServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\TestHelpersServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Treasury\\InboundTransferService' => 
  array (
    'type' => 'class',
    'classname' => 'InboundTransferService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Treasury\\InboundTransferService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Treasury\\OutboundPaymentService' => 
  array (
    'type' => 'class',
    'classname' => 'OutboundPaymentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Treasury\\OutboundPaymentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Treasury\\OutboundTransferService' => 
  array (
    'type' => 'class',
    'classname' => 'OutboundTransferService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Treasury\\OutboundTransferService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Treasury\\ReceivedCreditService' => 
  array (
    'type' => 'class',
    'classname' => 'ReceivedCreditService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Treasury\\ReceivedCreditService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Treasury\\ReceivedDebitService' => 
  array (
    'type' => 'class',
    'classname' => 'ReceivedDebitService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Treasury\\ReceivedDebitService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TestHelpers\\Treasury\\TreasuryServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'TreasuryServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\TestHelpers\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TestHelpers\\Treasury\\TreasuryServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TokenService' => 
  array (
    'type' => 'class',
    'classname' => 'TokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TopupService' => 
  array (
    'type' => 'class',
    'classname' => 'TopupService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TopupService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\TransferService' => 
  array (
    'type' => 'class',
    'classname' => 'TransferService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\TransferService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\CreditReversalService' => 
  array (
    'type' => 'class',
    'classname' => 'CreditReversalService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\CreditReversalService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\DebitReversalService' => 
  array (
    'type' => 'class',
    'classname' => 'DebitReversalService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\DebitReversalService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\FinancialAccountService' => 
  array (
    'type' => 'class',
    'classname' => 'FinancialAccountService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\FinancialAccountService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\InboundTransferService' => 
  array (
    'type' => 'class',
    'classname' => 'InboundTransferService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\InboundTransferService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\OutboundPaymentService' => 
  array (
    'type' => 'class',
    'classname' => 'OutboundPaymentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\OutboundPaymentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\OutboundTransferService' => 
  array (
    'type' => 'class',
    'classname' => 'OutboundTransferService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\OutboundTransferService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\ReceivedCreditService' => 
  array (
    'type' => 'class',
    'classname' => 'ReceivedCreditService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\ReceivedCreditService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\ReceivedDebitService' => 
  array (
    'type' => 'class',
    'classname' => 'ReceivedDebitService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\ReceivedDebitService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\TransactionEntryService' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionEntryService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\TransactionEntryService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\TransactionService' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\TransactionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\Treasury\\TreasuryServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'TreasuryServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\Treasury\\TreasuryServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Billing\\BillingServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'BillingServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Billing\\BillingServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Billing\\MeterEventAdjustmentService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventAdjustmentService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Billing\\MeterEventAdjustmentService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Billing\\MeterEventService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Billing\\MeterEventService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Billing\\MeterEventSessionService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventSessionService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Billing\\MeterEventSessionService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Billing\\MeterEventStreamService' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventStreamService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Billing\\MeterEventStreamService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\AccountLinkService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountLinkService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\AccountLinkService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\AccountService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\AccountService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\AccountTokenService' => 
  array (
    'type' => 'class',
    'classname' => 'AccountTokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\AccountTokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\Accounts\\PersonService' => 
  array (
    'type' => 'class',
    'classname' => 'PersonService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core\\Accounts',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\Accounts\\PersonService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\Accounts\\PersonTokenService' => 
  array (
    'type' => 'class',
    'classname' => 'PersonTokenService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core\\Accounts',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\Accounts\\PersonTokenService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\CoreServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'CoreServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\CoreServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\EventDestinationService' => 
  array (
    'type' => 'class',
    'classname' => 'EventDestinationService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\EventDestinationService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\Core\\EventService' => 
  array (
    'type' => 'class',
    'classname' => 'EventService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\Core\\EventService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\V2\\V2ServiceFactory' => 
  array (
    'type' => 'class',
    'classname' => 'V2ServiceFactory',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service\\V2',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\V2\\V2ServiceFactory',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Service\\WebhookEndpointService' => 
  array (
    'type' => 'class',
    'classname' => 'WebhookEndpointService',
    'isabstract' => false,
    'namespace' => 'Stripe\\Service',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Service\\WebhookEndpointService',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SetupAttempt' => 
  array (
    'type' => 'class',
    'classname' => 'SetupAttempt',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SetupAttempt',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SetupIntent' => 
  array (
    'type' => 'class',
    'classname' => 'SetupIntent',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SetupIntent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ShippingRate' => 
  array (
    'type' => 'class',
    'classname' => 'ShippingRate',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\ShippingRate',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Sigma\\ScheduledQueryRun' => 
  array (
    'type' => 'class',
    'classname' => 'ScheduledQueryRun',
    'isabstract' => false,
    'namespace' => 'Stripe\\Sigma',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Sigma\\ScheduledQueryRun',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SingletonApiResource' => 
  array (
    'type' => 'class',
    'classname' => 'SingletonApiResource',
    'isabstract' => true,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SingletonApiResource',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Source' => 
  array (
    'type' => 'class',
    'classname' => 'Source',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Source',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SourceMandateNotification' => 
  array (
    'type' => 'class',
    'classname' => 'SourceMandateNotification',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SourceMandateNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SourceTransaction' => 
  array (
    'type' => 'class',
    'classname' => 'SourceTransaction',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SourceTransaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Stripe' => 
  array (
    'type' => 'class',
    'classname' => 'Stripe',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Stripe',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\StripeClient' => 
  array (
    'type' => 'class',
    'classname' => 'StripeClient',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\StripeClient',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\StripeContext' => 
  array (
    'type' => 'class',
    'classname' => 'StripeContext',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\StripeContext',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\StripeObject' => 
  array (
    'type' => 'class',
    'classname' => 'StripeObject',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\StripeObject',
    'implements' => 
    array (
      0 => 'ArrayAccess',
      1 => 'Countable',
      2 => 'JsonSerializable',
    ),
  ),
  'Stripe\\Subscription' => 
  array (
    'type' => 'class',
    'classname' => 'Subscription',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Subscription',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SubscriptionItem' => 
  array (
    'type' => 'class',
    'classname' => 'SubscriptionItem',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SubscriptionItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\SubscriptionSchedule' => 
  array (
    'type' => 'class',
    'classname' => 'SubscriptionSchedule',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\SubscriptionSchedule',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\Association' => 
  array (
    'type' => 'class',
    'classname' => 'Association',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\Association',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\Calculation' => 
  array (
    'type' => 'class',
    'classname' => 'Calculation',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\Calculation',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\CalculationLineItem' => 
  array (
    'type' => 'class',
    'classname' => 'CalculationLineItem',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\CalculationLineItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\Registration' => 
  array (
    'type' => 'class',
    'classname' => 'Registration',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\Registration',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\Settings' => 
  array (
    'type' => 'class',
    'classname' => 'Settings',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\Settings',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\Transaction' => 
  array (
    'type' => 'class',
    'classname' => 'Transaction',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\Transaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Tax\\TransactionLineItem' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionLineItem',
    'isabstract' => false,
    'namespace' => 'Stripe\\Tax',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Tax\\TransactionLineItem',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\TaxCode' => 
  array (
    'type' => 'class',
    'classname' => 'TaxCode',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\TaxCode',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\TaxDeductedAtSource' => 
  array (
    'type' => 'class',
    'classname' => 'TaxDeductedAtSource',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\TaxDeductedAtSource',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\TaxId' => 
  array (
    'type' => 'class',
    'classname' => 'TaxId',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\TaxId',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\TaxRate' => 
  array (
    'type' => 'class',
    'classname' => 'TaxRate',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\TaxRate',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Terminal\\Configuration' => 
  array (
    'type' => 'class',
    'classname' => 'Configuration',
    'isabstract' => false,
    'namespace' => 'Stripe\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Terminal\\Configuration',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Terminal\\ConnectionToken' => 
  array (
    'type' => 'class',
    'classname' => 'ConnectionToken',
    'isabstract' => false,
    'namespace' => 'Stripe\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Terminal\\ConnectionToken',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Terminal\\Location' => 
  array (
    'type' => 'class',
    'classname' => 'Location',
    'isabstract' => false,
    'namespace' => 'Stripe\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Terminal\\Location',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Terminal\\OnboardingLink' => 
  array (
    'type' => 'class',
    'classname' => 'OnboardingLink',
    'isabstract' => false,
    'namespace' => 'Stripe\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Terminal\\OnboardingLink',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Terminal\\Reader' => 
  array (
    'type' => 'class',
    'classname' => 'Reader',
    'isabstract' => false,
    'namespace' => 'Stripe\\Terminal',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Terminal\\Reader',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\TestHelpers\\TestClock' => 
  array (
    'type' => 'class',
    'classname' => 'TestClock',
    'isabstract' => false,
    'namespace' => 'Stripe\\TestHelpers',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\TestHelpers\\TestClock',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Token' => 
  array (
    'type' => 'class',
    'classname' => 'Token',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Token',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Topup' => 
  array (
    'type' => 'class',
    'classname' => 'Topup',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Topup',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Transfer' => 
  array (
    'type' => 'class',
    'classname' => 'Transfer',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Transfer',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\TransferReversal' => 
  array (
    'type' => 'class',
    'classname' => 'TransferReversal',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\TransferReversal',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\CreditReversal' => 
  array (
    'type' => 'class',
    'classname' => 'CreditReversal',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\CreditReversal',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\DebitReversal' => 
  array (
    'type' => 'class',
    'classname' => 'DebitReversal',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\DebitReversal',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\FinancialAccount' => 
  array (
    'type' => 'class',
    'classname' => 'FinancialAccount',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\FinancialAccount',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\FinancialAccountFeatures' => 
  array (
    'type' => 'class',
    'classname' => 'FinancialAccountFeatures',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\FinancialAccountFeatures',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\InboundTransfer' => 
  array (
    'type' => 'class',
    'classname' => 'InboundTransfer',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\InboundTransfer',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\OutboundPayment' => 
  array (
    'type' => 'class',
    'classname' => 'OutboundPayment',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\OutboundPayment',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\OutboundTransfer' => 
  array (
    'type' => 'class',
    'classname' => 'OutboundTransfer',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\OutboundTransfer',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\ReceivedCredit' => 
  array (
    'type' => 'class',
    'classname' => 'ReceivedCredit',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\ReceivedCredit',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\ReceivedDebit' => 
  array (
    'type' => 'class',
    'classname' => 'ReceivedDebit',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\ReceivedDebit',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\Transaction' => 
  array (
    'type' => 'class',
    'classname' => 'Transaction',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\Transaction',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Treasury\\TransactionEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TransactionEntry',
    'isabstract' => false,
    'namespace' => 'Stripe\\Treasury',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Treasury\\TransactionEntry',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\ApiVersion' => 
  array (
    'type' => 'class',
    'classname' => 'ApiVersion',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\ApiVersion',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\CaseInsensitiveArray' => 
  array (
    'type' => 'class',
    'classname' => 'CaseInsensitiveArray',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\CaseInsensitiveArray',
    'implements' => 
    array (
      0 => 'ArrayAccess',
      1 => 'Countable',
      2 => 'IteratorAggregate',
    ),
  ),
  'Stripe\\Util\\DefaultLogger' => 
  array (
    'type' => 'class',
    'classname' => 'DefaultLogger',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\DefaultLogger',
    'implements' => 
    array (
      0 => 'Stripe\\Util\\LoggerInterface',
    ),
  ),
  'Stripe\\Util\\EventNotificationTypes' => 
  array (
    'type' => 'class',
    'classname' => 'EventNotificationTypes',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\EventNotificationTypes',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\EventTypes' => 
  array (
    'type' => 'class',
    'classname' => 'EventTypes',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\EventTypes',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\ObjectTypes' => 
  array (
    'type' => 'class',
    'classname' => 'ObjectTypes',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\ObjectTypes',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\RandomGenerator' => 
  array (
    'type' => 'class',
    'classname' => 'RandomGenerator',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\RandomGenerator',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\RequestOptions' => 
  array (
    'type' => 'class',
    'classname' => 'RequestOptions',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\RequestOptions',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Util\\Set' => 
  array (
    'type' => 'class',
    'classname' => 'Set',
    'isabstract' => false,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\Set',
    'implements' => 
    array (
      0 => 'IteratorAggregate',
    ),
  ),
  'Stripe\\Util\\Util' => 
  array (
    'type' => 'class',
    'classname' => 'Util',
    'isabstract' => true,
    'namespace' => 'Stripe\\Util',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Util\\Util',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Billing\\MeterEvent' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEvent',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Billing\\MeterEvent',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Billing\\MeterEventAdjustment' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventAdjustment',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Billing\\MeterEventAdjustment',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Billing\\MeterEventSession' => 
  array (
    'type' => 'class',
    'classname' => 'MeterEventSession',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Billing',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Billing\\MeterEventSession',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Collection' => 
  array (
    'type' => 'class',
    'classname' => 'Collection',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Collection',
    'implements' => 
    array (
      0 => 'Countable',
      1 => 'IteratorAggregate',
    ),
  ),
  'Stripe\\V2\\Core\\Account' => 
  array (
    'type' => 'class',
    'classname' => 'Account',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\Account',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\AccountLink' => 
  array (
    'type' => 'class',
    'classname' => 'AccountLink',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\AccountLink',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\AccountPerson' => 
  array (
    'type' => 'class',
    'classname' => 'AccountPerson',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\AccountPerson',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\AccountPersonToken' => 
  array (
    'type' => 'class',
    'classname' => 'AccountPersonToken',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\AccountPersonToken',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\AccountToken' => 
  array (
    'type' => 'class',
    'classname' => 'AccountToken',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\AccountToken',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\Event' => 
  array (
    'type' => 'class',
    'classname' => 'Event',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\Event',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\EventDestination' => 
  array (
    'type' => 'class',
    'classname' => 'EventDestination',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\EventDestination',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\Core\\EventNotification' => 
  array (
    'type' => 'class',
    'classname' => 'EventNotification',
    'isabstract' => true,
    'namespace' => 'Stripe\\V2\\Core',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\Core\\EventNotification',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\V2\\DeletedObject' => 
  array (
    'type' => 'class',
    'classname' => 'DeletedObject',
    'isabstract' => false,
    'namespace' => 'Stripe\\V2',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\V2\\DeletedObject',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\Webhook' => 
  array (
    'type' => 'class',
    'classname' => 'Webhook',
    'isabstract' => true,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\Webhook',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\WebhookEndpoint' => 
  array (
    'type' => 'class',
    'classname' => 'WebhookEndpoint',
    'isabstract' => false,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\WebhookEndpoint',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\WebhookSignature' => 
  array (
    'type' => 'class',
    'classname' => 'WebhookSignature',
    'isabstract' => true,
    'namespace' => 'Stripe',
    'extends' => 'PaymentPlugins\\Vendor\\Stripe\\WebhookSignature',
    'implements' => 
    array (
    ),
  ),
  'Stripe\\ApiOperations\\All' => 
  array (
    'type' => 'trait',
    'traitname' => 'All',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\All',
    ),
  ),
  'Stripe\\ApiOperations\\Create' => 
  array (
    'type' => 'trait',
    'traitname' => 'Create',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\Create',
    ),
  ),
  'Stripe\\ApiOperations\\Delete' => 
  array (
    'type' => 'trait',
    'traitname' => 'Delete',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\Delete',
    ),
  ),
  'Stripe\\ApiOperations\\NestedResource' => 
  array (
    'type' => 'trait',
    'traitname' => 'NestedResource',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\NestedResource',
    ),
  ),
  'Stripe\\ApiOperations\\Request' => 
  array (
    'type' => 'trait',
    'traitname' => 'Request',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\Request',
    ),
  ),
  'Stripe\\ApiOperations\\Retrieve' => 
  array (
    'type' => 'trait',
    'traitname' => 'Retrieve',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\Retrieve',
    ),
  ),
  'Stripe\\ApiOperations\\SingletonRetrieve' => 
  array (
    'type' => 'trait',
    'traitname' => 'SingletonRetrieve',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\SingletonRetrieve',
    ),
  ),
  'Stripe\\ApiOperations\\Update' => 
  array (
    'type' => 'trait',
    'traitname' => 'Update',
    'namespace' => 'Stripe\\ApiOperations',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\ApiOperations\\Update',
    ),
  ),
  'Stripe\\Service\\ServiceNavigatorTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'ServiceNavigatorTrait',
    'namespace' => 'Stripe\\Service',
    'use' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\Service\\ServiceNavigatorTrait',
    ),
  ),
  'Stripe\\BaseStripeClientInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'BaseStripeClientInterface',
    'namespace' => 'Stripe',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\BaseStripeClientInterface',
    ),
  ),
  'Stripe\\Exception\\ExceptionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ExceptionInterface',
    'namespace' => 'Stripe\\Exception',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\ExceptionInterface',
    ),
  ),
  'Stripe\\Exception\\OAuth\\ExceptionInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ExceptionInterface',
    'namespace' => 'Stripe\\Exception\\OAuth',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\Exception\\OAuth\\ExceptionInterface',
    ),
  ),
  'Stripe\\HttpClient\\ClientInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'ClientInterface',
    'namespace' => 'Stripe\\HttpClient',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\HttpClient\\ClientInterface',
    ),
  ),
  'Stripe\\HttpClient\\StreamingClientInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'StreamingClientInterface',
    'namespace' => 'Stripe\\HttpClient',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\HttpClient\\StreamingClientInterface',
    ),
  ),
  'Stripe\\StripeClientInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'StripeClientInterface',
    'namespace' => 'Stripe',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\StripeClientInterface',
    ),
  ),
  'Stripe\\StripeStreamingClientInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'StripeStreamingClientInterface',
    'namespace' => 'Stripe',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\StripeStreamingClientInterface',
    ),
  ),
  'Stripe\\Util\\LoggerInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'LoggerInterface',
    'namespace' => 'Stripe\\Util',
    'extends' => 
    array (
      0 => 'PaymentPlugins\\Vendor\\Stripe\\Util\\LoggerInterface',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        /**
         * @param string $class
         */
        public function autoload($class): void
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile): void
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        /**
         * @param ClassAliasArray $class
         */
        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        /**
         * @param InterfaceAliasArray $interface
         */
        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }

        /**
         * @param TraitAliasArray $trait
         */
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
