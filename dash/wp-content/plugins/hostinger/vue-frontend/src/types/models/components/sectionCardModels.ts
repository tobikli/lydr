export type SectionItem = {
  id: string;
  title: string;
  description: string;
  isToggleDisplayed?: boolean;
  toggleValue?: boolean;
  sideButton?: {
    text: string;
    onClick: () => void;
  };
  copyLink?: string;
};
