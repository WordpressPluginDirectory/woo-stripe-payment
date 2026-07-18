<?php

namespace PaymentPlugins\Vendor\Stripe\Util;

class EventNotificationTypes
{
    const v2EventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountClosedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountClosedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountCreatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountCreatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEventNotification::class,
        \PaymentPlugins\Vendor\Stripe\Events\V2CoreEventDestinationPingEventNotification::LOOKUP_TYPE => \PaymentPlugins\Vendor\Stripe\Events\V2CoreEventDestinationPingEventNotification::class,
        // The end of the section generated from our OpenAPI spec
    ];
}
