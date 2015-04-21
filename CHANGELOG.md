
unreleased:
===========



2015-04-21
==========
Refactoring and inheritance of order-bundle:

* RENAMED `Order::$contact` to `Order::$customerContact`
* RENAMED `Order::$account` to `Order::$customerAccount`

2015-01-12
==========
Introduced order type:

to set your own order type, your data array in OrderManagers save() method must contain either

* $data['type'] = {ID}
    or
* $data['type']['id] = {ID}

All available Types are listed in Sulu/Bundle/Sales/OrderBundle/Entity/OrderType.php

* DEPLOY (console):
```
    app/console doctrine:fixtures:load --fixtures vendor/sulu/sales-order-bundle/Sulu/Bundle/Sales/OrderBundle/DataFixtures/ORM/OrderTypes --append
```
* DEPLOY (sql):
```
    UPDATE so_orders SET idOrderTypes = 1;
```
