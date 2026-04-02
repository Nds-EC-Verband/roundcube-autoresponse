<?php

/**
 * Autoresponse Plugin for Roundcube
 *
 * Adds a dedicated "Abwesenheitsnotiz" entry to the Settings navigation.
 * Uses the built-in Roundcube plugin template (same as password plugin).
 *
 * Requires: managesieve plugin
 * PHP:      7.4+
 *
 * @license MIT
 */
class autoresponse extends rcube_plugin
{
    public $task    = 'settings';
    public $noframe = true;
    public $noajax  = true;

    /** @var rcube */
    private $rc;

    private const SCRIPT_NAME = 'autoresponse';

    // -------------------------------------------------------------------------
    // Init
    // -------------------------------------------------------------------------

    public function init(): void
    {
        $this->rc = rcube::get_instance();
        $this->add_texts('localization/', true);

        $this->add_hook('settings_actions', [$this, 'settings_actions']);

        $this->register_action('plugin.autoresponse',      [$this, 'action_view']);
        $this->register_action('plugin.autoresponse-save', [$this, 'action_save']);
    }

    // -------------------------------------------------------------------------
    // Hooks
    // -------------------------------------------------------------------------

    public function settings_actions(array $args): array
    {
        $args['actions'][] = [
            'action' => 'plugin.autoresponse',
            'class'  => 'autoresponse',
            'label'  => 'autoresponse',
            'title'  => 'autoresponse_title',
            'domain' => 'autoresponse',
        ];

        return $args;
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function action_view(): void
    {
        $this->rc->output->set_pagetitle($this->gettext('autoresponse'));

        $this->include_stylesheet($this->get_css_path());

        $this->rc->output->add_label('save', 'saving');
        $this->rc->output->set_env('action', 'plugin.autoresponse');

        // Register and immediately enable the save command in JS.
        // Without this the toolbar button stays permanently disabled because
        // Roundcube does not know the command 'plugin.autoresponse-save'.
        $this->rc->output->add_script(
            "rcmail.register_command('plugin.autoresponse-save', function() {" .
            "  document.getElementById('autoresponse-form').submit();" .
            "}, true);",
            'docready'
        );

        $this->register_handler('plugin.body', [$this, 'render_form']);
        $this->rc->output->send('plugin');
    }

    public function action_save(): void
    {
        $this->rc->request_security_check(rcube_utils::INPUT_POST);

        // CSS must be included here too — action_view() is not called on POST
        $this->include_stylesheet($this->get_css_path());

        $enabled   = rcube_utils::get_input_value('_autoresponse_enabled',   rcube_utils::INPUT_POST) === '1';
        $subject   = (string) rcube_utils::get_input_value('_autoresponse_subject',   rcube_utils::INPUT_POST, true);
        $body      = (string) rcube_utils::get_input_value('_autoresponse_body',      rcube_utils::INPUT_POST, true);
        $date_from = trim((string) rcube_utils::get_input_value('_autoresponse_date_from', rcube_utils::INPUT_POST));
        $date_to   = trim((string) rcube_utils::get_input_value('_autoresponse_date_to',   rcube_utils::INPUT_POST));

        if ($enabled) {
            if (trim($subject) === '' && trim($body) === '') {
                $this->rc->output->command('display_message', $this->gettext('error_empty'), 'error');
                $this->rc->output->send();
                return;
            }

            $has_from = $date_from !== '';
            $has_to   = $date_to   !== '';

            if ($has_from !== $has_to) {
                $this->rc->output->command('display_message', $this->gettext('error_date_both_required'), 'error');
                $this->rc->output->send();
                return;
            }

            if ($has_from) {
                if (!$this->is_valid_date($date_from) || !$this->is_valid_date($date_to)) {
                    $this->rc->output->command('display_message', $this->gettext('error_date_invalid'), 'error');
                    $this->rc->output->send();
                    return;
                }
                if ($date_from > $date_to) {
                    $this->rc->output->command('display_message', $this->gettext('error_date_range'), 'error');
                    $this->rc->output->send();
                    return;
                }
            }
        }

        $result = $this->save_vacation_sieve([
            'enabled'   => $enabled,
            'subject'   => $subject,
            'body'      => $body,
            'date_from' => $date_from,
            'date_to'   => $date_to,
        ]);

        if ($result === true) {
            $this->rc->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');
        } else {
            $this->rc->output->command('display_message', $this->gettext('sieve_error') . ' ' . $result, 'error');
        }

        // Re-register JS command for the re-rendered form
        $this->rc->output->add_script(
            "rcmail.register_command('plugin.autoresponse-save', function() {" .
            "  document.getElementById('autoresponse-form').submit();" .
            "}, true);",
            'docready'
        );

        $this->register_handler('plugin.body', [$this, 'render_form']);
        $this->rc->overwrite_action('plugin.autoresponse');
        $this->rc->output->send('plugin');
    }

    // -------------------------------------------------------------------------
    // Form renderer  (plugin.body slot)
    // -------------------------------------------------------------------------

public function render_form(array $attrib = []): string
    {
        $data = $this->load_vacation_data();

        $table = new html_table(['cols' => 2, 'class' => 'propform autoresponse-table']);

        // --- Enabled ---
        $cb_attrs = [
            'type'  => 'checkbox',
            'id'    => 'autoresponse_enabled',
            'name'  => '_autoresponse_enabled',
            'value' => '1',
            'class' => 'checkbox',
        ];
        if ($data['enabled']) {
            $cb_attrs['checked'] = 'checked';
        }
        $table->add('title', html::label('autoresponse_enabled', rcube::Q($this->gettext('enabled'))));
        $table->add(null, html::tag('input', $cb_attrs));

        // --- Subject ---
        $table->add('title', html::label('autoresponse_subject', rcube::Q($this->gettext('subject'))));
        $table->add(null, html::tag('input', [
            'type'  => 'text',
            'id'    => 'autoresponse_subject',
            'name'  => '_autoresponse_subject',
            'value' => rcube::Q($data['subject'], 'strict', false),
            'class' => 'text autoresponse-wide',
        ]));

        // --- Body ---
        $table->add('title', html::label('autoresponse_body', rcube::Q($this->gettext('body'))));
        $table->add(null, html::tag('textarea', [
            'id'    => 'autoresponse_body',
            'name'  => '_autoresponse_body',
            'rows'  => 8,
            'cols'  => 60,
            'class' => 'text autoresponse-wide',
        ], rcube::Q($data['body'], 'strict', false)));

        // --- Section heading: validity period ---
        $table->add(['colspan' => 2, 'class' => 'autoresponse-section-title'],
            html::tag('h3', null, rcube::Q($this->gettext('period_section')))
        );
        $table->add(['colspan' => 2, 'class' => 'autoresponse-hint'],
            rcube::Q($this->gettext('period_hint'))
        );

        // --- Date fields with clear button ---
        $date_field = function(string $id, string $name, string $value): string {
            $input = html::tag('input', [
                'type'  => 'date',
                'id'    => $id,
                'name'  => $name,
                'value' => rcube::Q($value, 'strict', false),
                'class' => 'text',
            ]);
            $clear = html::tag('button', [
                'type'    => 'button',
                'class'   => 'autoresponse-date-clear',
                'onclick' => "document.getElementById('" . rcube::JQ($id) . "').value=''; $('#' + '" . rcube::JQ($id) . "').trigger('change'); return false;",
                'title'   => '×',
            ], '×');
            return html::span(['class' => 'autoresponse-date-wrap'], $input . $clear);
        };

        // --- Date from ---
        $table->add('title', html::label('autoresponse_date_from', rcube::Q($this->gettext('date_from'))));
        $table->add(null, $date_field('autoresponse_date_from', '_autoresponse_date_from', $data['date_from']));

        // --- Date to ---
        $table->add('title', html::label('autoresponse_date_to', rcube::Q($this->gettext('date_to'))));
        $table->add(null, $date_field('autoresponse_date_to', '_autoresponse_date_to', $data['date_to']));

        // CSRF token + hidden action fields
        $hidden = html::tag('input', ['type' => 'hidden', 'name' => '_token',  'value' => $this->rc->get_request_token()])
                . html::tag('input', ['type' => 'hidden', 'name' => '_action', 'value' => 'plugin.autoresponse-save'])
                . html::tag('input', ['type' => 'hidden', 'name' => '_task',   'value' => 'settings']);

        // Wire up the form object so Roundcube can find it
        $this->rc->output->add_gui_object('passform', 'autoresponse-form');

        $submit_button = $this->rc->output->button([
            'command' => 'plugin.autoresponse-save',
            'class'   => 'button mainaction submit',
            'label'   => 'save',
        ]);
        $form_buttons = html::p(['class' => 'formbuttons footerleft'], $submit_button);

        $form = $this->rc->output->form_tag(
            [
                'id'     => 'autoresponse-form',
                'name'   => 'autoresponse-form',
                'method' => 'post',
                'action' => './?_task=settings&_action=plugin.autoresponse-save',
            ],
            $hidden .
            html::p(['class' => 'autoresponse-hint'], rcube::Q($this->gettext('hint'))) .
            $table->show()
        );

        // --- Client-Side Validation Logic (JavaScript) ---
        $js = "
            // Setzt Subject und Body auf Pflichtfelder, wenn Autoresponder aktiv ist
            $('#autoresponse_enabled').on('change', function() {
                var is_checked = $(this).is(':checked');
                $('#autoresponse_subject, #autoresponse_body').prop('required', is_checked);
            }).trigger('change'); // Führt es beim Laden direkt einmal aus

            // Setzt das Mindestdatum für 'bis' auf den Wert von 'von'
            $('#autoresponse_date_from').on('change', function() {
                $('#autoresponse_date_to').attr('min', $(this).val());
            }).trigger('change');
        ";
        $this->rc->output->add_script($js, 'docready');

        return html::div(['id' => 'prefs-title', 'class' => 'boxtitle'], $this->gettext('autoresponse'))
            . html::div(
                ['class' => 'box formcontainer scroller'],
                html::div(['class' => 'boxcontent formcontent'], $form) . $form_buttons
            );
    }

    // -------------------------------------------------------------------------
    // Sieve helpers
    // -------------------------------------------------------------------------

    private function load_vacation_data(): array
    {
        $defaults = [
            'enabled'   => false,
            'subject'   => '',
            'body'      => '',
            'date_from' => '',
            'date_to'   => '',
        ];

        try {
            $sieve = $this->get_sieve();
            if ($sieve === null) {
                return $defaults;
            }

            $content = $sieve->get_script(self::SCRIPT_NAME);
            if ($content === false || $content === null) {
                return $defaults;
            }

            $data = array_merge($defaults, $this->parse_vacation_script((string) $content));

            // Auto-disable the checkbox if the validity period has expired.
            // The Sieve script itself already stopped sending replies, but the
            // UI should reflect that state so the user is not confused.
            if ($data['enabled'] && $data['date_to'] !== '') {
                $today = (new DateTime())->format('Y-m-d');
                if ($data['date_to'] < $today) {
                    $data['enabled'] = false;
                }
            }

            return $data;

        } catch (Throwable $e) {
            $this->log_error($e->getMessage());
            return $defaults;
        }
    }

    /** @return true|string */
    private function save_vacation_sieve(array $data)
    {
        try {
            $sieve = $this->get_sieve();
            if ($sieve === null) {
                return $this->gettext('sieve_connect_error');
            }

            if (!$data['enabled']) {
                $sieve->save_script(self::SCRIPT_NAME, "# autoresponse disabled\r\n");
                $sieve->deactivate(self::SCRIPT_NAME);
                return true;
            }

            $script = $this->build_vacation_script($data);

            if (!$sieve->save_script(self::SCRIPT_NAME, $script)) {
                return $this->gettext('sieve_save_error');
            }
            if (!$sieve->activate(self::SCRIPT_NAME)) {
                return $this->gettext('sieve_activate_error');
            }

            return true;

        } catch (Throwable $e) {
            $this->log_error($e->getMessage());
            return $e->getMessage();
        }
    }

    private function build_vacation_script(array $data): string
    {
        $has_dates = $data['date_from'] !== '' && $data['date_to'] !== '';

        $requires = ['"vacation"'];
        if ($has_dates) {
            $requires[] = '"date"';
            $requires[] = '"relational"';
        }

        $subject = $this->sieve_escape($data['subject']);
        $body    = $this->sieve_escape($data['body']);
        $lines   = ['require [' . implode(', ', $requires) . '];', ''];

        if ($has_dates) {
            $from    = $this->sieve_escape($data['date_from']);
            $to      = $this->sieve_escape($data['date_to']);
            $lines[] = 'if allof (';
            $lines[] = '    currentdate :value "ge" "date" "' . $from . '",';
            $lines[] = '    currentdate :value "le" "date" "' . $to   . '"';
            $lines[] = ') {';
            $lines[] = '    vacation :days 1 :subject "' . $subject . '" "' . $body . '";';
            $lines[] = '}';
        } else {
            $lines[] = 'vacation :days 1 :subject "' . $subject . '" "' . $body . '";';
        }

        $lines[] = '';
        return implode("\r\n", $lines);
    }

    private function parse_vacation_script(string $script): array
    {
        $data = ['enabled' => false, 'subject' => '', 'body' => '', 'date_from' => '', 'date_to' => ''];

        if (preg_match('/^#\s*autoresponse disabled/i', ltrim($script))) {
            return $data;
        }
        if (!preg_match('/\bvacation\b/i', $script)) {
            return $data;
        }

        $data['enabled'] = true;

        if (preg_match('/:subject\s+"((?:[^"\\\\]|\\\\.)*)"/i', $script, $m)) {
            $data['subject'] = $this->sieve_unescape($m[1]);
        }
        if (preg_match('/\bvacation\b[^\r\n]*\s"((?:[^"\\\\]|\\\\.)*)"[\t ]*;/i', $script, $m)) {
            $data['body'] = $this->sieve_unescape($m[1]);
        }
        if (preg_match('/currentdate\s+:value\s+"ge"\s+"date"\s+"(\d{4}-\d{2}-\d{2})"/i', $script, $m)) {
            $data['date_from'] = $m[1];
        }
        if (preg_match('/currentdate\s+:value\s+"le"\s+"date"\s+"(\d{4}-\d{2}-\d{2})"/i', $script, $m)) {
            $data['date_to'] = $m[1];
        }

        return $data;
    }

    private function get_sieve(): ?object
    {
        $this->load_config();

        if (!class_exists('rcube_sieve', false)) {
            $lib = (string) $this->rc->config->get('autoresponse_sieve_lib', '');

            if ($lib !== '' && file_exists($lib)) {
                require_once $lib;
            } else {
                // Fallback: search standard plugin directory
                $candidates = [
                    RCUBE_PLUGINS_DIR . 'managesieve/lib/Roundcube/rcube_sieve.php',
                    RCUBE_PLUGINS_DIR . 'managesieve/lib/rcube_sieve.php',
                ];
                foreach ($candidates as $f) {
                    if (file_exists($f)) {
                        require_once $f;
                        break;
                    }
                }
            }
        }

        if (!class_exists('rcube_sieve', false)) {
            $this->log_error('rcube_sieve class not found – managesieve plugin must be installed.');
            return null;
        }

        $config = $this->rc->config;
        $host   = (string) $config->get('autoresponse_sieve_host', $config->get('managesieve_host', 'localhost'));
        $port   = (int)    $config->get('autoresponse_sieve_port', $config->get('managesieve_port', 0));
        $tls    = (bool)   $config->get('autoresponse_sieve_tls',  $config->get('managesieve_usetls', false));

        if (preg_match('#^(tls|ssl)://#i', $host)) {
            $tls  = true;
            $host = (string) preg_replace('#^(tls|ssl)://#i', '', $host);
        }
        if ($port === 0) {
            $port = getservbyname('sieve', 'tcp') ?: 4190;
        }

        $sieve = new rcube_sieve(
            $this->rc->user->get_username(),
            $this->rc->decrypt($_SESSION['password']),
            $host, $port,
            (string) $config->get('managesieve_auth_type', ''),
            $config->get('managesieve_auth_cid'),
            $config->get('managesieve_auth_pw'),
            $tls,
            (array)  $config->get('managesieve_disabled_extensions', []),
            (bool)   $config->get('managesieve_debug', false),
            (array)  $config->get('managesieve_conn_options', [])
        );

        if ($sieve->error()) {
            $this->log_error('rcube_sieve connect/login failed (error: ' . $sieve->error() . ')');
            return null;
        }

        return $sieve;
    }

    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------

    /**
     * Returns the skin-aware CSS path, falling back to elastic.
     */
    private function get_css_path(): string
    {
        $skin = (string) $this->rc->config->get('skin', 'elastic');
        $path = "skins/{$skin}/autoresponse.css";
        if (!file_exists($this->home . '/' . $path)) {
            $path = 'skins/elastic/autoresponse.css';
        }
        return $path;
    }

    private function sieve_escape(string $str): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $str);
    }

    private function sieve_unescape(string $str): string
    {
        return str_replace(['\\"', '\\\\'], ['"', '\\'], $str);
    }

    private function is_valid_date(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }

    private function log_error(string $message): void
    {
        rcube::raise_error(
            ['code' => 600, 'type' => 'php', 'message' => 'autoresponse plugin: ' . $message],
            true, false
        );
    }
}