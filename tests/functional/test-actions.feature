Feature: Panther Actions
  Scenario: View homepage
    Given I am on the homepage
    Then the page title should contain "Index page"

  Scenario: Go to another page
    Given I am on the homepage
    Then I should be on "/"
    When I go to "other-page.html"
    Then the page title should contain "Other"

  Scenario: Matches address by pattern
    Given I am on "other-page.html"
    Then the url should match "#page\.html$#"

  Scenario: Current address is homepage
    Given I am on the homepage
    Then I should be on the homepage

  Scenario: Follow links
    Given I am on the homepage
    When I follow "Next page"
    Then I should be on "other-page.html"

  Scenario: Press a button
    Given I am on the homepage
    When I press "Submit"
    Then the url should contain "other-page.html"

  Scenario: See a button
    Given I am on the homepage
    Then I should see the button "Submit"

  Scenario: Not see a button
    Given I am on the homepage
    Then I should not see the button "not here"

  Scenario: See a link
    Given I am on the homepage
    Then I should see the link "Next page"

  Scenario: Not see a link
    Given I am on the homepage
    Then I should not see the link "not here"

  Scenario: Page contains text
    Given I am on the homepage
    Then I should see "Page body"

  Scenario: Page does not contain text
    Given I am on the homepage
    Then I should not see "Something else"

  Scenario: Page contains pattern
    Given I am on the homepage
    Then I should see text matching "#Page#"

  Scenario: Page does not contain pattern
    Given I am on the homepage
    Then I should not see text matching "#Something#"

  Scenario: Response contains
    Given I am on the homepage
    Then the response should contain "<h1>"

  Scenario: Submit a form
    Given I am on the homepage
    When I submit the "form" form
    Then the url should contain "other-page.html"
