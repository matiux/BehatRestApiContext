<?php namespace Matiux\Features\Bootstrap;

use Matiux\Types\String;

use Behat\Behat\Tester\Exception\PendingException,
    Behat\Behat\Context\Context,
    Behat\Behat\Context\SnippetAcceptingContext,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use GuzzleHttp\Client;

use PHPUnit_Framework_Assert;

class RestContext implements Context, SnippetAcceptingContext, RestContextInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $requestMethod;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $baseApiUrl;

    /**
     * @var \GuzzleHttp\Message\Response
     */
    protected $response;

    protected $body;

    /**
     * @var array
     */
    protected $toSendData = [];

    public function __construct($baseUrl)
    {
        $this->baseApiUrl       = $baseUrl;

        $this->client           = new Client([

            'base_url' => $this->baseApiUrl
        ]);
    }

    /**
     * GET - Imposta l'endpoint della risorsa che voglio testare
     * Given that I want to find a "/v1/SephirothMailConfigs/3"
     *
     * @Given that I want to find a :resource
     */
    public function thatIWantToFindA($resource)
    {
        $this->requestMethod    = self::METHOD_GET;
        $this->resource         = $resource;
    }

    /**
     * POST - Imposta l'endpoint della risosta che voglio testare
     * Given that I want to add a new "/v1/SiteUser" with values:
     *   | field         | value                  |
     *   | config_id     |                        |
     *   | email         | prova@areariservata.it |
     *
     * @Given that I want to add a new :resource with values:
     */
    public function thatIWantToAddANewWithValues($resource, TableNode $table)
    {
        $this->requestMethod    = self::METHOD_POST;
        $this->resource         = $resource;
        $this->toSendData       = $this->prepareToSendData($table->getColumnsHash());
    }

    /**
     * PATCH | PUT - Imposta l'endpoint della risosta che voglio testare
     * Given that I want update an existing "/v1/SiteTwigCategories/7" by method "PATCH" with values:
     *   | field | value               |
     *   | name  | CategoriaModificata |
     *
     * @Given that I want update an existing :resource by method :method with values:
     */
    public function thatIWantUpdateAnExistingByMethodWithValues($resource, $method, TableNode $table)
    {
        $this->requestMethod    = constant("self::METHOD_$method");
        $this->resource         = $resource;
        $this->toSendData       = $this->prepareToSendData($table->getColumnsHash());
    }

    private function prepareToSendData($toSendData)
    {
        $postArray = [];

        foreach ($toSendData as $index => $data) {

            $value = new String($data['value']);

            if ($value->contains('array,'))
                $data['value'] = $this->handleArrayValue($data['value']);

            if (!strstr($data['field'], '.')) {

                $postArray[$data['field']] = $data['value'];

            } else {

                $field      = new String($data['field']);
                $postArray  = $field->insertInArrayByPath($postArray, $data['value'], true);
            }
        }

        return $postArray;
    }

    private function handleArrayValue($value)
    {
        $prepared       = [];
        $values         = explode(',', $value);

        array_shift($values);

        foreach ($values as $i => $value) {

            unset($values[$i]);

            $value              = explode('=', $value);

            if (1 == count($value)) {
                array_push($prepared, is_numeric(current($value)) ? (int)current($value) : current($value));
            }
            else {
                $prepared[$value[0]] = is_numeric($value[1]) ? (int)$value[1] : $value[1];
            }
        }

        return $prepared;
    }

    /**
     * Esegue la chiamata
     * When I request a resource
     *
     * @When I request a resource
     */
    public function iRequestAResource()
    {
        try {

            $this->buildRequest();

        } catch(\GuzzleHttp\Exception\ClientException $e) {

            $this->response = new \GuzzleHttp\Message\Response(
                $e->getResponse()->getStatusCode(),
                ['content-type' => $e->getResponse()->getHeader('content-type')]
            );

        }

        PHPUnit_Framework_Assert::assertNotEquals(null, $this->response, "The response is null");
        PHPUnit_Framework_Assert::assertTrue(is_a($this->response, 'GuzzleHttp\Message\Response'), 'The response is not of GuzzleHttp\Message\Response type');
    }

    private function buildRequest()
    {
        switch($this->requestMethod) {

            case 'GET':
                $this->response = $this->client->get($this->resource);
                break;
            case 'PATCH':
                $this->response = $this->client->patch($this->resource, ['json' => $this->toSendData]);
                break;
            case 'PUT':
                $this->response = $this->client->put($this->resource, ['json' => $this->toSendData]);
                break;
            case 'POST':
                $this->response = $this->client->post($this->resource, ['json' => $this->toSendData]);
                break;
        }

        $this->body = json_decode($this->response->getBody(), true);
    }

    /**
     * Controlla lo status code del responso
     * Then the response status code should be 404
     *
     * @Then the response status code should be :arg1
     */
    public function theResponseStatusCodeShouldBe($responseStatus)
    {
        PHPUnit_Framework_Assert::assertEquals($responseStatus, $this->response->getStatusCode(), 'The response status is not equal');
    }

    /**
     * Controlla il tipo del responso
     * And the response type should be "application/json"
     *
     * @Then the response type should be :arg1
     */
    public function theResponseTypeShouldBe($responseType)
    {
        PHPUnit_Framework_Assert::assertEquals($responseType, $this->response->getHeader('content-type'), 'The response status is not '.$responseType);
    }

    /**
     * Verifica che il responso contenga determinati valori
     * And the response contains:
     *
     * @Then the response contains:
     */
    public function theResponseContains(PyStringNode $strings)
    {
        $strings    = $strings->getStrings();

        foreach ($strings as $key) {

            PHPUnit_Framework_Assert::assertArrayHasKey($key, $this->body, "$key doesn't exist");
        }
    }

    /**
     * Verifica che un array contenga determinati valori
     * And each "configValues.mandrill" item contains:
     *
     * @Then :arg1 contains:
     */
    public function contains($arg1, PyStringNode $strings)
    {
        $path       = new String($arg1);
        $array      = $path->pathToArray($this->body);
        $strings    = $strings->getStrings();

        foreach ($strings as $key) {

            PHPUnit_Framework_Assert::assertArrayHasKey($key, $array, "$key doesn't exist");
        }
    }

    /**
     * Controlla il valore di uno specifico campo
     * And the value of "result" "=" "success"
     *
     * @Then the value of :what :operator :value
     */
    public function theValueOf($what, $operator, $value)
    {
        $path           = new String($what);
        $responseValue  = $path->pathToArray($this->body);

        switch ($operator) {

            case '=':
                PHPUnit_Framework_Assert::assertEquals($value, $responseValue);
                break;
        }
    }

    /**
     * Verifica il numero di elementi di un array
     * And "configValues.mailgun" has "7" items
     *
     * @Then :arg1 has :arg2 items
     */
    public function hasItems($arg1, $number)
    {
        $path       = new String($arg1);
        $array      = $path->pathToArray($this->body);

        PHPUnit_Framework_Assert::assertTrue($number == count($array));
    }

    /**
     * Controlla che il respondo non contega determinati campi
     * And the response doesn't contains:
     *
     * @Then the response doesn't contains:
     */
    public function theResponseDoesnTContains(PyStringNode $strings)
    {
        $strings    = $strings->getStrings();

        foreach ($strings as $key) {

            PHPUnit_Framework_Assert::assertArrayNotHasKey($key, $this->body, "$key exist!!");
        }
    }

    /**
     * Controllare che un elemento sia un array
     * And "values" is a collection
     *
     * @Then :arg1 is a collection
     */
    public function isACollection($arg1)
    {
        $path       = new String($arg1);
        $array      = $path->pathToArray($this->body);

        PHPUnit_Framework_Assert::assertTrue(is_array($array));

        //throw new PendingException();
    }

    /**
     * Controlla che un elemento sia un aoggetto
     *
     * @Then :arg1 is a object
     */
    public function isAObject($arg1)
    {
        $path       = new String($arg1);
        $array      = $path->pathToArray($this->body);

        PHPUnit_Framework_Assert::assertTrue(is_array($array));
    }

    /**
     * Controlla che ogni elemento di una collezione contenga determinati elementi
     * And each "values" item contains:
     *   """
     *   id
     *   name
     *   email
     *   """
     *
     * @Then each :arg1 item contains:
     */
    public function eachItemContains($arg1, PyStringNode $strings)
    {
        $path       = new String($arg1);
        $strings    = $strings->getStrings();
        $array      = $path->pathToArray($this->body);

        foreach ($array as $item) {

            foreach ($strings as $key) {

                PHPUnit_Framework_Assert::assertArrayHasKey($key, $item, "$key doesn't exist in $path");
            }
        }

        //throw new PendingException();
    }

    /**
     * Cancella una risorsa
     *
     * @Given that I want to delete :arg1:
     */
    public function thatIWantToDelete($arg1)
    {
        throw new PendingException();
    }
}
