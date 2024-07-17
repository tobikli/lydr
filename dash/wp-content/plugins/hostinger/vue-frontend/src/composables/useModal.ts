import { useModalStore } from '@/stores';
import { ModalName } from '@/types/enums';
import { ModalContent, ModalSettings } from '@/types/models';

export const useModal = () => {
  const modalStore = useModalStore();

  const openModal = (name: ModalName, data?: ModalContent, settings?: ModalSettings) => {
    modalStore.openModal(name, data, settings);
  };

  const closeModal = () => modalStore.closeModal();

  return { openModal, closeModal };
};
