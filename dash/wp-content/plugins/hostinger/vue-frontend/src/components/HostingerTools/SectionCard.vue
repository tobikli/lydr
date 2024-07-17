<script lang="ts" setup>
import Card from "@/components/Card.vue";
import Toggle from "@/components/Toggle.vue";
import CopyField from "@/components/CopyField.vue";
import { SectionItem } from "@/types";
import Button from "@/components/Button/Button.vue";
import SkeletonLoader from "@/components/Loaders/SkeletonLoader.vue";

type Props = {
  title: string;
  isLoading?: boolean;
  sectionItems: SectionItem[];
};

type Emits = {
  "save-section": [value: boolean, item: SectionItem];
};

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
</script>

<template>
  <div class="home-section">
    <Card v-if="isLoading">
      <SkeletonLoader class="h-mb-24" width="50%" :height="24" rounded />
      <SkeletonLoader
        v-for="(item, index) in sectionItems"
        :class="{ 'h-mb-24': index !== sectionItems.length - 1 }"
        width="100%"
        :height="item.copyLink ? 48 : 24"
        rounded
      />
    </Card>
    <Card v-else :header="props.title">
      <div
        class="home-section__section-item"
        v-for="item in sectionItems"
        :key="item.title"
      >
        <div class="d-flex flex-direction-column">
          <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex flex-column">
              <h3 class="h-m-0" item.title>{{ item.title }}</h3>
              <p class="h-m-0 text-body-2">
                {{ item.description }}
              </p>
            </div>
            <Button
              @click="item.sideButton?.onClick"
              v-if="item.sideButton?.text"
              variant="text"
              >{{ item.sideButton?.text }}</Button
            >

            <Toggle
              v-else-if="item.isToggleDisplayed"
              class="h-pl-16"
              :model-value="Boolean(item.toggleValue)"
              :bind="false"
              @click="emit('save-section', Boolean(!item.toggleValue), item)"
            />
          </div>
        </div>
        <CopyField class="h-mt-16" v-if="item.copyLink" :link="item.copyLink" />
      </div>
    </Card>
  </div>
</template>

<style lang="scss">
.home-section {
  &__section-item {
    margin-top: 16px;
    flex-direction: column;
    display: flex;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--gray-border);

    &:last-child {
      margin-bottom: 0;
      border-bottom: none;
      padding-bottom: 0;
    }
  }
}
</style>
