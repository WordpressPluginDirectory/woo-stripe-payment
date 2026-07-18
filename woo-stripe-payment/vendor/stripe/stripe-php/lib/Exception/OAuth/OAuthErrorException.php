<?php

namespace PaymentPlugins\Vendor\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }

        return \PaymentPlugins\Vendor\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
