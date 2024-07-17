<?php

use Concrete\Block\PageTitle\Controller;

defined('C5_EXECUTE') or die('Access Denied.');

/** @var Controller $controller */
/** @var bool|null $useCustomTitle */
/** @var bool|null $useFilterTitle */
/** @var bool|null $useFilterTopic */
/** @var bool|null $useFilterTag */
/** @var bool|null $useFilterDate */
/** @var string|null $topicTextFormat */
/** @var string|null $tagTextFormat */
/** @var string|null $dateTextFormat */
/** @var string|null $filterDateFormat */
/** @var string|null $titleText */
/** @var string|null $formatting */
/** @var \Concrete\Core\Form\Service\Form $form */

if (!strlen($titleText ?? '')) {
    $titleText = $controller->getTitleText();
}

?>

<div class="form-group">
    <label class="control-label form-label">
        <?php echo t("Custom Title"); ?>
    </label>

    <div class="form-check">
        <label for="useCustomTitle" class="form-check-label">
            <?php echo $form->checkbox('useCustomTitle', '1', $useCustomTitle ?? false); ?>
            <?php echo t('Override page name with custom title?'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?php echo $form->label('titleText', t('Custom Title Text')); ?>
    <?php echo $form->text('titleText', $titleText); ?>
</div>

<div class="form-group">
    <?php echo $form->label('formatting', t('Formatting Style')); ?>
    <?php echo $form->select('formatting',
        [
            "h1" => t("H1"),
            "h2" => t("H2"),
            "h3" => t("H3"),
            "h4" => t("H4"),
            "h5" => t("H5"),
            "h6" => t("H6")
        ], $this->controller->formatting); ?>
</div>

<div class="form-group">
    <label class="control-label form-label">
        <?php echo t("Page Title Filtering"); ?>
    </label>

    <div class="form-check">
        <label for="useFilterTitle" class="form-check-label">
            <?php echo $form->checkbox('useFilterTitle', '1', $useFilterTitle ?? false); ?>
            <?php echo t('Enable other blocks to filter this page title'); ?>
        </label>
    </div>

    <div class="card card-body bg-light filterTitleOptions">
        <div class="form-check">
            <label for="useFilterTopic" class="form-check-label">
                <?php echo $form->checkbox('useFilterTopic', '1', $useFilterTopic ?? false); ?>
                <?php echo t('Topic'); ?>
            </label>
        </div>

        <div class="form-check">
            <label for="useFilterTag" class="form-check-label">
                <?php echo $form->checkbox('useFilterTag', '1', $useFilterTag ?? false); ?>
                <?php echo t('Tag'); ?>
            </label>
        </div>

        <div class="form-check mb-3">
            <label for="useFilterDate" class="form-check-label">
                <?php echo $form->checkbox('useFilterDate', '1', $useFilterDate ?? false); ?>
                <?php echo t('Date'); ?>
            </label>
        </div>

        <div class="filterTopicOptions">
            <div class="form-group">
                <?php echo $form->label('topicTextFormat', t('Topic Text Formatting')); ?>
                <?php echo $form->select('topicTextFormat',
                    [
                        0 => t('Default'),
                        'upperWord' => t('Capitalize all words'),
                        'upperFirst' => t('Capitalize first word'),
                        'lowercase' => t('Lowercase'),
                        'uppercase' => t('Uppercase')
                    ], $topicTextFormat ?? 0); ?>
            </div>
        </div>

        <div class="filterTagOptions">
            <div class="form-group">
                <?php echo $form->label('tagTextFormat', t('Tag Text Formatting')); ?>
                <?php echo $form->select('tagTextFormat',
                    [
                        'upperWord' => t('Capitalize all words'),
                        'upperFirst' => t('Capitalize first word'),
                        'lowercase' => t('Lowercase'),
                        'uppercase' => t('Uppercase')
                    ], $tagTextFormat ?? null); ?>
            </div>
        </div>

        <div class="filterDateOptions">
            <div class="form-group">
                <?php echo $form->label('dateTextFormat', t('Date Text Formatting')); ?>
                <?php echo $form->select('dateTextFormat',
                    [
                        0 => t('Default'),
                        'upperWord' => t('Capitalize all words'),
                        'upperFirst' => t('Capitalize first word'),
                        'lowercase' => t('Lowercase'),
                        'uppercase' => t('Uppercase')
                    ], $dateTextFormat ?? 0); ?>
            </div>

            <div class="form-group">
                <?php echo $form->label('filterDateFormat', t('Date Year and Month Format')); ?>
                <?php echo $form->text('filterDateFormat', $filterDateFormat ?? t('F Y')); ?>
            </div>

            <div class="help-block">
                <?php echo sprintf(t('See the formatting options for year and month at %s.'), '<a href="http://www.php.net/date" target="_blank">php.net/date</a>'); ?>
            </div>
        </div>
    </div>
</div>

<script>
    (function ($) {
        $(function () {
            let $useFilterTitle = $('#useFilterTitle');
            let $useFilterTopic = $('#useFilterTopic');
            let $useFilterTag = $('#useFilterTag');
            let $useFilterDate = $('#useFilterDate');

            if ($useFilterTitle.prop('checked')) {
                $('.filterTitleOptions').show();
            } else {
                $('.filterTitleOptions').hide();
            }

            $useFilterTitle.on('change', function () {
                $('.filterTitleOptions').toggle();
            });

            if ($useFilterTopic.prop('checked')) {
                $('.filterTopicOptions').show();
            } else {
                $('.filterTopicOptions').hide();
            }

            $useFilterTopic.on('change', function () {
                $('.filterTopicOptions').toggle();
            });

            if ($useFilterTag.prop('checked')) {
                $('.filterTagOptions').show();
            } else {
                $('.filterTagOptions').hide();
            }

            $useFilterTag.on('change', function () {
                $('.filterTagOptions').toggle();
            });

            if ($useFilterDate.prop('checked')) {
                $('.filterDateOptions').show();
            } else {
                $('.filterDateOptions').hide();
            }

            $useFilterDate.on('change', function () {
                $('.filterDateOptions').toggle();
            });
        });
    })(jQuery);
</script>
