<?php

abstract class SocialNetworkPoster
{

  abstract public function getSocialNetwork(): SocialNetworkConnector;

  public function post(string $content)
  {
    $network = $this->getSocialNetwork();
    $network->login();
    $network->createPost($content);
    $network->logout();
  }
}

class FacebookNetworkPoster extends SocialNetworkPoster
{
  private $email;
  private $password;

  public function __construct(string $email, string $password)
  {

    $this->email = $email;
    $this->password = $password;

  }

  public function getSocialNetwork(): SocialNetworkConnector
  {
    return new FacebookConnector($this->email, $this->password);
  }
}

class LinkedInNetworkPoster extends SocialNetworkPoster
{
  private $email;
  private $password;

  public function __construct(string $email, string $password)
  {

    $this->email = $email;
    $this->password = $password;

  }

  public function getSocialNetwork(): SocialNetworkConnector
  {
    return new LinkedInNetworkConnector($this->email, $this->password);
  }
}

interface SocialNetworkConnector
{
  public function login(): void;
  public function logout(): void;
  public function createPost($content):void;
}

class FacebookConnector implements SocialNetworkConnector
{

  private $email;
  private $password;

  public function __construct(string $email, string $password)
  {

    $this->email = $email;
    $this->password = $password;

  }

  public function logIn(): void
  {
    echo "Send HTTP API request to log in user $this->email with " .
      "password $this->password\n";
  }

  public function logOut(): void
  {
    echo "Send HTTP API request to log out user $this->email\n";
  }

  public function createPost($content): void
  {
    echo "Send HTTP API requests to create a post in Facebook timeline.\n";
  }
}

class LinkedInConnector implements SocialNetworkConnector
{

  private $email;
  private $password;

  public function __construct(string $email, string $password)
  {

    $this->email = $email;
    $this->password = $password;

  }

  public function logIn(): void
  {
    echo "Send HTTP API request to log in user $this->email with " .
      "password $this->password\n";
  }

  public function logOut(): void
  {
    echo "Send HTTP API request to log out user $this->email\n";
  }

  public function createPost($content): void
  {
    echo "Send HTTP API requests to create a post in LinkedIn timeline.\n";
  }
}


function clientCode(SocialNetworkPoster $poster)
{
  $poster->post("Hello world");
  $poster->post("I had a large hamburger today");
}

echo "Testing ConcreteCreator1:\n";
clientCode(new FacebookNetworkPoster("john_smith", "******"));
echo "\n\n";

