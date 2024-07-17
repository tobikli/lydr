<?php

use Concrete\Core\Utility\Service\Url;
use Concrete\Core\Support\Facade\Url as UrlFacade;

defined('C5_EXECUTE') or die("Access Denied.");

/** @var $urlHelper Url */
?>

<div class="row row-cols-auto g-0 align-items-center">
    <?php if (!empty($itemsPerPageOptions)): ?>
    <div class="col-auto">
        <div class="btn-group">
            <button
                    type="button"
                    class="btn btn-secondary p-2 dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">

                <span id="selected-option">
                    <?php echo $itemsPerPage; ?>
                </span>
            </button>

            <ul class="dropdown-menu">
                <li class="dropdown-header">
                    <?php echo t('Items per page') ?>
                </li>

                <?php foreach ($itemsPerPageOptions as $itemsPerPageOption): ?>
                    <?php
                    $url = $urlHelper->setVariable([
                        'itemsPerPage' => $itemsPerPageOption
                    ]);
                    ?>

                    <li data-items-per-page="<?php echo $itemsPerPageOption; ?>">
                        <a class="dropdown-item <?php echo ($itemsPerPageOption === $itemsPerPage) ? 'active' : ''; ?>"
                           href="<?php echo h($url) ?>">
                            <?php echo $itemsPerPageOption; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-auto">
        <ul class="ccm-dashboard-header-icons">
            <li>
                <a href="<?php echo (string)UrlFacade::to("/ccm/system/dialogs/logs/delete_all"); ?>" class="ccm-hover-icon dialog-launch" dialog-title="Delete All" title="<?php echo h(t('Clear log')) ?>">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
            </li>
            <li>
                <a href="<?php echo (string)UrlFacade::to("/dashboard/reports/logs/export"); ?>" class="ccm-hover-icon" title="<?php echo h(t('Export CSV')) ?>">
                    <i class="fas fa-download" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
    </div>
</div>

