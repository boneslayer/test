Feature: Behat Test
    

#case: 4 test: 6
    Scenario: Test full results
        Given I am on "/"
        Then I should see 20 ".phones li" elements
#case: 6 test: 7
    Scenario: Test search field
        Given I am on "/"
        Then I should see 20 ".phones li" elements
        When I fill and keyup in ".col-md-2 input" element with "Xoom"
        And undefined expected
        And I wait "500" milliseconds
        Then I should see 2 ".phones li" elements
