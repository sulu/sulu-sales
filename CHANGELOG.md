CHANGELOG for Sulu Sales
========================

* dev-develop

    * FEATURE     [CoreBundle]    Renamed delivery cost to shipping costs
    * FEATURE     [OrderBundle]   Renamed delivery cost to shipping costs
    * ENHANCEMENT [OrderBundle]   Added prices to Order/OrderInterface

* 0.6.2 (2016-08-29)

    * ENHANCMENT  [CoreBundle]    Item table shows now elements for recurring prices
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
