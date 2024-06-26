<?php
class Subject implements \SplSubject
{
  /**
   * @var int For the sake of simplicity, the Subject's state, essential to
   * all subscribers, is stored in this variable.
   */
  public $state;

  /**
   * @var \SplObjectStorage List of subscribers. In real life, the list of
   * subscribers can be stored more comprehensively (categorized by event
   * type, etc.).
   */
  private $observers;

  public function __construct()
  {
    $this->observers = new \SplObjectStorage();
  }

  /**
   * The subscription management methods.
   */
  public function attach(\SplObserver $observer): void
  {
    echo "Subject: Attached an observer.\n";
    $this->observers->attach($observer);
  }

  public function detach(\SplObserver $observer): void
  {
    $this->observers->detach($observer);
    echo "Subject: Detached an observer.\n";
  }

  /**
   * Trigger an update in each subscriber.
   */
  public function notify(): void
  {
    echo "Subject: Notifying observers...\n";
    foreach ($this->observers as $observer) {
      $observer->update($this);
    }
  }

  /**
   * Usually, the subscription logic is only a fraction of what a Subject can
   * really do. Subjects commonly hold some important business logic, that
   * triggers a notification method whenever something important is about to
   * happen (or after it).
   */
  public function someBusinessLogic(): void
  {
    echo "\nSubject: I'm doing something important.\n";
    $this->state = rand(0, 10);

    echo "Subject: My state has just changed to: {$this->state}\n";
    $this->notify();
  }
}

class ConcreteObserverA implements \SplObserver
{
  public function update(\SplSubject $subject): void
  {
    if ($subject->state < 3) {
      echo "ConcreteObserverA: Reacted to the event.\n";
    }
  }
}

class ConcreteObserverB implements \SplObserver
{
  public function update(\SplSubject $subject): void
  {
    if ($subject->state == 0 || $subject->state >= 2) {
      echo "ConcreteObserverB: Reacted to the event.\n";
    }
  }
}

/**
 * The client code.
 */

$subject = new Subject();

$o1 = new ConcreteObserverA();
$subject->attach($o1);

$o2 = new ConcreteObserverB();
$subject->attach($o2);

$subject->someBusinessLogic();
$subject->someBusinessLogic();

$subject->detach($o2);

$subject->someBusinessLogic();
