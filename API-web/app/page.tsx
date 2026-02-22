export default function Home() {
  return (
    <div style={{ 
      display: 'flex', 
      flexDirection: 'column', 
      alignItems: 'center', 
      justifyContent: 'center', 
      minHeight: '100vh',
      padding: '40px',
      fontFamily: 'Arial, sans-serif',
      textAlign: 'center'
    }}>
      <h1 style={{ fontSize: '32px', marginBottom: '20px' }}>API-Web 服务</h1>
      <p style={{ fontSize: '18px', color: '#666', marginBottom: '40px' }}>
        安全替代后门服务器的 API 服务系统
      </p>
      <div style={{ marginTop: '40px' }}>
        <a 
          href="/admin" 
          style={{ 
            display: 'inline-block',
            padding: '12px 30px',
            background: '#0070f3',
            color: 'white',
            textDecoration: 'none',
            borderRadius: '6px',
            fontSize: '16px',
            fontWeight: 'bold'
          }}
        >
          进入管理后台 →
        </a>
      </div>
    </div>
  )
}
