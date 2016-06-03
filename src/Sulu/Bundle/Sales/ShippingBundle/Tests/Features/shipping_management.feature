@shipping_management
Feature: Manage shippings
    In order to manage shippings in the system
    As a user
    I want to be able to list, edit, delete and create shippings

    Background:
        Given I am logged in as an administrator with default locale

    Scenario: List shippings
        Given I am on "/admin/#sales/shippings"
        And I wait and expect to see element "#shippings-list .husky-table"
        Then I wait that the ".sulu-title" element should contain "Lieferungen"
        And I expect a data grid to appear
        And I should see an ".husky-table tbody tr" element
        And I wait that the "#shippings-list" element should contain "00001"

    Scenario: List shippings and open edit page
        Given I am on "/admin/#sales/shippings"
        And I wait and expect to see element "#shippings-list .husky-table"
        When I click the edit icon in the row containing "00001"
        Then wait that the url should match "/admin/#sales/shippings/edit:1/details"
        And I wait and expect to see element "#shipping-form"

    Scenario: Delete shipping in edit page
        Given I am on "/admin/#sales/shippings/edit:2/details"
        And I wait and expect to see element "#shipping-form"
        And I click the trash icon
        And I expect a confirmation dialog to appear
        And I confirm
        And wait that the url should match "/admin/#sales/shippings"
        And I wait and expect to see element "#shippings-list .husky-table"
        Then I should not see "00002"

    Scenario: Switch to shippings tab in sales order detail view
        Given I am on "/admin/#sales/orders/edit:1/details"
        And I wait and expect to see element "#order-form"
        When I click the tab item "Lieferungen"
        Then wait that the url should match "/admin/#sales/orders/edit:1/shippings"
        And I wait and expect to see element "#shippings-list"

    Scenario: Create a shipping from a sales order with success
        Given I am on "/admin/#sales/orders/edit:1/shippings"
        And I wait and expect to see element "#shippings-list .husky-datagrid"
        And I click element ".toolbar-item[data-id=add]"
        And wait that the url should match "/admin/#sales/orders/edit:1/shippings/add"
        And I wait and expect to see element "#shipping-form"
        And I wait that the "#delivery-address" element should contain "Kitzbühel"
        And I click the save icon
        And wait that the url should match "/admin/#sales/shippings/edit"
        Then I wait that the ".sulu-title" element should contain "Lieferung #"
