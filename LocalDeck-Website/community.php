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
        $items = retained_community_submissions($items);
        $items = array_slice($items, -999);
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

function retained_community_submissions(array $items): array
{
    $retentionCutoff = time() - (180 * 86400);
    return array_values(array_filter($items, static function (mixed $item) use ($retentionCutoff): bool {
        if (!is_array($item) || empty($item['createdAt'])) {
            return false;
        }
        $createdAt = strtotime((string) $item['createdAt']);
        return $createdAt !== false && $createdAt >= $retentionCutoff;
    }));
}

function prune_community_submissions(): void
{
    $path = LOCALDECK_SITE_ROOT . '/private/community-submissions.json';
    if (!is_file($path)) {
        return;
    }
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
        $retained = retained_community_submissions($items);
        if (count($retained) !== count($items)) {
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($retained, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            fflush($handle);
        }
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

try {
    prune_community_submissions();
} catch (Throwable $error) {
    error_log('LocalDeck supportretentie kon niet worden uitgevoerd: ' . $error->getMessage());
}

$_SESSION['community_csrf'] ??= bin2hex(random_bytes(24));
$_SESSION['community_form_started'] ??= time();
$successTicket = (string) ($_SESSION['community_success_ticket'] ?? '');
unset($_SESSION['community_success_ticket']);
$validTypes = ['question', 'bug', 'idea', 'docs'];
$requestedType = (string) ($_GET['type'] ?? 'question');
$formType = in_array((string) ($_POST['type'] ?? $requestedType), $validTypes, true) ? (string) ($_POST['type'] ?? $requestedType) : 'question';
$formTitle = mb_substr(trim((string) ($_POST['title'] ?? $_GET['title'] ?? '')), 0, 120);
$formError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    } elseif (time() - (int) $_SESSION['community_form_started'] < 2) {
        $formError = t('Het formulier werd te snel verstuurd. Controleer je bericht en probeer opnieuw.', 'The form was submitted too quickly. Review your message and try again.');
    } elseif (time() - $lastSubmission < 30) {
        $formError = t('Wacht even voordat je nog een onderwerp instuurt.', 'Please wait before submitting another topic.');
    } elseif (!in_array($type, $validTypes, true) || mb_strlen($title) < 6 || mb_strlen($title) > 120 || mb_strlen($message) < 20 || mb_strlen($message) > 5000) {
        $formError = t('Controleer het type, de titel (6–120 tekens) en uitleg (20–5000 tekens).', 'Check the type, title (6–120 characters), and description (20–5000 characters).');
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = t('Vul een geldig e-mailadres in of laat het veld leeg.', 'Enter a valid email address or leave the field empty.');
    } else {
        $submission = [
            'id' => 'LD-' . (new DateTimeImmutable())->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
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
            $_SESSION['community_success_ticket'] = $submission['id'];
            $_SESSION['community_csrf'] = bin2hex(random_bytes(24));
            $_SESSION['community_form_started'] = time();
            header('Location: ' . with_language('community.php?submitted=1') . '#new-topic', true, 303);
            exit;
        }
    }
}

$pageKey = 'support';
$pageTitle = t('LocalDeck Support & feedback', 'LocalDeck Support & feedback');
$pageDescription = t('Stel een vraag, meld een fout of deel een idee met het LocalDeck-team.', 'Ask a question, report a bug, or share an idea with the LocalDeck team.');
require __DIR__ . '/inc/header.php';
?>
<section class="page-hero community-hero">
    <div class="shell hero-grid small">
        <div><span class="eyebrow"><i></i>LOCALDECK SUPPORT</span><h1><?= e(t('Hulp, foutmeldingen en ideeën.', 'Help, bug reports, and ideas.')) ?></h1><p><?= e(t('Je bericht gaat rechtstreeks naar het LocalDeck-team. Na succesvolle verzending krijg je een herkenbaar ticketnummer voor je administratie.', 'Your message goes directly to the LocalDeck team. After successful delivery, you receive a recognizable ticket number for your records.')) ?></p><a class="button primary" href="#new-topic"><?= e(t('Bericht versturen', 'Send a message')) ?> <span>↓</span></a></div>
        <div class="community-orbit" aria-hidden="true"><span class="orbit-center"><img src="/assets/logo.png" alt=""></span><i class="orbit-item one">?</i><i class="orbit-item two">!</i><i class="orbit-item three">✦</i><i class="orbit-item four">⌘</i></div>
    </div>
