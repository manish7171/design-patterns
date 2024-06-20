<?php

interface InputFormat
{
  public function formatText(string $text): string;
}

class TextInput implements InputFormat
{

  public function formatText(string $text): string
  {
    return $text;
  }
}

class TextFormat implements InputFormat
{
  protected $inputFormat;

  public function __construct(InputFormat $inputFormat)
  {
    $this->inputFormat = $inputFormat;
  }

  public function formatText(string $text): string
  {
    return $this->inputFormat->formatText($text);
  }
}

class PlainTextFilter extends TextFormat
{
  public function formatText(string $text): string
  {
    $text = parent::formatText($text);

    return strip_tags($text);
  }
}

class DangerousHTMLTagsFilter extends TextFormat
{
  private $dangerousTagPatterns = [
    "|<script.*?>([\s\S]*)?</script>|i", // ...
  ];

  private $dangerousAttributes = [
    "onclick", "onkeypress", // ...
  ];
  public function formatText(string $text): string
  {
    $text = parent::formatText($text);

    foreach ($this->dangerousTagPatterns as $pattern) {
      $text = preg_replace($pattern, '', $text);
    }

    foreach ($this->dangerousAttributes as $attribute) {
      $text = preg_replace_callback('|<(.*?)>|', function ($matches) use ($attribute) {
        $result = preg_replace("|$attribute=|i", '', $matches[1]);
        return "<" . $result . ">";
      }, $text);
    }

    return $text;  
  }
}

class MarkdownFormat extends TextFormat
{
  public function formatText(string $text): string
  {
    $text = parent::formatText($text);

    // Format block elements.
    $chunks = preg_split('|\n\n|', $text);
    foreach ($chunks as &$chunk) {
      // Format headers.
      if (preg_match('|^#+|', $chunk)) {
        $chunk = preg_replace_callback('|^(#+)(.*?)$|', function ($matches) {
          $h = strlen($matches[1]);
          return "<h$h>" . trim($matches[2]) . "</h$h>";
        }, $chunk);
      } // Format paragraphs.
      else {
        $chunk = "<p>$chunk</p>";
      }
    }
    $text = implode("\n\n", $chunks);

    // Format inline elements.
    $text = preg_replace("|__(.*?)__|", '<strong>$1</strong>', $text);
    $text = preg_replace("|\*\*(.*?)\*\*|", '<strong>$1</strong>', $text);
    $text = preg_replace("|_(.*?)_|", '<em>$1</em>', $text);
    $text = preg_replace("|\*(.*?)\*|", '<em>$1</em>', $text);

    return $text;
  }
}

function displayCommentAsAWebsite(InputFormat $format, string $text)
{
    echo $format->formatText($text);
}

$dangerousComment = <<<HERE
Hello! Nice blog post!
Please visit my <a href='http://www.iwillhackyou.com'>homepage</a>.
<script src="http://www.iwillhackyou.com/script.js">
  performXSSAttack();
</script>
HERE;

/**
 * Naive comment rendering (unsafe).
 */
$naiveInput = new TextInput();
echo "Website renders comments without filtering (unsafe):\n";
displayCommentAsAWebsite($naiveInput, $dangerousComment);
echo "\n\n\n";
