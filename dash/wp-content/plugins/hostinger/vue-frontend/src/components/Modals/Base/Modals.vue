<script lang="ts" setup>
import Icon from "@/components/Icon/Icon.vue";
import { computed, defineAsyncComponent } from "vue";
import { useModal } from "@/composables";
import { useModalStore } from "@/stores";

const { closeModal } = useModal();
const modalStore = useModalStore();
const activeModal = computed(() => modalStore.activeModal);

const modalComponent = computed(
  () =>
    activeModal.value &&
    defineAsyncComponent(
      () => import(`@/components/Modals/${activeModal.value?.name}.vue`)
    )
);
</script>

<template>
  <Transition name="fade">
    <div v-if="activeModal" class="modal__wrapper">
      <div
        class="modal__container"
        :class="{
          'modal__container--xxl': activeModal.settings?.isXXL,
          'modal__container--xl': activeModal.settings?.isXL,
          'modal__container--lg': activeModal.settings?.isLG,
        }"
      >
        <Icon
          v-if="activeModal.settings?.hasCloseButton"
          name="icon-close"
          class="modal__icon"
          color="gray"
          @click="closeModal"
        />
        <div
          class="modal__content"
          :class="{
            'modal__content--no-content-padding':
              activeModal.settings?.noContentPadding,
            'modal__content--no-border': activeModal.settings?.noBorder,
          }"
        >
          <component :is="modalComponent" v-bind="activeModal.data" />
        </div>
      </div>
    </div>
  </Transition>
</template>

<style lang="scss" scoped>
.modal {
  &__wrapper {
    --modal-backdrop: rgba(0, 0, 0, 0.3);

    overflow: auto;
    position: fixed;
    z-index: var(--z-index-modal);
    height: 100%;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-color: var(--modal-backdrop);
    display: flex;
    align-items: center;
    justify-content: center;

    &.is-above-intercom {
      z-index: var(--z-index-intercom-2);
    }
  }

  &__icon {
    position: absolute;
    top: 16px;
    right: 16px;
    cursor: pointer;
  }

  &__container {
    position: relative;
    max-height: calc(100vh - 6rem);
    width: 100%;
    max-width: 500px;

    &--auto-width {
      max-width: none !important;
      width: auto;
    }

    &--lg {
      max-width: 600px;
    }

    &--xl {
      max-width: 800px;
    }

    &--xxl {
      max-width: 964px;
    }
  }

  &__content {
    border: 1px solid var(--gray-border);
    border-radius: 16px;
    background-color: var(--light);

    padding: 32px 40px 40px;
    @media screen and (max-width: 600px) {
      padding: 32px 40px 40px;
    }

    &--no-content-padding {
      padding: 0;
    }

    &--no-border {
      border: none;
    }
  }
}
</style>
