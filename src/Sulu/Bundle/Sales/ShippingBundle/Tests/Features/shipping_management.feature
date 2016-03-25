@shipping_management
Feature: Manage shippings
    In order to manage shippings in the system
    As a user
    I want to be able to list, edit, delete and create shippings

    Background:
        Given I am logged in as an administrator with locale 'de'

    Scenario: List shippings
        Given I am on "/admin/#sales/shippings"
        And I wait and expect to see element "#shippings-list .husky-table"
        Then I wait that the ".sulu-title" element should contain "Lieferungen"
        And I expect a data grid to appear
        And I should see an ".husky-table tbody tr" element
        And I wait that the "#shippings-list" element should contain "00001"
