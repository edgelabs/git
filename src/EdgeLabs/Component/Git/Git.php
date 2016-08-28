<?php

namespace EdgeLabs\Component\Git;

use EdgeLabs\Component\Git\Exception\GitRuntimeException;

/**
 * Code is influenced by sebastianbergmann/git
 *
 * @author Steve Todorov <steve.todorov@carlspring.com>
 */
class Git
{
    /**
     * @var string
     */
    private static $repositoryPath;

    /**
     * @param string $repositoryPath
     */
    public function __construct($repositoryPath = null)
    {
        self::$repositoryPath = realpath($repositoryPath);
    }

    /**
     * @param $repository
     * @param $path
     *
     * @return mixed
     */
    public static function cloneRepo($repository, $path = null)
    {
        if (!$path && realpath(self::$repositoryPath)) {
            $path = realpath(self::$repositoryPath);
        }

        return self::execute('clone ' . $repository . ' ' . $path);
    }

    /**
     * @param string $revision - git revision
     * @return bool
     */
    public function checkout($revision)
    {
        self::execute('checkout --force --quiet ' . $revision);
        return true;
    }

    /**
     * @param $args
     * @return bool
     */
    public function fetch($args)
    {
        self::execute('fetch ' . $args);
        return true;
    }

    /**
     * @return string
     */
    public function getCurrentBranch()
    {
        $output = self::execute('rev-parse --abbrev-ref HEAD');
        return $output[0];
    }

    /**
     * @return null|string
     */
    public function getCurrentRevision()
    {
        $output = self::execute("log --pretty=format:%H -n 1");

        if (count($output) == 1) {
            return $output[0];
        } else {
            return null;
        }
    }

    public function getPreviousRevision()
    {
        $output = self::execute("log --pretty=format:%H -n 2");

        if (count($output) == 2) {
            return $output[1];
        } else {
            return null;
        }
    }

    /**
     * @param  string $from
     * @param  string $to
     * @return string
     */
    public function getDiff($from, $to)
    {
        $output = self::execute(
            'diff --no-ext-diff ' . $from . ' ' . $to
        );
        return implode("\n", $output);
    }

    /**
     * @param int $limit
     * @param bool $ascOrder - get logs in asc order.
     * @return array
     */
    public function getRevisions($limit = 10, $ascOrder = false)
    {
        $limit   = " -" . abs($limit);
        $reverse = ($ascOrder ? ' --reverse ' : null);

        $keyword = 'end:' . substr(sha1(random_int(10, 1000) . microtime()), 0, 8);

        $output = self::execute('log --no-merges --date-order --format="%H;;; %B;;; %an;;; %ae;;; %at %n' . $keyword . '"' . $reverse . $limit);

        $revisions = array();
        $line      = null;
        foreach ($output as $partial) {
            if ($partial == $keyword && $line != "") {
                $tmp = explode(';;;', $line);

                $hash    = trim(@$tmp[0]);
                $message = trim(@$tmp[1]);
                $author  = trim(@$tmp[2]);
                $email   = trim(@$tmp[3]);
                $time    = trim(@$tmp[4]);

                $revision = new Revision($hash, $message, $author, $email, $time);

                $revisions[] = $revision;

                $line = null;
            } else {
                $line .= $partial;
                continue;
            }
        }

        return $revisions;
    }

    public function getStatus()
    {
        $status = self::execute('status');

        $modified = array();
        $added    = array();

        foreach ($status as $line) {
            preg_match('/.*modified:([ ]*)(.*)/i', $line, $matchModified);
            if (@$matchModified[2]) {
                $modified[] = @$matchModified[2];
            } else {
                preg_match('/.*new file:([ ]*)(.*)/i', $line, $matchNew);
                if (@$matchNew[2]) {
                    $added[] = $matchNew[2];
                }
            }
        }

        return array('message' => $status, 'modified' => $modified, 'added' => $added);
    }

    /**
     * @return bool
     */
    public function isWorkingCopyClean()
    {
        $output = self::execute('status');
        // nothing to commit, working (directory|tree) clean
        return preg_match('/nothing to commit/i', $output[count($output) - 1]) > 0;
    }

    public function isGitRepository($path = null)
    {
        try {
            $output = self::execute('rev-parse --is-inside-work-tree', $path);
            return (is_array($output) && trim($output[0]) == "true");
        } catch (\Exception $e) {
            if (preg_match('/not a git repository/i', $e->getMessage()) > 0) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * @param null $repository
     * @return array
     */
    public function getRemoteTags($repository = null)
    {
        $output = self::execute('ls-remote --tags ' . $repository);
        $result = array();

        if (is_array($output)) {
            foreach ($output as $line) {
                preg_match('/(.*)refs\/tags\/(.*)/i', $line, $match);
                if (@$match[2]) {
                    $result[@$match[2]] = trim(@$match[1]);
                }
            }
        }

        return $result;
    }

    /**
     * @param  string $command
     * @param  string $cwd
     * @return mixed
     */
    protected static function execute($command, $cwd = null)
    {
        if ($cwd)
            $git = 'git -C ' . escapeshellarg($cwd);
        else if (self::$repositoryPath != null)
            $git = 'git -C ' . escapeshellarg(self::$repositoryPath);
        else
            $git = 'git';

        $command = $git . ' ' . $command . ' 2>&1';

        if (DIRECTORY_SEPARATOR == '/') {
            $command = 'LC_ALL=en_US.UTF-8 ' . $command;
        }

        exec($command, $output, $returnValue);
        usleep(800);

        if ($returnValue !== 0) {
            array_unshift($output, 'Command: ' . $command);
            throw new GitRuntimeException(implode("\r\n", $output));
        }

        return $output;
    }

}

?>
