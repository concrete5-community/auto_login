<?php 
defined('C5_EXECUTE') or die('Access Denied.');
?>

<div class="ccm-dashboard-header-buttons">
    <button class="add-ip btn btn-primary">
        <?php  echo t('Add IP address') ?>
    </button>
</div>

<?php 
if (count($entries) === 0) {
    ?>
    <div class="alert alert-info"><button data-dismiss="alert" class="close" type="button">×</button><?php  echo t("No IP-addresses have been added yet.") ?></div>
    <?php 
} else {
    ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th style="width: 220px;"><?php  echo t('IP address') ?></th>
            <th><?php  echo t('User') ?></th>
            <th><?php  echo t('Description') ?></th>
            <th style="width: 120px;"><?php  echo t('Enabled') ?></th>
            <th style="width: 120px;">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        foreach ($entries as $ip => $entry) {
            ?>
            <tr data-ip="<?php  echo h($ip) ?>"
                data-token="<?php  echo $token->generate("auto_login::modify.{$ip}") ?>">
                <td><?php  echo h($ip) ?></td>
                <td><?php  echo h($entry['username']) ?></td>
                <td><?php  echo h($entry['description']) ?></td>
                <td><?php  echo ($entry['enabled']) ? t("Yes") : t("No"); ?></td>
                <td class="text-right">
                    <button data-href="<?php  echo URL::to('dashboard/system/registration/auto_login/delete') ?>" class="delete-ip btn btn-xs btn-danger">
                        <?php  echo t('Delete') ?>
                    </button>
                    <button data-href="<?php  echo URL::to('dashboard/system/registration/auto_login/edit') ?>" class="edit-ip btn btn-xs btn-success">
                        <?php  echo t('Edit') ?>
                    </button>
                </td>
            </tr>
            <?php 
        }
        ?>
        </tbody>
    </table>
    <?php 
}
?>
<script>
    (function () {
        var dialog_url = '<?php  echo URL::to('/dashboard/system/registration/auto_login/modify_dialog') ?>';
        var url = '<?php  echo URL::to('/dashboard/system/registration/auto_login/modify') ?>';

        // Add
        (function () {
            $('button.add-ip').click(function (e) {
                e.preventDefault();
                e.stopPropagation();

                $.post(dialog_url, function (data) {
                    var form = $('<div />').append(data);

                    form.submit(function (e) {
                        var data = form.find('form').serializeArray();

                        $.post(url, data, function (result) {
                            if (typeof result === 'object') {
                                if (result.error) {
                                    alert('<?php  echo t('Error adding IP address:') ?> ' + result.message);
                                    return false;
                                } else {
                                    window.location = '<?php  echo URL::to('/dashboard/system/registration/auto_login/add_success') ?>';
                                }
                            }
                        });

                        return false;
                    });

                    $.fn.dialog.open({
                        title: '<?php  echo t('Add IP address') ?>',
                        element: form.get(0)
                    });

                    return false;
                });

                return false;
            });
        }());

        // Edit
        (function () {
            $('button.edit-ip').click(function () {
                var tr = $(this).closest('tr');
                var ip = tr.data('ip');

                $.post(dialog_url, { ip: ip }, function (data) {
                    var form = $('<div />').append(data);

                    form.submit(function (e) {
                        var data = form.find('form').serializeArray();

                        $.post(url, data, function (result) {
                            if (typeof result === 'object') {
                                if (result.error) {
                                    alert('<?php  echo t('Error adding IP address:') ?> ' + result.message);
                                    return false;
                                } else {
                                    window.location = '<?php  echo URL::to('/dashboard/system/registration/auto_login/update_success') ?>';
                                }
                            }
                        });

                        return false;
                    });

                    $.fn.dialog.open({
                        title: '<?php  echo t('Update IP address') ?>',
                        element: form.get(0)
                    });

                    return false;
                });
            });
        }());

        // Delete
        (function () {
            $('button.delete-ip').click(function () {
                var tr = $(this).closest('tr');
                var ip = tr.data('ip');
                var token = tr.data('token');

                if (confirm('<?php  echo t("Do you want to delete this entry?") ?>')) {
                    $.getJSON($(this).data('href'), {
                        token: token,
                        ip: ip
                    }, function (result) {
                        if (typeof result === 'object') {
                            if (result.error) {
                                alert("<?php  echo t('Error deleting IP address:') ?> " + result.message);
                            } else {
                                window.location = '<?php  echo URL::to('dashboard/system/registration/auto_login/delete_success') ?>';
                            }
                        }
                    });
                }
            });
        }());

    }());
</script>