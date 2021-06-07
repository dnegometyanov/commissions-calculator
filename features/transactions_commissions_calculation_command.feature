Feature: Transactions commissions calculation command
  In order to calculate transaction commissions
  As a backoffice employee
  I want to execute shell command with given transactions input data and get transaction amounts.

  Scenario: Execute shell command with given transactions input data and get calculated commissions amounts
    When I run "php src/index.php input.csv"
    And I see
    """
    0.60
    3.00
    0.00
    0.06
    1.50
    0
    0.68
    0.30
    0.30
    3.00
    0.00
    0.00
    8601
    """