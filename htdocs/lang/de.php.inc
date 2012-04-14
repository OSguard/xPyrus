<?php

// $Id: de.php.inc 6216 2008-07-25 21:23:50Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/lang/de.php.inc $

define('WORD_YES','ja');
define('WORD_NO','nein');
define('WORD_UNKOWN','unbekannt');
define('WORD_EMAIL','Email');

define('ERR_FUNCTION_NOT_AVAILABLE', 'Diese Funktion ist momentan leider nicht verfügbar.');
define('ERR_NO_USER', 'Es konnte kein User mit diesem Namen gefunden werden.');
define('ERR_NO_LOGGIN', 'Diese Funktion steht nur angemeldeten Nutzern zur Verfügung.');
define('ERR_NO_EXTERN', 'Diese Funktion steht nur Nutzern dieses ' . PROJECT_NAME . '-Standortes zur Verfügung.');
define('ERR_NO_GROUP', 'Diese Organisation existiert nicht.');
define('ERR_NO_COURSE', 'Es wurde kein Fach ausgewählt oder es existiert kein Fach.');
define('ERR_NO_ENTRY', 'Es wurde kein Eintrag ausgewählt oder dieser existiert nicht.');
define('ERR_NO_TAG', 'Es wurde kein Datum ausgewählt oder dieses existiert nicht.');
define('ERR_NO_EVENT', 'Es wurde keine Veranstaltung ausgewählt oder diese existiert nicht.');
define('ERR_NO_NEWS', 'Es wurde keine News ausgewählt oder diese existiert nicht.');
define('ERR_NO_ATTACHMENT', 'Dein Anhang wurde nicht gefunden.');
define('ERR_NO_FILE', 'Die Datei wurde nicht gefunden.');
define('ERR_NO_STUDY_PATH', 'Kein Studiengang angegeben');

define('ERR_COURSE_DUPLICATE_FILE', 'Die Unterlage ist bereits von Dir oder jemand anderem hochgeladen worden.');
define('ERR_FORM_NOT_VALID', 'Deine Angaben reichen nicht aus um die gewünschte Aktion auszuführen. Die markierten Felder sind nicht oder nicht korrekt ausgefüllt.');
define('ERR_COURSE_NO_FILE', 'Du hast keine Unterlagendatei angegeben oder sie ist leer.');
define('ERR_COURSE_FILE_NOT_DOWNLOADED', 'Du hast diese Unterlage noch nicht heruntergeladen.');
define('ERR_COURSE_LARGE_FILE', 'Deine Unterlage hat das Dateigrößenlimit überschritten.');
define('ERR_COURSE_DUAL_RATE', 'Du kannst jede Unterlage nur einmal bewerten.');
define('ERR_COURSE_FILE_TOO_EXPENSIVE', 'Du kannst Dir den Download nicht leisten.');

define('ERR_ATTACHMENT', 'Dein Anhang hat das Dateigrößenlimit überschritten.');
define('ERR_ATTACHMENT_IMAGE', 'Dein Anhang ist keine gültige Bilddatei.');
define('ERR_PICTURE_MIMETYPE', 'Die Datei, die Du hochgeladen hast, ist kein Bild.');
define('ERR_NEWS_CAPTION_SHORT', 'Die Überschrift ist zu kurz.');
define('ERR_NEWS_START_DATE', 'Start-Datum nicht angegeben oder liegt in der Vergangenheit');
define('ERR_NEWS_END_DATE', 'End-Datum nicht angegeben oder liegt in der Vergangenheit');

define('ERR_DIARY_NO_TEXT', 'Der Tagebuch-Text darf nicht leer sein bzw. der Eintrag darf nicht nur aus Anhängen bestehen.');
define('ERR_GUESTBOOK_NO_TEXT', 'Der Gästebuch-Text darf nicht leer sein bzw. der Eintrag darf nicht nur aus Anhängen bestehen.');

define('ERR_NO_ENTRY_TO_REPORT', 'Kein Eintrag zu melden');

define('ERR_ON_IGNORELIST', 'Du stehst auf der Ignore-Liste des Users.');

define('ERR_ENTRY_TOO_LONG', 'Dein Text ist zu lang.');
define('ERR_ENTRY_TITLE_TOO_LONG', 'Dein Betreff/Deine Überschrift ist zu lang');
define('ERR_INVALID_RANDID', 'Dem Eintrag fehlen wichtige Informationen und er konnte nicht bearbeitet werden.');

