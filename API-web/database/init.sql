-- API-Web PostgreSQL 数据库初始化脚本

-- API 请求日志表
CREATE TABLE IF NOT EXISTS api_logs (
  id BIGSERIAL PRIMARY KEY,
  api_path VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  source_ip VARCHAR(45) NOT NULL,
  source_host VARCHAR(255),
  bot_id VARCHAR(100),
  request_data JSONB,
  response_data JSONB,
  status_code INTEGER,
  response_time INTEGER,
  error_message TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_api_logs_api_path ON api_logs(api_path);
CREATE INDEX idx_api_logs_source_ip ON api_logs(source_ip);
CREATE INDEX idx_api_logs_bot_id ON api_logs(bot_id);
CREATE INDEX idx_api_logs_created_at ON api_logs(created_at);

-- 机器人连接表
CREATE TABLE IF NOT EXISTS bot_connections (
  id BIGSERIAL PRIMARY KEY,
  bot_id VARCHAR(100) NOT NULL UNIQUE,
  bot_name VARCHAR(255),
  source_ip VARCHAR(45) NOT NULL,
  source_host VARCHAR(255),
  last_request_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  request_count BIGINT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_bot_connections_source_ip ON bot_connections(source_ip);
CREATE INDEX idx_bot_connections_last_request_time ON bot_connections(last_request_time);
