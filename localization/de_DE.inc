<?php

/**
 * Autoresponse Plugin – Deutsche Übersetzung
 */

$labels = [];

// Navigation & Seitentitel
$labels['autoresponse']       = 'Abwesenheitsnotiz';
$labels['autoresponse_title'] = 'Abwesenheitsnotiz verwalten';

// Formularfelder
$labels['enabled']            = 'Abwesenheitsnotiz aktiv';
$labels['subject']            = 'Betreff';
$labels['body']               = 'Nachricht';

// Abschnitt Gültigkeitszeitraum
$labels['period_section']     = 'Gültigkeitszeitraum (optional)';
$labels['period_hint']        = 'Wenn ein Zeitraum angegeben wird, wird die Abwesenheitsnotiz nur innerhalb dieses Zeitraums versendet. Beide Felder müssen ausgefüllt oder beide leer sein.';
$labels['date_from']          = 'Von';
$labels['date_to']            = 'Bis';

// Hinweistext oberhalb des Formulars
$labels['hint']               = 'Aktivieren Sie die Abwesenheitsnotiz, um eingehende E-Mails automatisch zu beantworten, z.B. während Ihres Urlaubs.';

// Erfolgsmeldungen
$labels['successfullysaved']  = 'Einstellungen wurden gespeichert.';

// Fehlermeldungen – Validierung
$labels['error_empty']            = 'Bitte geben Sie mindestens einen Betreff oder eine Nachricht ein.';
$labels['error_date_both_required'] = 'Bitte geben Sie entweder beide Datumsfelder an oder lassen Sie beide leer.';
$labels['error_date_invalid']     = 'Eines der angegebenen Datumsformate ist ungültig. Bitte das Format JJJJ-MM-TT verwenden.';
$labels['error_date_range']       = 'Das Startdatum muss vor dem Enddatum liegen.';

// Fehlermeldungen – Sieve
$labels['sieve_error']            = 'Sieve-Fehler:';
$labels['sieve_connect_error']    = 'Verbindung zum Sieve-Server fehlgeschlagen. Bitte prüfen Sie die Konfiguration.';
$labels['sieve_save_error']       = 'Das Sieve-Script konnte nicht gespeichert werden.';
$labels['sieve_activate_error']   = 'Das Sieve-Script konnte nicht aktiviert werden.';
$labels = [];
$labels['autoresponse']             = 'Abwesenheitsnotiz';
$labels['autoresponse_title']       = 'Automatische Abwesenheitsnotiz einrichten';
$labels['hint']                     = 'Konfigurieren Sie hier Ihre automatische Abwesenheitsnotiz.';
$labels['enabled']                  = 'Abwesenheitsnotiz aktiv';
$labels['subject']                  = 'Betreff';
$labels['body']                     = 'Nachrichtentext';
$labels['period_section']           = 'Gültigkeitszeitraum (optional)';
$labels['period_hint']              = 'Lassen Sie beide Felder leer, damit die Abwesenheitsnotiz dauerhaft gilt.';
$labels['date_from']                = 'Gültig ab';
$labels['date_to']                  = 'Gültig bis';
$labels['successfullysaved']        = 'Einstellungen wurden gespeichert.';
$labels['error_empty']              = 'Bitte geben Sie Betreff und/oder Nachrichtentext ein.';
$labels['error_date_both_required'] = 'Bitte geben Sie entweder beide Daten (von und bis) an oder lassen Sie beide leer.';
$labels['error_date_invalid']       = 'Das angegebene Datum ist ungültig (Format: JJJJ-MM-TT).';
$labels['error_date_range']         = 'Das Startdatum muss vor dem Enddatum liegen.';
$labels['sieve_error']              = 'Sieve-Fehler:';
$labels['sieve_connect_error']      = 'Verbindung zum Sieve-Server fehlgeschlagen.';
$labels['sieve_save_error']         = 'Das Sieve-Skript konnte nicht gespeichert werden.';
$labels['sieve_activate_error']     = 'Das Sieve-Skript konnte nicht aktiviert werden.';