define('ERR_PM_NO_RESIVERS', 'Keine gültigen Empfänger gefunden');
define('ERR_PM_POINTS', 'Du hast zu wenig Punkte um die PM zu senden.');

define('ERR_INSUFFICIENT_RIGHTS', 'Dir fehlen die Rechte, um diese Aktion auszuführen.');

define('ERR_FORUM_POINTS', 'Du hast zu wenig Punkte um ein anonymes Posting zu posten.');
define('ERR_FORUM_SHOW', 'Es ist kein Forum ausgewählt.');
define('ERR_FORUM_EXIST', 'Dieses Forum existiert nicht.');
define('ERR_FORUM_LOGIN', 'Nur eingeloggte User dürfen dieses Forum betreten.');
define('ERR_FORUM_GROUP', 'Nur Mitglieder der Organisation dürfen dieses Forum betreten.');

define('ERR_FORUM_THREAD_SHOW', 'Es ist kein Thread ausgewählt.');
define('ERR_FORUM_THREAD_EDIT', 'Die Informationen sind ungültig, die Änderung am Thread wird nicht gespeichert.');
define('ERR_FORUM_THREAD_LINK', 'Es wurde kein Thread gefunden, der zu diesem verschobenen Thread gehört.');
define('ERR_FORUM_THREAD_CAPTION', 'Die Thread Caption ist ungültig.');
define('ERR_FORUM_THREAD_RATING_USER', 'Kein User ausgewählt oder der User existiert nicht.');
define('ERR_FORUM_THREAD_RATING_OWN', 'Du bist böse, man darf sich nicht selbst bewerten!');
define('ERR_FORUM_THREAD_RATING_NOWRITE', 'Du bist böse, man darf erst bewerten, wenn man etwas geschrieben hat!');
define('ERR_FORUM_THREAD_RATING_NOAUTHOR', 'Du bist böse, man darf erst bewerten, wenn man dem User etwas geschrieben hat!');
define('ERR_FORUM_THREAD_RATING_NOVALUE', 'Konnte keine Bewertung finden');
define('ERR_FORUM_THREAD_RATING_REPEAT', 'Du kannst nur einmal bewerten.');
define('ERR_FORUM_THREAD_DEL_FIRST_ENTRY', 'Das erste Posting darf nicht gelöscht werden. Alternative: Thread verstecken/schließen.');

define('ERR_FORUM_ADD', 'Die Informationen sind ungültig, es wird kein Forum erstellt.');
define('ERR_FORUM_EDIT', 'Die Informationen sind ungültig, die Änderung am Forum wird nicht gespeichert.');
define('ERR_FORUM_NO_TEMPLATE', 'Bitte wähle ein Template aus!');
define('ERR_CATEGORY_SHOW', 'Es ist keine Kategorie ausgewählt.');
define('ERR_CATEGORY_ADD', 'Die Informationen sind ungültig, es wird keine Kategorie erstellt');
define('ERR_CATEGORY_EDIT', 'Die Informationen sind ungültig, die Änderung an der Kategorie wird nicht gespeichert');

define('ERR_GROUP_EDIT', 'Der Organisationsname und/oder die Beschreibung sind nicht gültig.');
define('ERR_GROUP_MEMBERSHIP', 'Du bist kein Mitglied in der Organisation');

define('ERR_BANNER_ADMIN', 'Eingabewerte sind ungültig. Banner wird nicht eingetragen.');
define('ERR_BANNER_ADMIN_STARTDATE', 'Das Anfangsdatum ist nicht gültig.');
define('ERR_BANNER_ADMIN_ENDDATE', 'Das Enddatum ist nicht gültig.');
define('ERR_BANNER_ADMIN_FILE', 'Banner konnte nicht hochgeladen werden, die Datei ist möglicherweise zu groß.');
define('ERR_BANNER_ADMIN_NO_FILE', 'Kein Bild für Banner hochgeladen.');
define('ERR_BANNER_INSUFFICIENT_INFORMATION', 'Nicht genug Informationen, um einen Eintrag zu erzeugen.');

