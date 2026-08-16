<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
$sessionDirectory = __DIR__ . '/private/sessions';
if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0700, true) && !is_dir($sessionDirectory)) {
    throw new RuntimeException('De lokale sessiemap kon niet worden gemaakt.');
}
session_save_path($sessionDirectory);
session_start();

function save_community_submission(array $submission): void
{
    $path = LOCALDECK_SITE_ROOT . '/private/community-submissions.json';
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Opslag is niet schrijfbaar.');
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Opslag is tijdelijk bezet.');
        }
        rewind($handle);
        $existing = json_decode(stream_get_contents($handle) ?: '', true);
        $items = is_array($existing) ? $existing : [];
        $items[] = $submission;
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

$_SESSION['community_csrf'] ??= bin2hex(random_bytes(24));
$formError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validTypes = ['question', 'bug', 'idea', 'docs'];
    $type = (string) ($_POST['type'] ?? '');
    $title = trim((string) ($_POST['title'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $honeypot = trim((string) ($_POST['website'] ?? ''));
    $csrf = (string) ($_POST['csrf'] ?? '');
    $lastSubmission = (int) ($_SESSION['community_last_submission'] ?? 0);

    if (!hash_equals((string) $_SESSION['community_csrf'], $csrf)) {
        $formError = t('De sessie is verlopen. Vernieuw de pagina en probeer opnieuw.', 'The session expired. Refresh the page and try again.');
    } elseif ($honeypot !== '') {
        $formError = t('De inzending kon niet worden verwerkt.', 'The submission could not be processed.');
    } elseif (time() - $lastSubmission < 30) {
        $formError = t('Wacht even voordat je nog een onderwerp instuurt.', 'Please wait before submitting another topic.');
    } elseif (!in_array($type, $validTypes, true) || mb_strlen($title) < 6 || mb_strlen($title) > 120 || mb_strlen($message) < 20 || mb_strlen($message) > 5000) {
        $formError = t('Controleer het type, de titel (6–120 tekens) en uitleg (20–5000 tekens).', 'Check the type, title (6–120 characters), and description (20–5000 characters).');
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = t('Vul een geldig e-mailadres in of laat het veld leeg.', 'Enter a valid email address or leave the field empty.');
    } else {
        $submission = [
            'id' => bin2hex(random_bytes(12)),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'name' => $name === '' ? t('Anoniem', 'Anonymous') : mb_substr($name, 0, 80),
            'email' => $email,
            'language' => $language,
            'createdAt' => (new DateTimeImmutable())->format(DATE_ATOM),
        ];
        $_SESSION['community_last_submission'] = time();
        $mailAccepted = send_community_submission_email($submission);
        $submission['status'] = $mailAccepted ? 'sent' : 'delivery_failed';
        $submission['delivery'] = [
            'channel' => 'email',
            'attemptedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
            'accepted' => $mailAccepted,
        ];
        $backupSaved = false;
        try {
            save_community_submission($submission);
            $backupSaved = true;
        } catch (Throwable $error) {
            error_log('LocalDeck community-back-up kon niet worden opgeslagen: ' . $error->getMessage());
        }

        if (!$mailAccepted) {
            $formError = $backupSaved
                ? t(
                    'Je bericht kon nu niet per e-mail worden afgeleverd. De inhoud is als noodkopie bewaard; probeer het over enkele minuten opnieuw.',
                    'Your message could not be delivered by email right now. A fallback copy was saved; please try again in a few minutes.'
                )
                : t(
                    'Je bericht kon nu niet worden afgeleverd of bewaard. De ingevulde inhoud staat nog in het formulier; probeer het over enkele minuten opnieuw.',
                    'Your message could not be delivered or saved right now. Your input is still in the form; please try again in a few minutes.'
                );
        } else {
            $_SESSION['community_csrf'] = bin2hex(random_bytes(24));
            header('Location: ' . with_language('community.php?submitted=1') . '#new-topic', true, 303);
            exit;
        }
    }
}

$pageKey = 'community';
$pageTitle = t('LocalDeck Community — vragen, fouten en ideeën', 'LocalDeck Community — questions, bugs, and ideas');
$pageDescription = t('De plek voor hulpvragen, foutmeldingen, documentatie en ideeën rond LocalDeck.', 'The place for support questions, bug reports, documentation, and ideas around LocalDeck.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero community-hero">
    <div class="shell hero-grid small">
        <div><span class="eyebrow"><i></i>LOCALDECK COMMUNITY</span><h1><?= e(t('Samen maken we lokaal ontwikkelen beter.', 'Together, we make local development better.')) ?></h1><p><?= e(t('Stel een vraag, meld een fout of deel een idee. Je bericht wordt rechtstreeks per e-mail naar het LocalDeck-team gestuurd.', 'Ask a question, report a bug, or share an idea. Your message is sent directly to the LocalDeck team by email.')) ?></p><a class="button primary" href="#new-topic"><?= e(t('Nieuw onderwerp insturen', 'Submit a new topic')) ?> <span>↓</span></a></div>
        <div class="community-orbit" aria-hidden="true"><span class="orbit-center"><img src="assets/logo.png" alt=""></span><i class="orbit-item one">?</i><i class="orbit-item two">!</i><i class="orbit-item three">✦</i><i class="orbit-item four">⌘</i></div>
    </div>
</section>
<section class="section shell">
    <div class="notice-banner"><span>ℹ</span><div><b><?= e(t('Rechtstreeks contact', 'Direct contact')) ?></b><p><?= e(t('Het formulier stuurt je bericht naar het LocalDeck-team en bewaart een lokale afleverkopie. Er worden geen IP-adressen opgeslagen.', 'The form sends your message to the LocalDeck team and keeps a local delivery copy. No IP addresses are stored.')) ?></p></div></div>
    <div class="section-heading"><span class="eyebrow"><i></i><?= e(t('Kies een categorie', 'Choose a category')) ?></span><h2><?= e(t('Waar kunnen we mee helpen?', 'How can we help?')) ?></h2></div>
    <div class="category-grid">
        <a href="#new-topic" data-topic-type="question"><span class="category-icon violet">?</span><div><h3><?= e(t('Vragen & hulp', 'Questions & help')) ?></h3><p><?= e(t('Installatie, services, projecten en dagelijks gebruik.', 'Installation, services, projects, and daily use.')) ?></p></div><b>→</b></a>
        <a href="#new-topic" data-topic-type="bug"><span class="category-icon red">!</span><div><h3><?= e(t('Fout melden', 'Report a bug')) ?></h3><p><?= e(t('Deel stappen, verwacht gedrag en diagnostische informatie.', 'Share steps, expected behavior, and diagnostic information.')) ?></p></div><b>→</b></a>
        <a href="#new-topic" data-topic-type="idea"><span class="category-icon cyan">✦</span><div><h3><?= e(t('Ideeën', 'Ideas')) ?></h3><p><?= e(t('Stel functies, verbeteringen en integraties voor.', 'Suggest features, improvements, and integrations.')) ?></p></div><b>→</b></a>
        <a href="#new-topic" data-topic-type="docs"><span class="category-icon amber">≡</span><div><h3><?= e(t('Wiki & uitleg', 'Wiki & documentation')) ?></h3><p><?= e(t('Meld onduidelijke, verouderde of ontbrekende uitleg.', 'Report unclear, outdated, or missing documentation.')) ?></p></div><b>→</b></a>
    </div>
</section>
<section class="section community-form-section" id="new-topic">
    <div class="shell form-layout">
        <div>
            <span class="eyebrow"><i></i><?= e(t('Nieuwe inzending', 'New submission')) ?></span>
            <h2><?= e(t('Vertel ons wat er speelt.', 'Tell us what is happening.')) ?></h2>
            <p><?= e(t('Schrijf één duidelijk onderwerp per inzending. Vermeld bij fouten de gebruikte LocalDeck-versie en de stappen waarmee het probleem herhaalbaar is.', 'Write one clear topic per submission. For bugs, include the LocalDeck version and steps that reproduce the issue.')) ?></p>
            <ul class="check-list"><li><?= e(t('Geen wachtwoord of geheime sleutel meesturen', 'Never include a password or secret key')) ?></li><li><?= e(t('Diagnoserapporten eerst op persoonsgegevens controleren', 'Check diagnostic reports for personal data first')) ?></li><li><?= e(t('Inzendingen worden vóór publicatie beoordeeld', 'Submissions are reviewed before publication')) ?></li></ul>
        </div>
        <form class="topic-form" method="post" action="<?= e(with_language('community.php')) ?>#new-topic">
            <?php if (isset($_GET['submitted'])): ?><div class="form-message success" role="status">✓ <?= e(t('Bedankt. Je bericht is per e-mail naar het LocalDeck-team verzonden.', 'Thank you. Your message was emailed to the LocalDeck team.')) ?></div><?php endif; ?>
            <?php if ($formError !== ''): ?><div class="form-message error" role="alert"><?= e($formError) ?></div><?php endif; ?>
            <input type="hidden" name="csrf" value="<?= e((string) $_SESSION['community_csrf']) ?>">
            <label class="honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
            <label><span><?= e(t('Categorie', 'Category')) ?></span><select name="type" data-topic-select required><option value="question"<?= (string) ($_POST['type'] ?? '') === 'question' ? ' selected' : '' ?>><?= e(t('Vraag of hulp', 'Question or help')) ?></option><option value="bug"<?= (string) ($_POST['type'] ?? '') === 'bug' ? ' selected' : '' ?>><?= e(t('Foutmelding', 'Bug report')) ?></option><option value="idea"<?= (string) ($_POST['type'] ?? '') === 'idea' ? ' selected' : '' ?>><?= e(t('Idee of verbetering', 'Idea or improvement')) ?></option><option value="docs"<?= (string) ($_POST['type'] ?? '') === 'docs' ? ' selected' : '' ?>><?= e(t('Wiki of documentatie', 'Wiki or documentation')) ?></option></select></label>
            <label><span><?= e(t('Titel', 'Title')) ?></span><input name="title" minlength="6" maxlength="120" required value="<?= e((string) ($_POST['title'] ?? '')) ?>" placeholder="<?= e(t('Vat het onderwerp kort samen', 'Summarize the topic briefly')) ?>"></label>
            <label><span><?= e(t('Uitleg', 'Description')) ?></span><textarea name="message" minlength="20" maxlength="5000" rows="8" required placeholder="<?= e(t('Wat verwachtte je, wat gebeurde er en hoe kunnen we het herhalen?', 'What did you expect, what happened, and how can we reproduce it?')) ?>"><?= e((string) ($_POST['message'] ?? '')) ?></textarea></label>
            <div class="form-two"><label><span><?= e(t('Naam (optioneel)', 'Name (optional)')) ?></span><input name="name" maxlength="80" value="<?= e((string) ($_POST['name'] ?? '')) ?>"></label><label><span><?= e(t('E-mail (optioneel)', 'Email (optional)')) ?></span><input type="email" name="email" maxlength="160" value="<?= e((string) ($_POST['email'] ?? '')) ?>"></label></div>
            <button class="button primary" type="submit"><?= e(t('Onderwerp insturen', 'Submit topic')) ?> <span>→</span></button>
            <small><?= e(t('Dit is geen accountregistratie. Je gegevens worden alleen gebruikt om je bericht te behandelen en eventueel te beantwoorden.', 'This is not account registration. Your details are used only to handle your message and, when possible, reply to it.')) ?></small>
        </form>
    </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