</section>
<section class="section shell">
    <div class="notice-banner"><span>ℹ</span><div><b><?= e(t('Rechtstreeks contact zonder account', 'Direct contact without an account')) ?></b><p><?= e(t('Het formulier stuurt je bericht naar het LocalDeck-team en bewaart maximaal 180 dagen een lokale afleverkopie. Er worden geen IP-adressen opgeslagen.', 'The form sends your message to the LocalDeck team and retains a local delivery copy for up to 180 days. No IP addresses are stored.')) ?></p></div></div>
    <div class="section-heading"><span class="eyebrow"><i></i><?= e(t('Kies een categorie', 'Choose a category')) ?></span><h2><?= e(t('Waar kunnen we mee helpen?', 'How can we help?')) ?></h2></div>
    <div class="category-grid">
        <a href="#new-topic" data-topic-type="question"><span class="category-icon violet">?</span><div><h3><?= e(t('Vragen & hulp', 'Questions & help')) ?></h3><p><?= e(t('Installatie, services, projecten en dagelijks gebruik.', 'Installation, services, projects, and daily use.')) ?></p></div><b>→</b></a>
        <a href="#new-topic" data-topic-type="bug"><span class="category-icon red">!</span><div><h3><?= e(t('Fout melden', 'Report a bug')) ?></h3><p><?= e(t('Deel stappen, verwacht gedrag en diagnostische informatie.', 'Share steps, expected behavior, and diagnostic information.')) ?></p></div><b>→</b></a>
        <a href="#new-topic" data-topic-type="idea"><span class="category-icon cyan">✦</span><div><h3><?= e(t('Ideeën', 'Ideas')) ?></h3><p><?= e(t('Stel functies, verbeteringen en integraties voor.', 'Suggest features, improvements, and integrations.')) ?></p></div><b>→</b></a>
        <a href="#new-topic" data-topic-type="docs"><span class="category-icon amber">≡</span><div><h3><?= e(t('Wiki & uitleg', 'Wiki & documentation')) ?></h3><p><?= e(t('Meld onduidelijke, verouderde of ontbrekende uitleg.', 'Report unclear, outdated, or missing documentation.')) ?></p></div><b>→</b></a>
    </div>
