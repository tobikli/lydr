<script setup lang="ts">
import { computed } from "vue";
import { HLabelTheme, H_LABEL_THEME_CONFIGURATION } from "@/types";
import { wrapInCssVar } from "@/utils/helpers";

interface Props {
  theme?: HLabelTheme;
}

const props = withDefaults(defineProps<Props>(), {
  theme: "primary",
});

const configuration = computed(() => H_LABEL_THEME_CONFIGURATION[props.theme]);

const style = computed(() => ({
  backgroundColor: wrapInCssVar(configuration.value.backgroundColor),
  color: wrapInCssVar(configuration.value.color),
}));
</script>

<template>
  <div class="label text-overline">
    <slot />
  </div>
</template>

<style lang="scss" scoped>
.label {
  display: flex;
  padding: 4px 8px;
  align-items: center;
  gap: 10px;
  border-radius: 6px;
  background-color: v-bind("style.backgroundColor");
  color: v-bind("style.color");
}
</style>
