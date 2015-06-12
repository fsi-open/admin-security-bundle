<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class MailContext implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario @email
     */
    public function cleanEmailSpool()
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->getSpoolDir())) {
            $filesystem->remove($this->getSpoolFiles());
        }
    }

    /**
     * @Given /^no emails were sent$/
     */
    public function thereShouldBeNoEmailSent()
    {
        $files = $this->getSpoolFiles();

        expect($files->count())->toBe(0);
    }

    /**
     * @Given I should receive email:
     */
    public function iShouldReceiveEmail(TableNode $table)
    {
        $files = $this->getSpoolFiles();
        $files->sortByModifiedTime();

        if ($files->count() < 1) {
            throw new \Exception("There should be at least 1 email");
        }

        $expected = $table->getRowsHash();

        if (false === ($email = $this->fetchEmail($expected['subject']))) {
            throw new \Exception(sprintf('There is no email with "%s" subject', $expected['subject']));
        }

        expect(key($email->getFrom()))->toBe($expected['from']);
        expect(key($email->getTo()))->toBe($expected['to']);
    }

    private function fetchEmail($subject)
    {
        $files = $this->getSpoolFiles();
        foreach ($files as $file) {
            /** @var \Swift_Message $message */
            $message = unserialize(file_get_contents($file));

            if ($subject === $message->getSubject()) {
                unlink($file);

                return $message;
            }
        }

        return false;
    }

    /**
     * @return Finder
     */
    private function getSpoolFiles()
    {
        $finder = new Finder();
        $finder->in($this->getSpoolDir())->ignoreDotFiles(true)->files();

        return $finder;
    }

    private function getSpoolDir()
    {
        return $this->kernel->getContainer()->getParameter('swiftmailer.spool.default.file.path');
    }
}
