<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Service\TestHelpers;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class RefundService extends \PaymentPlugins\Vendor\Stripe\Service\AbstractService
{
    /**
     * Expire a refund with a status of <code>requires_action</code>.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\PaymentPlugins\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \PaymentPlugins\Vendor\Stripe\Refund
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function expire($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/refunds/%s/expire', $id), $params, $opts);
    }
}
