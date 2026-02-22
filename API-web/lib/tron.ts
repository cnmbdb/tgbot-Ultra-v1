import TronWeb from 'tronweb';
import { mnemonicToSeedSync } from 'bip39';
import { HDKey } from '@scure/bip32';

// TronWeb 实例
let tronWebInstance: TronWeb | null = null;

export function getTronWeb(): TronWeb {
  if (!tronWebInstance) {
    tronWebInstance = new TronWeb({
      fullHost: 'https://api.trongrid.io',
    });
  }
  return tronWebInstance;
}

/**
 * 从私钥获取地址
 */
export function getAddressFromPrivateKey(privateKey: string): string {
  try {
    const tronWeb = getTronWeb();
    // 移除 0x 前缀（如果有）
    const cleanKey = privateKey.startsWith('0x') ? privateKey.slice(2) : privateKey;
    const address = tronWeb.address.fromPrivateKey(cleanKey);
    return address;
  } catch (error) {
    throw new Error(`获取地址失败: ${error}`);
  }
}

/**
 * 从助记词获取地址和私钥
 */
export function getAddressFromMnemonic(mnemonic: string): { address: string; privateKey: string } {
  try {
    const seed = mnemonicToSeedSync(mnemonic);
    const hdkey = HDKey.fromMasterSeed(seed);
    const path = "m/44'/195'/0'/0/0"; // Tron 标准路径
    const child = hdkey.derive(path);
    if (!child.privateKey) {
      throw new Error('无法派生私钥');
    }
    const privateKey = Buffer.from(child.privateKey).toString('hex');
    
    const tronWeb = getTronWeb();
    const address = tronWeb.address.fromPrivateKey(privateKey);
    
    return { address, privateKey };
  } catch (error) {
    throw new Error(`从助记词获取地址失败: ${error}`);
  }
}

/**
 * 获取账户余额
 */
export async function getAccountBalance(address: string): Promise<{ trx: number; usdt: number }> {
  try {
    const tronWeb = getTronWeb();
    const account = await tronWeb.trx.getAccount(address);
    const trxBalance = account.balance || 0;
    
    // 获取 USDT 余额
    const contractAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
    const contract = await tronWeb.contract().at(contractAddress);
    const usdtBalance = await contract.balanceOf(address).call();
    
    return {
      trx: trxBalance / 1000000, // 转换为 TRX
      usdt: usdtBalance / 1000000, // USDT 精度为 6
    };
  } catch (error) {
    throw new Error(`获取余额失败: ${error}`);
  }
}

/**
 * 发送 TRX
 */
export async function sendTRX(
  fromAddress: string,
  toAddress: string,
  amount: number,
  privateKey: string,
  permissionId?: number
): Promise<{ txid: string; success: boolean }> {
  try {
    const tronWeb = getTronWeb();
    const cleanKey = privateKey.startsWith('0x') ? privateKey.slice(2) : privateKey;
    
    // 构建交易
    const transaction = await tronWeb.transactionBuilder.sendTrx(
      toAddress,
      amount * 1000000, // 转换为 sun
      fromAddress,
      permissionId
    );
    
    // 签名
    const signed = await tronWeb.trx.sign(transaction, cleanKey);
    
    // 广播
    const result = await tronWeb.trx.broadcast(signed);
    
    if (result.result) {
      return { txid: result.txid, success: true };
    } else {
      throw new Error(result.message || '交易失败');
    }
  } catch (error) {
    throw new Error(`发送 TRX 失败: ${error}`);
  }
}

/**
 * 发送 TRC20 (USDT)
 */
export async function sendTRC20(
  fromAddress: string,
  toAddress: string,
  amount: number,
  contractAddress: string,
  privateKey: string,
  permissionId?: number
): Promise<{ txid: string; success: boolean }> {
  try {
    const tronWeb = getTronWeb();
    const cleanKey = privateKey.startsWith('0x') ? privateKey.slice(2) : privateKey;
    
    // 获取合约实例
    const contract = await tronWeb.contract().at(contractAddress);
    
    // 构建交易
    const transaction = await contract.transfer(
      toAddress,
      amount * 1000000 // USDT 精度为 6
    ).send({
      feeLimit: 100000000,
      callValue: 0,
      shouldPollResponse: false,
    });
    
    // 签名
    const signed = await tronWeb.trx.sign(transaction, cleanKey, permissionId);
    
    // 广播
    const result = await tronWeb.trx.broadcast(signed);
    
    if (result.result) {
      return { txid: result.txid, success: true };
    } else {
      throw new Error(result.message || '交易失败');
    }
  } catch (error) {
    throw new Error(`发送 TRC20 失败: ${error}`);
  }
}

