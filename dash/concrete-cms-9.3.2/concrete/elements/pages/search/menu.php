<?php

use Concrete\Core\Utility\Service\Url;

defined('C5_EXECUTE') or die("Access Denied.");

/** @var $urlHelper Url */
?>

<div class="row row-cols-auto g-0 align-items-center">
    <?php if (!empty($itemsPerPageOptions)): ?>
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
    <?php endif; ?>
</div>

