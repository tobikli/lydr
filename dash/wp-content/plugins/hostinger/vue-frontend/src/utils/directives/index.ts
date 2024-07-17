import { App } from 'vue';
import { vTooltip } from '@/utils/directives/tooltipDirective';

export const setDirectives = (app: App) => {
  app.directive('tooltip', vTooltip);
};
