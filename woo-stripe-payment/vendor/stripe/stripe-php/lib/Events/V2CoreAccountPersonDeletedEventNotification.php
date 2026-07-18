<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Events;

/**
 * @property \PaymentPlugins\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 */
class V2CoreAccountPersonDeletedEventNotification extends \PaymentPlugins\Vendor\Stripe\V2\Core\EventNotification
{
    const LOOKUP_TYPE = 'v2.core.account_person.deleted';
    public $related_object;

    /**
     * Retrieves the full event object from the API. Make an API request on every call.
     *
     * @return V2CoreAccountPersonDeletedEvent
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchEvent()
    {
        return parent::fetchEvent();
    }

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \PaymentPlugins\Vendor\Stripe\V2\Core\AccountPerson
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchRelatedObject()
    {
        return parent::fetchRelatedObject();
    }
}
