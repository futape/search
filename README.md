# futape/search

This framework offers a basic set of utilities for building your search index and search it using various customizable
matching strategies.

You are completely free in the choice and the structure of the objects to index.  
News matchers can be implemented and added in seconds. The same applies to highlighters.

## Install

```bash
composer require futape/search
```

## Architecture

### Matchers & Values

Matchers are components for matching an indexed value against a search term. The type of the value and of the search
term is technically completely free. However, different concrete matchers may specify or force a type.  
A matcher class always goes along with a value class, being the class, and the *only* class, whose instances the matcher
can work with. A value instance in turn contains the real value, the matcher matches against. Again, the type of that
value is technically undefined, however a value may specify or force one.  
Besides the managed value, a value instance also manages a matching score, as well as a highlighted version of the
managed value.  
Every value has a highlighter attached to it. By default it's initialized with a `DummyHighlighter`. As soon as a
searchable is added to the `Index`, the index's highlighter (`HtmlHighlighter` by default) is forwarded to all values
provided by the searchable.  
Please note, a value instance is intended to be provided by exactly one searchable at a time and is not shared by
multiple searchables. Otherwise you may encounter undefined behavior.  
There are an abstract matcher and value you can extend to build your own, as well as a few predefined concrete
matchers. There's also a `AbstractArrayValue` class which extends the `AbstractValue` class and manages the managed
value to be an array.

#### Token Matcher

The `TokenMatcher` works with `TokenValues`, which managed a 1-dim string array.  
The managed array is searched strictly for a given search term. The score is increased by 1 for every match and the
value matching value is highlighted.

#### Building Your Own Matcher

To create your own matcher, just create one class extending the `AbstractMatcher` class and one extending the
`AbstractValue` class. Then set the `SUPPORTED_VALUE` constant of your matcher class to the FQCN of your value class.  
In your matcher class, you have to implement the `matchValue` method which takes the value managed by an instance of
your value class, the search term and a highlighter instance, as well as references to the highlighted value and the
matching score.  
Your value has to implement the `resetHighlighted` method which takes a copy of the value managed by the value instance,
which will become the highlighted value, resets it to its initial state and returns it. Most often this means to
lowlight it using the `lowlight` method of the value's highlighter.

To implement a matcher that just compares the value to the term, highlights that value if it matches and increases the
score, you may create a class like below:

**EqualsMatcher.php**

```php
use Futape\Search\Matcher\AbstractMatcher;

class EqualsMatcher extends AbstractMatcher
{
    const SUPPORTED_VALUE = EqualsValue::class;

    /**
     * @param mixed $value
     * @param mixed $term
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue($value, $term, HighlighterInterface $highlighter, &$highlighted, int &$score): void
    {
        if ($value == $term) {
            $highlighted = $highlighter->highlight($highlighted);
            $score++;
        }
    }
}
```

**EqualsValue.php**

```php
use Futape\Search\Matcher\AbstractValue;

class EqualsValue extends AbstractValue
{
    /**
     * @param mixed $highlighted
     * @return mixed
     */
    protected function resetHighlighted($highlighted)
    {
        return $this->getHighlighter()->lowlight($highlighted);
    }
}
```

### Highlighters

Highlighters are used to highlight matching values, as well as for lowlighting values (i.e. process the value
according to the highlighter's logic and character but don't highlight it).  
Technically a highlighter may highlight any value, being it a scalar value like a string or a float, or something like
an object, it just needs to know how to do it.  
A highlighter doesn't know about the type of the value it should handle and every highlighter behaves differently and
implements its own way of highlighting values and has a different return value.
A string highlighter for example tries to highlight the value as a string and fails if the value can't be converted to
one. Other highlighters may not convert the value at all and just wrap them into a special "highlighted" object.  
There are a few predefined highlighters as well as an abstract one and an interface to built you own
highlighters.

#### String Highlighters

String highlighters are made for highlighting strings.  
There are two predefined string highlighters:

+ `PlainHighlighter`: Highlights a value in a markdown-like manner for usage in plaintext (e.g. `**Foo** Bar`)
+ `HtmlHighlighter`: Highlights a value using HTML `mark` tags (e.g. `<mark>Foo</mark> Bar`) and HTML-escapes special
  characters

