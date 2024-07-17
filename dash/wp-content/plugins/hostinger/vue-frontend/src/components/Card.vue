<script setup lang="ts">
import { defineProps, defineEmits, withDefaults, useSlots } from "vue";

interface Props {
  header?: string;
}

type Emits = {
  (eventName: "click"): void;
};

withDefaults(defineProps<Props>(), {});
const emit = defineEmits<Emits>();
const slots = useSlots();
</script>

<template>
  <div class="card" @click="emit('click')">
    <div class="card__header">
      <slot v-if="slots.header" name="header" />
      <h2 class="h-m-0" v-else-if="header">{{ header }}</h2>
    </div>
    <div class="card__body">
      <slot v-if="slots.default" />
    </div>

    <div class="card__footer" v-if="slots.footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.card {
  border-radius: 16px;
  border: 1px solid var(--gray-border);
  background: var(--light);
  display: flex;
  padding: 24px;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  align-self: stretch;
  text-align: left;
  max-width: unset;
  width: 100%;

  &__header,
  &__body,
  &__footer {
    display: flex;
    width: 100%;
  }

  &__body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }

  &__footer {
    padding: 16px;
    cursor: pointer;
    text-align: right;
  }
}
</style>
