<?php

defined('C5_EXECUTE') or die("Access Denied.");

$ui = Core::make(Concrete\Core\Application\Service\UserInterface::class);

$renderer->render($instance);
?>

<?=View::element('icons')?>
<div id="ccm-page-controls-wrapper" class="ccm-ui">
    <div id="ccm-toolbar">
        <ul class="ccm-toolbar-item-list">
            <li class="ccm-logo float-start"><span><?= $ui->getToolbarLogoSRC() ?></span></li>
            <li class="float-start">
                <a href="<?=URL::to('/dashboard/boards/instances', 'view', $instance->getBoard()->getBoardID())?>">
                    <svg>
                        <use xlink:href="#icon-arrow-left"/>
                    </svg>
                </a>
            </li>
        </ul>
    </div>
</div>

<script type="text/javascript">

    $(function() {
        Concrete.Vue.activateContext('cms', function (Vue, config) {
            new Vue({
                el: 'div[data-vue-app=board]',
                components: config.components
            })
        })
    });

</script>
