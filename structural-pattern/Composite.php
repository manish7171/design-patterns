<?php


abstract class FormElement
{
  protected $data;

  public function __construct(public string $name, public string $title){}

  public function getName(): string
  {
    return $this->name;
  }

  public function setData(string $data): void
  {
    $this->data = $data;
  }

  public function getData()
  {
    return $this->data;
  }

  abstract public function render(): string;
}

class Input extends FormElement
{
  
  public function __construct(public string $name, public string $title, private string $type)
  {
    parent::__construct($name, $title);
  }

  public function render(): string
  {
    return "<label for=\"{$this->name}\">{$this->title}</label>\n" .
      "<input name=\"{$this->name}\" type=\"{$this->type}\" value=\"{$this->data}\">\n";
  }
}

abstract class FieldComposite extends FormElement
{
  private $fields = [];

  public function add(FormElement $field): void
  {
    $name = $field->getName();
    $this->fields[$name] = $field;
  }

  public function remove(FormElement $component): void
  {
    $this->fields = array_filter($this->fields, function ($child) use ($component) {
      return $child != $component;
    });
  }

  public function setData($data): void
  {
    foreach ($this->fields as $name => $field) {
      if (isset($data[$name])) {
        $field->setData($data[$name]);
      }
    }
  }

  public function getData()
  {
    $data = [];

    foreach ($this->fields as $name => $field) {
      $data[$name] = $field->getData();
    }

    return $data;
  }

  public function render(): string
  {
    $output = "";
    foreach($this->fields as $field){
      $output .= $field->render();
    }
    return $output;
  }
}

class Fieldset extends FieldComposite
{
    public function render(): string
    {
        // Note how the combined rendering result of children is incorporated
        // into the fieldset tag.
        $output = parent::render();
        
        return "<fieldset><legend>{$this->title}</legend>\n$output</fieldset>\n";
    }
}
  /**
 * And so is the form element.
 */
class Form extends FieldComposite
{
    protected $url;

    public function __construct(string $name, string $title, string $url)
    {
        parent::__construct($name, $title);
        $this->url = $url;
    }

    public function render(): string
    {
        $output = parent::render();
        return "<form action=\"{$this->url}\">\n<h3>{$this->title}</h3>\n$output</form>\n";
    }
}
function getProductForm()
{
  $form = new Form('product', "Add product", "/product/add");
  $form->add(new Input('name', "Name", 'text'));
  $form->add(new Input('description', "Description", 'text'));

  $picture = new Fieldset('photo', "Product photo");
  $picture->add(new Input('caption', "Caption", 'text'));
  $picture->add(new Input('image', "Image", 'file'));
  $form->add($picture);

  return $form;
}

function loadProductData(FormElement $form)
{
  $data = [
        'name' => 'Apple MacBook',
        'description' => 'A decent laptop.',
        'photo' => [
            'caption' => 'Front photo.',
            'image' => 'photo1.png',
        ],
    ];

  $form->setData($data);
}

function renderProduct($form)
{
  echo "Render Product Data. \n";
  echo$form->render();
}

$form = getProductForm();
loadProductData($form);
renderProduct($form);
