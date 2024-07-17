<script lang="ts" setup>
import { computed } from 'vue';
import { Color } from '@/types';
import { wrapInCssVar } from '@/utils/helpers';

const DIMENSION_MAP = {
  small: '24px',
  medium: '40px',
  large: '72px'
} as const;

type HCircleLoaderSize = keyof typeof DIMENSION_MAP;

interface Props {
  color?: Color;
  size?: HCircleLoaderSize;
  dimensions?: string;
  borderSize?: string;
  borderColor?: Color;
}

const props = withDefaults(defineProps<Props>(), {
  color: 'primary',
  size: 'medium'
});

const getDimensions = () => {
  if (props.dimensions) {
    return props.dimensions;
  }

  return DIMENSION_MAP[props.size];
};

const getBorder = () => props.borderSize || '4px';

const getBorderColor = (): string => {
  if (props.borderColor) {
    return wrapInCssVar(props.borderColor);
  }

  return wrapInCssVar(`${props.color}-light`);
};

const style = computed(() => ({
  color: wrapInCssVar(props.color),
  borderColor: getBorderColor(),
  width: getDimensions(),
  borderSize: getBorder(),
  height: getDimensions()
}));
</script>

<template>
  <div class="loader" />
</template>

<style lang="scss" scoped>
.loader {
  border: v-bind('style.borderSize') solid v-bind('style.borderColor');
  border-top: v-bind('style.borderSize') solid v-bind('style.color');
  width: v-bind('style.width');
  height: v-bind('style.height');
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
