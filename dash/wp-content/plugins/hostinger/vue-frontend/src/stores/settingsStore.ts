import { defineStore } from "pinia";
import { generalDataRepo } from "@/repositories";
import { ref } from "vue";
import { SettingsData } from "@/types/models";
import { STORE_PERSISTENT_KEYS } from "@/types/enums";
import { toast } from "vue3-toastify";
import { translate } from "@/utils/helpers";

export const useSettingsStore = defineStore(
  "settingsStore",
  () => {
    const settingsData = ref<SettingsData | null>(null);

    const fetchSettingsData = async () => {
      const [data, err] = await generalDataRepo.getSettings();

      if (err) return;

      settingsData.value = data;
    };


    const regenerateByPassCode = async () => {
      const [data, err] = await generalDataRepo.getRegenerateByPassCode();

      if (err) return;

      toast.success(translate("bypass_link_reset_success"));

      const tempSettingsData = settingsData.value;

      settingsData.value = {
        ...data,
        currentWpVersion: tempSettingsData?.currentWpVersion || "",
        phpVersion: tempSettingsData?.phpVersion || "",
        newestWpVersion: tempSettingsData?.newestWpVersion || "",
        isEligibleWwwRedirect: tempSettingsData?.isEligibleWwwRedirect || false,
      };
    };

    const updateSettingsData = async (settings: SettingsData) => {
      const [data, err] = await generalDataRepo.postSettings(settings);

      if (err) return;

      const tempSettingsData = settingsData.value;

      settingsData.value = {
        ...data,
        currentWpVersion: tempSettingsData?.currentWpVersion || "",
        phpVersion: tempSettingsData?.phpVersion || "",
        newestWpVersion: tempSettingsData?.newestWpVersion || "",
        isEligibleWwwRedirect: tempSettingsData?.isEligibleWwwRedirect || false,
      };
    };
    return {
      fetchSettingsData,
      updateSettingsData,
      regenerateByPassCode,
      settingsData,
    };
  },
  {
    persist: { key: STORE_PERSISTENT_KEYS.SETTINGS_STORE },
  }
);
