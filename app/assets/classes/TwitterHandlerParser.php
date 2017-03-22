<?php
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Element\Link;

class TwitterHandlerParser extends AbstractInlineParser
{
  public function getCharacters()
  {
    return ['@'];
  }

  public function parse(InlineParserContext $inlineContext)
  {
    $cursor = $inlineContext->getCursor();
    $previousChar = $cursor->peek(-1);
    if ($previousChar !== null && $previousChar !== ' ') {
      return false;
    }

    $previousState = $cursor->saveState();
    $cursor->advance();
    $handle = $cursor->match('/^[A-Za-z0-0_]{1,15}(?!\w)/');
    if (empty($handle)) {
      $cursor->restoreState($previousState);
      return false;
    }
    $profileURL = '/recipe?title=' . $handle;
    $inlineContext->getContainer()->appendChild(new Link($profileURL, '@' . $handle));
    return true;
  }
}

?>

