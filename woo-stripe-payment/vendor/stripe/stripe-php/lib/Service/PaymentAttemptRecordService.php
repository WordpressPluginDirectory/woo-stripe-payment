<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class PaymentAttemptRecordService extends AbstractService
{
    /**
     * List all the Payment Attempt Records attached to the specified Payment Record.
     *
     * @param null|array{expand?: string[], limit?: int, payment_record: string, starting_after?: string} $params
     * @param null|RequestOptionsArray|\PaymentPlugins\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \PaymentPlugins\Vendor\Stripe\Collection<\PaymentPlugins\Vendor\Stripe\PaymentAttemptRecord>
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/payment_attempt_records', $params, $opts);
    }

    /**
     * Retrieves a Payment Attempt Record with the given ID.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\PaymentPlugins\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \PaymentPlugins\Vendor\Stripe\PaymentAttemptRecord
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/payment_attempt_records/%s', $id), $params, $opts);
    }
}
