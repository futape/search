<?php


namespace Futape\Search\Matcher;


use Futape\Search\TermCollection;

/**
 * Matches implementing this interface always get a TermCollection passed to their matchValue() method
 */
interface TermCollectionAware
{
    /**
     * @param TermCollection $termCollection
     * @return TermCollection
     */
    public function processTermCollection(TermCollection $termCollection): TermCollection;
}
