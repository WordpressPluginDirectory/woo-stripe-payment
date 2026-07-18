<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Events;

class V1BillingMeterNoMeterFoundEventNotification extends \PaymentPlugins\Vendor\Stripe\V2\Core\EventNotification
{
    const LOOKUP_TYPE = 'v1.billing.meter.no_meter_found';

    /**
     * Retrieves the full event object from the API. Make an API request on every call.
     *
     * @return V1BillingMeterNoMeterFoundEvent
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchEvent()
    {
        return parent::fetchEvent();
    }
}
