import { defineStore } from 'pinia';
import { ref } from 'vue';
import { ModalContent, ModalSettings } from '@/types';
import { ModalName } from '@/types/enums';

export const useModalStore = defineStore('modalStore', () => {
  interface Modal {
    name: ModalName;
    data?: ModalContent;
    settings?: ModalSettings;
  }

  const activeModal = ref<Modal | null>(null);

  const openModal = (name: ModalName, data?: ModalContent, settings?: ModalSettings) => {
    activeModal.value = { name, data, settings };
  };

  const closeModal = () => (activeModal.value = null);

  return { activeModal, openModal, closeModal };
});
