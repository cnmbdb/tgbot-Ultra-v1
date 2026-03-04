declare module 'mysql2/promise' {
  import { Pool, PoolOptions } from 'mysql2';
  export function createPool(config: PoolOptions): Pool;
  export interface Pool {
    execute(sql: string, params?: any[]): Promise<[any, any]>;
  }
}
