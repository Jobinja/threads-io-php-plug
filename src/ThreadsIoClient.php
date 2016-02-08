<?php

namespace Jobinja\ThreadsIo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Jobinja\ThreadsIo\Exceptions\ThreadsIoBadRequestException;
use Jobinja\ThreadsIo\Exceptions\ThreadsIoInvalidKeyException;
use Jobinja\ThreadsIo\Exceptions\ThreadsIoPlugException;
use Jobinja\ThreadsIo\Exceptions\ThreadsIoServerException;
use Jobinja\ThreadsIo\Response\Response;

/**
 * This is an abstraction layer between the Service and the HTTP Requests
 * It implements the basic methods of the Threads.io API
 *
 * Class ThreadsIoClient
 *
 * @package Jobinja\ThreadsIo
 */
class ThreadsIoClient
{
    /**
     * Threads endpoint url
     */
    const END_POINT = 'https://input.threads.io/v1/';

    /**
     * Keyword action for user identification
     */
    const IDENTIFY_ACTION = "identify";

    /**
     * Keyword action for event tracking
     */
    const TRACK_ACTION = "track";

    /**
     * Keyword action for a page visit
     */
    const VISIT_ACTION = "page";

    /**
     * Keyword action for user removal
     */
    const REMOVE_ACTION = "remove";

    /**
     * The Guzzle client (v5) used to make HTTP Requests
     *
     * @var Client
     */
    private $httpClient;

    /**
     * The API Key provided by Threads.io to connect to your account
     *
     * @var string
     */
    private $eventKey;

    /**
     * Indicates that should the client mock external requests for
     * development or not.
     *
     * @var bool
     */
    protected $mock = false;

    /**
     * Client constructor
     *
     * @param string $eventKey
     * @param null   $endPoint
     * @param bool   $mock
     */
    public function __construct($eventKey, $endPoint = null, $mock = false)
    {
        $this->setEventKey($eventKey);

        // Setting up base API URL and Basic Authentication
        $this->setHttpClient(new Client([
            'base_uri' => $endPoint ?: static::END_POINT,
            RequestOptions::AUTH => [$this->getEventKey(), ""],
        ]));
    }

    /**
     * Basic implementation of the API method "identify"
     *
     * @param string             $userId
     * @param \DateTimeImmutable $datetime
     * @param array              $traits
     *
     * @return Response
     * @throws ThreadsIoPlugException
     */
    public function identify($userId, \DateTimeImmutable $datetime, $traits)
    {
        if (!is_array($traits)) {
            throw new ThreadsIoPlugException("The traits you passed to the user are wrong. Please verify its format (array) or values.");
        }

        $params = [
            "userId"    => $userId,
            "timestamp" => $this->formatDate($datetime),
            "traits"    => $traits,
        ];
        $request = $this->createRequest(self::IDENTIFY_ACTION, $params);
        return $this->call($request);
    }

    /**
     * Basic implementation of the API method "track"
     *
     * @param string             $userId
     * @param string             $event
     * @param \DateTimeImmutable $datetime
     * @param array              $properties
     *
     * @return Response
     * @throws ThreadsIoPlugException
     */
    public function track($userId, $event, \DateTimeImmutable $datetime, $properties)
    {

        if (!is_array($properties)) {
            throw new ThreadsIoPlugException("The properties you passed to the tracking function are wrong. Please verify its format (array) or values.");
        }

        $request = $this->createRequest(self::TRACK_ACTION, [
            "userId"     => $userId,
            "event"      => $event,
            "timestamp"  => $this->formatDate($datetime),
            "properties" => $properties,
        ]);
        return $this->call($request);
    }

    /**
     * Basic implementation of the API method "page"
     *
     * @param string             $userId
     * @param string             $name
     * @param array              $properties
     * @param \DateTimeImmutable $datetime
     *
     * @return Response
     * @throws ThreadsIoPlugException
     */
    public function page($userId, $name, $properties, \DateTimeImmutable $datetime)
    {

        if (!is_array($properties)) {
            throw new ThreadsIoPlugException("The properties you passed to the page function are wrong. Please verify its format (array) or values.");
        }

        $request = $this->createRequest(self::VISIT_ACTION, [
            "eventKey"   => $this->getEventKey(),
            "userId"     => $userId,
            "name"       => $name,
            "properties" => $properties,
            "timestamp"  => $this->formatDate($datetime),
        ]);
        return $this->call($request);
    }

    /**
     * Basic implementation of the API method "remove"
     *
     * @param                    $userId
     * @param \DateTimeImmutable $datetime
     *
     * @return Response
     */
    public function remove($userId, \DateTimeImmutable $datetime)
    {
        $request = $this->createRequest(self::REMOVE_ACTION, [
            "timestamp" => $this->formatDate($datetime),
            "userId"    => $userId,
        ]);
        return $this->call($request);
    }

    /**
     * Executes the HTTP Request given in argument
     *
     * @param array $request
     *
     * @return Response
     */
    private function call($request)
    {
        if ($this->isMockMode()) {
            return $this->mockResponseForRequest($request);
        }

        try {
            $response = $this->getHttpClient()->request($request['method'], $request['action'], $request['params']);
            return new Response($response->getBody());
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 401) {
                throw new ThreadsIoInvalidKeyException($e);
            }
            throw new ThreadsIoBadRequestException($e);
        } catch (ServerException $e) {
            throw new ThreadsIoServerException($e);
        }
    }

    /**
     * Mock response for given request
     *
     * @param array $request
     * @return Response
     */
    private function mockResponseForRequest($request)
    {
        return new Response(json_encode(['success' => true]));
    }

    /**
     * Indicates that request is in mock mode
     *
     * @return bool
     */
    private function isMockMode()
    {
        return (bool)$this->mock;
    }

    /**
     * Format the date based on the ISO 8601 date formats
     *
     * @see https://docs.threads.io/docs/threads-timestamp-format
     *
     * @param \DateTimeImmutable $datetime
     *
     * @return string
     */
    private function formatDate(\DateTimeImmutable $datetime)
    {
        return str_replace('+00:00', '.000Z', $datetime->setTimezone(new \DateTimeZone('UTC'))->format('c'));
    }

    /**
     * Prepare the Request to be executed by $this->call() method
     *
     * @param        $action
     *
     * @param array  $params
     * @param string $method
     * @return array
     */
    private function createRequest($action, array $params = [], $method = 'POST')
    {
        return [
            'method' => $method,
            'action' => $action,
            'params' => ['json' => $params]
        ];
    }

    /**
     * Getter for $httpClient
     *
     * @return Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Setter for $httpClient
     *
     * @param Client $httpClient
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Getter for $eventKey
     *
     * @return string
     */
    public function getEventKey()
    {
        return $this->eventKey;
    }

    /**
     * Setter for $eventKey
     *
     * @param string $eventKey
     */
    public function setEventKey($eventKey)
    {
        $this->eventKey = $eventKey;
    }
}