import axios from "axios";
import { AxiosRequestConfig } from "axios";
import { camelToSnakeObj, snakeToCamelObj } from "@/utils/services";
import { asyncCall } from "@/utils/helpers";

const TIMEOUT_TIME = 120_000;

export const axiosInstance = axios.create({
  timeout: TIMEOUT_TIME,
  withCredentials: false,
  headers: {
    Accept: "application/json;charset=UTF-8",
    "Content-Type": "application/json;charset=UTF-8",
  },
});

// REQUEST INTERCEPTOR - camel to snake
axiosInstance.interceptors.request.use((req: any) => {
  if (req.plain) return req;

  if (req.data) {
    req.data = camelToSnakeObj(req.data);
  }

  if (req.params) {
    req.params = camelToSnakeObj(req.params);
  }

  return req;
});

axiosInstance.interceptors.response.use(
  (res: any) => {
    return snakeToCamelObj({
      ...res,
      data: res.data,
    });
  },
  (error: Error) => Promise.reject(error)
);

const httpService = {
  get<T>(url: string, config?: AxiosRequestConfig) {
    return asyncCall<T>(axiosInstance.get(url, config));
  },
  post<T>(url: string, data?: any, config?: AxiosRequestConfig) {
    return asyncCall<T>(axiosInstance.post(url, data, config));
  },
  put<T>(url: string, data?: any, config?: AxiosRequestConfig) {
    return asyncCall<T>(axiosInstance.put(url, data, config));
  },
  patch<T>(url: string, data?: any, config?: AxiosRequestConfig) {
    return asyncCall<T>(axiosInstance.patch(url, data, config));
  },
  delete<T>(url: string, config?: AxiosRequestConfig) {
    return asyncCall<T>(axiosInstance.delete(url, config));
  },
  request<T>(config: AxiosRequestConfig) {
    return asyncCall<T>(axiosInstance(config));
  },
};

export default httpService;
