<?php
interface Command
{
  public function execute(): void;

  public function getId(): int;

  public function getStatus(): int;
}

abstract class WebScrapingCommand implements Command
{
  public $id;

  public $status = 0;

  /**
   * @var string URL for scraping.
   */
  public $url;

  public function __construct(string $url)
  {
    $this->url = $url;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getStatus(): int
  {
    return $this->status;
  }

  public function getURL(): string
  {
    return $this->url;
  }

  public function execute(): void
  {
    $html = $this->download();
    $this->parse($html);
    $this->complete();
  }

  public function download(): string
  {
    $html = file_get_contents($this->getURL());
    echo "WebScrapingCommand: Downloaded {$this->url}\n";

    return $html;
  }

  abstract public function parse(string $html): void;

  public function complete(): void
  {
    $this->status = 1;
    Queue::get()->completeCommand($this);
  }
}

class IMDBGenresScrapingCommand extends WebScrapingCommand
{
  public function __construct()
  {
    $this->url = "https://www.imdb.com/feature/genre/";
  }

  /**
   * Extract all genres and their search URLs from the page:
   * https://www.imdb.com/feature/genre/
   */
  public function parse($html): void
  {
    preg_match_all("|href=\"(https://www.imdb.com/search/title\?genres=.*?)\"|", $html, $matches);
    echo "IMDBGenresScrapingCommand: Discovered " . count($matches[1]) . " genres.\n";

    foreach ($matches[1] as $genre) {
      Queue::get()->add(new IMDBGenrePageScrapingCommand($genre));
    }
  }
}

class IMDBGenrePageScrapingCommand extends WebScrapingCommand
{
  private $page;

  public function __construct(string $url, int $page = 1)
  {
    parent::__construct($url);
    $this->page = $page;
  }

  public function getURL(): string
  {
    return $this->url . '?page=' . $this->page;
  }

  /**
   * Extract all movies from a page like this:
   * https://www.imdb.com/search/title?genres=sci-fi&explore=title_type,genres
   */
  public function parse(string $html): void
  {
    preg_match_all("|href=\"(/title/.*?/)\?ref_=adv_li_tt\"|", $html, $matches);
    echo "IMDBGenrePageScrapingCommand: Discovered " . count($matches[1]) . " movies.\n";

    foreach ($matches[1] as $moviePath) {
      $url = "https://www.imdb.com" . $moviePath;
      Queue::get()->add(new IMDBMovieScrapingCommand($url));
    }

    // Parse the next page URL.
    if (preg_match("|Next &#187;</a>|", $html)) {
      Queue::get()->add(new IMDBGenrePageScrapingCommand($this->url, $this->page + 1));
    }
  }
}
class IMDBMovieScrapingCommand extends WebScrapingCommand
{
  /**
   * Get the movie info from a page like this:
   * https://www.imdb.com/title/tt4154756/
   */
  public function parse(string $html): void
  {
    if (preg_match("|<h1 itemprop=\"name\" class=\"\">(.*?)</h1>|", $html, $matches)) {
      $title = $matches[1];
    }
    echo "IMDBMovieScrapingCommand: Parsed movie $title.\n";
  }
}

class MockQueue
{

  private $db = [];

  public function __construct()
  {
  }

  public function isEmpty(): bool
  {
    return count($this->db) === 0;
  }

  public function add(Command $command): void
  {
    $this->db[] = $command;
  }

  public function getCommand(): Command
  {
    return array_shift($this->db);
  }

  public function completeCommand(Command $command): void
  {
    echo "Complete Command \n";
  }

  public function work(): void
  {
    while (!$this->isEmpty()) {
      $command = $this->getCommand();
      $command->execute();
    }
  }

  /**
   * For our convenience, the Queue object is a Singleton.
   */
  public static function get(): Queue
  {
    static $instance;
    if (!$instance) {
      $instance = new Queue();
    }

    return $instance;
  }
}
class Queue
{
  private $db;

  public function __construct()
  {
    $this->db = new \SQLite3(
      __DIR__ . '/commands.sqlite',
      SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE
    );

    $this->db->query('CREATE TABLE IF NOT EXISTS "commands" (
            "id" INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
            "command" TEXT,
            "status" INTEGER
        )');
  }

  public function isEmpty(): bool
  {
    $query = 'SELECT COUNT("id") FROM "commands" WHERE status = 0';

    return $this->db->querySingle($query) === 0;
  }

  public function add(Command $command): void
  {
    $query = 'INSERT INTO commands (command, status) VALUES (:command, :status)';
    $statement = $this->db->prepare($query);
    $statement->bindValue(':command', base64_encode(serialize($command)));
    $statement->bindValue(':status', $command->getStatus());
    $statement->execute();
  }

  public function getCommand(): Command
  {
    $query = 'SELECT * FROM "commands" WHERE "status" = 0 LIMIT 1';
    $record = $this->db->querySingle($query, true);
    $command = unserialize(base64_decode($record["command"]));
    $command->id = $record['id'];

    return $command;
  }

  public function completeCommand(Command $command): void
  {
    $query = 'UPDATE commands SET status = :status WHERE id = :id';
    $statement = $this->db->prepare($query);
    $statement->bindValue(':status', $command->getStatus());
    $statement->bindValue(':id', $command->getId());
    $statement->execute();
  }

  public function work(): void
  {
    while (!$this->isEmpty()) {
      $command = $this->getCommand();
      $command->execute();
    }
  }

  /**
   * For our convenience, the Queue object is a Singleton.
   */
  public static function get(): Queue
  {
    static $instance;
    if (!$instance) {
      $instance = new Queue();
    }

    return $instance;
  }
}

/**
 * The client code.
 */

$queue = Queue::get();

if ($queue->isEmpty()) {
  $queue->add(new IMDBGenresScrapingCommand());
}

$queue->work();
