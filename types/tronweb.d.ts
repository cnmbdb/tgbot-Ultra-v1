declare module 'tronweb' {
  export default class TronWeb {
    constructor(options?: any);
    static fromMnemonic(mnemonic: string, network?: string): TronWeb;
    trx: any;
    isAddress(address: string): boolean;
    toHex(str: string): string;
    fromHex(hex: string): string;
    [key: string]: any;
  }
}
