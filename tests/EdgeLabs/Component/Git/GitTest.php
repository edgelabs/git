<?php

use EdgeLabs\Component\Git\Git;
use EdgeLabs\Component\Git\Revision;

/**
 * @author Steve Todorov <steve.todorov@carlspring.com>
 */
class GitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Git $git
     */
    private $git;

    private static $checkedOut = false;
    private static $tmpDir;

    public function setUp(){
        if(!self::$tmpDir) {
            self::$tmpDir = sys_get_temp_dir()."/tmp-".substr(sha1(random_int(10,10000).microtime()),0,6);
        }

        $this->git = new Git(self::$tmpDir);
    }

    public function testCloneRepo()
    {
        Git::cloneRepo('https://github.com/edgelabs/testing-repository', self::$tmpDir);
        $this->assertFileExists(self::$tmpDir);
    }

    public function testCheckout()
    {
        self::$checkedOut = true;
        $this->git->checkout('1.0.1');
        $this->assertEquals('5f961363ddc8c8a6dade3023f110cd72a20e6934', $this->git->getCurrentRevision());
    }

    public function testGetCurrentBranch()
    {
        if(self::$checkedOut) {
            $this->git->checkout('master');
        }

        $branch = $this->git->getCurrentBranch();
        $this->assertEquals('master', $branch, "Expected current branch to be 'master', but received '".$branch."'");
    }

    public function testGetStatus()
    {
        $status = $this->git->getStatus();

        $this->assertArrayHasKey('message', $status);
        $this->assertArrayHasKey('modified', $status);
        $this->assertArrayHasKey('added', $status);
    }

    public function testGetRevisionsDesc()
    {
        $revisions = $this->git->getRevisions();

        $this->assertCount(10, $revisions);

        /**
         * @var Revision $latest
         */
        $latest = $revisions[0];

        $this->assertEquals('Steve Todorov', $latest->getAuthor());
        $this->assertEquals('steve-todorov@users.noreply.github.com', $latest->getEmail());
        $this->assertEquals('bda7496f3c06d52e25c3eddd86dfec8250d7f8e0', $latest->getHash());
        $this->assertEquals(1472399160, $latest->getDate()->format('U'));
    }

    public function testGetRevisionsAsc()
    {
        $revisions = $this->git->getRevisions(10, true);

        $this->assertCount(10, $revisions);

        /**
         * @var Revision $latest
         */
        $latest = $revisions[9];

        $this->assertEquals('Steve Todorov', $latest->getAuthor());
        $this->assertEquals('steve-todorov@users.noreply.github.com', $latest->getEmail());
        $this->assertEquals('bda7496f3c06d52e25c3eddd86dfec8250d7f8e0', $latest->getHash());
        $this->assertEquals(1472399160, $latest->getDate()->format('U'));
    }

    public function testGetCurrentRevision()
    {
        $revision = $this->git->getCurrentRevision();
        $expected = 'bda7496f3c06d52e25c3eddd86dfec8250d7f8e0';
        $this->assertNotNull($revision);
        $this->assertEquals($expected, $revision, "Expected to have received revision '".$expected."', but received ".$revision);
    }

    public function testGetPreviousRevision()
    {
        $revision = $this->git->getPreviousRevision();
        $expected = '5f961363ddc8c8a6dade3023f110cd72a20e6934';
        $this->assertNotNull($revision);
        $this->assertEquals($expected, $revision, "Expected to have received revision '".$expected."', but received ".$revision);
    }

    public function testGetRemoteTags()
    {
        $tags = $this->git->getRemoteTags();

        $this->assertCount(3, $tags);
        $this->assertArrayHasKey('1.0.0', $tags);
        $this->assertArrayHasKey('1.0.1', $tags);
        $this->assertArrayHasKey('1.0.2', $tags);
    }

    public function testGetTagFromHash()
    {
        $tag1 = $this->git->getTagFromHash('cfe6b389803750c8ba7637e380a9cfae0b861950');
        $tag2 = $this->git->getTagFromHash('5f961363ddc8c8a6dade3023f110cd72a20e6934');
        $tag3 = $this->git->getTagFromHash('bda7496f3c06d52e25c3eddd86dfec8250d7f8e0');

        $this->assertEquals('1.0.0', $tag1);
        $this->assertEquals('1.0.1', $tag2);
        $this->assertEquals('1.0.2', $tag3);
    }

    public function testIsWorkingCopyClean()
    {
        $this->assertTrue($this->git->isWorkingCopyClean());
    }

    public function testIsGitRepository()
    {
        $this->assertTrue($this->git->isGitRepository(), "Expected to receive 'true' for path under git version control");
        $this->assertFalse($this->git->isGitRepository(sys_get_temp_dir()), "Expected to receive 'false' for path not under git version control");
    }
}

?>
