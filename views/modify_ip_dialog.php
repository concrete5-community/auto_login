<?php 
defined('C5_EXECUTE') or die('Access Denied.');

$form = Core::make('helper/form');
$user_selector = Core::make('helper/form/user_selector');
?>

<div class="modify-ip ccm-ui">
    <form class="form-horizontal">
        <input type="hidden" name="old_ip" value="<?php  echo $entry['old_ip'] ?>"/>
        <input type="hidden" name="token" value="<?php  echo $token->generate('auto_login::ip.modify') ?>"/>

        <div class="form-group">
            <label class="control-label col-sm-3">
                <?php  echo t('IP address') ?> *
            </label>

            <div class="col-sm-9">
                <input class='form-control' type="text" name="ip" placeholder="<?php  echo t('E.g. %s', '143.33.122.17') ?>" value="<?php  echo $entry['ip'] ?>" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3">
                <?php  echo t('User') ?> *
            </label>

            <div class="col-sm-9" style="padding-top: 7px">
                <?php  echo $user_selector->selectUser('userID', $entry['uID']); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3">
                <?php  echo t('Description') ?>
            </label>

            <div class="col-sm-9">
                <input class='form-control' type="text" name="description" maxlength="150" value="<?php  echo $entry['description'] ?>" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3">
                <?php  echo t('Status') ?>
            </label>

            <div class="col-sm-9">
                <?php 
                echo $form->select('enabled', array(0 => t('Disabled'), 1 => t('Enabled')), $entry['enabled']);
                ?>
            </div>
        </div>

        <div class="form-group" style="margin-top: 40px;">
            <div class="col-sm-9 col-sm-offset-3">
                <button class="auto-login-modify-ip-submit btn btn-primary">
                    <?php  echo t('Submit') ?>
                </button>
            </div>
        </div>
    </form>
</div>

