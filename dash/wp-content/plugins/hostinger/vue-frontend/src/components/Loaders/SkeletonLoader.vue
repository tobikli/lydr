<script lang="ts" setup>
import { computed } from 'vue';

type DimensionValue = number | string;

type Props = {
  circle?: boolean;
  rounded?: boolean;
  roundedXs?: boolean;
  roundedXl?: boolean;
  width?: DimensionValue;
  height?: DimensionValue;
  isInline?: boolean;
};

const props = defineProps<Props>();

const classes = computed(() => ({
  'skeleton-loader--circle': props.circle,
  'skeleton-loader--rounded': props.rounded,
  'skeleton-loader--rounded-xs': props.roundedXs,
  'skeleton-loader--rounded-xl': props.roundedXl,
  'skeleton-loader--inline': props.isInline,
}));

const getSkeletonSize = (value?: DimensionValue) => {
  if (Number.isInteger(value)) {
    return `${value}px`;
  }

  return value;
};
</script>

<template>
  <div
    class="skeleton-loader"
    :class="{ ...classes }"
    :style="{
      'max-width': getSkeletonSize(width),
      height: getSkeletonSize(height),
      width: props.isInline ? getSkeletonSize(width) : undefined,
    }"
  />
</template>

<style lang="scss" scoped>
.skeleton-loader {
  position: relative;
  overflow: hidden;
  background-color: var(--gray-1);
  width: 100%;

  &::after {
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    content: "";
    position: absolute;
    animation: HSkeletonLoader-keyframes-wave 1.6s linear 0.5s infinite;
    transform: translateX(-100%);
    background: linear-gradient(90deg, transparent, var(--gray-2));
  }

  &--circle {
    border-radius: 50%;
  }

  &--rounded {
    border-radius: 8px;
  }
  &--rounded-xs {
    border-radius: 4px;
  }
  &--rounded-xl {
    border-radius: 24px;
  }

  &--inline {
    display: inline-block;
    vertical-align: middle;
  }
}

@keyframes HSkeletonLoader-keyframes-wave {
  0% {
    transform: translateX(-100%);
  }
  60% {
    transform: translateX(100%);
  }
  100% {
    transform: translateX(100%);
  }
}

</style>
