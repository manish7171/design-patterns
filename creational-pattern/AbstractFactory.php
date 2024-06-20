<?php

interface TemplateFactory
{
  public function createTitleTemplate(): TitleTemplate;

  public function createPageTemplate(): PageTemplate;

  public function getRenderer(): TemplateRenderer;
}

class TwigTemplateFactory implements TemplateFactory
{

  public function createTitleTemplate(): TitleTemplate
  {
    return new TwigTitleTemplate();
  }

  public function createPageTemplate(): PageTemplate
  {
    return new TwigPageTemplate($this->createTitleTemplate());
  }

  public function getRenderer(): TemplateRenderer
  {
    return new TwigRenderer();
  }

}

class PHPTemplateFactory implements TemplateFactory
{

  public function createTitleTemplate(): TitleTemplate
  {
    return new PHPTemplateTitleTemplate();
  }

  public function createPageTemplate(): PageTemplate
  {
    return new PHPTemplatePageTemplate($this->createTitleTemplate());
  }

  public function getRenderer(): TemplateRenderer
  {
    return new PHPTemplateRenderer();
  }
}

interface TitleTemplate
{
  public function getTemplateString(): string;
}

class TwigTitleTemplate implements TitleTemplate
{
  public function getTemplateString(): string
  {
    return "<h1>{{title}}</h1>";
  }
}

class PHPTemplateTitleTemplate implements TitleTemplate
{
    public function getTemplateString(): string
    {
        return "title";
    }
}

interface PageTemplate
{
  public function getTemplateString(): string;
}

abstract class BasePageTemplate implements PageTemplate
{
  protected $titleTemplate;
  public function __construct(TitleTemplate $titleTemplate)
  {
    $this->titleTemplate = $titleTemplate;
  }

}

class TwigPageTemplate extends BasePageTemplate
{
    public function getTemplateString(): string
    {
        $renderedTitle = $this->titleTemplate->getTemplateString();

        return <<<HTML
        <div class="page">
            $renderedTitle
            <article class="content">{{ content }}</article>
        </div>
        HTML;
    }
}

class PHPTemplatePageTemplate extends BasePageTemplate
{
    public function getTemplateString(): string
    {
        $renderedTitle = $this->titleTemplate->getTemplateString();

        return <<<HTML
        <div class="page">
            $renderedTitle
            <article class="content"><?= \$content; ?></article>
        </div>
        HTML;
    }
}

interface TemplateRenderer
{
  public function render(string $templateString, array $arg = []): string;
}

class TwigRenderer implements TemplateRenderer
{
  public function render(string $templateString, array $arguments = []): string
  {
    return "This is rendering from Twig";
  }
}

class PHPTemplateRenderer implements TemplateRenderer
{
    public function render(string $templateString, array $arguments = []): string
    {
        $result = "This is rendering from php";
        return $result;
    }
}
class Page
{
  private $title;
  private $content;
  public function __construct(string $title, string $content)
  {
    $this->title = $title;
    $this->content = $content;

  }

  public function render(TemplateFactory $factory)
  {

    $template = $factory->createPageTemplate(); 
    $render = $factory->getRenderer();
    return $render->render($template->getTemplateString(), [$this->title, $this->content]);
  }

}
$page = new Page('title', 'subject');

echo $page->render(new PHPTemplateFactory());

