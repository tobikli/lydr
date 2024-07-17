import { toUnicode as punyUnicode } from "punycode";
import { AxiosResponse } from "axios";
import { toast } from "vue3-toastify";
import { ResponseError, BaseResponse } from "@/types";
import { useGeneralStoreData } from "@/stores";

/**
 * Converts a string to Unicode using Punycode encoding.
 * If the input string is falsy, it returns the input string itself.
 *
 * @param str - The string to convert to Unicode.
 * @returns The Unicode representation of the input string, or the input string itself if it is falsy.
 */
export const toUnicode = (str: string) => (str ? punyUnicode(str) : str);

/**
 * Converts the first character of a word to uppercase and returns the modified word.
 * If the word is an empty string, an empty string is returned.
 *
 * @param word - The word to convert to title case.
 * @returns The word with the first character in uppercase.
 */
export const toTitleCase = (word: string = "") =>
  word.charAt(0).toUpperCase() + word.slice(1);

/**
 * Delays the execution for the specified number of milliseconds.
 * @param ms - The number of milliseconds to delay the execution.
 * @returns A Promise that resolves after the specified delay.
 */
export const timeout = (ms: number) =>
  new Promise((resolve) => setTimeout(resolve, ms));

/**
 * Copies the given string to the clipboard.
 *
 * @param copiedString - The string to be copied.
 * @param message - The success message to be displayed.
 * @param toastrParams - Additional parameters for the toast notification.
 */
export const copyString = (
  copiedString: string,
  message = "Copied successfully",
  toastrParams = {}
) => {
  const el = document.createElement("textarea");
  el.value = copiedString;

  el.setAttribute("readonly", "");
  document.body.appendChild(el);
  el.select();

  document.execCommand("copy");
  document.body.removeChild(el);

  const copyString = "Text has been copied successfully";

  if (!message) return;
  toast.success(copyString, toastrParams);
};
/**
 * Wraps the given value in a CSS variable.
 * @param value - The value to be wrapped.
 * @returns The wrapped value as a CSS variable.
 */
export const wrapInCssVar = (value: string | number) => `var(--${value})`;
/**
 * Capitalizes the first letter of a string.
 * @param str - The input string.
 * @returns The input string with the first letter capitalized.
 */
export const capitalize = (str: string) =>
  str.charAt(0).toUpperCase() + str.substring(1);

// eslint-disable-next-line func-style
/**
 * Calls an asynchronous function and handles the response.
 * @param promise - The promise to be resolved.
 * @returns A tuple containing the response data and any error that occurred.
 */
export async function asyncCall<T>(
  promise: Promise<AxiosResponse<T>>
): BaseResponse<T> {
  try {
    const response: any = await promise;

    if (
      !response.error ||
      (Array.isArray(response.error) && !response.error.length)
    ) {
      return [response.data.data, null];
    }

    return [{} as any, response];
  } catch (er) {
    return [{} as any, er as ResponseError];
  }
}
/**
 * Retrieves the URL of an asset based on the provided path.
 * @param path - The path of the asset.
 * @returns The URL of the asset.
 */
export const getAssetSource = (path: string) => {
  const { pluginUrl } = useGeneralStoreData();
  // @ts-ignore
  return `${pluginUrl}vue-frontend/src/assets/${path}`;
};

/**
 * Converts a kebab-case string to camelCase.
 * @param string - The kebab-case string to convert.
 * @returns The camelCase version of the input string.
 */
export const kebabToCamel = (string: string) => {
  return string.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
};

/**
 * Compares two version numbers and returns a comparison result.
 * @param newVersion - The old version number.
 * @param currentVersion - The new version number.
 * @returns -1 if currentVersion is greater than newVersion, 1 if currentVersion is less than newVersion, 0 if they are equal.
 */
export const isNewerVerison = ({
  newVersion,
  currentVersion,
}: {
  newVersion: string;
  currentVersion: string;
}) => {
  if (!newVersion || !currentVersion) return false;

  const newVersionParts = newVersion.split(".");
  const currentVersionParts = currentVersion.split(".");
  for (
    let i = 0;
    i < Math.max(currentVersionParts.length, newVersionParts.length);
    i++
  ) {
    const newPart = parseInt(currentVersionParts[i]) || 0;
    const oldPart = parseInt(newVersionParts[i]) || 0;
    if (newPart > oldPart) return false;
    if (newPart < oldPart) return true;
  }

  return false;
};

/**
 * Returns the base URL of a given URL.
 * @param url - The input URL.
 * @returns The base URL of the input URL.
 */
export const getBaseUrl = (url: string) => {
  const parsedUrl = new URL(url);
  return `${parsedUrl.protocol}//${parsedUrl.host}${parsedUrl.pathname.split("/").slice(0, -1).join("/")}/`;
};

export const translate = (key: string) => {
  // @ts-ignore
  return hostinger_tools_data.translations[key] || key;
};
