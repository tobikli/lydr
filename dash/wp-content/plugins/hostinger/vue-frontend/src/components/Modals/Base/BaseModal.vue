<script lang="ts" setup>
import Icon from "@/components/Icon/Icon.vue";
import { Color, IconUnion } from "@/types";

interface Props {
  title?: string;
  subtitle?: string;
  titleIcon?: {
    name: IconUnion;
    color: Color;
  };
}

defineProps<Props>();
</script>

<template>
  <div class="base-modal">
    <span class="base-modal__title-container">
      <Icon
        v-if="titleIcon"
        class="h-mr-12"
        :name="titleIcon.name"
        :color="titleIcon.color"
      />
      <h2 v-if="title" class="base-modal__title text-heading-2">{{ title }}</h2>
    </span>
    <p v-if="subtitle" class="base-modal__subtitle">{{ subtitle }}</p>
    <slot />
  </div>
</template>

<style lang="scss" scoped>
.base-modal {
  &__title-container {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
  }
  &__title {
    font-size: 20px;
    color: var(--dark);
    margin: 0;
    font-weight: 700;
  }

  &__subtitle {
    font-size: 14px;
    margin-top: 4px;
    margin-bottom: 24px;
    color: var(--gray);
  }
}
</style>
