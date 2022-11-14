<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Assert\Assertion;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use Swift_Message;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use function count;
use function file_get_contents;
use function unserialize;

final class MailContext extends AbstractContext
{
    private string $spoolDirectory;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager,
        string $spoolDirectory
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->spoolDirectory = $spoolDirectory;
    }

    /**
     * @BeforeScenario @email
     */
    public function cleanEmailSpool(): void
    {
        $filesystem = new Filesystem();
        if (false === $filesystem->exists($this->spoolDirectory)) {
            return;
        }

        $filesystem->remove($this->getSpoolFiles());
    }

    /**
     * @Given /^no emails were sent$/
     */
    public function thereShouldBeNoEmailSent(): void
    {
        $files = $this->getSpoolFiles();
        Assertion::count($files, 0, 'There were "%s" emails sent, where none should.');
    }

    /**
     * @Given I should receive email:
     * @Then an email should be sent:
     */
    public function iShouldReceiveEmail(TableNode $table): void
    {
        $files = $this->getSpoolFiles();
        $files->sortByModifiedTime();

        if (0 === count($files)) {
            throw new Exception("There should be at least 1 email");
        }

        $expected = $table->getRowsHash();
        $email = $this->fetchEmail($expected['subject']);
        if (null === $email) {
            throw new Exception(sprintf('There is no email with "%s" subject', $expected['subject']));
        }

        Assertion::same(key($email->getFrom()), $expected['from']);
        Assertion::same(key($email->getTo()), $expected['to']);
        Assertion::same($email->getReplyTo(), $expected['reply_to']);
    }

    /**
     * @Given I clear the email pool
     */
    public function iClearTheEmailPool(): void
    {
        $this->cleanEmailSpool();
        $this->thereShouldBeNoEmailSent();
    }

    private function fetchEmail(string $subject): ?Swift_Message
    {
        $files = $this->getSpoolFiles();
        foreach ($files as $file) {
            $fileContents = file_get_contents((string) $file);
            Assertion::string($fileContents, 'Unable to parse file contents');

            /** @var Swift_Message $message */
            $message = unserialize($fileContents);
            if ($message->getSubject() === $subject) {
                unlink((string) $file);
                return $message;
            }
        }

        return null;
    }

    private function getSpoolFiles(): Finder
    {
        $finder = new Finder();
        $finder->in($this->spoolDirectory)->ignoreDotFiles(true)->files();

        return $finder;
    }
}
