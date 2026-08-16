<?php
declare(strict_types=1);

require __DIR__ . '/../inc/bootstrap.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$submission = [
    'id' => 'test-message-1',
    'type' => 'bug',
    'title' => "Testtitel\r\nBcc: attacker@example.com",
    'message' => "Regel één\nRegel twee met voldoende testinhoud.",
    'name' => 'Testgebruiker',
    'email' => 'reply@example.com',
    'language' => 'nl',
    'createdAt' => '2026-08-16T12:00:00+02:00',
];

$captured = [];
$accepted = send_community_submission_email(
    $submission,
    static function (string $to, string $subject, string $body, string $headers) use (&$captured): bool {
        $captured = compact('to', 'subject', 'body', 'headers');
        return true;
    }
);

assert_true($accepted, 'De testtransporteur had de e-mail moeten accepteren.');
assert_true($captured['to'] === 'chatgpt@platinumradio.nl', 'De ontvanger is niet de vaste beheerinbox.');
assert_true(str_contains($captured['headers'], 'From: LocalDeck Website <website@localdeck.nl>'), 'De veilige afzender ontbreekt.');
assert_true(str_contains($captured['headers'], 'Reply-To: reply@example.com'), 'Reply-To ontbreekt voor een geldig adres.');
assert_true(!str_contains($captured['subject'], "\r\nBcc:"), 'Het onderwerp bevat een geïnjecteerde header.');
assert_true(str_contains($captured['body'], 'test-message-1'), 'Het bericht-ID ontbreekt in de e-mail.');
assert_true(!str_contains($captured['body'], 'REMOTE_ADDR'), 'Technische clientgegevens horen niet in de e-mail.');

$invalidReply = $submission;
$invalidReply['email'] = "reply@example.com\r\nBcc: attacker@example.com";
$invalidEmail = community_submission_email($invalidReply);
assert_true(!str_contains($invalidEmail['headers'], 'Reply-To:'), 'Een ongeldig antwoordadres mag geen Reply-To-header krijgen.');
assert_true(
    send_community_submission_email($submission, static fn (): bool => false) === false,
    'Een mislukte transportpoging moet false retourneren.'
);

echo "community-mail: OK\n";
