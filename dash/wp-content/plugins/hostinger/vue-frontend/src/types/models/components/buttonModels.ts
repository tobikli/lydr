import type { RouteLocationRaw } from "vue-router";
import { IconUnion } from "@/types/enums";
export type ButtonSize = "small" | "medium" | "large";
export type ButtonVariant = "contain" | "outline" | "text";
export type ButtonColor = "primary" | "danger" | "warning" | "dark";

export interface IButtonV2Styling {
  backgroundColor: "primary" | "danger" | "warning" | "transparent" | "dark";
  hoverBackgroundColor:
    | "primary-dark"
    | "danger-dark"
    | "danger-light"
    | "warning-dark"
    | "warning-light"
    | "primary-light"
    | "meteorite-gray";
  border: "none" | "1px solid var(--gray-border)" | "1px solid var(--light)";
  color: "primary" | "danger" | "warning" | "dark" | "white";
  disabled: {
    color: "white" | "gray";
    backgroundColor: "gray" | "transparent";
  };
}

export type IButtonVariantConfiguration = Record<
  ButtonVariant,
  Record<ButtonColor, IButtonV2Styling>
>;

export interface IButtonProps {
  size?: ButtonSize;
  variant?: ButtonVariant;
  color?: ButtonColor;
  isDisabled?: boolean | null;
  isHovered?: boolean;
  isLoading?: boolean;
  iconPrepend?: IconUnion;
  iconAppend?: IconUnion;
  to?: RouteLocationRaw;
  target?: "_blank" | "_self" | "_parent" | "_top";
}

export interface IButtonPropsMandatory extends IButtonProps {
  size: ButtonSize;
  variant: ButtonVariant;
  color: ButtonColor;
}
