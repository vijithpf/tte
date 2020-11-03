<?php
/* @var MailHelper $this */
/* @var string $msgSubject */
/* @var string $msgBody */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$subject = t('%s - %s', $msgSubject, SITE);

ob_start();
Loader::element('mail/wrapper', array('mh' => $this, 'template' => 'common'));
$bodyHTML = ob_get_clean();
