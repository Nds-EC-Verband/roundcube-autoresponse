<?php

/**
 * Autoresponse Plugin – English translation
 */

$labels = [];

// Navigation & page title
$labels['autoresponse']       = 'Out of Office';
$labels['autoresponse_title'] = 'Manage out-of-office reply';

// Form fields
$labels['enabled']            = 'Out-of-office reply active';
$labels['subject']            = 'Subject';
$labels['body']               = 'Message';

// Validity period section
$labels['period_section']     = 'Validity period (optional)';
$labels['period_hint']        = 'If a date range is specified, the out-of-office reply will only be sent within that period. Either fill in both fields or leave both empty.';
$labels['date_from']          = 'From';
$labels['date_to']            = 'Until';

// Hint text above the form
$labels['hint']               = 'Enable the out-of-office reply to automatically respond to incoming e-mails, e.g. during your vacation.';

// Success messages
$labels['successfullysaved']  = 'Settings saved successfully.';

// Validation error messages
$labels['error_empty']              = 'Please enter at least a subject or a message.';
$labels['error_date_both_required'] = 'Please fill in both date fields or leave both empty.';
$labels['error_date_invalid']       = 'One of the dates has an invalid format. Please use the format YYYY-MM-DD.';
$labels['error_date_range']         = 'The start date must be before the end date.';

// Sieve error messages
$labels['sieve_error']            = 'Sieve error:';
$labels['sieve_connect_error']    = 'Could not connect to the Sieve server. Please check your configuration.';
$labels['sieve_save_error']       = 'The Sieve script could not be saved.';
$labels['sieve_activate_error']   = 'The Sieve script could not be activated.';