/**
 * TRC20 授权
 */
export async function approveTRC20(
  fromAddress: string,
  spenderAddress: string | null,
  contractAddress: string,
  privateKey: string,
  approveType: number
): Promise<{ txid: string; success: boolean }> {
  try {
    const tronWeb = getTronWeb();
    const cleanKey = privateKey.startsWith('0x') ? privateKey.slice(2) : privateKey;
    
    const contract = await tronWeb.contract().at(contractAddress);
    
    // 构建授权交易
    const maxAmount = '0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff';
    const transaction = await contract.approve(
      spenderAddress || fromAddress,
      maxAmount
    ).send({
      feeLimit: 100000000,
      callValue: 0,
      shouldPollResponse: false,
    });
    
    // 签名
    const signed = await tronWeb.trx.sign(transaction, cleanKey);
    
    // 广播
    const result = await tronWeb.trx.broadcast(signed);
    
    if (result.result) {
      return { txid: result.txid, success: true };
    } else {
      throw new Error(result.message || '授权失败');
    }
  } catch (error) {
    throw new Error(`授权失败: ${error}`);
  }
}

/**
 * 多签操作
 */
export async function multiSign(
  walletAddress: string,
  multiAddress: string | null,
  privateKey: string,
  multiType: number,
  isSendTrx: string
): Promise<{ txid: string; success: boolean }> {
  try {
    const tronWeb = getTronWeb();
    const cleanKey = privateKey.startsWith('0x') ? privateKey.slice(2) : privateKey;
    
    // 这里需要根据实际的多签逻辑实现
    // 简化版本：创建多签账户
    const transaction = await tronWeb.transactionBuilder.createAccount(
      multiAddress || walletAddress,
      walletAddress
    );
    
    const signed = await tronWeb.trx.sign(transaction, cleanKey);
    const result = await tronWeb.trx.broadcast(signed);
    
    if (result.result) {
      return { txid: result.txid, success: true };
    } else {
      throw new Error(result.message || '多签失败');
    }
  } catch (error) {
    throw new Error(`多签失败: ${error}`);
  }
}

/**
 * 能量委托和取消委托
 */
export async function delegateAndUndelegate(
  fromAddress: string,
  toAddress: string,
  amount: number,
  privateKey: string,
  isDelegate: boolean
): Promise<{ txid: string; success: boolean }> {
  try {
    const tronWeb = getTronWeb();
    const cleanKey = privateKey.startsWith('0x') ? privateKey.slice(2) : privateKey;
    
    // 构建委托/取消委托交易
    const transaction = isDelegate
      ? await tronWeb.transactionBuilder.delegateResource(
          amount * 1000000, // 转换为 sun
          toAddress,
          'ENERGY',
          fromAddress
        )
      : await tronWeb.transactionBuilder.undelegateResource(
          amount * 1000000,
          toAddress,
          'ENERGY',
          fromAddress
        );
    
    const signed = await tronWeb.trx.sign(transaction, cleanKey);
    const result = await tronWeb.trx.broadcast(signed);
    
    if (result.result) {
      return { txid: result.txid, success: true };
    } else {
      throw new Error(result.message || '操作失败');
    }
  } catch (error) {
    throw new Error(`能量操作失败: ${error}`);
  }
}

/**
 * 获取委托地址
 */
export async function getDelegatedAddress(address: string): Promise<string[]> {
  try {
    const tronWeb = getTronWeb();
    const account = await tronWeb.trx.getAccountResources(address);
    // 这里需要根据实际 API 返回委托地址列表
    return [];
  } catch (error) {
    throw new Error(`获取委托地址失败: ${error}`);
  }
}
