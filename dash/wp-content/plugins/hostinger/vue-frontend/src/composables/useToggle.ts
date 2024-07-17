import { ref } from 'vue';

export const useToggle = (isDefaultToggled = false) => {
  const isToggled = ref(isDefaultToggled);

  const toggle = () => (isToggled.value = !isToggled.value);

  const toggleOn = () => (isToggled.value = true);

  const toggleOff = () => (isToggled.value = false);

  return {
    isToggled,
    toggle,
    toggleOff,
    toggleOn
  };
};
