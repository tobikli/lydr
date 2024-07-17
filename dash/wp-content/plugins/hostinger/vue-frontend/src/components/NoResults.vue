<script setup lang="ts">
import { ref } from 'vue';
interface Props {
  title: string;
  subtitle?: string;
  imageSrc?: string;
  hasImage?: boolean;
}
withDefaults(defineProps<Props>(), {
  title: hst_affiliate_data.translations.nothing_found,
  subtitle: hst_affiliate_data.translations.try_other_results,
  imageSrc: hst_affiliate_data.plugin_url + 'assets/img/no-search-results.svg',
  hasImage: true
});
</script>
<template>
  <div class="d-flex justify-content-center">
    <div class="text-center">
      <img v-if="hasImage" :src="imageSrc" alt="nothing-found" class="h-mb-8" />
      <h3 v-if="title" v-trans>
        {{ title }}
      </h3>
      <p v-if="subtitle" v-trans>
        {{ subtitle }}
      </p>
      <slot />
    </div>
  </div>
</template>
