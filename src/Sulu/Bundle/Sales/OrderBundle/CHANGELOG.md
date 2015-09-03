# CHANGELOG

## unreleased:

### 2015-04-21

Refactoring and inheritance of order-bundle:

* RENAMED `Order::$contact` to `Order::$customerContact`
* RENAMED `Order::$account` to `Order::$customerAccount`

#### DEPLOY

##### 1. Step (SQL)

```{sql}
    SET FOREIGN_KEY_CHECKS=0;
    
    CREATE TABLE IF NOT EXISTS temp_sc_item LIKE sc_item;
    INSERT temp_sc_item SELECT * FROM sc_item;
    DROP TABLE IF EXISTS sc_item;
    
    CREATE TABLE IF NOT EXISTS temp_so_order_items LIKE so_order_items;
    INSERT temp_so_order_items SELECT * FROM so_order_items;
    DROP TABLE IF EXISTS so_order_items;
    
    CREATE TABLE IF NOT EXISTS temp_ss_shipping_items LIKE ss_shipping_items;
    INSERT temp_ss_shipping_items SELECT * FROM ss_shipping_items;
    DROP TABLE IF EXISTS ss_shipping_items;
```

##### 2. Step (CONSOLE)

Now in console type 

```
    app/console doctrine:schema:update --force
```

##### 3. Step (SQL)

```{sql}
    INSERT so_order_items SELECT * FROM temp_so_order_items;
    DROP TABLE IF EXISTS temp_so_order_items;

    INSERT ss_shipping_items SELECT * FROM temp_ss_shipping_items;
    DROP TABLE IF EXISTS temp_ss_shipping_items;

    INSERT INTO sc_items (id,name,number,quantity,quantityUnit,useProductsPrice,tax,price,discount,description,weight,width,height,length,supplierName,created,changed,bitmaskStatus,totalNetPrice,idAccountsSupplier,idProducts,idUsersChanger,idUsersCreator,idOrderAddressesDelivery) SELECT * FROM temp_sc_item;
    DROP TABLE IF EXISTS temp_sc_item;

    SET FOREIGN_KEY_CHECKS=1;
```

### 2015-01-12

Introduced order type:

to set your own order type, your data array in OrderManagers save() method must contain either

* $data['type'] = {ID}
    or
* $data['type']['id] = {ID}

All available Types are listed in Sulu/Bundle/Sales/OrderBundle/Entity/OrderType.php

#### 1. DEPLOY (console):
```
    app/console doctrine:fixtures:load --fixtures vendor/sulu/sales-order-bundle/Sulu/Bundle/Sales/OrderBundle/DataFixtures/ORM/OrderTypes --append
```
#### 2. DEPLOY (sql):
```
    UPDATE so_orders SET idOrderTypes = 1;
```
