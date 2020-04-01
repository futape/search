# Upgrading futape/search

## Upgrading 1.0.0 -> 2.0.0

### Properties made private

The visibility of most properties is changed from protected to private if they are not part of the API.
This change may break your code if you accessed these properties directly before. Use the corresponding getters and
setters instead.

## Upgrading 2.0.0 -> 3.0.0

### Removed AbstractMatcher::$highlighter

The following methods have been removed:

+ `AbstractMatcher::getHighlighter()`
+ `AbstractMatcher::setHighlighter()`

Instead of managing a highlighter instance in a matcher's property, the highlighter is now attached to the value
instance and is retrieved from there. It is then passed to `AbstractMatcher::matchValue()` when matching a value.
The signature of that method changed as follows:

```php
abstract protected function matchValue(
    $value,
    $term,
    HighlighterInterface $highlighter,
    &$highlighted,
    int &$score
): void;
```

In your implementation use the `$highlighter` argument instead of calling `AbstractMatcher::getHighlighter()`.

### HighlighterInterface::lowlight() and abstract AbstractValue::resetHighlighted() method added

In order to provide automatic HTML-escaping, a `lowlight()` method has been added the `HighlighterInterface`.
If you developed your own highlighters by implementing that interface, you have to add that method to your classes.

Also an abstract `resetHighlighted()` method has been added to the `AbstractValue` class and has to be implemented by
any matcher class extending that class. The implementation should set a highlighted value to its initial state.
See the [readme](https://github.com/futape/search/tree/3.0.0#building-your-own-matcher) for more information.

## Upgrading 3.0.0 -> 4.0.0

### Added HighlighterInterface::highlightAreas()

Custom highlighters implementing the `HighlighterInterface` need to implement the `highlightAreas()` method.
The method expects a string value and an array of area markers and has to highlight these areas of the passed string in
their own way and return the highlighted result.  
You may want to use `HighlighterHelper::processAreas()` to normalize and validate the areas array.

If your custom highlighter extends the `AbstractStringHighlighter`, you don't have to do anything, the method is already
implemented.
