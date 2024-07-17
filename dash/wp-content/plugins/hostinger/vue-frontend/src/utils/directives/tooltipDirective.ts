import { DirectiveBinding } from 'vue';
import { MouseEvent } from '@/types/enums';

const TOOLTIP_ATTR = 'tooltip';

interface ComplexTooltip {
  content: string;
  autoWidth?: boolean;
}

type TooltipBinding = DirectiveBinding<string> | DirectiveBinding<ComplexTooltip>;

const isString = (value: any) => typeof value === 'string';

const getCurrentRotation = (el: HTMLElement) => {
  const computedStyles = window.getComputedStyle(el, null);
  const transformProp =
    computedStyles.getPropertyValue('-webkit-transform') ||
    computedStyles.getPropertyValue('-moz-transform') ||
    computedStyles.getPropertyValue('-ms-transform') ||
    computedStyles.getPropertyValue('-o-transform') ||
    computedStyles.getPropertyValue('transform') ||
    'none';
  if (transformProp != 'none') {
    const values = transformProp.split('(')[1].split(')')[0].split(',');

    const angle = Math.round(Math.atan2(values[1] as any, values[0] as any) * (180 / Math.PI));

    return angle < 0 ? angle + 360 : angle;
  }

  return 0;
};

const getTooltipClass = ({ modifiers }: DirectiveBinding, isRotated: boolean) => {
  const position = Object.keys(modifiers)[0];

  return `has-tooltip--${position || 'bottom'}${isRotated ? '-rotated' : ''}`;
};

const getTooltipContent = (binding: TooltipBinding) => {
  if (!binding.value) return null;

  return isString(binding.value) ? binding.value : (binding.value as ComplexTooltip).content;
};

const addTooltip = (el: HTMLElement, binding: TooltipBinding) => {
  const content = getTooltipContent(binding);

  if (!content) return removeTooltip(el, binding);

  const rotation = getCurrentRotation(el);

  el.setAttribute(TOOLTIP_ATTR, content as string);

  document.documentElement.style.setProperty('--tooltip-rotation', `-${rotation}deg`);

  if (!isString(binding.value) && (binding.value as ComplexTooltip).autoWidth) {
    document.documentElement.style.setProperty('--tooltip-width', 'auto');
  }

  el.style.transition = '0s';
  const zIndex = getComputedStyle(document.documentElement).getPropertyValue('--z-index-child-2');
  el.style.zIndex = zIndex;
  el.style.position = 'relative';

  el.classList.add(getTooltipClass(binding, rotation === 180));
};

const removeTooltip = (el: HTMLElement, binding: TooltipBinding) => {
  el.removeAttribute(TOOLTIP_ATTR);

  el.style.zIndex = '';
  el.style.position = '';

  el.classList.remove(getTooltipClass(binding, true));
  el.classList.remove(getTooltipClass(binding, false));
};

const unbind = (el: HTMLElement, binding: TooltipBinding) => {
  el.removeEventListener(MouseEvent.MouseOver, () => addTooltip(el, binding));
  el.removeEventListener(MouseEvent.MouseLeave, () => removeTooltip(el, binding));
  el.removeEventListener(MouseEvent.Click, () => removeTooltip(el, binding));
};

const bind = (el: HTMLElement, binding: TooltipBinding) => {
  el.addEventListener(MouseEvent.MouseOver, () => addTooltip(el, binding));
  el.addEventListener(MouseEvent.MouseLeave, () => removeTooltip(el, binding));
  el.addEventListener(MouseEvent.Click, () => removeTooltip(el, binding));
};

export const vTooltip = {
  mounted: (el: HTMLElement, binding: TooltipBinding) => bind(el, binding),
  updated: (el: HTMLElement, binding: TooltipBinding) => bind(el, binding),
  beforeUnmount: (el: HTMLElement, binding: TooltipBinding) => unbind(el, binding)
};
