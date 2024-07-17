<script setup lang="ts">
import { RouterView } from "vue-router";
import Modals from "@/components/Modals/Base/Modals.vue";
import Wrapper from "@/layouts/Wrapper.vue";
import { useRoute } from "vue-router";

const route = useRoute();
</script>

<template>
  <div>
    <div id="overhead-button" />
    <Wrapper :title="route.meta.title">
      <RouterView v-slot="{ Component }">
        <Component :is="Component" />
      </RouterView>
    </Wrapper>
    <Modals />
  </div>
</template>

<style lang="scss" scoped>
:deep(.h-button-v2) {
  &:hover {
    cursor: pointer;
  }
}
#overhead-button {
  position: absolute;
  right: 0;
  padding: 40px;

  @media (max-width: 576px) {
    padding: 16px;
  }
}
</style>
