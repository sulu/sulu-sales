CHANGELOG for Sulu Sales
========================
* dev-develop

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
