<?php defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Attribute\Key\Category as AttributeCategory;
use Concrete\Core\Form\Service\Form;

$packageRepository = $packageRepository ?? null;
$connection = $connection ?? null;

$app = Concrete\Core\Support\Facade\Application::getFacadeApplication();
$ci = $app->make('helper/concrete/urls');
$ch = $app->make('helper/concrete/ui');
$tp = new TaskPermission();
$pkgArray = Package::getInstalledList();

$ci = $app->make('helper/concrete/urls');
$txt = $app->make('helper/text');
$nav = $app->make('helper/navigation');
$config = $app->make('config');
$displayDeleteBtn = $config->get('concrete.misc.display_package_delete_button');
/** @var Form $form */
$form = $app->make(Form::class);
$catList = AttributeCategory::getList();

if ($this->controller->getTask() == 'install_package' && isset($showInstallOptionsScreen) && $showInstallOptionsScreen && $tp->canInstallPackages()) {
    ?>
    <form method="post" action="<?=$this->action('install_package', $pkg->getPackageHandle())?>">
        <?php
        echo $token->output('install_options_selected');
        echo View::element('dashboard/install', null, $pkg->getPackageHandle());
        $swapper = $pkg->getContentSwapper();
        if ($swapper->allowsFullContentSwap($pkg)) {
            ?>
            <h4><?=t('Clear this Site?')?></h4>
            <p><?=t('%s can fully clear your website of all existing content and install its own custom content in its place. If you\'re installing a theme for the first time you may want to do this. Clear all site content?', t($pkg->getPackageName())) ?></p>
            <?php
            $u = $app->make(Concrete\Core\User\User::class);
            if ($u->isSuperUser()) {
                $disabled = [];
                ?>
                <div class="alert-message warning"><p><?=t('This will clear your home page, uploaded files and any content pages out of your site completely. It will completely reset your site and any content you have added will be lost.')?></p></div>
                <?php
            } else {
                $disabled = ['disabled'=>true];
                ?>
                <div class="alert-message info"><p><?=t('Only the %s user may reset the site\'s content.', USER_SUPER)?></p></div>
                <?php
            }
            ?>
            <div class="form-group">
                <label class="control-label form-label"><?=t("Swap Site Contents")?></label>
                <div class="form-check">
                    <?=$form->radio('pkgDoFullContentSwap',0, 0, $disabled)?>
                    <?=$form->label('pkgDoFullContentSwap1',t('No. Do <strong>not</strong> remove any content or files from this website.'), ['class' => 'form-check-label'])?>
                </div>
                <div class="form-check">
                    <?=$form->radio('pkgDoFullContentSwap',1, 0, $disabled)?>
                    <?=$form->label('pkgDoFullContentSwap2',t('Yes. Reset site content with the content found in this package'), ['class' => 'form-check-label'])?>
                </div>

            </div>
            <?php if (count($pkg->getContentSwapFiles()) === 1) {?>
                <?php echo $form->hidden("contentSwapFile", array_pop(array_keys($pkg->getContentSwapFiles()))) ?>
            <?php } else {?>
                <div class="form-group">
                    <?php echo $form->label("contentSwapFile", t("Starting Point")); ?>
                    <?php echo $form->select("contentSwapFile", $pkg->getContentSwapFiles()); ?>
                </div>
            <?php } ?>
            <?php
        }
        ?>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?=$this->url('/dashboard/extend/install')?>" class="btn btn-secondary float-start"><?=t('Cancel')?></a>
                <input type="submit" value="<?=t('Install %s', t($pkg->getPackageName()))?>" class="btn btn-primary float-end" />
            </div>
        </div>
    </form>
    <?php
} elseif (isset($pkg) && is_object($pkg) && $this->controller->getTask() == 'uninstall' && $tp->canUninstallPackages()) {
    $pkgID = $pkg->getPackageID();
    ?>
    <form method="post" class="form-stacked" id="ccm-uninstall-form" action="<?= $view->action('do_uninstall_package'); ?>">
        <?= $token->output('uninstall'); ?>
        <input type="hidden" name="pkgID" value="<?=$pkgID ?>" />
        <fieldset>
            <h2><?= t('Uninstall Package'); ?></h2>
            <table class="table table-bordered table-striped">
                <tr>
                    <td class="ccm-marketplace-list-thumbnail"><img src="<?= $ci->getPackageIconURL($pkg); ?>" /></td>
                    <td class="ccm-addon-list-description" style="width: 100%"><h3><?= t($pkg->getPackageName()) ?> - <?= $pkg->getPackageVersion(); ?></h3><?= t($pkg->getPackageDescription()); ?></td>
                </tr>
            </table>
            <?php
            if ($pkg->hasUninstallNotes()) {
                View::element('dashboard/uninstall', null, $pkg->getPackageHandle());
            }
            ?>
            <div class="alert alert-danger">
                <?=t('This will remove all elements associated with the %s package. While you can reinstall the package, this may result in data loss.', t($pkg->getPackageName())) ?>
            </div>
            <div class="form-group">
                <label class="control-label form-label"><?= t('Move package to trash directory on server?'); ?></label>
                <div class="form-check">
                    <?= $app->make('helper/form')->checkbox('pkgMoveToTrash', 1); ?>
                    <label for="pkgMoveToTrash" class="form-check-label"><?= t('Yes, remove the package\'s directory from the installation directory.'); ?></label>
                </div>
            </div>
        </fieldset>
        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
            <?= $ch->submit(t('Uninstall'), 'ccm-uninstall-form', 'right', 'btn-danger'); ?>
            <?= $ch->button(t('Cancel'), $view->url('/dashboard/extend/install', 'inspect_package', $pkg->getPackageID()), ''); ?>
            </div>
        </div>
    </form>
    <?php
} else {
    // grab the total numbers of updates.
    // this consists of
    // 1. All packages that have greater pkgAvailableVersions than pkgVersion
    // 2. All packages that have greater pkgVersion than getPackageCurrentlyInstalledVersion
    $local = [];
    $remote = [];
    $pkgAvailableArray = [];

    if ($tp->canInstallPackages()) {
        $local = Package::getLocalUpgradeablePackages();
        $remote = Package::getRemotelyUpgradeablePackages();
    }
    // now we strip out any dupes for the total
    $updates = 0;
    $localHandles = [];
    foreach ($local as $_pkg) {
        ++$updates;
        $localHandles[] = $_pkg->getPackageHandle();
    }
    foreach ($remote as $_pkg) {
        if (!in_array($_pkg->getPackageHandle(), $localHandles)) {
            ++$updates;
        }
    }
    if ($tp->canInstallPackages()) {
        foreach (Package::getAvailablePackages() as $_pkg) {
            if (empty($pkgAvailableArray)) {
                Localization::clearCache();
            }
            Concrete\Core\Support\Facade\Package::setupLocalization($_pkg);
            $pkgAvailableArray[] = $_pkg;
        }
    }
    $thisURL = $view->url('/dashboard/extend/install');
    $sortMe = [];
    foreach ($pkgAvailableArray as $p) {
        $sortMe[] = ['name' => t($p->getPackageName()), 'pkg' => $p];
    }
    usort($sortMe, function (array $a, array $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    $availableArray = [];
    foreach ($sortMe as $p) {
        $availableArray[] = $p['pkg'];
    }
    // Load featured add-ons from the marketplace.
    $purchasedBlocksSource = [];
    if (
        $packageRepository instanceof \Concrete\Core\Marketplace\PackageRepositoryInterface &&
        $connection instanceof \Concrete\Core\Marketplace\Connection &&
        Config::get('concrete.marketplace.enabled') &&
        $tp->canInstallPackages()
    ) {
        $purchasedBlocksSource = $packageRepository->getPackages($connection, true);
    }

    $skipHandles = [];
    $handles = [];
    foreach ($purchasedBlocksSource as $remotePackage) {
        $handles[] = $remotePackage->handle;
    }

    foreach ($availableArray as $ava) {
        if (in_array($ava->getPackageHandle(), $handles)) {
            $skipHandles[] = $ava->getPackageHandle();
        }
    }

    $currentlyInstalledHandles = Package::getInstalledHandles();
    foreach ($currentlyInstalledHandles as $packageHandle) {
        $skipHandles[] = $packageHandle;
    }

    $purchasedBlocks = [];
    foreach ($purchasedBlocksSource as $pb) {
        if (!in_array($pb->handle, $skipHandles)) {
            $purchasedBlocks[$pb->handle] = $pb;
        }
    }

    if (isset($pkg) && is_object($pkg)) {
        ?>
        <table class="table table-bordered table-striped">
            <tr>
                <td class="ccm-marketplace-list-thumbnail"><img src="<?= $ci->getPackageIconURL($pkg); ?>" /></td>
                <td class="ccm-addon-list-description" style="width: 100%"><h3><?= t($pkg->getPackageName()) ?> - <?= $pkg->getPackageVersion(); ?></h3><?= t($pkg->getPackageDescription()) ?></td>
            </tr>
        </table>
        <?php
        foreach ($categories as $category) {
            /** @var Concrete\Core\Package\ItemCategory\ItemInterface */
            if ($category->hasItems($pkg)) {
                $category->renderList($pkg);
            }
        }
        ?>
        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <?php
                $tp = new TaskPermission();
                if ($tp->canUninstallPackages()) {
                    echo $ch->button(t('Uninstall Package'), $view->url('/dashboard/extend/install', 'uninstall', $pkg->getPackageID()), 'right', 'btn-danger');
                }
                ?>
                <a href="<?= $view->url('/dashboard/extend/install'); ?>" class=" btn btn-secondary"><?= t('Back to Add Functionality'); ?></a>
            </div>
        </div>
        <?php
    } else {
        if (isset($installedPKG) && is_object($installedPKG) && $installedPKG->hasInstallPostScreen()) {
            ?>
            <div style="display: none">
                <div id="ccm-install-post-notes">
                    <div class="ccm-ui">
                        <?= View::element('dashboard/install_post', null, $installedPKG->getPackageHandle()); ?>
                        <div class="dialog-buttons">
                            <a href="javascript:void(0)" onclick="jQuery.fn.dialog.closeAll()" class="btn btn-primary float-end"><?= t('Ok'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
            $(function () {
                $('#ccm-install-post-notes').dialog({
                    width: 500,
                    modal: true,
                    height: 400,
                    title: <?= json_encode(t('Installation Notes')); ?>,
                    buttons:[{}],
                    'open': function () {
                        $(this).parent().find('.ui-dialog-buttonpane').addClass("ccm-ui").html('');
                        $(this).find('.dialog-buttons').appendTo($(this).parent().find('.ui-dialog-buttonpane'));
                        $(this).find('.dialog-buttons').remove();
                    }
                });
            });
            </script>
            <?php
        }
        if ($updates > 0) {
            ?>
            <div class="alert alert-info">
                <h5><?= t('Add-On updates are available!'); ?></h5>
                <a class="btn btn-sm btn-secondary float-end" href="<?= $view->url('/dashboard/extend/update'); ?>"><?= t('Update Add-Ons'); ?></a>
                <?php
                if ($updates == 1) {
                    ?><p><?= t('There is currently <strong>1</strong> update available.'); ?></p><?php

                } else {
                    ?><p><?= t('There are currently <strong>%s</strong> updates available.', $updates); ?></p><?php

                } ?>
            </div>
            <?php
        } ?>
        <h3><?= t('Currently Installed'); ?></h3>
        <?php
        if (count($pkgArray) > 0) {
            usort($pkgArray, function($a,$b){
                return t($a->getPackageName()) <=> t($b->getPackageName());
            });
            foreach ($pkgArray as $pkg) {
                ?>
                <div class="d-flex border p-3">
                    <img style="height: 50px" class="me-3" src="<?= $ci->getPackageIconURL($pkg); ?>" />
                    <div>
                        <h4><?= t($pkg->getPackageName()) ?> <span class="badge bg-info" style="margin-right: 10px"><?= tc('AddonVersion', 'v.%s', $pkg->getPackageVersion()); ?></span></h4>
                        <p><?= t($pkg->getPackageDescription()) ?></p>
                    </div>
                    <div class="d-block ms-auto">
                        <a href="<?= URL::to('/dashboard/extend/install', 'inspect_package', $pkg->getPackageID()); ?>" class="btn btn-sm btn-secondary"><?= t('Details'); ?></a>
                    </div>
                </div>
                <?php
            }
        } else {
            ?><p><?= t('No packages have been installed.'); ?></p><?php
        }
        if ($tp->canInstallPackages()) {
            ?>
            <hr/>
            <h3><?= t('Awaiting Installation'); ?></h3>
            <?php
            if (count($availableArray) === 0 && count($purchasedBlocks) === 0) {
                if (!$connection) {
                    ?><p><?= t('Nothing currently available to install.'); ?></p><?php
                }
            } else {
                foreach ($purchasedBlocks as $pb) {
                    assert($pb instanceof \Concrete\Core\Marketplace\Model\RemotePackage);
                    ?>
                    <div class="d-flex border p-3">
                        <img style="height: 50px" class="me-3" src="<?= h($pb->icon); ?>" />
                        <div>
                            <h4><?= h($pb->name); ?> <span class="badge bg-info" style="margin-right: 10px"><?= tc('AddonVersion', 'v.%s', h($pb->version)); ?></span></h4>
                            <p><?= h($pb->summary); ?></p>
                        </div>
                        <div class="d-block ms-auto">
                            <a href="<?= URL::to('/dashboard/extend/install', 'download', $pb->id); ?>" class="btn btn-sm btn-secondary"><?= t('Download'); ?></a>
                        </div>
                    </div>
                    <?php
                }
                foreach ($availableArray as $obj) {
                    ?>
                    <div class="d-flex border p-3">
                        <img style="height: 50px" class="me-3" src="<?= $ci->getPackageIconURL($obj); ?>" />
                        <div>
                            <h4><?= t($obj->getPackageName()) ?> <span class="badge bg-info"><?= tc('AddonVersion', 'v.%s', $obj->getPackageVersion()); ?></span></h4>
                            <div><?= t($obj->getPackageDescription()) ?></div>
                        </div>
                        <?php
                        if ($obj instanceof Concrete\Core\Package\BrokenPackage) {
                            ?>
                            <div class="ms-auto launch-tooltip float-end" title="<?= h($obj->getInstallErrorMessage()) ?>">
                                <button type="button" disabled="disabled" class="btn btn-sm btn-secondary"><i class="fas fa-exclamation-circle"></i> <?= t('Can\'t Install!'); ?></button>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="btn-group ms-auto d-block">
                                <a href="<?= URL::to('/dashboard/extend/install', 'install_package', $obj->getPackageHandle()); ?>" class="btn btn-sm btn-secondary"><?= t('Install'); ?></a><?php
                                if ($displayDeleteBtn) {
                                    ?><a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="deletePackage('<?= $obj->getPackageHandle() ?>', '<?= $obj->getPackageName() ?>')"><?= t('Delete') ?></a>
                                    <?php
                                } ?>
                            </div>
                        <?php }
                        ?>
                    </div>
                    <?php
                }
            }

            View::element('dashboard/marketplace_connect_offer');

        }
    }
    ?>
    <script>
        deletePackage = function (packageHandle, packageName) {
            ConcreteAlert.confirm(
                <?= json_encode(t('Are you sure you want to delete this package?')) ?> + '<br/><code>' + packageName + '</code>',
                function() {
                    $("button[data-dialog-action='submit-confirmation-dialog']").prop("disabled", true);
                    location.href = "<?= $controller->action('delete_package') ?>/" + packageHandle + "/<?= $token->generate('delete_package') ?>";
                },
                'btn-danger',
                <?= json_encode(t('Delete')) ?>
            );
        };
    </script>
<?php
}
