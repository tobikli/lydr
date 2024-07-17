<script setup lang="ts">
interface Props {
  stepsCount: number;
  isClickable?: boolean;
  wide?: boolean;
  step?: number;
}

type Emits = {
  (eventName: 'on-click', index: number): void;
};

const props = withDefaults(defineProps<Props>(), {
  stepsCount: 0,
  isClickable: false,
  wide: false,
  step: 0
});

const emit = defineEmits<Emits>();

const getIsActive = (index: number, step: number) =>
  (!props.isClickable && index <= step) || (props.isClickable && index === step);
</script>

<template>
  <div class="stepper">
    <div
      v-for="(_, index) in stepsCount"
      :key="index"
      class="stepper__indicator"
      :class="{
        'stepper__indicator--active': getIsActive(index, step),
        'stepper__indicator--clickable': isClickable,
        'stepper__indicator--wide': wide
      }"
      @click="emit('on-click', index)"
    />
  </div>
</template>

<style lang="scss" scoped>
.stepper {
  display: flex;
  justify-content: center;
  margin-top: 24px;

  &__indicator {
    width: 8px;
    height: 8px;
    margin-left: 4px;
    border-radius: 50%;
    background-color: rgba(114, 117, 134, 0.3);

    &--wide {
      margin: 0 8px;
    }
    &--active {
      background-color: var(--primary);
    }
    &--clickable {
      cursor: pointer;
    }
  }
}
</style>