define('ERR_TAG_ADMIN', 'Kein gültiges Tag.');
define('ERR_ROLE_NOT_EXISTING_ADMIN', 'Rolle existiert nicht.');
define('ERR_USER_NOT_GIVEN_ADMIN', 'Kein User angegeben.');
define('ERR_ROLE_NOT_GIVEN_ADMIN', 'Keine Rolle angegeben.');
define('ERR_GROUP_NOT_GIVEN_ADMIN', 'Keine Organisation angegeben.');
define('ERR_ADD_OR_DEL_ADMIN', 'Bitte für eine Aktion (Hinzufügen/Löschen) entscheiden.');
define('ERR_NO_NAME_OR_DESCRIPTION', 'Name und/oder Beschreibung fehlen.');
define('ERR_INVALID_BIRTHDATE', 'Geburtsdatum ist ungültig.');

define('ERR_SEARCH', 'Keine Suchparameter gefunden');

define('ERR_USER_REGISTRATION_NONMATCHING_UNIMAIL', 'Deine E-Mail-Adresse passt nicht zur gewählten Hochschule.');
// TODO: use rewrite_ - function here for URL
define('ERR_USER_REGISTRATION_EXISTING_UNIMAIL', 'Du bist/warst schon registriert. Wenn Du einen Grund dafür hast, <a href="/support">wende Dich bitte an den Support</a> :-)');
define('ERR_USER_REGISTRATION_INVALID_USERNAME', 'Der Benutzername enthält nicht erlaubte Zeichen.');
define('ERR_USER_REGISTRATION_EXISTING_USERNAME', 'Der Benutzername ist bereits vergeben.');
define('ERR_USER_REGISTRATION_INVALID_PRIVATE_EMAIL', 'Deine private E-Mail-Adresse ist ungültig.');
define('ERR_USER_REGISTRATION_INVALID_PASSWORD', 'Das Passwort ist leer oder enthält Leerzeichen.');
define('ERR_USER_REGISTRATION_NONMATCHING_PASSWORD', 'Die beiden eingegebenen Passwörter sind nicht identisch!');

define('ERR_USER_CANVASS_LOGOFF','Nur wenn du eingeloggt bist, kannst du ' . PROJECT_NAME . ' weiterempfehlen');
define('ERR_USER_CANVASS_NAME','Bitte gib einen Namen an, damit sich dein Freund angesprochen fühlt.');
define('ERR_USER_CANVASS_EMAIL','Du musst eine gültige E-Mail-Adresse angeben');

define('ERR_FEATURES_TOOMANY', 'Du hast mehr Features ausgewählt als Dir zur Verfügung stehen.');
define('ERR_FEATURE_NOT_EXISTING', 'Feature existiert nicht.');

define('ERR_FRIENDLIST_SELF', 'Du willst Dich nicht etwa selber hinzufügen, oder?');

define('ERR_USER_INVALID_PASSWORD', 'Du hast kein gültiges Passwort eingegeben. Es muss mindestens 4 Zeichen lang sein und darf keine Leerzeichen enthalten.');
define('ERR_INVALID_PASSWORD', 'Du hast kein gültiges Passwort eingegeben. Aktion abgebrochen');
define('ERR_INVALID_TERMS_OF_USE', 'Du musst die Nutzerbedingung akzeptieren');


define('ERR_BLOG_DOESNT_EXIST', 'Dieses Blog gibt es nicht auf ' . PROJECT_NAME . '.');
define('ERR_BLOG_EXISTS', 'Du hast bereits ein Blog auf ' . PROJECT_NAME . '.');

define('ERR_USERNAME_TOO_SHORT', 'Zu wenig Zeichen für den Usernamen angegeben. Es müssen mindestens 3 Zeichen sein.');

define('ERR_ERROR_OCCURED', 'Es sind Fehler aufgetreten');

define('ENTRY_SCRIPSIT', 'schrieb');
define('ENTRY_NO_SUBJECT', 'kein Betreff');

define('COLOR_RED','rot');
define('COLOR_YELLOW','gelb');
define('COLOR_GREEN', 'grün');

define('STATUS_RED', 'Vergeben');
define('STATUS_YELLOW', 'Eventuell zu überzeugen');
define('STATUS_GREEN', 'Ich schau mich um');
define('STATUS_NONE', 'geheim');

define('CAPTION_SOCCER_BET', 'EM-Tippspiel');
define('CAPTION_BIRTHDAY_REMINDER', 'Geburtstags-Erinnerung');
define('CAPTION_USER_WARNING', 'Verwarnung');
define('CAPTION_GROUP_APPLICATION', 'Organisation-Aufnahme');
define('CAPTION_BLOG_NEW_COMMENT', 'Neuer Blogkommentar');
define('CAPTION_WELCOME_NEW', 'Willkommen auf ' . PROJECT_NAME);

