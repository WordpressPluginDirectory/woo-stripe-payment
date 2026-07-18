<?php

namespace PaymentPlugins\Vendor\Stripe\Util;

class EventTypes
{
    const v2EventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountClosedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountClosedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountCreatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountCreatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEvent::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreEventDestinationPingEvent::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreEventDestinationPingEvent::class,
        // The end of the section generated from our OpenAPI spec
    ];
}
