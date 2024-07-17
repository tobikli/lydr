<?php

use Concrete\Core\Support\Facade\Url as UrlFacade;
use Concrete\Core\Utility\Service\Url;

defined('C5_EXECUTE') or die("Access Denied.");

$checker = new Permissions($currentFolder);

/** @var $urlHelper Url */
?>

<div class="row row-cols-auto g-0 align-items-center">

    <div class="btn-group me-3">
        <a href="<?=$view->url('/dashboard/users/groups')?>" class="btn btn-primary p-2"><i class="fa fa-bars"></i></a>
        <a href="<?=$view->url('/dashboard/users/groups', 'view_tree')?>" class="btn btn-secondary p-2"><i class="fa fa-sitemap"></i></a>
    </div>

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
                <a class="ccm-hover-icon launch-tooltip" data-bs-placement="top" title="<?php echo h(t('Jump to Folder')) ?>" data-launch-dialog="navigate-group-manager" href="javascript:void(0);">
                    <i class="fas fa-share" aria-hidden="true"></i>
                </a>
            </li>

            <?php if ($checker->canAddGroupFolder()) { ?>
            <li>
                <a class="ccm-hover-icon launch-tooltip" data-bs-placement="top" title="<?php echo h(t('New Folder')) ?>" href="javascript:void(0);" data-launch-dialog="add-group-manager-folder">
                    <i class="fas fa-folder-plus" aria-hidden="true"></i>
                </a>
            </li>
            <?php } ?>
            <?php if ($checker->canAddGroup()) { ?>

            <li>
                <a class="ccm-hover-icon launch-tooltip" data-bs-placement="top" title="<?php echo h(t('Add Group')) ?>"
                   href="<?php echo (string)UrlFacade::to("/dashboard/users/add_group"); ?>">
                    <i class="fas fa-users" aria-hidden="true"></i>
                </a>
            </li>

            <?php } ?>
        </ul>
    </div>
</div>

<?php
$addFolderAction = (string)UrlFacade::to('/ccm/system/dialogs/tree/node/add/group_folder');

if (isset($currentFolder)) {
    $addFolderAction .= '?treeNodeID=' . $currentFolder->getTreeNodeID();
}
?>

<script>
    $(function() {
        $('a[data-launch-dialog=add-group-manager-folder]').on('click', function (e) {
            e.preventDefault();

            $.fn.dialog.open({
                width: 550,
                height: 'auto',
                modal: true,
                title: '<?php echo t('Add Folder')?>',
                href: '<?php echo $addFolderAction?>'
            });
        });

        $('a[data-launch-dialog=navigate-group-manager]').on('click', function(e) {
            e.preventDefault();

            $.fn.dialog.open({
                width: '560',
                height: '500',
                modal: true,
                title: '<?php echo t('Jump to Folder')?>',
                href: '<?php echo UrlFacade::to('/ccm/system/dialogs/groups/jump_to_folder')?>'
            });
        });
    });
</script>
