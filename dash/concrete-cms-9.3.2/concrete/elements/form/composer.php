<?php
defined('C5_EXECUTE') or die("Access Denied.");
?>

<div class="form-group">
    <?php if ($view->supportsLabel()) { ?>
        <label class="control-label form-label" for="<?=$view->getControlID()?>"><?=$view->getLabel()?></label>
    <?php } ?>

    <?php if ($context->isRequired()) : ?>
        <span class="label label-info"><?= t('Required') ?></span>
    <?php endif; ?>

    <?php if ($context->getTooltip()): ?>
        <i class="fas fa-question-circle launch-tooltip" data-bs-toggle="tooltip" title="<?= h($context->getTooltip()); ?>"></i>
    <?php endif; ?>

    <?php $view->renderControl()?>
</div>
