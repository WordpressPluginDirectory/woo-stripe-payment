<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1\Admin;

use PaymentPlugins\Stripe\Rest\Routes\V1\AbstractRoute;

/**
 * Abstract base class for admin REST API routes.
 *
 * Permissions are declared via a 'permissions' array in each route config
 * returned by get_routes(). AdminRestController converts these to permission_callback
 * functions at registration time.
 *
 * @since 4.0.0
 */
abstract class AbstractAdminRoute extends AbstractRoute {

}
