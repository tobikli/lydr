<script lang="ts" setup>
import Card from "@/components/Card.vue";
import Label from "@/components/Label.vue";
import Button from "@/components/Button/Button.vue";
import SkeletonLoader from "@/components/Loaders/SkeletonLoader.vue";

type Props = {
  title: string;
  description?: string;
  toolImageSrc: string;
  version?: string;
  actionButton?: {
    text: string;
    onClick?: () => void;
  };
  isLoading?: boolean;
};

defineProps<Props>();
</script>

<template>
  <Card v-if="isLoading">
    <template #header>
      <SkeletonLoader width="50%" :height="24" rounded />
    </template>
    <SkeletonLoader width="100%" :height="24" rounded />
  </Card>
  <Card v-else class="tool-version-card">
    <template #header>
      <div class="d-flex justify-content-between w-100">
        <div class="d-flex">
          <img
            class="h-mr-8"
            height="24"
            width="24"
            :src="toolImageSrc"
            alt="Tool icon"
          />
          <h3 class="h-m-0">
            {{ title }}
          </h3>
        </div>

        <Label v-if="version">{{ version }}</Label>
      </div>
    </template>
    <p class="text-body-2">{{ description }}</p>
    <Button
      class="h-mt-20"
      iconAppend="icon-launch-light"
      @click="actionButton?.onClick"
      v-if="actionButton?.text"
      >{{ actionButton.text }}</Button
    >
  </Card>
</template>
