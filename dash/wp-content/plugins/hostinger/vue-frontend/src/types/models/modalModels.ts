export interface ModalContent {
    title?: string;
    subtitle?: string;
    data?: any;
    onSuccess?: () => void;
    onClose?: () => void;
    [key: string]: any;
  }
  
  export interface ModalSettings {
    isXL?: boolean;
    isXXL?: boolean;
    isLG?: boolean;
    hasCloseButton?: boolean;
    noContentPadding?: boolean;
    noBorder?: boolean;
  }
  