<?php

namespace EdgeLabs\Component\Git\Exception;

/**
 * @author Steve Todorov <steve.todorov@carlspring.com>
 */
class UnexpectedGitOutputException extends \RuntimeException
{
    public function __construct($command, array $output, $code = 0, \Exception $previous = null){
        parent::__construct("Unexpected git output: git ".$command."\r\n".implode("\r\n", $output), $code, $previous);
    }
}

?>
