import { computed } from 'vue';

import {
  BUTTON_ICON_CONFIGURATION,
  BUTTON_LOADER_DIMENSION_CONFIGURATION,
  BUTTON_SIZE_CONFIGURATION,
  BUTTON_VARIANT_CONFIGURATION,
} from '@/components/Button/configuration';
import type { Color, IButtonPropsMandatory } from '@/types';
import { wrapInCssVar } from '@/utils/helpers';

export const useButton = (props: IButtonPropsMandatory) => {
  if (!BUTTON_VARIANT_CONFIGURATION[props.variant][props.color]) {
    throw new Error(
      `Invalid variant and color combination: ${props.variant} ${props.color}`,
    );
  }

  const tag = computed(() => {
    if (typeof props.to === 'string') {
      return 'a';
    }

    if (props.to) {
      return 'router-link';
    }

    return 'button';
  });

  const configuration = computed(
    () => BUTTON_VARIANT_CONFIGURATION[props.variant][props.color],
  );

  const iconConfiguration = computed(() => {
    const color = props.isDisabled
      ? configuration.value.disabled.color
      : configuration.value.color;

    return {
      size: BUTTON_ICON_CONFIGURATION[props.size].size,
      color,
    };
  });

  const getColorConfiguration = () => wrapInCssVar(configuration.value.color);

  const getBackgroundConfiguration = () =>
    wrapInCssVar(configuration.value.backgroundColor);

  const getLoaderBorderColor = (): Color | undefined => {
    if (props.variant === 'contain') {
      return 'white';
    }

    return undefined;
  };

  const style = computed(() => ({
    border: configuration.value.border,
    padding: BUTTON_SIZE_CONFIGURATION[props.size].padding,
    backgroundColor: getBackgroundConfiguration(),
    color: getColorConfiguration(),
    colorDisabled: wrapInCssVar(configuration.value.disabled.color),
    backgroundColorDisabled: wrapInCssVar(
      configuration.value.disabled.backgroundColor,
    ),
    backgroundHoverColor: wrapInCssVar(
      configuration.value.hoverBackgroundColor,
    ),
    icon: iconConfiguration.value,
    loader: {
      borderColor: getLoaderBorderColor(),
      size: BUTTON_LOADER_DIMENSION_CONFIGURATION[props.size].size,
      border: BUTTON_LOADER_DIMENSION_CONFIGURATION[props.size].border,
    },
  }));

  return {
    style,
    tag,
    configuration,
  };
};
