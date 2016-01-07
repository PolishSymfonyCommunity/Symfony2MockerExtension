Feature: Mocking services

  Scenario: Successful expectations
    Given I am working on an application that sends e-mails
    When I mock the mailer in my test
    And I call the action that would call the mailer
    Then the mock expectations should pass

  Scenario: Failing expectations
    Given I am working on an application that sends e-mails
    When I mock the mailer in my test
    And I call the action that would not call the mailer
    Then the mock expectations should fail