<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class MeterEventAdjustmentService extends \PaymentPlugins\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a billing meter event adjustment.
     *
     * @param null|array{cancel?: array{identifier?: string}, event_name: string, expand?: string[], type: string} $params
     * @param null|RequestOptionsArray|\PaymentPlugins\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \PaymentPlugins\Vendor\Stripe\Billing\MeterEventAdjustment
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing/meter_event_adjustments', $params, $opts);
    }
}
