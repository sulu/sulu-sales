CHANGELOG for Sulu Sales
========================

* 0.10.6 (2018-06-14)
    * BUGFIX      [CoreBundle]     Undid fix for the price calculation for products with variants as it causes problems in the checkout.

* 0.10.5 (2018-06-14)
    * BUGFIX      [CoreBundle]     Fixed price calculation for products with variants.

* 0.10.3 (2018-02-09)
    * BUGFIX      [OrderBundle]    Fixed check for permissions of sales-orders to be independent on language.

* 0.10.2 (2018-01-31)
    * BUGFIX      [OrderBundle]    Fixed bug when persisted order entity is required but only an updated version is provided by the manager.

* 0.10.1 (2018-01-23)
    * BUGFIX      [CoreBundle]     Fixed loading of taxClass from parents for product-variants.

* 0.10.0 (2018-01-12)
    * BUGFIX      [General]        Fixed test which was not working reliable.
    * BUGFIX      [General]        Fixed link to wkhtmltopdf in for travis.
    * FEATURE     [General]        Changed dependencies for Sulu and willdurand/hateoas-bundle to work with newer Sulu versions.

* 0.9.0 (2017-01-19)
    * FEATURE     [General]        Changed dependency for Sulu to work with version 1.3
    * FEATURE     [CoreBundle]     Added locale manager.
    * FEATURE     [OrderBundle]    Use of the locale manager.
    * FEATURE     [ShippingBundle] Use of the locale manager.

* 0.8.10 (2017-01-13)
    * BUGFIX      [CoreBundle]    Fixed size of transition overlay.

* 0.8.9 (2016-12-15)
    * ENHANCEMENT [CoreBundle]    Implemented functionality for handling product variants.

* 0.8.8 (2016-12-07)
    * BUGFIX      [CoreBundle]    Fixed doctrine schema.

* 0.8.7 (2016-11-28)
    * BUGFIX      [CoreBundle]    Fixed correct usage of totalPrice.

* 0.8.6 (2016-11-24)
    * ENHANCEMENT [CoreBundle]    Added field formOfAddress to BaseOrderAddress.

* 0.8.5 (2016-09-30)
    * BUGFIX      [OrderBundle]   Fixed simple account and simple contact widget to accept empty data.
    * BUGFIX      [OrderBundle]   Fixed sulu persistence for sales order.

* 0.8.4 (2016-09-27)
    * BUGFIX      [OrderBundle]   Fixed contacts dropdown in orders form when account has more than 10 contacts.
    * BUGFIX      [OrderBundle]   Fixed bug in tests (for current product-bundle)

* 0.6.3 (2016-09-27)
    * FEATURE     [OrderBundle]   Added csv export function to order list.
    * ENHANCEMENT [OrderBundle]   Extended order widget to be capable of missing contact or account data.

* 0.8.3 (2016-09-16)
    * BUGFIX      [OrderBundle]   Prepared pdf order manager for better extensibility.

* 0.8.2 (2016-09-15)
    * ENHANCEMENT [OrderBundle]   Made OrderFactory ready for extending.

* 0.8.1 (2016-09-14)
    * BUGFIX      [OrderBundle]   Fixed order entity class for inheritance.

* 0.8.0 (2016-09-13)
    * FEATURE     [OrderBundle]   Added functionality to generate pdf files of orders with a configurable template.
                                  Accessible over a website route.
    * FEATURE     [OrderBundle]   Adapted sales-order to support sulu persistence.

* 0.7.2 (2016-09-07)
    * BUGFIX      [OrderBundle]   ApiOrder: Added methods for all prices

* 0.7.1 (2016-09-07)
    * BUGFIX      [CoreBundle]    Built dist files

* 0.7.0 (2016-09-06)
    * FEATURE     [CoreBundle]    Renamed delivery cost to shipping costs
    * FEATURE     [OrderBundle]   Renamed delivery cost to shipping costs
    * ENHANCEMENT [OrderBundle]   Added prices to Order/OrderInterface

* 0.6.2 (2016-08-29)
    * ENHANCEMENT [CoreBundle]    Item table shows now elements for recurring prices
                                  only when `sulu_product.display_recurring_prices` is set to `true

* 0.6.1 (2016-08-24)
    * BUGFIX      [OrderBundle]   Fixed bug with empty customer type select
    * BUGFIX      [CoreBundle]    Fixed initial format of global price when adding an empty row to item-table.
    * BUGFIX      [CoreBundle]    Fixed bug in overlay in component editable-data-row
    * ENHANCEMENT [OrderBundle]   Adopted UI of form

* 0.6.0 (2016-08-23)
    * FEATURE     [OrderBundle]   Adopted UI for customer types
    * FEATURE     [CoreBundle]    Added model CustomerType and CustomerTypeManager
    * BUGFIX      [CoreBundle]    Style fixes in global total price of item-table.

* 0.5.1 (2016-08-22)
    * BUGFIX      [CoreBundle]    Fixed creation of empty cart.

* 0.5.0 (2016-08-22)
    * FEATURE     [CoreBundle]    Changed visual appearance of addons in item-table.
    * FEATURE     [CoreBundle]    Added option to not display currency in item-table.
    * BUGFIX      [CoreBundle]    Displaying first found tax class in item tables overlay.
    * FEATURE     [CoreBundle]    Display overlay (on click) also when item-table is disabled.
    * FEATURE     [CoreBundle]    Added recurring prices to item-table.
    * BUGFIX      [OrderBundle]   Fixed error when sidebar order-info is not defined in widgets config.
    * BUGFIX      [OrderBundle]   Fixed update of items in an order.

* 0.4.4 (2016-08-12)
    * FEATURE     [CoreBundle]    Added event that is triggered when order status changes.

* 0.4.3 (2016-07-28)
    * BUGFIX      [CoreBundle]    Set isRecurringPrice on items to fix calculation of order totals.

* 0.4.2 (2016-07-28)
    * BUGFIX      [General]       Set parent item if an addon is added.
    * BUGFIX      [CoreBundle]    Fixed relation between item and parent item.

* 0.4.1 (2016-07-19)
    * FEATURE     [General]       Updated for usage with recurring price calculation
    * BUGFIX      [CoreBundle]    Fixed creating independent items in item table caused by
                                  changes in pricing bundle
    * ENHANCEMENT [General]       Moved db diagram from sulu/docs to sales

* 0.4.0 (2016-07-06)
    * FEATURE     [CoreBundle]    Added type and addon to items

* 0.3.1 (2016-06-30)
    * FEATURE     [CoreBundle]    Added recurring price for items

* 0.3.0 (2016-06-30)
    * BUGFIX      [ShippingBundle] Fixed postbox addresses in ShippingBundle
    * BUGFIX      [General]        Fixed Tests in all bundles and added travis configuration
    * ENHANCEMENT [General]        Adapted Contact to ContactInterface in case inheritance
                                   is used
    * FEATURE     [CoreBundle]     Added handling of gross-prices to item-table
