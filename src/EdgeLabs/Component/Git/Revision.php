<?php

namespace EdgeLabs\Component\Git;

use DateTime;

/**
 * @author Steve Todorov <steve.todorov@carlspring.com>
 */
class Revision
{
    private $hash;
    private $author;
    private $email;
    private $date;
    private $message;

    public function __construct(string $hash = null, string $message = null, string $author = null, string $email = null, string $date = null)
    {
        $this->hash    = $hash;
        $this->message = $message;
        $this->author  = $author;
        $this->email   = str_replace('<', '', str_replace('>', '', $email));
        $this->date    = DateTime::createFromFormat('U', $date);
    }

    /**
     * Get $this->hash
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set $this->hash
     *
     * @param mixed $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Get $this->author
     *
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set $this->author
     *
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get $this->email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set $this->email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get $this->date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set $this->date
     *
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get $this->message
     *
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set $this->message
     *
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}

?>
