<?php

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Controller\Dialog\Logs\DeleteAll;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;

/** @var int $logItem */
/** @var DeleteAll $controller */

$app = Application::getFacadeApplication();
/** @var Form $form */
$form = $app->make(Form::class)
?>

<div class="ccm-ui">
    <form method="post" data-dialog-form="delete-all-log-entry" action="<?php echo $controller->action('submit') ?>">
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" data-dialog-action="cancel">
                <?php echo t('Cancel') ?>
            </button>

            <button type="button" data-dialog-action="submit" class="btn btn-danger float-end">
                <?php echo t('Delete') ?>
            </button>
        </div>

        <strong>
            <?php echo t('Are you sure you wish to clear the log?') ?>
        </strong>
    </form>
</div>
