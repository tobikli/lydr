<?php
defined('C5_EXECUTE') or die("Access Denied.");
?>

<div data-component="search-field-selector" class="ccm-search-field-selector">

    <div data-container="search-fields" class="ccm-search-fields-advanced">

    </div>

    <div class="btn-group">
        <button type="button" class="btn btn-success dropdown-toggle" data-bs-flip="false" data-bs-display="static" data-bs-toggle="dropdown" data-bs-boundary="document">
            <?=t('Add Field')?>
        </button>
        <div class="dropdown-menu">
            <?php foreach($manager->getGroups() as $group) { ?>
                <div class="dropdown-header"><?=$group->getName()?></div>
                <?php foreach($group->getFields() as $field) { ?>
                    <a class="dropdown-item" data-action="<?=$addFieldAction?>"
                       data-search-field-key="<?=$field->getKey()?>" href="#"><?=$field->getDisplayName()?></a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <script type="text/template" data-template="search-field-row">
        <div class="ccm-search-field-selector-row">
            <input type="hidden" name="field[]" value="<%=field.key%>">
            <div class="mb-3">
                <label class="form-label"><%=field.label%></label>
                <a data-search-remove="search-field" class="ccm-hover-icon float-end" href="#">
                    <svg width="20" height="20"><use xlink:href="#icon-minus-circle" /></svg>
                    </i></a>
                <% if (typeof(field) != 'undefined') { %><%=field.element%><% } %>
            </div>
        </div>
    </script>

    <?php if (isset($query)) { ?>
        <script type="text/json" data-template="default-query">
            <?=json_encode($query)?>
        </script>
    <?php } ?>

</div>

<?php if ($includeJavaScript) { ?>
<script type="text/javascript">
    $(function() {
        $('div[data-component=search-field-selector]').concreteSearchFieldSelector({});
    });
</script>
<?php } ?>
