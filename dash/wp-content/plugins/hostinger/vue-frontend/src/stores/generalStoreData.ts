import { defineStore } from "pinia";
import { ref } from "vue";
import { snakeToCamelObj } from "@/utils/services";
import { STORE_PERSISTENT_KEYS, HostingerToolsData } from "@/types";

export const useGeneralStoreData = defineStore(
  "generalStoreData",
  () => {
    const toolsData = ref<HostingerToolsData>(
      // @ts-ignore
      snakeToCamelObj(hostinger_tools_data)
    );

    console.log("toolsData", toolsData);

    return {
      ...toolsData.value,
    };
  },
  {
    persist: { key: STORE_PERSISTENT_KEYS.TOOLS_GENERAL_DATA_STORE },
  }
);
