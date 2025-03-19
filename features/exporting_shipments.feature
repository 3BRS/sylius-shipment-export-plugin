@exporting_shipments
Feature: Exporting shipments
    In order to export shipments to carriers
    As an Administrator
    I want to be able to browse list of them and export them

    Background:
        Given the store operates on a single channel in "United States"
        And the store allows shipping with "Czech post" identified by "ceska-posta-do-ruky"
        And the store allows paying with "Cash on delivery"
        And the store has a product "EyePhone"
        And the guest customer placed order with "EyePhone" product for "Here" and "United States" based billing address with "Czech post" shipping method and "Cash on delivery" payment
        And I am logged in as an administrator

    @ui
    Scenario: Browsing shipments to export
        When I browse shipments export for shipping Czech post
        Then I should see 1 shipment in the list

    @ui
    Scenario: Exporting shipments for Czech post
        When I browse shipments export for shipping Czech post
        Then I can select download CSV from menu to export shipments for Czech post
