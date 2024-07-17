import { Route } from "@/types/enums";
import Home from "@/views/HostingerTools.vue";
import { translate } from "@/utils/helpers";
export default [
  {
    path: "/",
    name: Route.Base.HOSTINGER_TOOLS,
    meta: {
      title: translate("routes_hostinger_tools"),
    },
    component: Home,
  },
];