define('SYSTEM_USER_NAME', '' . PROJECT_NAME . '-Support');
define('SYSTEM_USER_LINK', 'Zur Support-Seite');

#MediaCneter
define('MC_ROOT_DIR_NAME', 'Meine Dateien');
define('MC_ROOT_DIR_DESC', 'Alle Dateien, die du hochgeladen hast!');


#Mail
define('MANTIS_MAIL_MESSAGE_HEADER', 'Hallo und vielen Dank für die Meldung/Nachricht. Wir werden sie so schnell wie möglich bearbeiten. Sie wurde abgelegt unter der Nummer ');
define('MANTIS_MAIL_MESSAGE_REQUEST', 'Bei Rückfragen bitte diese Nummer angeben.');
define('MANTIS_MAIL_MESSAGE_FOOTER', "Viele Grüße\n\n " . PROJECT_NAME . ".\n\n-- \n Bitte auf diese Mail NICHT antworten, sie wurde automatisch generiert. Bei Rückfragen bitte das Supportformular nutzen.");
define('MANTIS_MAIL_MESSAGE_CAPTION', 'Bestätigung der Anfrage auf ' . PROJECT_NAME);
define('MANTIS_MAIL_MESSAGE_SENDER', 'Kontaktformular auf ' . PROJECT_NAME);

define('MANTIS_EMAIL_USE_NOTICE','nur in drigenden Fällen, sonst PN benutzen');

define('SUPPORT_MAIL', '' . PROJECT_NAME . '-Support');

define('CAPTCHA_CALCULATE', 'Bitte rechne für uns aus: ');

define('DEFAULT_GROUP', 'Organisation');


define('BLOGOSPHERE_TITLE', PROJECT_NAME . ' - Blogosphäre');
define('BLOGOSPHERE_DESCRIPTION', 'Alle Blogs auf ' . PROJECT_NAME);

define('CAPTCHA_PLUS', 'plus');
define('CAPTCHA_MINUS', 'minus');
define('CAPTCHA_TIMES', 'mal');

# navigation
// IMPORTANT (linap, 10.05.2007): do _not_ name the constants hierarchically by the place
// where it ought to be called in our rewrite structure. It is meant as an abstraction!
define('NAME_HOME', 'Startseite');
define('NAME_GROUPS', 'Organisationen');
define('NAME_PM_INBOX', 'Nachrichtenübersicht');
define('NAME_PM_OUTBOX', 'Nachrichtenübersicht &ndash; gesendete Nachrichten');
define('NAME_PM_COMPOSE', 'Nachricht verfassen');

define('NAME_NEWS_ARCHIEV', 'News-Archiv');
define('NAME_NEWS_ADD', 'News schreiben');
define('NAME_NEWS_EDIT', 'News bearbeiten');
define('NAME_NEWS_DEL', 'News löschen');
define('NAME_NEWS_RSS', 'News von ' . PROJECT_NAME);

define('NAME_ENTRY_DEL', 'Eintrag löschen');
define('NAME_ENTRY_DEL_FROM', 'Eintrag löschen von');
define('NAME_FORUM_DEL','Löschen des Forums');
define('NAME_CATEGORY_DEL','Kategorie löschen');
define('NAME_AWARD_DEL','Award löschen');
define('NAME_AWARD_RANG_DEL','Rang des Users löschen');
define('NAME_USERS_DEL','Löschen der User');
define('NAME_ROLE_DEL','Role löschen');
define('NAME_GROUP_DEL','Organisation löschen');
define('NAME_FILE_DEL', 'Datei löschen');
define('NAME_EVENT_DEL_FROM','Event löschen von');

define('NAME_EVENTS', 'Campus-Kalender');
define('NAME_EVENTS_FEED', 'Campus-Kalender');

define('NAME_FORUM_ALL', '' . PROJECT_NAME . '-Foren');
define('NAME_FORUM_LATEST', 'Letzte Beiträge');
define('NAME_FORUM_LATEST_ENTRY', 'Letzter Beitrag');
define('NAME_FORUM_SEARCH', 'Forumssuche');
define('NAME_FORUM_LATEST_RSS', 'Letzte Foren-Beiträge auf ' . PROJECT_NAME . '');
define('NAME_FORUM_VIRTUAL', 'Virtuelles Forum');
define('NAME_FORUM_HISTORY', 'Geschichte des Forumbeitrags');
define('NAME_FORUM_THREAD_DEL','Thread löschen');

