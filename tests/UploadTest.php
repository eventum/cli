<?php

namespace Eventum\Console\Test;

use Eventum\Console\Application;
use Eventum\Console\Command\AddAttachmentCommand;
use Eventum_RPC_Exception;
use Symfony\Component\Console\Tester\CommandTester;

class UploadTest extends TestCase
{
    /**
     * @dataProvider getEmptyFileUploadData
     */
    public function testUploadEmptyFile($input)
    {
        $application = new Application();
        $application->add(new AddAttachmentCommand());

        $command = $application->find('add-attachment');

        $tester = new CommandTester($command);
        try {
            $tester->execute(
                array_merge(array('command' => $command->getName()), $input)
            );
            $this->fail();

        } catch (Eventum_RPC_Exception $e) {
            $this->assertEquals("Empty file uploaded", $e->getMessage());
        }
    }

    public function getEmptyFileUploadData()
    {
        return array(
            array(array('issue_id' => '1', 'file' => '/dev/null')),
        );
    }
}