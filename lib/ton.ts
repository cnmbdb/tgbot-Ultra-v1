import { mnemonicToPrivateKey } from '@ton/crypto';

// 这里不直接从 '@ton/ton' 导入类型，避免类型变动导致构建失败，运行时按 any 处理。
// eslint-disable-next-line @typescript-eslint/no-var-requires
const ton: any = require('@ton/ton');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const core: any = require('@ton/core');

// TON Premium 处理函数
// 机器人期望的返回格式：
// { code: 200, data: { txhash: 'TON_TX_HASH' }, msg: '...' }
export async function processTonPremium(params: {
  username: string;
  mnemonic: string;
  hash_value: string;
  cookie: string;
  months: number | string;
}) {
  const { username, mnemonic, hash_value, cookie, months } = params;

  try {
    const endpoint = process.env.TON_ENDPOINT || 'https://toncenter.com/api/v2/jsonRPC';
    const apiKey = process.env.TON_API_KEY || '';
    const receiver = process.env.TON_PREMIUM_RECEIVER_ADDRESS;
    const pricePerMonthStr = process.env.TON_PREMIUM_PRICE_PER_MONTH || '0.1';

    if (!receiver) {
      return {
        code: 500,
        msg: 'TON_PREMIUM_RECEIVER_ADDRESS 未配置，请在环境变量中设置 Premium 收款地址。',
      };
    }

    const monthsNum = Number(months);
    const pricePerMonth = Number(pricePerMonthStr);

    if (!Number.isFinite(monthsNum) || monthsNum <= 0) {
      return {
        code: 400,
        msg: '无效的 months 参数',
      };
    }

    if (!Number.isFinite(pricePerMonth) || pricePerMonth <= 0) {
      return {
        code: 500,
        msg: 'TON_PREMIUM_PRICE_PER_MONTH 未正确配置。',
      };
    }

    const amountTon = monthsNum * pricePerMonth;

    // 1. 从助记词派生钱包密钥
    const words = mnemonic.trim().split(/\s+/);
    if (words.length < 12) {
      return {
        code: 400,
        msg: '助记词格式不正确',
      };
    }

    const keyPair = await mnemonicToPrivateKey(words);

    // 2. 创建 TON 客户端与钱包合约
    const client = new ton.TonClient({
      endpoint,
      apiKey: apiKey || undefined,
    });

    const wallet = ton.WalletContractV4.create({
      publicKey: keyPair.publicKey,
      workchain: 0,
    });

    const openedWallet = client.open(wallet);

    // 3. 读取当前 seqno
    const seqno: number = await openedWallet.getSeqno();

    // 4. 构造转账消息
    const toAddress = core.Address.parse(receiver);
    const value = core.toNano(amountTon.toString());

    const commentCell = core.beginCell()
      .storeUint(0, 32) // 通用文本标识
      .storeStringTail(`TON Premium for @${username}, months=${monthsNum}`)
      .endCell();

    const transfer = await openedWallet.createTransfer({
      seqno,
      secretKey: keyPair.secretKey,
      messages: [
        core.internal({
          to: toAddress,
          value,
          body: commentCell,
        }),
      ],
    });

    // 5. 发送交易
    await openedWallet.send(transfer);

    // 6. 使用消息体哈希作为 txhash（与链上交易哈希一致）
    const txhash = Buffer.from(transfer.hash()).toString('hex');

    console.log('[TON Premium] sent tx', {
      username,
      months: monthsNum,
      hash_value,
      cookie_length: cookie?.length || 0,
      endpoint,
      receiver,
      amountTon,
      txhash,
    });

    return {
      code: 200,
      msg: 'success',
      data: {
        txhash,
        username,
        months: monthsNum,
        amountTon,
        receiver,
      },
    };
  } catch (error: any) {
    console.error('[TON Premium] error', error);
    return {
      code: 500,
      msg: error.message || '处理失败',
    };
  }
}