</section>
<section class="section support-faq"><div class="shell"><div class="section-heading"><span class="eyebrow"><i></i>FAQ</span><h2><?= e(t('Misschien staat het antwoord hier al.', 'The answer may already be here.')) ?></h2></div><div class="faq-grid"><details><summary><?= e(t('Waarom opent localhost niet?', 'Why does localhost not open?')) ?></summary><p><?= e(t('Controleer eerst Apache en PHP, voer Poorten controleren uit en gebruik daarna Domeinen & HTTPS herstellen.', 'First check Apache and PHP, run Check ports, and then use Repair domains & HTTPS.')) ?></p></details><details><summary><?= e(t('Waar staan mijn websites?', 'Where are my websites stored?')) ?></summary><p><?= e(t('Projecten staan standaard in de map websites binnen de gekozen LocalDeck-omgeving.', 'Projects are stored by default in the websites folder inside the selected LocalDeck environment.')) ?></p></details><details><summary><?= e(t('Wordt testmail echt verzonden?', 'Is test mail actually sent?')) ?></summary><p><?= e(t('Nee. Mailpit vangt lokale projectmail op. Alleen dit openbare supportformulier gebruikt echte servermail naar het LocalDeck-team.', 'No. Mailpit captures local project mail. Only this public support form uses real server mail to the LocalDeck team.')) ?></p></details><details><summary><?= e(t('Kan ik een XAMPP-project importeren?', 'Can I import a XAMPP project?')) ?></summary><p><?= e(t('Ja. Gebruik de migratiewizard en bewaar de oorspronkelijke map en database-export totdat het nieuwe project volledig is getest.', 'Yes. Use the migration wizard and keep the original folder and database export until the new project is fully tested.')) ?></p></details></div></div></section>
<section class="section community-form-section" id="new-topic">
    <div class="shell form-layout">
        <div>
            <span class="eyebrow"><i></i><?= e(t('Nieuwe inzending', 'New submission')) ?></span>
            <h2><?= e(t('Vertel ons wat er speelt.', 'Tell us what is happening.')) ?></h2>
            <p><?= e(t('Schrijf één duidelijk onderwerp per inzending. Vermeld bij fouten de gebruikte LocalDeck-versie en de stappen waarmee het probleem herhaalbaar is.', 'Write one clear topic per submission. For bugs, include the LocalDeck version and steps that reproduce the issue.')) ?></p>
            <ul class="check-list"><li><?= e(t('Geen wachtwoord of geheime sleutel meesturen', 'Never include a password or secret key')) ?></li><li><?= e(t('Diagnoserapporten eerst op persoonsgegevens controleren', 'Check diagnostic reports for personal data first')) ?></li><li><?= e(t('Bewaar het ticketnummer na verzending', 'Keep the ticket number after sending')) ?></li></ul>
        </div>
        <form class="topic-form" method="post" action="<?= e(with_language('community.php')) ?>#new-topic">
            <?php if (isset($_GET['submitted']) && preg_match('/^LD-\d{8}-[A-F0-9]{6}$/', $successTicket)): ?><div class="form-message success ticket-success" role="status"><span>✓</span><div><?= e(t('Bedankt. Je bericht is per e-mail naar het LocalDeck-team verzonden.', 'Thank you. Your message was emailed to the LocalDeck team.')) ?><code><?= e($successTicket) ?></code></div></div><?php endif; ?>
            <?php if ($formError !== ''): ?><div class="form-message error" role="alert"><?= e($formError) ?></div><?php endif; ?>
            <input type="hidden" name="csrf" value="<?= e((string) $_SESSION['community_csrf']) ?>">
            <label class="honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
            <label><span><?= e(t('Categorie', 'Category')) ?></span><select name="type" data-topic-select required><option value="question"<?= $formType === 'question' ? ' selected' : '' ?>><?= e(t('Vraag of hulp', 'Question or help')) ?></option><option value="bug"<?= $formType === 'bug' ? ' selected' : '' ?>><?= e(t('Foutmelding', 'Bug report')) ?></option><option value="idea"<?= $formType === 'idea' ? ' selected' : '' ?>><?= e(t('Idee of verbetering', 'Idea or improvement')) ?></option><option value="docs"<?= $formType === 'docs' ? ' selected' : '' ?>><?= e(t('Wiki of documentatie', 'Wiki or documentation')) ?></option></select></label>
            <label><span><?= e(t('Titel', 'Title')) ?></span><input name="title" minlength="6" maxlength="120" required value="<?= e($formTitle) ?>" placeholder="<?= e(t('Vat het onderwerp kort samen', 'Summarize the topic briefly')) ?>"></label>
            <label><span><?= e(t('Uitleg', 'Description')) ?></span><textarea name="message" minlength="20" maxlength="5000" rows="8" required placeholder="<?= e(t('Wat verwachtte je, wat gebeurde er en hoe kunnen we het herhalen?', 'What did you expect, what happened, and how can we reproduce it?')) ?>"><?= e((string) ($_POST['message'] ?? '')) ?></textarea></label>
            <div class="form-two"><label><span><?= e(t('Naam (optioneel)', 'Name (optional)')) ?></span><input name="name" maxlength="80" value="<?= e((string) ($_POST['name'] ?? '')) ?>"></label><label><span><?= e(t('E-mail (optioneel)', 'Email (optional)')) ?></span><input type="email" name="email" maxlength="160" value="<?= e((string) ($_POST['email'] ?? '')) ?>"></label></div>
            <button class="button primary" type="submit"><?= e(t('Bericht versturen', 'Send message')) ?> <span>→</span></button>
            <small><?= e(t('Dit is geen accountregistratie. Je gegevens worden alleen gebruikt om je bericht te behandelen en eventueel te beantwoorden.', 'This is not account registration. Your details are used only to handle your message and, when possible, reply to it.')) ?> <a href="<?= e(with_language('privacy.php')) ?>"><?= e(t('Lees het privacybeleid.', 'Read the privacy policy.')) ?></a></small>
        </form>
    </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>
