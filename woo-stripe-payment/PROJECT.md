# Project Notes

## BNPL Messaging Options Migration

The individual messaging display options (checkout location, font size, color, etc.) are being removed from the Affirm, Afterpay, and Klarna gateway settings pages. Since we now use Stripe's combined `paymentMethodMessaging` element, these per-gateway options are no longer needed. All messaging configuration is being consolidated into the advanced settings page.

---

## Blocks JS Refactoring

### webpack.config.js

- Introduced `createJsConfig` base factory so all JS builds (`javascriptCore`, `frontendScripts`, `jsBlocksConfig`, package configs) flow through the same pipeline, gaining `DependencyExtractionWebpackPlugin` and `AddSplitChunkDependencies` consistently.
- `createPackageJsConfig` now delegates to `createJsConfig`.
- `jsBlocksConfig` uses `createPackageJsConfig('blocks', 'blocks', {injectPolyfill: true})`.
- Removed dead helpers: `createBabelRule`, `babelRuleWithReact`, `ignoreScssRule`.
- Fixed missing `@woocommerce/blocks-registry` entry in `wcDepMap` in `bin/webpack-helpers.js` (mapped to `['wc', 'wcBlocksRegistry']`).

### Express Checkout Consolidation (`payment-methods/express-checkout/index.js`)

Apple Pay, Google Pay, and Link had near-identical implementations. Consolidated into a shared module exporting:

- `ExpressCheckoutContent` — shared React component wrapping Stripe `Elements` + `ExpressCheckoutElement`. Reads button options from `data.button.*` directly. Supports optional `containerClass` and `errorBoundary` props.
- `createCanMakePayment` — factory returning a cached `canMakePayment` function (promise cached in closure to avoid redundant Stripe element creation on repeated calls).

Each gateway file (`applepay/payment-method.js`, `googlepay/payment-method.js`, `link/link-express-checkout.js`) is now ~30 lines, calling `registerExpressPaymentMethod` directly with `activeMethod` derived from `data.displayRule`.

### Local Payment Methods (`payment-methods/local-payment/`)

- All files migrated from `getSettings()`/`getData('key')` pattern to `getSetting()`/`data.key` direct property access.
- `canMakePayment` unified: all gateways use `canMakePayment(data.gatewayId)` from `local-payment-method.js`. Custom callbacks (previously in `billie`, `amazon_pay`, `ach`) removed — availability is determined server-side via `extensions.wc_stripe.cart.paymentMethods`.
- All files wrapped with `if (data)` guard to skip registration when gateway settings are absent.
- `getData={getData}` prop replaced with `data={data}` throughout `PaymentMethod` usage.
- Removed `getSettings` import from `../util` across all local payment files.

### BNPL Message Label (`components/checkout/bnpl-message-label/index.js`)

- Migrated to a `data` prop (receives `stripeBNPLCart_data` settings object) instead of individual `countryCode` and `elementOptions` props.
- `countryCode` now derived from live billing address via `store.getCustomerData().billingAddress.country`, falling back to `data.countryCode`.
- Location logic (`below_total` vs `payment_method_title`) moved inside `BNPLMessageLabel` — imports and uses the plugin's `PaymentMethodLabel` component directly, eliminating per-gateway wrapper components (e.g. `AffirmPaymentMethodLabel`).
- BNPL gateway files (`affirm.js`, `klarna.js`, `afterpay.js`) now use `BNPLMessageLabel` directly as the `label` prop with no intermediate wrapper.

### BNPL Cart/Checkout Messaging (`blocks/cart/bnpl-messages/frontend.js`)

- On the checkout page, messaging is now suppressed if no BNPL payment method is available (checked via `extensions.wc_stripe.cart.paymentMethods`).
- Options object (amount, currency, countryCode, paymentMethodTypes) built inside a single `useMemo` keyed on `[cartTotals, extensions]`. On checkout, `paymentMethodTypes` is filtered to only methods with `available: true`; on cart, all configured methods are shown.

### `useElementOptions` Hook (`payment-methods/hooks/use-element-options.js`)

Extracted the repeated Stripe Elements options computation (mode, currency, amount) into a shared memoized hook:

```js
useElementOptions({cartTotal, currency, elementOptions, shouldSavePayment})
```

Used in:
- `payment-methods/upm/index.js`
- `payment-methods/local-payment/local-payment-method.js`
- `payment-methods/express-checkout/index.js`
- `payment-methods/credit-card/payment-element.js` (with installments fallback)
