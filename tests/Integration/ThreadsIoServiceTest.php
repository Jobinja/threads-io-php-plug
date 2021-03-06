<?php
namespace Jobinja\ThreadsIo\Tests\Integration;

use Jobinja\ThreadsIo\Entities\Event;
use Jobinja\ThreadsIo\Entities\Page;
use Jobinja\ThreadsIo\Entities\User;
use Jobinja\ThreadsIo\Exceptions\ThreadsIoInvalidKeyException;
use Jobinja\ThreadsIo\Exceptions\ThreadsIoPlugException;
use Jobinja\ThreadsIo\ThreadsIoClient;
use Jobinja\ThreadsIo\ThreadsIoService;

class ThreadsIoServiceTest extends \PHPUnit_Framework_TestCase {

    public function getClient()
    {
        return new ThreadsIoClient($GLOBALS['eventKey']);
    }

    public function testInvalidKey()
    {
        $this->setExpectedException(ThreadsIoInvalidKeyException::class);
        $client = new ThreadsIoClient('SOME_RANDOM');
        $client->identify('SOME_RANDOM', new \DateTimeImmutable(), []);
    }

    public function testIdentify() {
        $client = $this->getClient();
        $service = new ThreadsIoService($client);
        $user = new User('testUser1', [
            "name"=>"Ritchie Blackmore",
            "instrument"=>"Guitar",
            "brands" => [
                "gibson",
                "squier",
                "fender"
            ]
        ]);

        // With no DateTime
        $result = $service->identify($user);
        $this->assertTrue($result, "The user identification works, with auto-generated DateTime!");
        // With DateTime
        $now = new \DateTimeImmutable();
        $result = $service->identify($user, $now);
        $this->assertTrue($result, "The user identification works!");
    }

    public function testTrack() {
        $now = new \DateTimeImmutable();

        $client = $this->getClient();
        $service = new ThreadsIoService($client);
        $event = new Event("Connected", ["toto"=>"tata"]);
        $user = new User('testUser1', [
            "name"=>"Ritchie Blackmore",
            "instrument"=>"Guitar",
            "brands" => [
                "gibson",
                "squier",
                "fender"
            ]
        ]);

        // With no DateTime
        $result = $service->track($user, $event);
        $this->assertTrue($result, "The user tracking works, with auto-generated DateTime!");
        // With DateTime
        $result = $service->track($user, $event, $now);
        $this->assertTrue($result, "The user tracking identification works!");
    }

    public function testPage() {
        $now = new \DateTimeImmutable();

        $client = $this->getClient();
        $service = new ThreadsIoService($client);
        $page = new Page("Welcome Page", ["toto"=>"tata"]);
        $user = new User('testUser1', [
            "name"=>"Ritchie Blackmore",
            "instrument"=>"Guitar",
            "brands" => [
                "gibson",
                "squier",
                "fender"
            ]
        ]);

        // With no DateTime
        $result = $service->page($user, $page, $now);
        $this->assertTrue($result, "The user page tracking works, with auto-generated DateTime!");
        // With DateTime
        $result = $service->page($user, $page);
        $this->assertTrue($result, "The user page tracking identification works!");
    }

    public function testRemove() {
        $client = $this->getClient();
        $service = new ThreadsIoService($client);
        $user = new User('testUser1', [
            "name"=>"Ritchie Blackmore",
            "instrument"=>"Guitar",
            "brands" => [
                "gibson",
                "squier",
                "fender"
            ]
        ]);

        // With no DateTime
        $result = $service->remove($user);
        $this->assertTrue($result, "The user removal works, with auto-generated DateTime!");
        // With DateTime
        $now = new \DateTimeImmutable();
        $result = $service->remove($user, $now);
        $this->assertTrue($result, "The user removal works!");
    }

    public function testExceptions() {
        $client = $this->getClient();
        $service = new ThreadsIoService($client);

        $user = new User('testUser1', "traits");
        $event = new Event("test", "properties");
        $page = new Page("Welcome Page", "properties");

        $triggeredError = false;

        try {
            $service->identify($user);
        }
        catch(ThreadsIoPlugException $e) {
            $triggeredError = true;
        }

        $this->assertTrue($triggeredError, "The identify function triggers errors correctly!");

        try {
            $triggeredError = false;
            $service->track($user, $event);
        }
        catch(ThreadsIoPlugException $e) {
            $triggeredError = true;
        }

        $this->assertTrue($triggeredError, "The track function triggers errors correctly!");

        try {
            $triggeredError = false;
            $service->page($user, $page);
        }
        catch(ThreadsIoPlugException $e) {
            $triggeredError = true;
        }

        $this->assertTrue($triggeredError, "The page function triggers errors correctly!");

        $service->remove($user);
    }
}
