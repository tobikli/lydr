<template>
  <div
    class="accordion-card"
    :class="{
      'accordion-card--is-toggleable': props.togglable,
      'accordion-card--is-hidden': !cardVisible
    }"
  >
    <div class="accordion-card__header" @click.prevent="toggleCard">
      <div class="accordion-card__title">
        <slot name="title" />
      </div>
      <div v-if="slots['title-right']" class="accordion-card__title accordion-card__title--align-right">
        <slot name="title-right" />
      </div>
      <div v-if="props.togglable" class="accordion-card__arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path
            d="M11.9998 14.95C11.8665 14.95 11.7415 14.9292 11.6248 14.8875C11.5081 14.8458 11.3998 14.775 11.2998 14.675L6.6998 10.075C6.51647 9.89166 6.4248 9.65833 6.4248 9.37499C6.4248 9.09166 6.51647 8.85833 6.6998 8.67499C6.88314 8.49166 7.11647 8.39999 7.3998 8.39999C7.68314 8.39999 7.91647 8.49166 8.0998 8.67499L11.9998 12.575L15.8998 8.67499C16.0831 8.49166 16.3165 8.39999 16.5998 8.39999C16.8831 8.39999 17.1165 8.49166 17.2998 8.67499C17.4831 8.85833 17.5748 9.09166 17.5748 9.37499C17.5748 9.65833 17.4831 9.89166 17.2998 10.075L12.6998 14.675C12.5998 14.775 12.4915 14.8458 12.3748 14.8875C12.2581 14.9292 12.1331 14.95 11.9998 14.95Z"
            fill="#673DE6"
          />
        </svg>
      </div>
    </div>
    <Vue3SlideUpDown v-model="cardVisible" :duration="400">
      <div class="accordion-card__body">
        <slot name="body" />
      </div>
    </Vue3SlideUpDown>
  </div>
</template>

<script lang="ts" setup>
import { ref, useSlots } from 'vue';
import { Vue3SlideUpDown } from 'vue3-slide-up-down';

const slots = useSlots();

const props = defineProps({
  togglable: {
    type: Boolean,
    default: false,
    required: false
  },
  isVisible: {
    type: Boolean,
    default: true,
    required: false
  }
});

const cardVisible = ref(true);

if (!props.isVisible) {
  cardVisible.value = false;
}
const toggleCard = () => {
  if (props.togglable) {
    cardVisible.value = !cardVisible.value;
  }
};
</script>
