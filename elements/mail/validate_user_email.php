<?php
/* @var string $uEmail */
/* @var string $uHash */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$activationURL = BASE_URL . View::url('/login', 'v', $uHash);
$ui            = UserInfo::getByEmail($uEmail);
$username      = $ui->getUserName();
?>

<p>Dear <?php echo $username; ?>,</p>
<p>Please click the following URL in order to activate your account for <?php echo e(SITE); ?>:</p>
<p><a href="<?php echo $activationURL; ?>"><?php echo $activationURL; ?></a></p>
<p>Thank you!</p>
<p>
    Regards,<br>
    <?php echo e(SITE); ?>
</p>
