Feature: Protractor Test 2
    

#case: 2 test: 3
    
    Scenario: Test full result
        Given I on "/"
        Then I should see "20" results
#case: 3 test: 4
    
    Scenario: Test search field
        Given I am on "/"
        And undefined expected
        When I fill "query" with "Xoom"
        I should see "2" results
