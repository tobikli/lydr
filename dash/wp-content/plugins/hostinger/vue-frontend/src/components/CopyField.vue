<script setup lang="ts">
import { defineProps, withDefaults } from "vue";
import { copyString } from "@/utils/helpers";
import Icon from "@/components/Icon/Icon.vue";

interface Props {
  link: string;
}

withDefaults(defineProps<Props>(), {});
</script>

<template>
  <div class="copy-field" @click="copyString(link)">
    <span class="copy-field__link">{{ link }}</span>
    <Icon name="icon-content-copy" color="primary" />
  </div>
</template>

<style lang="scss" scoped>
.copy-field {
  display: flex;
  cursor: pointer;
  justify-content: space-between;
  padding: 12px 16px;
  border-radius: 8px;
  word-wrap: break-word;
  align-items: center;
  background: var(--gray-light);

  &__link {
    flex: 1 1 auto;
    color: var(--gray);
    overflow-wrap: anywhere;
  }
}
</style>
