<?php defined('C5_EXECUTE') or die('Access Denied.');
$c = Page::getCurrentPage();
?>

<div class="ccm-block-top-navigation-bar" <?php if ($includeTransparency) { ?>style="display: none" data-transparency="navbar"<?php } ?>>
    <nav class="navbar navbar-expand-lg navbar-light <?php if ($includeStickyNav && !$c->isEditMode()) { ?>fixed-top<?php } ?>">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?=$home->getCollectionLink()?>">
                <?php if ($logo && ($includeBrandLogo && $includeBrandText)) { ?>
                    <img src="<?=$logo->getURL()?>" class="logo align-text-center">
                    <?php if (isset($transparentLogo)) { ?>
                        <img src="<?=$transparentLogo->getURL()?>" class="logo-transparent align-text-center">
                    <?php } ?>
                    <?=$brandingText?>
                <?php } ?>
                <?php if ($logo && ($includeBrandLogo && !$includeBrandText)) { ?>
                    <img src="<?=$logo->getURL()?>" class="logo">
                    <?php if (isset($transparentLogo)) { ?>
                        <img src="<?=$transparentLogo->getURL()?>" class="logo-transparent">
                    <?php } ?>
                <?php } ?>

                <?php if (!$includeBrandLogo && $includeBrandText) { ?>
                    <?=$brandingText?>
                <?php } ?>
            </a>

            <?php if ($includeNavigation || $includeSearchInput || isset($languages)) { ?>
                <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#top-navigation-bar-<?=$bID?>" aria-controls="#top-navigation-bar-<?=$bID?>" aria-expanded="false" aria-label="<?=t('Toggle Navigation')?>">
                    <?php /* Custom animated Toggler */ ?>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <?php /* End animated toggler */?>

                    <?php
                    /* Standard bootstrap toggler. Uncomment to use */
                    /*
                    ?>
                    <span class="navbar-toggler-icon"></span>
                    <?php */ ?>
                </button>
                <div class="collapse navbar-collapse" id="top-navigation-bar-<?=$bID?>">
                    <?php if ($includeSearchInput) { ?>
                        <form method="get" action="<?=$searchAction?>">
                            <div class="input-group">
                                <input class="form-control border-end-0 border" type="search" name="query" placeholder="<?=t('Search')?>" aria-label="<?=t('Search')?>">
                                <span class="input-group-append">
                                    <button class="btn bg-white border-start-0 border" type="submit">
                                        <i class="fas fa-search text-secondary"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                    <?php } ?>
                    <?php if (isset($languages) && $languages) { ?>
                        <div class="dropdown-center<?php if ($includeSearchInput) { ?> ms-3 order-2<?php } else { ?> ms-auto order-1<?php } ?>">
                            <button class="btn btn-link dropdown-toggle d-block mx-auto" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-globe"></i>
                                <span class="d-lg-none ms-2"><?= t('Switch Language') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-lg-end w-100">
                                <?php foreach ($languages as $language) { ?>
                                    <li>
                                        <a class="dropdown-item<?php if ($language->isActive()) { echo ' active'; } ?>"
                                           href="<?= h($language->getUrl()) ?>"><?= h($language->getName()) ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <?php if ($includeNavigation) { ?>
                    <ul class="navbar-nav">
                        <?php foreach ($navigation->getItems() as $item) {
                            /**
                             * @var $item \Concrete\Core\Navigation\Item\PageItem
                             */
                            if (count($item->getChildren()) > 0) { ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link<?= $item->isActiveParent() ? " nav-path-selected" : ""; ?> dropdown-toggle<?= $item->isActive() ? " active" : ""; ?>" data-concrete-toggle="dropdown" target="<?=$controller->getPageItemNavTarget($item)?>" href="<?= $item->getUrl() ?>">
                                        <?=$item->getName()?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($item->getChildren() as $dropdownChild) { ?>
                                            <li><a class="dropdown-item<?= $dropdownChild->isActive() ? " active" : ""; ?><?= $dropdownChild->isActiveParent() ? " nav-path-selected" : ""; ?>" target="<?=$controller->getPageItemNavTarget($dropdownChild)?>" href="<?=$dropdownChild->getUrl()?>"><?=$dropdownChild->getName()?></a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } else { ?>
                                <li class="nav-item"><a class="nav-link<?= $item->isActiveParent() ? " nav-path-selected" : ""; ?><?= $item->isActive() ? " active" : ""; ?>" target="<?=$controller->getPageItemNavTarget($item)?>" href="<?=$item->getUrl()?>"><?=$item->getName()?></a></li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </nav>
</div>
