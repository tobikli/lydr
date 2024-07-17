import type { ButtonSize, IButtonVariantConfiguration } from '@/types';

export const BUTTON_SIZE_CONFIGURATION: Record<
  ButtonSize,
  { padding: string }
> = {
  small: {
    padding: '6px 16px',
  },
  medium: {
    padding: '8px 24px',
  },
  large: {
    padding: '12px 32px',
  },
};

export const BUTTON_ICON_CONFIGURATION = {
  small: {
    size: 16,
  },
  medium: {
    size: 24,
  },
  large: {
    size: 24,
  },
};

export const BUTTON_LOADER_DIMENSION_CONFIGURATION = {
  small: { size: '20px', border: '3px' },
  medium: { size: '24px', border: '4px' },
  large: { size: '32px', border: '5px' },
};

export const BUTTON_VARIANT_CONFIGURATION: IButtonVariantConfiguration = {
  contain: {
    primary: {
      backgroundColor: 'primary',
      hoverBackgroundColor: 'primary-dark',
      border: 'none',
      color: 'white',
      disabled: {
        color: 'white',
        backgroundColor: 'gray',
      },
    },
    danger: {
      backgroundColor: 'danger',
      hoverBackgroundColor: 'danger-dark',
      border: 'none',
      color: 'white',
      disabled: {
        color: 'white',
        backgroundColor: 'gray',
      },
    },
    dark: {
      backgroundColor: 'dark',
      hoverBackgroundColor: 'meteorite-gray',
      border: '1px solid var(--light)',
      color: 'white',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    warning: {
      backgroundColor: 'warning',
      hoverBackgroundColor: 'warning-dark',
      border: 'none',
      color: 'dark',
      disabled: {
        color: 'white',
        backgroundColor: 'gray',
      },
    },
  },
  outline: {
    primary: {
      backgroundColor: 'transparent',
      hoverBackgroundColor: 'primary-light',
      border: '1px solid var(--gray-border)',
      color: 'primary',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    dark: {
      backgroundColor: 'dark',
      hoverBackgroundColor: 'meteorite-gray',
      border: '1px solid var(--light)',
      color: 'white',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    danger: {
      backgroundColor: 'transparent',
      hoverBackgroundColor: 'danger-light',
      border: '1px solid var(--gray-border)',
      color: 'danger',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    warning: {
      backgroundColor: 'transparent',
      hoverBackgroundColor: 'warning-light',
      border: '1px solid var(--gray-border)',
      color: 'warning',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
  },
  text: {
    primary: {
      backgroundColor: 'transparent',
      hoverBackgroundColor: 'primary-light',
      border: 'none',
      color: 'primary',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    danger: {
      backgroundColor: 'transparent',
      hoverBackgroundColor: 'danger-light',
      border: 'none',
      color: 'danger',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    dark: {
      backgroundColor: 'dark',
      hoverBackgroundColor: 'meteorite-gray',
      border: '1px solid var(--light)',
      color: 'white',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
    warning: {
      backgroundColor: 'transparent',
      hoverBackgroundColor: 'warning-light',
      border: 'none',
      color: 'warning',
      disabled: {
        color: 'gray',
        backgroundColor: 'transparent',
      },
    },
  },
};
