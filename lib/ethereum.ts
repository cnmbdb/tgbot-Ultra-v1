import { ethers } from 'ethers';
import { mnemonicToSeedSync } from 'bip39';
import { HDKey } from '@scure/bip32';

/**
 * 从私钥获取以太坊地址
 */
export function getEthereumAddressFromPrivateKey(privateKey: string): string {
  try {
    const cleanKey = privateKey.startsWith('0x') ? privateKey : `0x${privateKey}`;
    const wallet = new ethers.Wallet(cleanKey);
    return wallet.address;
  } catch (error) {
    throw new Error(`获取以太坊地址失败: ${error}`);
  }
}

/**
 * 从助记词获取以太坊地址和私钥
 */
export function getEthereumAddressFromMnemonic(mnemonic: string): { address: string; privateKey: string } {
  try {
    const seed = mnemonicToSeedSync(mnemonic);
    const hdkey = HDKey.fromMasterSeed(seed);
    const path = "m/44'/60'/0'/0/0"; // 以太坊标准路径
    const child = hdkey.derive(path);
    if (!child.privateKey) {
      throw new Error('无法派生私钥');
    }
    const privateKey = Buffer.from(child.privateKey).toString('hex');
    
    const wallet = new ethers.Wallet(`0x${privateKey}`);
    return { address: wallet.address, privateKey: `0x${privateKey}` };
  } catch (error) {
    throw new Error(`从助记词获取以太坊地址失败: ${error}`);
  }
}

/**
 * 获取以太坊账户余额
 */
export async function getEthereumBalance(address: string, rpcUrl?: string): Promise<{ eth: string; usdt: string }> {
  try {
    const provider = rpcUrl 
      ? new ethers.JsonRpcProvider(rpcUrl)
      : new ethers.JsonRpcProvider('https://eth.llamarpc.com');
    
    const balance = await provider.getBalance(address);
    const ethBalance = ethers.formatEther(balance);
    
    // 获取 USDT 余额（ERC20）
    const usdtAddress = '0xdAC17F958D2ee523a2206206994597C13D831ec7';
    const usdtAbi = [
      'function balanceOf(address owner) view returns (uint256)',
      'function decimals() view returns (uint8)',
    ];
    
    const usdtContract = new ethers.Contract(usdtAddress, usdtAbi, provider);
    const usdtBalance = await usdtContract.balanceOf(address);
    const decimals = await usdtContract.decimals();
    const usdtBalanceFormatted = ethers.formatUnits(usdtBalance, decimals);
    
    return {
      eth: ethBalance,
      usdt: usdtBalanceFormatted,
    };
  } catch (error) {
    throw new Error(`获取以太坊余额失败: ${error}`);
  }
}
