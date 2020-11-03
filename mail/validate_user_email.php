<?php
/* @var MailHelper $this */
/* @var string $uEmail */
/* @var string $uHash */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$subject = SITE . ' Registration - Validate Email Address';

ob_start();
Loader::element('mail/wrapper', array('mh' => $this, 'template' => 'validate_user_email'));
$bodyHTML = ob_get_clean();
