<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Events;

/**
 * @property \PaymentPlugins\Vendor\Stripe\EventData\V2CoreAccountLinkReturnedEventData $data data associated with the event
 */
class V2CoreAccountLinkReturnedEvent extends \PaymentPlugins\Vendor\Stripe\V2\Core\Event
{
    const LOOKUP_TYPE = 'v2.core.account_link.returned';

    public static function constructFrom($values, $opts = null, $apiMode = 'v2')
    {
        $evt = parent::constructFrom($values, $opts, $apiMode);
        if (null !== $evt->data) {
            $evt->data = \PaymentPlugins\Vendor\Stripe\EventData\V2CoreAccountLinkReturnedEventData::constructFrom($evt->data, $opts, $apiMode);
        }

        return $evt;
    }
}
