<script lang="ts" setup>
import BaseModal from "@/components/Modals/Base/BaseModal.vue";
import { useModal } from "@/composables";
import { translate } from "@/utils/helpers";
import Button from "@/components/Button/Button.vue";

interface Props {
  data: {
    onConfirm: () => void;
  };
}

const props = defineProps<Props>();

const { closeModal } = useModal();

const onProceed = () => {
  if (props.data.onConfirm) props.data.onConfirm();

  closeModal();
};
</script>

<template>
  <BaseModal
    :titleIcon="{
      name: 'icon-info',
      color: 'danger',
    }"
    :title="translate('bypass_link_reset_modal_title')"
  >
    <p class="h-mb-24 text-gray">
      {{ translate("bypass_link_reset_modal_description") }}
    </p>
    <div class="d-flex justify-content-end">
      <Button
        color="danger"
        variant="text"
        class="h-mr-16"
        @click="closeModal"
        >{{ translate("bypass_link_reset_modal_cancel") }}</Button
      >
      <Button color="danger" @click="onProceed">
        {{ translate("bypass_link_reset_modal_reset_link") }}
      </Button>
    </div>
  </BaseModal>
</template>
