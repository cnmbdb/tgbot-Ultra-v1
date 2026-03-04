export default function Home() {
  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        minHeight: '100vh',
        padding: '40px',
        fontFamily: 'Arial, sans-serif',
        textAlign: 'center',
      }}
    >
      <h1 style={{ fontSize: '32px', marginBottom: '20px' }}>服务正常运行中</h1>
      <p style={{ fontSize: '18px', color: '#666', marginBottom: '16px' }}>
        这是一个通用服务首页，不展示具体接口信息。
      </p>
      <p style={{ fontSize: '14px', color: '#999', maxWidth: '520px', lineHeight: 1.6 }}>
        如需进行系统配置或查看统计信息，请联系系统管理员获取专用管理地址。
      </p>
    </div>
  );
}
