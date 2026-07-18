<?php

// File generated from our OpenAPI spec

namespace PaymentPlugins\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class TaxCodeService extends AbstractService
{
    /**
     * A list of <a href="https://stripe.com/docs/tax/tax-categories">all tax codes
     * available</a> to add to Products in order to allow specific tax calculations.
     *
     * @param null|array{ending_before?: string, expand?: string[], limit?: int, starting_after?: string} $params
     * @param null|RequestOptionsArray|\PaymentPlugins\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \PaymentPlugins\Vendor\Stripe\Collection<\PaymentPlugins\Vendor\Stripe\TaxCode>
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/tax_codes', $params, $opts);
    }

    /**
     * Retrieves the details of an existing tax code. Supply the unique tax code ID and
     * Stripe will return the corresponding tax code information.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\PaymentPlugins\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \PaymentPlugins\Vendor\Stripe\TaxCode
     *
     * @throws \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/tax_codes/%s', $id), $params, $opts);
    }
}
