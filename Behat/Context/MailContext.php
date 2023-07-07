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
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FSi\FixturesBundle\Listener\MailCollector;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class MailContext extends AbstractContext
{
    private MailCollector $mailerCollector;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager,
        MailCollector $mailerCollector
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->mailerCollector = $mailerCollector;
    }

    /**
     * @BeforeScenario
     * @Given I clear the email pool
     */
    public function clearEmails(): void
    {
        $this->mailerCollector->resetEmails();
    }

    /**
     * @Given /^no emails were sent$/
     */
    public function thereShouldBeNoEmailSent(): void
    {
        Assertion::count(
            $this->mailerCollector->getEmails(),
            0,
            'There were "%s" emails sent, where none should.'
        );
    }

    /**
     * @Given I should receive email:
     * @Then an email should be sent:
     */
    public function iShouldReceiveEmail(TableNode $table): void
    {
        Assertion::minCount($this->mailerCollector->getEmails(), 1, 'There should be at least "%s" email');

        $emails = $this->mailerCollector->getEmails();
        usort(
            $emails,
            static fn(Email $a, Email $b): int => $a->getDate() <=> $b->getDate()
        );

        /** @var array{ subject: string, from: string, to: string, reply_to: string } $expected */
        $expected = $table->getRowsHash();
        $expectedSubject = $expected['subject'];
        $email = $this->getEmailBySubject($expectedSubject);

        Assertion::notNull($email, "There is no email with \"{$expectedSubject}\" subject");
        Assertion::same($this->getEmailAsString($email->getFrom()), $expected['from']);
        Assertion::same($this->getEmailAsString($email->getTo()), $expected['to']);
        Assertion::same($this->getEmailAsString($email->getReplyTo()), $expected['reply_to']);
    }

    private function getEmailBySubject(string $subject): ?Email
    {
        return array_reduce(
            $this->mailerCollector->getEmails(),
            function (?Email $accumulator, Email $email) use ($subject): ?Email {
                if (null !== $accumulator) {
                    return $accumulator;
                }

                if ($email->getSubject() === $subject) {
                    $accumulator = $email;
                }

                return $accumulator;
            }
        );
    }

    /**
     * @param array<Address> $emails
     */
    private function getEmailAsString(array $emails): string
    {
        $email = reset($emails);
        Assertion::isInstanceOf($email, Address::class);

        return $email->toString();
    }
}
