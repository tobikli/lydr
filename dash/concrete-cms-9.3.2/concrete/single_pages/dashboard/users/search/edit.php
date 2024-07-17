<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Form\Service\Widget\FileFolderSelector;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\Component\AvatarCropperInstanceFactory;

/**
 * @var Concrete\Core\Page\View\PageView $view
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\User\UserInfo $user
 * @var string[] $folderList
 * @var bool $canEditAvatar
 * @var bool $canEditUserName
 * @var bool $canEditEmail
 * @var bool $canEditPassword
 * @var bool $canEditHomeFileManagerFolderID
 * @var bool $canEditTimezone
 * @var bool $canEditLanguage
 * @var bool $canViewAccountModal
 * @var string[] $allowedEditAttributes
 * @var bool $canAddGroup
 * @var bool $shouldViewIgnoredIPMismatches
 * @var bool $canEditIgnoredIPMismatches
 * @var string $groupsJSON
 * @var Doctrine\ORM\PersistentCollection $attributeSets
 * @var Concrete\Core\Entity\Attribute\Key\UserKey[] $unassigned
 * @var Concrete\Core\Url\UrlImmutable $saveAvatarUrl
 */
$app = Application::getFacadeApplication();

$fileFolderSelector = $app->make(FileFolderSelector::class);
/** @var Concrete\Core\Form\Service\Widget\FileFolderSelector $fileFolderSelector */

$dh = $app->make('helper/date');
/** @var $dh Concrete\Core\Localization\Service\Date */

$languages = Localization::getAvailableInterfaceLanguages();
$locales = [];
if ($languages !== []) {
    array_unshift($languages, Localization::BASE_LOCALE);
    foreach ($languages as $lang) {
        $locales[$lang] = \Punic\Language::getName($lang, $lang);
    }
    asort($locales);
    $locales = array_merge(['' => tc('Default locale', '** Default')], $locales);
}
$userEntity = $user->getEntityObject();
?>

