<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Exception;
use Swift_Message;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use function expect;

final class MailContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario @email
     */
    public function cleanEmailSpool()
    {
        $filesystem = new Filesystem();
        if (false === $filesystem->exists($this->getSpoolDir())) {
            return;
        }

        $filesystem->remove($this->getSpoolFiles());
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
     * @Then an email should be sent:
     */
    public function iShouldReceiveEmail(TableNode $table)
    {
        $files = $this->getSpoolFiles();
        $files->sortByModifiedTime();

        if (0 === $files->count()) {
            throw new Exception("There should be at least 1 email");
        }

        $expected = $table->getRowsHash();
        $email = $this->fetchEmail($expected['subject']);
        if (null === $email) {
            throw new Exception(sprintf('There is no email with "%s" subject', $expected['subject']));
        }

        expect(key($email->getFrom()))->toBe($expected['from']);
        expect(key($email->getTo()))->toBe($expected['to']);
        expect(key($email->getReplyTo()))->toBe($expected['reply_to']);
    }

    /**
     * @Given I clear the email pool
     */
    public function iClearTheEmailPool(): void
    {
        $this->cleanEmailSpool();
        $this->thereShouldBeNoEmailSent();
    }

    private function fetchEmail($subject): ?Swift_Message
    {
        $files = $this->getSpoolFiles();
        foreach ($files as $file) {
            /** @var Swift_Message $message */
            $message = unserialize(file_get_contents((string) $file));

            if ($subject === $message->getSubject()) {
                unlink((string) $file);
                return $message;
            }
        }

        return null;
    }

    private function getSpoolFiles(): Finder
    {
        $finder = new Finder();
        $finder->in($this->getSpoolDir())->ignoreDotFiles(true)->files();

        return $finder;
    }

    private function getSpoolDir(): string
    {
        return $this->kernel->getContainer()->getParameter('swiftmailer.spool.default.file.path');
    }
}
