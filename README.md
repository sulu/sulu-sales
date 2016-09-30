# sulu-sales [![Build Status](https://travis-ci.org/sulu/sulu-sales.svg?branch=develop)](https://travis-ci.org/sulu/sulu-sales)

## Configuration

The following description contains the default configuration for the different sales bundles.

### SuluSalesCoreBundle

```
sulu_sales_core:
    priceformatter_digits: 2
    shop_location: ~ # Is used for calculating correct tax
    email_from: ~ # Originator of sales emails like an order confirmation
    email_templates: # Footer templates for email
        footer_txt: ~
        footer_html: ~
```

### SuluSalesOrderBundle

Confirmation emails are only sent, when using CartManager for creating orders.

```
sulu_sales_order:
    pdf_templates:
        confirmation: SuluSalesOrderBundle:Pdf:order.confirmation.html.twig
        base: SuluSalesCoreBundle:Pdf:pdf-base.html.twig
        header: SuluSalesCoreBundle:Pdf:pdf-base-header.html.twig
        footer: SuluSalesCoreBundle:Pdf:pdf-base-footer.html.twig
        macros: SuluSalesCoreBundle:Pdf:pdf-macros.html.twig
        dynamically: SuluSalesOrderBundle:Pdf:order.dynamically.html.twig
        dynamically_base: SuluSalesOrderBundle:Pdf:order.confirmation.html.twig
    pdf_response_type: 'inline'
    pdf_order_confirmation_name_prefix: order_confirmation
    pdf_order_dynamically_name_prefix: order_pdf
    email_templates:
        footer_txt: ~
        footer_html: ~
    shop_email_from: ~ # Originator of confirmation email
    shop_email_confirmation_to: ~ # Defines an extra recipient for confirmation email
    send_email_confirmation_to_customer: false # Defines if a confirmation mail should be sent to customer
    objects:
        sales_order:
            model: Sulu\Bundle\Sales\OrderBundle\Entity\Order
            repository: Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository
```

You also need to tell doctrine, how to resolve the target entities:

```
doctrine:
    orm:
        resolve_target_entities:
            Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface: Sulu\Bundle\Sales\CoreBundle\Entity\Item
            Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusInterface: Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus
```

#### Pdf templates dynamically

The `dynamically` pdf template can be configured easily. That way it is possible to dynamically change the look of the
pdf generated from a given order object. To add even more flexibility it is possible to configure the
`dynamically_base` template which is the template, that will be extended by `dynamically`. Like: *dynamically extends
dynamically_base*.

Since there are defaults specified and default templates exist in the bundle, it is not strictly necessary to configure
those parameters.

#### Pdf response type

The way the order bundle reacts on http requests that return a pdf. It is possible to configure the response so that, for example, it is returned as `inline` so that the pdf file would be shown in a new tab, or as `attachment`. As an attachment it would start a download without opening the file in the browser.

These configurations can be set in the `parameters.yml`-file of your application under the key:

```
sulu_sales_orderbundle_pdf_responsetype: inline
```

The bundle-sided configuration for this key defaults to `inline`.

#### Pdf naming prefixes

The naming prefixes, that can be configured are : `pdf_order_confirmation_name_prefix` and
`pdf_order_dynamically_name_prefix`. That way it is easily configurable how the returned file is named. So if a dynamic
template is rendered, the `pdf_order_dynamically_name_prefix` will be used and suffixed with the order number. Applying
the same logic, the filename for the confirmation pdf is generated.

If not configured, the default values are applied. Those are like shown in the example config above.

## Routing

The routing of the Bundle depends on the routing files that live in the respective `Resources/config/routing`
directories.

Those are, grouped by bundle:

- SuluSalesOrderBundle:
  - routing.xml
  - routing_api.xml
  - routing_website.xml
- SuluSalesCoreBundle:
  - routing.xml
- SuluSalesShippingBundle:
  - routing.xml
  - routing_api.xml

It is also important to know, that the routing for tests is configured separately. (Check the Tests/ directory)

## How to run tests

The following command is going to run tests for all sales bundles:

```
composer install

tests/app/console doctrine:schema:update --force

phpunit
```

## Documentation

### Extend Sales Order

You can use sulu persistence to extend the Order entity and repository.

### Events

The following events are triggered by the system:

| Constant | Parameter  | Triggered | Parameters |
|---|---|---|---|
|SalesOrderEvents:StatusChanged| sulu_sales_order.events.status_changed  | Triggered when the status of an order changes.  | SuluSalesOrderStatusChangeEvent |
