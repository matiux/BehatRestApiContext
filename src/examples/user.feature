Feature: Testing API
  In order to handle a User model
  As a user
  I want to able to handle basic CRUD operation on User controller

  Rules:
  - Create
  - Show
  - Update
  - Delete
  - List

  @single
  Scenario: Get User by ID
    Given that I want to find a "/v1/User/3"
    When I request a resource
    Then the response status code should be 200
    And the response type should be "application/json"
    And the response contains:
      """
      id
      name
      email
      role_id
      created_at
      updated_at
      deleted_at
      """
    And the response doesn't contains:
      """
      """

  @list
  Scenario: Get all User models
    Given that I want to find a "/v1/User"
    When I request a resource
    Then the response status code should be 200
    And the response type should be "application/json"
    And the response contains:
      """
      page
      total
      data
      """
    And "data" is a collection
    And each "data" item contains:
      """
      id
      name
      email
      role_id
      created_at
      updated_at
      deleted_at
      """

  @voidlist
  Scenario: Get a void collection of User
    Given that I want to find a "/v1/User"
    When I request a resource
    Then the response status code should be 200
    And the response type should be "application/json"
    And the response contains:
      """
      data
      """
    And "data" is a collection
    And "data" is void

  @update
  Scenario: Update User
    Given that I want update an existing "/v1/User/3" by method "PATCH" with values:
      | field | value         |
      | email | new@email.com |
      | name  | Brian         |
    When I request a resource
    Then the response status code should be 200
    And the response contains:
      """
      id
      name
      email
      role_id
      created_at
      updated_at
      deleted_at
      """
    And the response doesn't contains:
      """
      """

  @404
  Scenario: Get non exists User by ID
    Given that I want to find a "/v1/User/352352"
    When I request a resource
    Then the response status code should be 404
    And the response type should be "application/json"

  @create
  Scenario: Creating a new User
    Given that I want to add a new "/v1/User" with values:
      | field   | value            |
      | name    | Brian            |
      | email   | brian@domain.com |
      | role_id | 3                |
    When I request a resource
    Then the response status code should be 201
    And the response type should be "application/json"
    And the response contains:
      """
      id
      name
      email
      role_id
      created_at
      updated_at
      deleted_at
      """
    And the response doesn't contains:
      """
      """

  Scenario: Delete a User model
    Given that I want to delete "/v1/User/34":
    When I request a resource
    Then the response status code should be 200
