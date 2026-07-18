<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Events;

/**
 * @property \PaymentPlugins\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 */
class V2CoreAccountIncludingFutureRequirementsUpdatedEvent extends \PaymentPlugins\Vendor\Stripe\V2\Core\Event
{
    const LOOKUP_TYPE = 'v2.core.account[future_requirements].updated';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \PaymentPlugins\Vendor\Stripe\V2\Core\Account
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchRelatedObject()
    {
        $apiMode = \PaymentPlugins\Vendor\Stripe\Util\Util::getApiMode($this->related_object->url);
        list($object, $options) = $this->_request('get', $this->related_object->url, [], [
            'stripe_context' => $this->context,
            'headers' => ['Stripe-Request-Trigger' => 'event=' . $this->id],
        ], [], $apiMode);

        return \PaymentPlugins\Vendor\Stripe\Util\Util::convertToStripeObject($object, $options, $apiMode);
    }
}
