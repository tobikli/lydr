<script setup lang="ts">
import { computed, ref, watch } from "vue";

interface Props {
  modelValue: boolean;
  bind?: boolean;
  isDisabled?: boolean;
}

type Emits = {
  "update:modelValue": [value: boolean];
  click: [];
};

const props = withDefaults(defineProps<Props>(), {
  bind: true,
});

const emit = defineEmits<Emits>();

const inputValue = ref(props.modelValue);

const displayValue = computed(() =>
  props.bind ? inputValue.value : props.modelValue
);

const onChange = (event: Event) => {
  inputValue.value = (event.target as HTMLInputElement).checked;
  emit("update:modelValue", inputValue.value);
};

const onClick = () => {
  if (!props.bind && !props.isDisabled) {
    emit("click");
  }
};

watch(
  () => props.modelValue,
  (value) => {
    inputValue.value = value;
  }
);
</script>

<template>
  <div class="toggle__element-container">
    <label
      class="toggle h-mb-0"
      :class="{ active: displayValue, 'toggle--disabled': isDisabled }"
      @click="onClick"
    >
      <input
        type="checkbox"
        :checked="displayValue"
        :disabled="!bind || isDisabled"
        @change="onChange"
      />
      <span />
    </label>
  </div>
</template>
<style lang="scss" scoped>
$toggle-width: 40px;
$toggle-height: 22px;
$toggle-border-radius: 1.25em;
$toggle-border-size: 1px;

.toggle {
  width: $toggle-width;
  height: $toggle-height;
  background: var(--gray-border);
  border: 1px solid var(--gray-border);
  border-radius: $toggle-border-radius;
  position: relative;
  overflow: hidden;
  transition: all 0.5s;
  cursor: pointer;
  display: flex;
  align-items: center;

  color: var(--grayscale-000-gray-000-38);

  &.active {
    background: var(--primary);
  }

  &.toggle > span {
    width: 20px;
    height: 20px;
    transform: translate(0%, 0);
    border: 0;
    box-shadow: var(--shadow);
    opacity: 1;
  }

  input {
    margin-left: -999px;
    height: 0;
    width: 0;
    overflow: hidden;
    position: absolute;
    opacity: 0;
  }

  input:empty ~ span &--on {
    display: none;
  }

  input:empty ~ span &--off {
    display: inline;
  }

  input:checked ~ span {
    opacity: 1;
    background: var(--light);
    transform: translate(100%, 0);
  }

  input:checked ~ span > &--on {
    display: inline;
  }

  input:checked ~ span &--off {
    display: none;
  }

  > span {
    width: $toggle-height;
    height: $toggle-height;
    opacity: 0.4;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--gray-light);
    border-radius: 50%;
    transform: translate(0, 0);
    margin: -1px;
    transition: all 0.15s;
    overflow: hidden;

    background: var(--light);
  }

  &--disabled {
    opacity: 0.38;
    pointer-events: none;
  }

  &--disabled,
  &--unavailable {
    color: var(--gray-border);
  }
}

.toggle--primary {
  color: var(--primary);
}

.toggle--secondary {
  color: var(--secondary);
}

.toggle--success {
  color: var(--success);
}

.toggle--info {
  color: var(--primary-hostinger);
}

.toggle--warning {
  color: var(--warning);
}

.toggle--warning-regular {
  color: var(--warning-regular);
}

.toggle--danger {
  color: var(--danger);
}

.toggle--light {
  color: var(--light);
}

.toggle--dark {
  color: var(--dark);
}

.toggle--black {
  color: var(--dark);
}

.toggle--gray {
  color: var(--gray);
}

.toggle--gray-light {
  color: var(--gray-light);
}

.toggle--header-bg {
  color: var(--header-bg);
}

.toggle--danger-light {
  color: var(--danger-light);
}

.toggle--success-dark {
  color: var(--success-dark);
}

.toggle--success-light {
  color: var(--success-light);
}

.toggle--warning-light {
  color: var(--warning-light);
}

.toggle--warning-dark {
  color: var(--warning-dark);
}
</style>
