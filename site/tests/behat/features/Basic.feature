Feature: View Site

    Scenario: View the home page
        Given I am on "/"
        Then I should see "building strong developers"

    Scenario: Login to the site
        Given I am on "/register.php"
        Then I should see "Personal Data"