define('NAME_USER_PROFILE', 'Persönliche Einstellungen');
define('NAME_USER_DELETE', 'Account löschen');
define('NAME_USER_NEW', 'Anmeldung');
define('NAME_USER_ACTIVATE', 'Aktivierung');
define('NAME_USER_PASSWORD_LOST', 'Passwort vergessen');
define('NAME_USER_SEARCH', 'Usersuche');
define('NAME_USER_CANVASS', 'User werben');

define('NAME_REVERSE_FRIENDLIST', 'Umgekehrte Freundesliste');

define('NAME_HELP', 'Hilfe');
define('NAME_FAQ', 'Häufig gestellte Fragen (FAQ)');
define('NAME_FORMATCODE', 'Formatcode');
define('NAME_SUPPORT', 'Support-Anfrage');

define('NAME_STUDIES', 'Studium');
define('NAME_COURSE', 'Fach');
define('NAME_COURSE_FILE', 'Unterlage');
define('NAME_COURSE_FILES', 'Unterlagen');
define('NAME_COURSE_FILE_LATEST', 'neuste Unterlagen');
define('NAME_COURSE_FILE_LATEST_RSS', 'neuste Unterlagen auf ' . PROJECT_NAME . '');

define('NAME_TERMS_OF_USE', 'Allgemeine Nutzungsbedingungen');
define('NAME_PRIVACY', 'Datenschutz');
define('NAME_IMPRINT', 'Impressum');
define('NAME_TOOLBAR', '' . PROJECT_NAME . '-Toolbar');

define('NAME_BLOGOSPHERE', 'Blogosphäre');
define('NAME_COMMENTS', 'Kommentare');

define('NAME_APPLICATION', 'Bewerben');
define('NAME_LEAVE', 'Austreten');

define('NAME_RELOAD', 'Seite neu laden');
define('NAME_EDIT', 'Bearbeiten von');
define('NAME_DELETE', 'Löschen von');
define('QUOTE_LEFT', '„');
define('QUOTE_RIGHT', '”');
define('NAME_INVISIBLE','invisible');

define('NAME_DATE_YESTERDAY', 'gestern');
define('NAME_DATE_TODAY', 'heute');
define('NAME_DATE_TOMORROW', 'morgen');

define('NAME_FIRST_NEW_ENTRY', 'zum ersten ungelesenen Beitrag');

define('NAME_SPORTS_HOME', '' . PROJECT_NAME . '-Tippspiele');
define('NAME_SOCCER_BET', '-Tippspiel');
define('NAME_SOCCER_BET_RANKING', ' Rangliste');

define('LINK_USER_PAGE', 'Zur User-Profil-Seite von ');

define('DO_ACTION_FORUM_THREAD_DEL','Dieser Thread wird unwiderruflich gelöscht!');
define('DO_ACTION_FORUM_ENTRY_DEL','Der Beitrag in diesem Thread wird unwiderruflich gelöscht!');
define('DO_ACTION_FORUM_DEL','Alle Thread und Beiträge dieses Forums werden unwiderruflich gelöscht!');
define('DO_ACTION_CATEGORY_DEL','Alle Foren, Thread und Beiträge dieser Kategorie werden unwiderruflich gelöscht!');
define('DO_ACTION_AWARD_DEL','Dieser Award wird unwiderruflich geloescht!');
define('DO_ACTION_AWARD_RANG_DEL','Dieser Rang wird unwiderruflich gelöscht!');
define('DO_ACTION_USERS_DEL','Diese User werden unwiderruflich gelöscht!');
define('DO_ACTION_ROLE_DEL','Die Rolle ist nicht mehr vorhanden und die User verlieren die rechte!');
define('DO_ACTION_GROUP_DEL','Die Organisation ist nicht mehr vorhanden und die User verlieren ihr Zugehörigkeit!');
define('DO_ACTION_FILE_DEL','Die Datei wird unwiderruflich gelöscht!');
define('DO_ACTION_NEWS_DEL','Die News wird unwiderruflich gelöscht!');
define('DO_ACTION_EVENT_DEL','Das Event wird unwiderruflich gelöscht!');

