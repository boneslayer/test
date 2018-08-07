Feature: test cucumber and mink


	Scenario: Test angular page		
		When I am on the homepage
		And I wait 2 seconds
		Then I should see 20 ".phones li" elements

		