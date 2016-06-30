# sulu-sales ![alt text](https://travis-ci.org/sulu/sulu-sales.svg?branch=develop)

## Configuration

### Sulu-sales order bundle

The available configuration in `app/config.yml` is:
```
sulu_sales_order:
    pdf_templates:
        confirmation: PoolAlpinSalesOrderBundle:Pdf:pool-alpin.order.confirmation.html.twig
        base: PoolAlpinBaseBundle:Pdf:pdf-base.html.twig
        header: PoolAlpinBaseBundle:Pdf:pdf-base-header.html.twig
        footer: PoolAlpinBaseBundle:Pdf:pdf-base-footer.html.twig
        macros: PoolAlpinBaseBundle:Pdf:pdf-macros.html.twig
    pdf_response_type: '%sulu_sales_orderbundle_pdf_responsetype%'
    email_templates:
        footer_txt: PoolAlpinBaseBundle:Email:email.footer.txt.twig
        footer_html: PoolAlpinBaseBundle:Email:email.footer.html.twig
    shop_email_from: '%mailer_from%'
    shop_email_confirmation_to: '%mailer_from%'
    send_email_confirmation_to_customer: false
```

#### Pdf response type

The way the order bundle reacts on http requests that return a pdf. It is possible to configure the response so that, for example, it is returned as `inline` so that the pdf file would be shown in a new tab, or as `attachment`. As an attachment it would start a download without opening the file in the browser.

These configurations can be set in the `parameters.yml`-file of your application under the key:
```
sulu_sales_orderbundle_pdf_responsetype: inline
```
The bundle-sided configuration for this key defaults to `inline`.


#### How to run tests

The following command is going to run tests for all sales bundles:

```
composer install

tests/app/console doctrine:schema:update --force

phpunit
```
