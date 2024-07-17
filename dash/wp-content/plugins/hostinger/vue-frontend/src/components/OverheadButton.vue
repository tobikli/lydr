<script setup lang="ts">
import { computed } from "vue";
import type { RouteLocationNamedRaw } from "vue-router";
import { useRouter } from "vue-router";

type Props = {
  text: string;
  route?: RouteLocationNamedRaw;
  to?: string;
  action?: () => void;
};

type Emits = {
  click: [];
};

const emit = defineEmits<Emits>();
const props = defineProps<Props>();
const router = useRouter();

const buttonText = computed(() => props?.text || "");

const onButtonClick = () => {
  emit("click");
  (props?.action || redirectToRoute)();
};

const tag = computed(() => {
  if (typeof props.to === "string") {
    return "a";
  }

  if (props.to) {
    return "router-link";
  }

  return "button";
});
const redirectToRoute = () => {
  const { name, query } = props?.route || {};

  if (!name) return;

  router.push({
    name,
    query,
  });
};
</script>
<template>
  <Teleport :key="buttonText" to="#overhead-button">
    <component :is="tag" class="overhead-button text-button-2" @click="onButtonClick">
      {{ buttonText }}
    </component>
    <slot />
  </Teleport>
</template>

<style scoped lang="scss">
.overhead-button {
  display: inline-flex;
  padding: 12px 32px;
  align-items: flex-start;
  gap: 12px;
  color: var(--primary);
  border-radius: 8px;
  border: 1px solid var(--gray-border);
  background: var(--light);

  &:hover {
    cursor: pointer;
    transition: 0.3s;
    opacity: 0.7;
  }
}
</style>
