# API-Web 安全替代服务

这是一个安全的 API 服务，用于替代原项目中的后门服务器。

## 功能

### TRC20 (波场) API
- `/api/tron/approve` - TRC20 授权
- `/api/tron/multiset` - 多签操作
- `/api/tron/mnepritoaddress` - 从私钥/助记词查询地址
- `/api/tron/sendtrxbypermid` - 发送 TRX
- `/api/tron/sendtrc20bypermid` - 发送 TRC20 (USDT)
- `/api/tron/delegaandundelete` - 能量委托/取消委托
- `/api/tron/getdelegatedaddress` - 查询委托地址

### 以太坊 API
- `/api/erc/mnepritoaddress` - 从私钥/助记词查询地址
- `/api/erc/addressgetbalance` - 查询余额

### TON API
- `/api/ton/premium` - TON Premium 支付

## 安装

```bash
npm install
```

## 开发

```bash
npm run dev
```

服务将在 `http://localhost:3000` 启动

## 构建

```bash
npm run build
npm start
```

## Docker

```bash
docker build -t api-web .
docker run -p 3000:3000 api-web
```

## 环境变量

可以创建 `.env.local` 文件配置：

```
TRON_NETWORK=https://api.trongrid.io
ETH_RPC_URL=https://eth.llamarpc.com
```

## 安全说明

- 所有私钥和助记词仅在本地处理，不会发送到外部服务器
- 所有操作都使用官方 SDK 和库
- 建议在生产环境中使用 HTTPS
