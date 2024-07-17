export interface ResponseError {
  error: string;
  errorDescription: string;
  code: number;
}

export type BaseResponse<T> = Promise<[T, ResponseError | null]>;