<section data-section="basics" class="mb-3 row">
    <div class="col-lg-12">
        <div class="ccm-user-detail-basics">
            <div class="ccm-user-detail-basics-avatar">
                <?php if ($canEditAvatar) { ?>
                    <a href="#"
                       data-bs-toggle="modal" data-bs-target="#edit-avatar-modal"
                       class="ccm-user-detail-basics-avatar-edit"><?= $user->getUserAvatar()->output() ?></a>
                <?php } else { ?>
                    <?= $user->getUserAvatar()->output() ?>
                <?php } ?>
            </div>
            <div class="ccm-user-detail-basics-name">
                <h5 class="mb-2"><?= $userEntity->getUserName() ?></h5>
                <div class="mb-2"><a href="mailto:<?= $userEntity->getUserEmail() ?>"><?= $userEntity->getUserEmail() ?></a></div>
                <?php
                $privateMessagesEnabled = $user->getAttribute('profile_private_messages_enabled');
                $profileURL = $user->getUserPublicProfileURL();
                if ($profileURL || $privateMessagesEnabled) {
                ?>
                <div>
                    <div class="btn-group btn-group-sm">
                        <?php
                        if ($privateMessagesEnabled) {
                            $u = Core::make(Concrete\Core\User\User::class);
                            if ($u->getUserID() != $userEntity->getUserID()) { ?>
                                <a href="<?php echo View::url('/account/messages', 'write', $userEntity->getUserID())?>" class="btn btn-secondary"><?php echo t("Send Private Message")?></a>
                            <?php } ?>
                        <?php } ?>

                        <?php
                        if ($profileURL) {
                            ?>
                            <a href="<?=$profileURL?>" class="btn btn-secondary"><?=t("View Public Profile")?></a>
                            <?php
                        } ?>

                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<hr class="mb-4"/>

<?php if ($canViewAccountModal) { ?>
<div id="folderSelectorSourceContainer" class="d-none">
    <?php echo $fileFolderSelector->selectFileFolder('uHomeFileManagerFolderID', $userEntity->getHomeFileManagerFolderID()); ?>
</div>
<?php } ?>

<section data-section="account">
    <?php if ($canViewAccountModal) { ?>
        <button
                data-bs-toggle="modal" data-bs-target="#edit-account-modal"
                class="btn-section btn btn-secondary"><?= t('Edit') ?></button>
    <?php } ?>
    <h3><?= t('Account') ?></h3>
    <dl class="ccm-user-detail-account">
        <dt><?= t('Date Created') ?></dt>
        <dd>
            <div><?= $dh->formatDateTime($userEntity->getUserDateAdded()) ?></div>
        </dd>
        <dt><?= t('Last IP Address') ?></dt>
        <dd>
            <div><?= $user->getLastIPAddress() ?? t('None') ?></div>
        </dd>
        <dt><?= t('Last Password Change') ?></dt>
        <dd>
            <div><?= $userEntity->getUserLastPasswordChange() === null ? t('Never') : $dh->formatDateTime($userEntity->getUserLastPasswordChange()) ?></div>
        </dd>
        <dt><?= t('Last Login') ?></dt>
        <dd>
            <div><?= $userEntity->getUserLastLogin() ? $dh->formatDateTime($userEntity->getUserLastLogin()) : t('Never') ?></div>
        </dd>

        <dt><?= t('Last Seen Online') ?></dt>
        <dd>
            <div><?= $userEntity->getUserLastOnline() ? $dh->formatDateTime($userEntity->getUserLastOnline()) : t('Never') ?></div>
        </dd>
        <dt><?= t('# Logins') ?></dt>
        <dd>
            <div><?= $userEntity->getUserTotalLogins() ?></div>
        </dd>
        <?php
        if (Config::get('concrete.misc.user_timezones')) {
            $uTimezone = $userEntity->getUserTimezone();
            if (empty($uTimezone)) {
                $uTimezone = date_default_timezone_get();
            }
            ?>
            <dt><?= t('Timezone') ?></dt>
            <dd>
                <div><?= $dh->getTimezoneDisplayName($uTimezone) ?></div>
            </dd>
            <?php
        }
        ?>

        <?php
        if (count($languages) > 0) {
            ?>
            <dt><?= t('Language') ?></dt>
            <?php if ($userEntity->getUserDefaultLanguage()) { ?>
                <dd>
                    <div><?= h(Punic\Language::getName($userEntity->getUserDefaultLanguage())) ?></div>
                </dd>
            <?php } else { ?>
                <dd>
                    <div><?= h(Punic\Language::getName(Config::get('concrete.locale'))) ?></div>
                </dd>
            <?php } ?>
            <?php
        }
        ?>
        <dt>
            <?php echo t('Home Folder') ?>
        </dt>
        <dd>
            <div>
                <?php echo $userEntity->getHomeFileManagerFolderID() !== null && isset($folderList[$userEntity->getHomeFileManagerFolderID()]) ? $folderList[$userEntity->getHomeFileManagerFolderID()] : t('None') ?>
            </div>
        </dd>
        <?php
        if ($shouldViewIgnoredIPMismatches) {
            ?>
            <dt>
                <?= t('Ignored IP changes for') ?>
            </dt>
            <dd>
                <div>
                    <?php
                    if ($userEntity->getIgnoredIPMismatches() === []) {
                        echo t('None');
                    } else {
                        echo nl2br(htmlspecialchars(implode("\n", $userEntity->getIgnoredIPMismatches())));
                    }
                    ?>
                </div>
            </dd>
            <?php
        }
        if (Config::get('concrete.user.registration.validate_email')) {
            ?>
            <dt><?= t('Full Registration Record') ?></dt>
            <dd>
                <div><?= ($userEntity->isUserFullRecord()) ? t('Yes') : t('No') ?></div>
            </dd>
            <dt><?= t('Email Validated') ?></dt>
            <dd>
                <div>
                    <?php
                    switch ($userEntity->isUserValidated()) {
                        case '-1':
                            print t('Unknown');
                            break;
                        case '0':
                            print t('No');
                            break;
                        case '1':
                            print t('Yes');
                            break;
                    }
                    ?>
                </div>
            </dd>
            <?php
        }
        ?>

    </dl>

    <?php if ($canEditAvatar) {
        $avatarUploadUrl = $view->action('update_avatar', $user->getUserID());
        $cropper = app(AvatarCropperInstanceFactory::class)->createInstance();
        $cropper->setUploadUrl($avatarUploadUrl);
        $config = app('config');
        $width = $config->get('concrete.icons.user_avatar.width');
        $height = $config->get('concrete.icons.user_avatar.height');
        ?>

        <div class="modal fade" tabindex="-1" role="dialog" id="edit-avatar-modal">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= t('Edit Profile Picture') ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= t('Close') ?>"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center" v-if="avatar">
                            <div class="mb-3">
                                <div class="d-inline-block" style="max-width: <?=$width?>px; max-height: <?=$height?>px">
                                    <?=$user->getUserAvatar()->output()?>
                                </div>
                            </div>
                            <button @click="deleteAvatar" class="btn-danger btn"><?=t('Delete')?></button>
                        </div>
                        <div v-else>
                            <?=$cropper->getTag()?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>

    <?php if ($canViewAccountModal) { ?>

        <form method="post" @submit.prevent="saveAccount">
            <div class="modal fade" tabindex="-1" role="dialog" id="edit-account-modal">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= t('Edit Account') ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= t('Close') ?>"></button>
                        </div>
                        <div class="modal-body">
                            <fieldset>
                                <legend><?= t('Basic Information'); ?></legend>
                                <?php
                                if ($canEditUserName) {
                                    ?>
                                    <div class="form-group">
                                        <?= $form->label('uName', t('Username')); ?>
                                        <?= $form->text('uName', $userEntity->getUserName()); ?>
                                    </div>
                                    <?php
                                }
                                if ($canEditEmail) {
                                    ?>
                                    <div class="form-group">
                                        <?= $form->label('uEmail', t('Email')); ?>
                                        <?= $form->text('uEmail', $userEntity->getUserEmail()); ?>
                                    </div>
                                    <?php
                                }
                                if ($canEditTimezone) {
                                    if (Config::get('concrete.misc.user_timezones')) {
                                        ?>
                                        <div class="form-group">
                                            <?= $form->label('uTimezone', t('Time Zone')); ?>
                                            <?= $form->select('uTimezone', $dh->getTimezones(), $userEntity->getUserTimezone() ?? date_default_timezone_get()); ?>
                                        </div>
                                        <?php
                                    }
                                }
                                if ($canEditLanguage) {
                                    if (is_array($locales) && count($locales)) {
                                        ?>
                                        <div class="form-group">
                                            <?= $form->label('uDefaultLanguage', t('Language')); ?>
                                            <?= $form->select('uDefaultLanguage', $locales, Localization::activeLocale()); ?>
                                        </div>
                                        <?php
                                    }
                                }
                                if ($canEditHomeFileManagerFolderID) {
                                    ?>
                                    <div class="form-group">
                                        <?php echo $form->label('uHomeFileManagerFolderID', t('Home Folder')); ?>
                                        <div id="folderSelectorDestinationContainer"></div>
                                    </div>
                                    <?php
                                }
                                if ($shouldViewIgnoredIPMismatches && $canEditIgnoredIPMismatches) {
                                    ?>
                                    <div class="form-group">
                                        <?= $form->label('ignoredIPMismatches', t('Ignored IP changes for')) ?>
                                        <?= $form->textarea('ignoredIPMismatches', implode("\n", $userEntity->getIgnoredIPMismatches())) ?>
                                        <div class="small text-muted">
                                            <?= t('Separate IP addresses with spaces or new lines.') ?><br />
                                            <?= t(
                                                'Accepted values are single addresses (IPv4 like %1$s, and IPv6 like %2$s) and ranges in subnet format (IPv4 like %3$s, and IPv6 like %4$s).',
                                                '<code>127.0.0.1</code>',
                                                '<code>::1</code>',
                                                '<code>127.0.0.1/24</code>',
                                                '<code>::1/8</code>'
                                            ) ?><br />
                                            <?= t('Please remark that you may also define global IP ranges.') ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </fieldset>
                            <?php if ($canEditPassword) { ?>
                                <fieldset>
                                    <legend>
                                        <?php echo t('Change Password'); ?>
                                    </legend>

                                    <div class="form-group">
                                        <?php echo $form->label('uPasswordNew', t('New Password')); ?>
                                        <password-input name="uPasswordNew" :strength-meter="true"/>
                                    </div>

                                    <div class="form-group">
                                        <?php echo $form->label('uPasswordNewConfirm', t('Confirm New Password')); ?>
                                        <password-input name="uPasswordNewConfirm"/>
                                    </div>

                                    <div class="form-group">
                                        <?php echo $form->label('uPasswordMine', t('Your Password (for additional security)')); ?>
                                        <password-input name="uPasswordMine"/>
                                    </div>

                                    <div class="help-block"><?php echo h(t('Leave blank to leave the password unchanged.')); ?></div>
                                </fieldset>
                            <?php } ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Close') ?></button>
                            <button type="submit" class="btn btn-primary"><?= t('Save') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php } ?>

</section>

<hr class="mt-5 mb-4"/>

<section data-section="groups" v-cloak>
    <?php
    if ($canAddGroup) {
    ?>
    <button class="btn-section btn btn-secondary"
            data-bs-toggle="modal" data-bs-target="#edit-groups-modal"
    ><?= t('Edit') ?></button>
    <?php } ?>

    <h3><?= t('Groups') ?></h3>
    <dl class="ccm-user-detail-groups">
        <template v-for="group in groups">
            <dt><span v-html="group.gDisplayName"></span></dt>
            <dd>
                <div>{{group.gDateTimeEntered}}</div>
            </dd>
        </template>
    </dl>

    <?php if ($canAddGroup) { ?>
    <div class="modal fade" tabindex="-1" role="dialog" id="edit-groups-modal">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= t('Edit Groups') ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= t('Close') ?>"></button>
                </div>
                <div class="modal-body">
                    <h4><?= t('Selected Groups') ?></h4>
                    <div class="mb-3" v-if="groups.length > 0">
                        <div class="d-flex" v-for="group in groups">
                            <div class="me-auto"><span v-html="group.gDisplayName"></span></div>
                            <div><a class="ccm-hover-icon" href="#" @click.prevent="removeGroup(group.gID)"><i
                                            class="fas fa-minus-circle"></i></a></div>
                        </div>
                    </div>
                    <div v-if="groups.length === 0" class="mb-3"><?= t('None') ?></div>

                    <h4><?= t('Add Group') ?></h4>
                    <concrete-group-chooser mode="select" @select="addGroup"></concrete-group-chooser>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= t('Close') ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</section>

<hr class="mt-5 mb-4"/>

<section data-section="attributes">
    <?php
    if (count($allowedEditAttributes)) {
        ?>
        <a class="dialog-launch btn-section btn btn-secondary"
           href="<?=URL::to('/ccm/system/dialogs/user/attributes', $userEntity->getUserID())?>"
           dialog-width="800" dialog-height="640" dialog-title="<?=t('Edit Attributes')?>">
        <?= t('Edit') ?></a>
    <?php } ?>

    <h3><?= t('Custom Attributes') ?></h3>

    <?php foreach ($attributeSets as $set) { ?>
        <h5 class="mt-3"><?php echo $set->getAttributeSetDisplayName() ?></h5>
        <dl class="ccm-user-detail-attributes">
            <?php foreach ($set->getAttributeKeys() as $key) { ?>
                <dt><?= $key->getAttributeKeyDisplayName() ?></dt>
                <dd><?php
                    $value = $user->getAttributeValueObject($key);
                    if ($value) {
                        echo $value->getDisplayValue();
                    }
                    ?>
                </dd>
            <?php } ?>
        </dl>
    <?php } ?>


    <?php if (count($unassigned)) { ?>
        <?php if (count($attributeSets)) { ?>
            <h5 class="mt-3"><?= t('Other') ?></h5>
        <?php } ?>

        <dl class="ccm-user-detail-attributes">
            <?php foreach ($unassigned as $key) { ?>
                <dt><?= $key->getAttributeKeyDisplayName() ?></dt>
                <dd>
                    <div><?php
                        $value = $user->getAttributeValueObject($key);
                        if ($value) {
                            echo $value->getDisplayValue();
                        }
                        ?></div>
                </dd>
            <?php } ?>

        </dl>
    <?php } ?>

</section>

<script>
    $(document).ready(function () {

        Concrete.Vue.activateContext('backend', function (Vue, config) {
            new Vue({
                el: '[data-section=basics]',
                components: config.components
            })
        })

        Concrete.Vue.activateContext('backend', function (Vue, config) {
            new Vue({
                components: config.components,
                el: 'section[data-section=account]',
                data: {
                    avatar: <?=$user->hasAvatar() ? json_encode(['path' => $user->getUserAvatar()->getPath()]) : 'null' ?>
                },
                methods: {
                    deleteAvatar() {
                        var data = []
                        var my = this
                        data.push({'name': 'ccm_token', 'value': '<?=$token->generate('delete_avatar')?>'})
                        $.concreteAjax({
                            url: "<?=$view->action('delete_avatar', $userEntity->getUserID())?>",
                            data: data,
                            success: function (r) {
                                my.avatar = null
                            }
                        });
                    },
                    saveAccount() {
                        var my = this
                        var data = $(this.$el).find("form").serializeArray()
                        data.push({'name': 'ccm_token', 'value': '<?=$token->generate('save_account')?>'})

                        $.concreteAjax({
                            url: "<?=$view->action('save_account', $userEntity->getUserID())?>",
                            data: data,
                            success: function (r) {
                                window.location.reload()
                            }
                        });
                    },
                },
            });

            new Vue({
                components: config.components,
                el: 'section[data-section=groups]',
                data: {
                    'groups': <?=$groupsJSON?>
                },
                mounted() {
                    $('[data-button=assign-groups]').dialog();
                },
                methods: {
                    addGroup(group) {
                        var my = this
                        $.concreteAjax({
                            url: "<?=URL::to('/ccm/system/user/add_group')?>",
                            data: {
                                gID: group.gID,
                                uID: '<?=$userEntity->getUserID()?>',
                                ccm_token: '<?= $token->generate('add_group') ?>'
                            },
                            success: function (r) {
                                if (r.groups) {
                                    r.groups.forEach(group => my.groups.push(group))
                                }
                            }
                        });
                    },
                    removeGroup(gID) {
                        var my = this
                        $.concreteAjax({
                            url: "<?=URL::to('/ccm/system/user/remove_group')?>",
                            data: {
                                gID: gID,
                                uID: '<?=$userEntity->getUserID()?>',
                                ccm_token: '<?= $token->generate('remove_group') ?>'
                            },
                            success: function (r) {
                                const index = my.groups.findIndex(function (group) {
                                    return group.gID == r.group.gID
                                });
                                my.groups.splice(index, 1)
                            }
                        });
                    }
                },
            });
        })

        /*
         * Small hack to get the folder selector running within a vue context.
         * The folder selector contains a script tag which is not allowed within a vue context.
         * So we render the component outside and swap the containers.
         *
         * Not a nice way - but it works.
         *
         * @todo: Create a vue version of file folder selector and replace this hacky workaround with the vue component
         */
        setTimeout(function() {
            // setTimeout needed for https://github.com/concretecms/concretecms/issues/11152
            $("#folderSelectorDestinationContainer").before($("#folderSelectorSourceContainer").removeClass("d-none"));
        }, 500);
    });
</script>
