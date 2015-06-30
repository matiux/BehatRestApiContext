Metodi per le features
============
- thatIWantToFindA($resource);
	* GET - Imposta l'endpoint della risorsa che voglio testare
    * Usage
    	
    	Given that I want to find a "/v1/Resource/3"
- thatIWantToAddANewWithValues($resource, TableNode $table);
	* POST - Imposta l'endpoint della risosta che voglio testare
	* Usage:
	
		Given that I want to add a new "/v1/Resource" with values:
- thatIWantToDelete($resource);
	* DELETE - Cancella una risorsa
	* Usage:
	
		Given that I want to delete "/v1/Resource/57":
- thatIWantUpdateAnExistingByMethodWithValues($resource, $method, TableNode $table);
	* PATCH | PUT - Imposta l'endpoint della risosta che voglio testare
	* Usage: 

		Given that I want update an existing "/v1/Resource/7" by method "PATCH|PUT" with values:
    	| field | value               |
    	| name  | CategoriaModificata |
- iRequestAResource();
	* Esegue la chiamata tramite guzzle
	* Usage:
	
		When I request a resource
- theResponseStatusCodeShouldBe($responseStatus);
	* Controlla lo status code del responso
	* Usage:
	
		Then the response status code should be 200
- theResponseTypeShouldBe($responseType);
	* Controlla il tipo del responso
	* Usage:
	
		And the response type should be "application/json"
- theResponseContains(PyStringNode $strings);
	* Verifica che il responso contenga determinate chiavi
	* Usage: 

		    And the response contains:
              """
              id
              name
              email
              """
- contains($arg1, PyStringNode $strings);
	* Verifica che un array contenga determinate chiavi
	* Usage:
		
		    And "data.results.0" contains:
              """
              id
              name
              email
              """
- theValueOf($what, $operator, $value);
	* Controlla il valore di uno specifico campo
	* Usage:
	
		And the value of "data.results.0.name" "=" "Peter"
- hasItems($arg1, $number);
	* Verifica il numero di elementi di un array
	* Usage: 
	
		And "data.results" has "6" items
- theResponseDoesnTContains(PyStringNode $strings);
	* Controlla che il responso non contega determinate chiavi
	* Usage:
	
		    And the response doesn't contains:
              """
              surname
              """
- isACollection($arg1);
	* Controllare che un elemento sia un array
	* Usage:
	
		And "data.results" is a collection
- eachItemContains($arg1, PyStringNode $strings);
	* Controlla che ogni elemento di una collezione contenga determinati elementi
	* Usage:
	
		And each "data.results" item contains:
              """
              id
              name
              email
              """
- isEmpty($arg1);
	* Verifica se un array è vuoto
	* Usage: 
	
		Then "data.results" is empty
