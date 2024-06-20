<?php

interface Notification
{
  public function send(string $title, string $message): void;
}

class EmailNotification implements Notification
{
  private $adminEmail;
  
  public function send(string $title, string $message): void
  {
        echo "Sent email with title '$title' to '{$this->adminEmail}' that says '$message'.";
  }
}

class SlackApi
{
  private $login;
  private $apiKey;

  public function __construct(string $login, string $apiKey)
  {
    $this->login = $login;
    $this->apiKey = $apiKey;
  }

  public function logIn(): void
  {
    // Send authentication request to Slack web service.
    echo "Logged in to a slack account '{$this->login}'.\n";
  }

  public function sendMessage(string $chatId, string $message): void
  {
    // Send message post request to Slack web service.
    echo "Posted following message into the '$chatId' chat: '$message'.\n";
  }
}

class SlackNotification implements Notification
{

  public function __construct(private readonly SlackApi $slackApi, private readonly string $chatId){}

  public function send(string $title, string $message): void
  {
    $slackMessage = "#" . $title . "# " . strip_tags($message);
    $this->slackApi->login();
    $this->slackApi->sendMessage($this->chatId, $slackMessage);

  }
}

function clientCode(Notification $notification)
{
   echo $notification->send("Website is down!",
        "<strong style='color:red;font-size: 50px;'>Alert!</strong> " .
        "Our website is not responding. Call admins and bring it up!");
}

echo "Client code is designed correctly and works with email notifications:\n";
$notification = new EmailNotification("developers@example.com");
clientCode($notification);
echo "\n\n";


echo "The same client code can work with other classes via adapter:\n";
$slackApi = new SlackApi("example.com", "XXXXXXXX");
$notification = new SlackNotification($slackApi, "Example.com Developers");
clientCode($notification);
