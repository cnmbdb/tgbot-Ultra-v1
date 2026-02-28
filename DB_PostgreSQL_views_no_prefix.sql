-- =============================================================================
-- 为兼容历史代码（使用“无 t_ 前缀”表名），在 PostgreSQL 中创建同名 VIEW 指向现有 t_ 前缀表
-- 说明：
-- - 不改动现有真实表（t_*），仅新增视图（无前缀）
-- - 适用于 Hyperf/旧脚本里写死的无前缀表名
-- - 这些视图是“单表直接 select *”形式，Postgres 通常允许通过视图进行 insert/update/delete
-- - 可重复执行：如果视图已存在会跳过
-- =============================================================================

DO $$
BEGIN
  -- -------------------------
  -- Transit（兑币/闪兑）相关
  -- -------------------------
  IF to_regclass('public.transit_wallet') IS NULL THEN
    EXECUTE 'CREATE VIEW public.transit_wallet AS SELECT * FROM public.t_transit_wallet';
  END IF;

  IF to_regclass('public.transit_wallet_black') IS NULL THEN
    EXECUTE 'CREATE VIEW public.transit_wallet_black AS SELECT * FROM public.t_transit_wallet_black';
  END IF;

  IF to_regclass('public.transit_wallet_coin') IS NULL THEN
    EXECUTE 'CREATE VIEW public.transit_wallet_coin AS SELECT * FROM public.t_transit_wallet_coin';
  END IF;

  IF to_regclass('public.transit_wallet_trade_list') IS NULL THEN
    EXECUTE 'CREATE VIEW public.transit_wallet_trade_list AS SELECT * FROM public.t_transit_wallet_trade_list';
  END IF;

  IF to_regclass('public.transit_user_wallet') IS NULL THEN
    EXECUTE 'CREATE VIEW public.transit_user_wallet AS SELECT * FROM public.t_transit_user_wallet';
  END IF;

  -- -------------------------
  -- Monitor（日志里已报错：monitor_wallet）
  -- -------------------------
  IF to_regclass('public.monitor_wallet') IS NULL THEN
    EXECUTE 'CREATE VIEW public.monitor_wallet AS SELECT * FROM public.t_monitor_wallet';
  END IF;

  -- -------------------------
  -- Collection（归集）
  -- -------------------------
  IF to_regclass('public.collection_wallet') IS NULL THEN
    EXECUTE 'CREATE VIEW public.collection_wallet AS SELECT * FROM public.t_collection_wallet';
  END IF;

  IF to_regclass('public.collection_wallet_list') IS NULL THEN
    EXECUTE 'CREATE VIEW public.collection_wallet_list AS SELECT * FROM public.t_collection_wallet_list';
  END IF;

  -- -------------------------
  -- Premium（会员）
  -- -------------------------
  IF to_regclass('public.premium_platform') IS NULL THEN
    EXECUTE 'CREATE VIEW public.premium_platform AS SELECT * FROM public.t_premium_platform';
  END IF;

  IF to_regclass('public.premium_platform_order') IS NULL THEN
    EXECUTE 'CREATE VIEW public.premium_platform_order AS SELECT * FROM public.t_premium_platform_order';
  END IF;

  IF to_regclass('public.premium_platform_package') IS NULL THEN
    EXECUTE 'CREATE VIEW public.premium_platform_package AS SELECT * FROM public.t_premium_platform_package';
  END IF;

  IF to_regclass('public.premium_wallet_trade_list') IS NULL THEN
    EXECUTE 'CREATE VIEW public.premium_wallet_trade_list AS SELECT * FROM public.t_premium_wallet_trade_list';
  END IF;

  -- -------------------------
  -- FMS（充值）
  -- -------------------------
  IF to_regclass('public.fms_recharge_order') IS NULL THEN
    EXECUTE 'CREATE VIEW public.fms_recharge_order AS SELECT * FROM public.t_fms_recharge_order';
  END IF;

  IF to_regclass('public.fms_wallet_trade_list') IS NULL THEN
    EXECUTE 'CREATE VIEW public.fms_wallet_trade_list AS SELECT * FROM public.t_fms_wallet_trade_list';
  END IF;

  -- -------------------------
  -- Telegram（部分旧表名无前缀）
  -- -------------------------
  IF to_regclass('public.telegram_bot_ad') IS NULL THEN
    EXECUTE 'CREATE VIEW public.telegram_bot_ad AS SELECT * FROM public.t_telegram_bot_ad';
  END IF;

  IF to_regclass('public.telegram_bot_ad_keyboard') IS NULL THEN
    EXECUTE 'CREATE VIEW public.telegram_bot_ad_keyboard AS SELECT * FROM public.t_telegram_bot_ad_keyboard';
  END IF;

  IF to_regclass('public.telegram_bot_user') IS NULL THEN
    EXECUTE 'CREATE VIEW public.telegram_bot_user AS SELECT * FROM public.t_telegram_bot_user';
  END IF;

  -- -------------------------
  -- Energy（日志里已报错：energy_platform_package）
  -- -------------------------
  IF to_regclass('public.energy_platform_package') IS NULL THEN
    EXECUTE 'CREATE VIEW public.energy_platform_package AS SELECT * FROM public.t_energy_platform_package';
  END IF;
END $$;

