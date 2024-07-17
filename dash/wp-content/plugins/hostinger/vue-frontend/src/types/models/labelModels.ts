export const H_LABEL_THEME = {
  WARNING_DARK: 'warningDark',
  WARNING_LIGHT: 'warningLight',
  SUCCESS_LIGHT: 'successLight',
  DANGER: 'danger',
  DANGER_LIGHT: 'dangerLight',
  PRIMARY: 'primary',
  GRAY: 'gray',
  grayLight: 'grayLight',
  WHITE: 'white'
} as const;

export const H_LABEL_THEME_CONFIGURATION: IHLabelV2Configuration = {
  warningDark: {
    backgroundColor: 'warning-light',
    color: 'warning-dark'
  },
  warningLight: {
    backgroundColor: 'warning-light',
    color: 'warning'
  },
  successLight: {
    backgroundColor: 'success-light',
    color: 'success'
  },
  danger: {
    backgroundColor: 'danger',
    color: 'white'
  },
  dangerLight: {
    backgroundColor: 'danger-light',
    color: 'danger'
  },
  primary: {
    backgroundColor: 'primary-light',
    color: 'primary'
  },
  gray: {
    backgroundColor: 'gray',
    color: 'white'
  },
  grayLight: {
    backgroundColor: 'gray-light',
    color: 'gray'
  },
  white: {
    backgroundColor: 'white',
    color: 'dark'
  }
} as const;

export interface IHLabelV2Styling {
  backgroundColor:
    | 'warning-light'
    | 'success-light'
    | 'danger'
    | 'danger-light'
    | 'primary-light'
    | 'gray'
    | 'gray-light'
    | 'white';

  color: 'warning-dark' | 'warning' | 'success' | 'danger' | 'primary' | 'gray' | 'white' | 'dark';
}
export type HLabelTheme = (typeof H_LABEL_THEME)[keyof typeof H_LABEL_THEME];

export type IHLabelV2Configuration = Record<HLabelTheme, IHLabelV2Styling>;
