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
        confirmation: SuluSalesOrderBundle:Pdf:pool-alpin.order.confirmation.html.twig
        base: SuluSalesCoreBundle:Pdf:pdf-base.html.twig
        header: SuluSalesCoreBundle:Pdf:pdf-base-header.html.twig
        footer: SuluSalesCoreBundle:Pdf:pdf-base-footer.html.twig
        macros: SuluSalesCoreBundle:Pdf:pdf-macros.html.twig
    pdf_response_type: 'inline'
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

#### Pdf response type

The way the order bundle reacts on http requests that return a pdf. It is possible to configure the response so that, for example, it is returned as `inline` so that the pdf file would be shown in a new tab, or as `attachment`. As an attachment it would start a download without opening the file in the browser.

These configurations can be set in the `parameters.yml`-file of your application under the key:

```
sulu_sales_orderbundle_pdf_responsetype: inline
```

The bundle-sided configuration for this key defaults to `inline`.


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
