<script lang="ts" setup>
import { computed } from "vue";
import type { Color } from "@/types";
import type { IconUnion } from "@/types/enums/iconModels";
import { wrapInCssVar } from "@/utils/helpers";
import { defineAsyncComponent } from "vue";
import { toTitleCase } from "@/utils/helpers";
import { kebabToCamel } from "@/utils/services/snakeCamelService";

interface Props {
  dimensions?: `${number}px`;
  color?: Color;
  name: IconUnion;
}

const props = withDefaults(defineProps<Props>(), {
  dimensions: `24px`,
  color: "white",
});

const iconColor = computed(() => {
  if (!props.color) return "";

  return wrapInCssVar(props.color);
});

const selectedIcon = computed(() => {
  return defineAsyncComponent(
    () =>
      import(
        `@/components/Icon/Icons/${kebabToCamel(toTitleCase(props.name))}.vue`
      )
  );
});
</script>

<template>
  <svg class="icon" aria-hidden="true">
    <g>
      <Component :is="selectedIcon" />
    </g>
  </svg>
</template>

<style lang="scss" scoped>
.icon {
  transition: 0.3s ease transform;
  fill: currentColor;
  color: v-bind(iconColor);
  width: v-bind(dimensions);
  height: v-bind(dimensions);
  min-width: v-bind(dimensions);
}
</style>