#### Dummy Highlighter

The `DummyHighlighter` exists just to have a highlighter that implements the `HighlighterInterface`, but doesn't have
any functionality. It just returns the value as it comes in.

#### Building Your Own Highlighter

To build your own highlighter, you may either create it from scratch by implementing the `HighlighterInterface` or
by extending the `AbstractStringHighlighter`.  
Latter is an abstract class for creating string highlighters. The only thing you have to do it to define one string that
starts a highlighted part and one that ends it.

**YellHighlighter.php**

```php
use Futape\Search\Highlighter\AbstractStringHighlighter;

class YellHighlighter extends AbstractStringHighlighter
{
    /** @var string */
    protected $opening = '!!!';

    /** @var string */
    protected $closing = '!!!';
}
```

### Indexed Objects (Searchables)

These are the objects in the index you can search through.  
Because the source of the data to match against is completely unknown to this framework and is domain to your
application, there aren't any concrete classes to build indexed objects.  
Instead you have to create your own *Searchables*.

Searchables are objects that provide various value objects containing the data of some entity. How these data is
retrieved is completely up to you.  
When the index is searched, the value objects are passed to matchers supporting these value objects, and are compared
against the search term by them.

To create your own searchable, you may either implement the `SearchableInterface` or extend the `AbstractSearchable`
class.  
When extending the `AbstractSearchable`, the only method to implement is `initMatcherValues`, which is called by the
constructor and should populate the matcher values by calling the `registerMatcherValue` method. You may do something
like below.

```php
use Futape\Search\AbstractSearchable;
use Your\Domain\Model\Article;

class ArticleSearchable extends AbstractSearchable {
{
    /** @var Article */
    protected $article;
    
    public function __construct(Article $article)
    {
        $this->article = $article;
        parent::__construct();
    }

    protected function initMatcherValues(): void
    {
        $this->registerMatcherValue('tags', new TokenValue($this->article->getTags()));
        $this->registerMatcherValue('categories', new TokenValue($this->article->getCategories()));
        // ...
    }
    
    public function getArticle(): Article
    {
        return $this->article;
    }
}
```

Again, you are completely free about the source of the data. Instead of passing a model instance to the constructor
like in the example above, you may pass just a value (or even nothing) and do some API calls or anything else you can
imagine.

## Usage

```php
use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Index;
use Futape\Search\Matcher\Token\TokenMatcher;
use Your\Domain\Model\Article;
// See "Indexed Objects (Searchables)" section for this example searchable
use Your\Domain\Search\ArticleSearchable;

$index = (new Index(new PlainHighlighter()))
    // Attach a matcher to process the searchables' values
    ->attachMatcher(new TokenMatcher())
    
    // Add searchables to the index
    ->addSearchable(new ArticleSearchable(new Article(42))) // tags: animals, zoo, plants; categories: nature, plants
    ->addSearchable(new ArticleSearchable(new Article(101))) // tags: park, plants; categories: vacation
    ->addSearchable(new ArticleSearchable(new Article(61))) // tags: skyscrapers, concrete; categories: vacation, cities
    
    // Execute the search
    ->search('plants');

foreach ($index->getSearchables() as $searchable) {
    var_dump($searchable->getScore());
    var_dump($searchable->getMatcherValue('tags')->getScore());
    var_dump($searchable->getMatcherValue('tags')->getHighlighted());
    var_dump($searchable->getMatcherValue('categories')->getScore());
    var_dump($searchable->getMatcherValue('categories')->getHighlighted());
}

/*
first iteration (article 42):
2
1
['animals', 'zoo', '**plants**']
1
['nature', '**plants**']

second iteration (article 101):
1
1
['park', '**plants**']
0
['vacation']

third iteration (article 61):
0
0
['skyscrapers', 'concrete']
0
['vacation', 'cities']
*/

```

## Testing

The library is tested by unit tests using PHP Unit.

To execute the tests, install the composer dependencies (including the dev-dependencies), switch into the `tests`
directory and run the following command:

```bash
../vendor/bin/phpunit
```
