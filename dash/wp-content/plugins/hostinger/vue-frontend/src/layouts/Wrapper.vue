<script lang="ts" setup>
type Props = {
  title: any;
};

const props = defineProps<Props>();
</script>

<template>
  <div class="wrapper">
    <div class="wrapper__content">
      <h1 class="text-title-3">{{ props.title }}</h1>

      <slot />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.wrapper {
  padding: 48px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: left;
  min-height: calc(100vh - var(--header-height));

  @media (max-width: 768px) {
    padding-right: 10px;
    padding-left: 0px;
  }

  &__content {
    max-width: 740px;
    width: 100%;
  }
}
</style>