# translating some DB entities
function translate_privacy($name) {
    switch ($name) {
    case 'no one': return 'keiner';
    case 'all': return 'alle';
    case 'logged in': return 'eingeloggt';
    case 'group': return 'Organisationsmitglieder';
    case 'on friendlist': return 'auf meiner Freundesliste';
    }
    return '';
}
function translate_box($name) {
    switch ($name) {
    case 'friendslist': return 'Freundesliste';
    case 'birthday': return 'Geburtstagskinder';
    case 'birthday_personal': return 'nächste Geburtstage';
    case 'shoutbox': return 'Shoutbox';
    case 'courses': return 'Fächer';
    case 'courses_files': return 'Unterlagen';
    case 'user_search': return 'Suche';
    case 'user_online': return 'User online';
    case 'blog': return 'Blogosphäre';
    }
    // return original text here is appropiate here
    // because somebody could forget to insert translation here for new boxes ...
    return $name;
}
function translate_rating_cat($name) {
    switch ($name) {
    case 'helpful': return 'Nützlichkeit';
    case 'scientific level': return 'wissenschaftlicher Anspruch';
    case 'document description': return 'zutreffende Beschreibung';
    case 'freetext': return 'Anmerkung';
    }
    return '';
}

function translate_rights($name) {
    switch ($name) {
    case 'BLOG_FILTER': return 'Tagebuch Filter';
    case 'FEATURE_BOX_REARRANGEMENT': return 'Boxen managen';
    case 'FEATURE_REVERSE_FRIENDLIST': return 'Umgekehrte Freundesliste';
    case 'FEATURE_SMALLWORLD': return 'Vernetzung der Freundesliste';
    case 'FRIENDLIST_EXTENDED_CATEGORIES': return 'Erweiterte Freundesliste';
    case 'GB_ADVANCED_STATS': return 'Erweiterte Gästebuchstatistik';
    case 'GB_ENTRY_COMMENT': return 'Gästebuch kommentieren';
    case 'GB_FILTER': return 'Gästebuch filtern';
    case 'PM_ADD_ATTACHMENT': return 'PN mit Anhängen';
    case 'PM_SENDTO_FRIENDS': return 'Rundmail an Freund als PN';
    }
    return $name;
}

function translate_nationality($nat) {
    if ($nat == 'unknown') {
        return 'unbekannt';
    }
    return $nat;
}

function translate_mantis($text){
	switch ($text) {
	case F_SOURCE_REPORT_ENTRY: return 'Eintrag melden';
    case F_SOURCE_ERROR_REPORT: return 'Fehler berichten';
    case F_SOURCE_DELETE_ENTRY: return 'Löschung beantragen';
    case F_SOURCE_GENERAL_QUERY: return 'Allgemeine Anfrage';
    case F_SOURCE_FEATURE_REQUEST: return 'Funktionenwunsch';
    case F_SOURCE_CHANGE_USERNAME: return 'Username ändern';
    case F_SOURCE_CHANGE_UNI: return 'Hochschule wechseln';
    case F_SOURCE_CHANGE_BIRTHDAY: return 'Geburtstag ändern';
    case F_SOURCE_DELETE_ACCOUNT: return 'Account löschen';
    case F_SOURCE_ADD_ME_TO_GROUP: return 'Zu Organisation hinzufügen';
    case F_SOURCE_DELETE_ME_FROM_GROUP: return 'Aus Organisation entfernen';
    case F_SOURCE_FOUND_GROUP: return 'Neue Organisation eintragen lassen';
    case F_SOURCE_MISSING_COURSE: return 'Fehlendes Fach melden';
    case F_SOURCE_MISC: return 'Sonstiges';
    case F_SOURCE_UNKNOWN: return 'Unbekannt';
	case F_SOURCE_CALENDER: return 'Kalender';	
	}
    return $text;
}

function genitiv($word) {
    if ($word[strlen($word)-1] == 's') {
        return $word . '\'';
    }
    // no Deppenapostroph here
    return $word . 's';
}

function course_rating_desc($rating){
	switch ($rating) {
		case 6: return 'sehr gut';
        case 5: return 'gut';
        case 4: return 'mittelmäßig';
        case 3: return 'geht so';
        case 2: return 'nicht zu empfehlen';
        case 1: return 'überzeugt überhaupt nicht';
	}
    return $rating;
}

?>
