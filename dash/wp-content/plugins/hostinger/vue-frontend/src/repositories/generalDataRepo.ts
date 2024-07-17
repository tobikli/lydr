import http from "@/utils/services/httpService";
import { SettingsData, Header } from "@/types";

// @ts-ignore
const URL = `${hostinger_tools_data.rest_base_url}hostinger-tools-plugin/v1`;

export const generalDataRepo = {
  getSettings: () =>
    http.get<SettingsData>(`${URL}/get-settings`, {
      //@ts-ignore
      headers: { [Header.WP_NONCE]: hostinger_tools_data.nonce },
    }),
  postSettings: (data: SettingsData) =>
    http.post<SettingsData>(`${URL}/update-settings`, data, {
      //@ts-ignore
      headers: { [Header.WP_NONCE]: hostinger_tools_data.nonce },
    }),

  getRegenerateByPassCode: () =>
    http.get<SettingsData>(`${URL}/regenerate-bypass-code`, {
      //@ts-ignore
      headers: { [Header.WP_NONCE]: hostinger_tools_data.nonce },
    }),
};
