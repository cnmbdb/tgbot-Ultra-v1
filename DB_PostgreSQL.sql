--
-- PostgreSQL database dump
--

\restrict 8ju2b8gnQVtGC2EErNdMWUaKlKS6g46lbaOrvSPadhKlauUJmy9xgQjdNZ70n4J

-- Dumped from database version 15.16
-- Dumped by pg_dump version 15.16

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

DROP INDEX IF EXISTS public.idx_energy_third_part_1;
ALTER TABLE IF EXISTS ONLY public.t_transit_wallet_trade_list DROP CONSTRAINT IF EXISTS t_transit_wallet_trade_list_pkey;
ALTER TABLE IF EXISTS ONLY public.t_transit_wallet DROP CONSTRAINT IF EXISTS t_transit_wallet_pkey;
ALTER TABLE IF EXISTS ONLY public.t_transit_wallet_coin DROP CONSTRAINT IF EXISTS t_transit_wallet_coin_pkey;
ALTER TABLE IF EXISTS ONLY public.t_transit_wallet_black DROP CONSTRAINT IF EXISTS t_transit_wallet_black_pkey;
ALTER TABLE IF EXISTS ONLY public.t_transit_user_wallet DROP CONSTRAINT IF EXISTS t_transit_user_wallet_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_user DROP CONSTRAINT IF EXISTS t_telegram_bot_user_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot DROP CONSTRAINT IF EXISTS t_telegram_bot_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_keyreply DROP CONSTRAINT IF EXISTS t_telegram_bot_keyreply_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_keyreply_keyboard DROP CONSTRAINT IF EXISTS t_telegram_bot_keyreply_keyboard_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_keyboard DROP CONSTRAINT IF EXISTS t_telegram_bot_keyboard_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_group DROP CONSTRAINT IF EXISTS t_telegram_bot_group_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_command DROP CONSTRAINT IF EXISTS t_telegram_bot_command_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_ad DROP CONSTRAINT IF EXISTS t_telegram_bot_ad_pkey;
ALTER TABLE IF EXISTS ONLY public.t_telegram_bot_ad_keyboard DROP CONSTRAINT IF EXISTS t_telegram_bot_ad_keyboard_pkey;
ALTER TABLE IF EXISTS ONLY public.t_sys_data_dictionary DROP CONSTRAINT IF EXISTS t_sys_data_dictionary_pkey;
ALTER TABLE IF EXISTS ONLY public.t_sys_config DROP CONSTRAINT IF EXISTS t_sys_config_pkey;
ALTER TABLE IF EXISTS ONLY public.t_sys_admin_opt_log DROP CONSTRAINT IF EXISTS t_sys_admin_opt_log_pkey;
ALTER TABLE IF EXISTS ONLY public.t_shop_order DROP CONSTRAINT IF EXISTS t_shop_order_pkey;
ALTER TABLE IF EXISTS ONLY public.t_shop_goods DROP CONSTRAINT IF EXISTS t_shop_goods_pkey;
ALTER TABLE IF EXISTS ONLY public.t_shop_goods_cdkey DROP CONSTRAINT IF EXISTS t_shop_goods_cdkey_pkey;
ALTER TABLE IF EXISTS ONLY public.t_shop_goods_bot DROP CONSTRAINT IF EXISTS t_shop_goods_bot_pkey;
ALTER TABLE IF EXISTS ONLY public.t_roles DROP CONSTRAINT IF EXISTS t_roles_pkey;
ALTER TABLE IF EXISTS ONLY public.t_roles DROP CONSTRAINT IF EXISTS t_roles_name_guard_name_key;
ALTER TABLE IF EXISTS ONLY public.t_role_has_permissions DROP CONSTRAINT IF EXISTS t_role_has_permissions_pkey;
ALTER TABLE IF EXISTS ONLY public.t_premium_wallet_trade_list DROP CONSTRAINT IF EXISTS t_premium_wallet_trade_list_pkey;
ALTER TABLE IF EXISTS ONLY public.t_premium_platform DROP CONSTRAINT IF EXISTS t_premium_platform_pkey;
ALTER TABLE IF EXISTS ONLY public.t_premium_platform_package DROP CONSTRAINT IF EXISTS t_premium_platform_package_pkey;
ALTER TABLE IF EXISTS ONLY public.t_premium_platform_order DROP CONSTRAINT IF EXISTS t_premium_platform_order_pkey;
ALTER TABLE IF EXISTS ONLY public.t_permissions DROP CONSTRAINT IF EXISTS t_permissions_pkey;
ALTER TABLE IF EXISTS ONLY public.t_permissions DROP CONSTRAINT IF EXISTS t_permissions_name_guard_name_key;
ALTER TABLE IF EXISTS ONLY public.t_monitor_wallet DROP CONSTRAINT IF EXISTS t_monitor_wallet_pkey;
ALTER TABLE IF EXISTS ONLY public.t_monitor_bot DROP CONSTRAINT IF EXISTS t_monitor_bot_pkey;
ALTER TABLE IF EXISTS ONLY public.t_model_has_roles DROP CONSTRAINT IF EXISTS t_model_has_roles_pkey;
ALTER TABLE IF EXISTS ONLY public.t_model_has_permissions DROP CONSTRAINT IF EXISTS t_model_has_permissions_pkey;
ALTER TABLE IF EXISTS ONLY public.t_fms_wallet_trade_list DROP CONSTRAINT IF EXISTS t_fms_wallet_trade_list_pkey;
ALTER TABLE IF EXISTS ONLY public.t_fms_recharge_order DROP CONSTRAINT IF EXISTS t_fms_recharge_order_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_wallet_trade_list DROP CONSTRAINT IF EXISTS t_energy_wallet_trade_list_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_third_part DROP CONSTRAINT IF EXISTS t_energy_third_part_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_special DROP CONSTRAINT IF EXISTS t_energy_special_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_special_list DROP CONSTRAINT IF EXISTS t_energy_special_list_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_quick_order DROP CONSTRAINT IF EXISTS t_energy_quick_order_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_platform DROP CONSTRAINT IF EXISTS t_energy_platform_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_platform_package DROP CONSTRAINT IF EXISTS t_energy_platform_package_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_platform_order DROP CONSTRAINT IF EXISTS t_energy_platform_order_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_platform_bot DROP CONSTRAINT IF EXISTS t_energy_platform_bot_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_ai_trusteeship DROP CONSTRAINT IF EXISTS t_energy_ai_trusteeship_pkey;
ALTER TABLE IF EXISTS ONLY public.t_energy_ai_bishu DROP CONSTRAINT IF EXISTS t_energy_ai_bishu_pkey;
ALTER TABLE IF EXISTS ONLY public.t_collection_wallet DROP CONSTRAINT IF EXISTS t_collection_wallet_pkey;
ALTER TABLE IF EXISTS ONLY public.t_collection_wallet_list DROP CONSTRAINT IF EXISTS t_collection_wallet_list_pkey;
ALTER TABLE IF EXISTS ONLY public.t_admin DROP CONSTRAINT IF EXISTS t_admin_pkey;
ALTER TABLE IF EXISTS ONLY public.t_admin_login_log DROP CONSTRAINT IF EXISTS t_admin_login_log_pkey;
ALTER TABLE IF EXISTS public.t_transit_wallet_trade_list ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_transit_wallet_coin ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_transit_wallet_black ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_transit_wallet ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_transit_user_wallet ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_user ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_keyreply_keyboard ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_keyreply ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_keyboard ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_group ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_command ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_ad_keyboard ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot_ad ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_telegram_bot ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_sys_data_dictionary ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_sys_config ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_sys_admin_opt_log ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_shop_order ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_shop_goods_cdkey ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_shop_goods_bot ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_shop_goods ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_roles ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_premium_wallet_trade_list ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_premium_platform_package ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_premium_platform_order ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_premium_platform ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_permissions ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_monitor_wallet ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_monitor_bot ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_fms_wallet_trade_list ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_fms_recharge_order ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_wallet_trade_list ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_third_part ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_special_list ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_special ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_quick_order ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_platform_package ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_platform_order ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_platform_bot ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_platform ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_ai_trusteeship ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_energy_ai_bishu ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_collection_wallet_list ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_collection_wallet ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_admin_login_log ALTER COLUMN rid DROP DEFAULT;
ALTER TABLE IF EXISTS public.t_admin ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.t_transit_wallet_trade_list_rid_seq;
DROP TABLE IF EXISTS public.t_transit_wallet_trade_list;
DROP SEQUENCE IF EXISTS public.t_transit_wallet_rid_seq;
DROP SEQUENCE IF EXISTS public.t_transit_wallet_coin_rid_seq;
DROP TABLE IF EXISTS public.t_transit_wallet_coin;
DROP SEQUENCE IF EXISTS public.t_transit_wallet_black_rid_seq;
DROP TABLE IF EXISTS public.t_transit_wallet_black;
DROP TABLE IF EXISTS public.t_transit_wallet;
DROP SEQUENCE IF EXISTS public.t_transit_user_wallet_rid_seq;
DROP TABLE IF EXISTS public.t_transit_user_wallet;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_user_rid_seq;
DROP TABLE IF EXISTS public.t_telegram_bot_user;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_rid_seq;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_keyreply_rid_seq;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_keyreply_keyboard_rid_seq;
DROP TABLE IF EXISTS public.t_telegram_bot_keyreply_keyboard;
DROP TABLE IF EXISTS public.t_telegram_bot_keyreply;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_keyboard_rid_seq;
DROP TABLE IF EXISTS public.t_telegram_bot_keyboard;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_group_rid_seq;
DROP TABLE IF EXISTS public.t_telegram_bot_group;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_command_rid_seq;
DROP TABLE IF EXISTS public.t_telegram_bot_command;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_ad_rid_seq;
DROP SEQUENCE IF EXISTS public.t_telegram_bot_ad_keyboard_rid_seq;
DROP TABLE IF EXISTS public.t_telegram_bot_ad_keyboard;
DROP TABLE IF EXISTS public.t_telegram_bot_ad;
DROP TABLE IF EXISTS public.t_telegram_bot;
DROP SEQUENCE IF EXISTS public.t_sys_data_dictionary_rid_seq;
DROP TABLE IF EXISTS public.t_sys_data_dictionary;
DROP SEQUENCE IF EXISTS public.t_sys_config_rid_seq;
DROP TABLE IF EXISTS public.t_sys_config;
DROP SEQUENCE IF EXISTS public.t_sys_admin_opt_log_rid_seq;
DROP TABLE IF EXISTS public.t_sys_admin_opt_log;
DROP SEQUENCE IF EXISTS public.t_shop_order_rid_seq;
DROP TABLE IF EXISTS public.t_shop_order;
DROP SEQUENCE IF EXISTS public.t_shop_goods_rid_seq;
DROP SEQUENCE IF EXISTS public.t_shop_goods_cdkey_rid_seq;
DROP TABLE IF EXISTS public.t_shop_goods_cdkey;
DROP SEQUENCE IF EXISTS public.t_shop_goods_bot_rid_seq;
DROP TABLE IF EXISTS public.t_shop_goods_bot;
DROP TABLE IF EXISTS public.t_shop_goods;
DROP SEQUENCE IF EXISTS public.t_roles_id_seq;
DROP TABLE IF EXISTS public.t_roles;
DROP TABLE IF EXISTS public.t_role_has_permissions;
DROP SEQUENCE IF EXISTS public.t_premium_wallet_trade_list_rid_seq;
DROP TABLE IF EXISTS public.t_premium_wallet_trade_list;
DROP SEQUENCE IF EXISTS public.t_premium_platform_rid_seq;
DROP SEQUENCE IF EXISTS public.t_premium_platform_package_rid_seq;
DROP TABLE IF EXISTS public.t_premium_platform_package;
DROP SEQUENCE IF EXISTS public.t_premium_platform_order_rid_seq;
DROP TABLE IF EXISTS public.t_premium_platform_order;
DROP TABLE IF EXISTS public.t_premium_platform;
DROP SEQUENCE IF EXISTS public.t_permissions_id_seq;
DROP TABLE IF EXISTS public.t_permissions;
DROP SEQUENCE IF EXISTS public.t_monitor_wallet_rid_seq;
DROP TABLE IF EXISTS public.t_monitor_wallet;
DROP SEQUENCE IF EXISTS public.t_monitor_bot_rid_seq;
DROP TABLE IF EXISTS public.t_monitor_bot;
DROP TABLE IF EXISTS public.t_model_has_roles;
DROP TABLE IF EXISTS public.t_model_has_permissions;
DROP SEQUENCE IF EXISTS public.t_fms_wallet_trade_list_rid_seq;
DROP TABLE IF EXISTS public.t_fms_wallet_trade_list;
DROP SEQUENCE IF EXISTS public.t_fms_recharge_order_rid_seq;
DROP TABLE IF EXISTS public.t_fms_recharge_order;
DROP SEQUENCE IF EXISTS public.t_energy_wallet_trade_list_rid_seq;
DROP TABLE IF EXISTS public.t_energy_wallet_trade_list;
DROP SEQUENCE IF EXISTS public.t_energy_third_part_rid_seq;
DROP TABLE IF EXISTS public.t_energy_third_part;
DROP SEQUENCE IF EXISTS public.t_energy_special_rid_seq;
DROP SEQUENCE IF EXISTS public.t_energy_special_list_rid_seq;
DROP TABLE IF EXISTS public.t_energy_special_list;
DROP TABLE IF EXISTS public.t_energy_special;
DROP SEQUENCE IF EXISTS public.t_energy_quick_order_rid_seq;
DROP TABLE IF EXISTS public.t_energy_quick_order;
DROP SEQUENCE IF EXISTS public.t_energy_platform_rid_seq;
DROP SEQUENCE IF EXISTS public.t_energy_platform_package_rid_seq;
DROP TABLE IF EXISTS public.t_energy_platform_package;
DROP SEQUENCE IF EXISTS public.t_energy_platform_order_rid_seq;
DROP TABLE IF EXISTS public.t_energy_platform_order;
DROP SEQUENCE IF EXISTS public.t_energy_platform_bot_rid_seq;
DROP TABLE IF EXISTS public.t_energy_platform_bot;
DROP TABLE IF EXISTS public.t_energy_platform;
DROP SEQUENCE IF EXISTS public.t_energy_ai_trusteeship_rid_seq;
DROP TABLE IF EXISTS public.t_energy_ai_trusteeship;
DROP SEQUENCE IF EXISTS public.t_energy_ai_bishu_rid_seq;
DROP TABLE IF EXISTS public.t_energy_ai_bishu;
DROP SEQUENCE IF EXISTS public.t_collection_wallet_rid_seq;
DROP SEQUENCE IF EXISTS public.t_collection_wallet_list_rid_seq;
DROP TABLE IF EXISTS public.t_collection_wallet_list;
DROP TABLE IF EXISTS public.t_collection_wallet;
DROP SEQUENCE IF EXISTS public.t_admin_login_log_rid_seq;
DROP TABLE IF EXISTS public.t_admin_login_log;
DROP SEQUENCE IF EXISTS public.t_admin_id_seq;
DROP TABLE IF EXISTS public.t_admin;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: t_admin; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_admin (
    id bigint NOT NULL,
    name character varying(30) NOT NULL,
    password character varying(100) NOT NULL,
    head character varying(255) DEFAULT NULL::character varying,
    status smallint DEFAULT '1'::smallint NOT NULL,
    remember_token character varying(100) DEFAULT NULL::character varying,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    white_ip character varying(3000) DEFAULT NULL::character varying
);


--
-- Name: t_admin_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_admin_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_admin_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_admin_id_seq OWNED BY public.t_admin.id;


--
-- Name: t_admin_login_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_admin_login_log (
    rid integer NOT NULL,
    admin_name character varying(100) NOT NULL,
    login_ip character varying(1000) DEFAULT NULL::character varying,
    login_time timestamp without time zone
);


--
-- Name: t_admin_login_log_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_admin_login_log_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_admin_login_log_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_admin_login_log_rid_seq OWNED BY public.t_admin_login_log.rid;


--
-- Name: t_collection_wallet; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_collection_wallet (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    chain_type character varying(10) DEFAULT 'trc'::character varying NOT NULL,
    wallet_addr character varying(200) NOT NULL,
    notice_tg_uid character varying(100) DEFAULT NULL::character varying,
    notice_tg_groupid character varying(100) DEFAULT NULL::character varying,
    trx_collection_amount numeric(16,6) DEFAULT 0 NOT NULL,
    usdt_collection_amount numeric(16,6) DEFAULT 0 NOT NULL,
    trx_reserved_amount numeric(16,6) DEFAULT 0 NOT NULL,
    usdt_reserved_amount numeric(16,6) DEFAULT 0 NOT NULL,
    status smallint DEFAULT 1 NOT NULL,
    create_time timestamp without time zone,
    update_time timestamp without time zone
);


--
-- Name: t_collection_wallet_list; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_collection_wallet_list (
    rid integer NOT NULL,
    wallet_addr character varying(200) NOT NULL,
    collection_wallet_addr character varying(200) NOT NULL,
    coin_name character varying(20) NOT NULL,
    collection_amount numeric(16,6) DEFAULT 0 NOT NULL,
    collection_time timestamp without time zone,
    collection_hash character varying(200) DEFAULT NULL::character varying,
    create_time timestamp without time zone
);


--
-- Name: t_collection_wallet_list_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_collection_wallet_list_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_collection_wallet_list_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_collection_wallet_list_rid_seq OWNED BY public.t_collection_wallet_list.rid;


--
-- Name: t_collection_wallet_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_collection_wallet_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_collection_wallet_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_collection_wallet_rid_seq OWNED BY public.t_collection_wallet.rid;


--
-- Name: t_energy_ai_bishu; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_ai_bishu (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    tg_uid character varying(100) NOT NULL,
    wallet_addr character varying(100) NOT NULL,
    status smallint DEFAULT 0 NOT NULL,
    current_bandwidth_quantity bigint DEFAULT 0 NOT NULL,
    current_energy_quantity bigint DEFAULT 0 NOT NULL,
    is_buy character(1) DEFAULT 'N'::bpchar NOT NULL,
    is_notice character(1) DEFAULT 'N'::bpchar NOT NULL,
    is_notice_admin character(1) DEFAULT 'N'::bpchar NOT NULL,
    total_buy_usdt numeric(14,2) DEFAULT 0 NOT NULL,
    max_buy_quantity integer DEFAULT 0 NOT NULL,
    total_buy_quantity integer DEFAULT 0 NOT NULL,
    total_buy_energy_quantity bigint DEFAULT 0 NOT NULL,
    comments character varying(2000) DEFAULT NULL::character varying,
    back_comments character varying(2000) DEFAULT NULL::character varying,
    create_by integer NOT NULL,
    create_time timestamp without time zone NOT NULL,
    update_by integer,
    update_time timestamp without time zone,
    energy_platform_rid integer,
    bishu_stop_day integer DEFAULT 0 NOT NULL
);


--
-- Name: t_energy_ai_bishu_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_ai_bishu_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_ai_bishu_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_ai_bishu_rid_seq OWNED BY public.t_energy_ai_bishu.rid;


--
-- Name: t_energy_ai_trusteeship; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_ai_trusteeship (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    tg_uid character varying(100) NOT NULL,
    wallet_addr character varying(100) NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    current_bandwidth_quantity bigint DEFAULT '0'::bigint NOT NULL,
    current_energy_quantity bigint DEFAULT '0'::bigint NOT NULL,
    min_energy_quantity integer DEFAULT 32000 NOT NULL,
    per_buy_energy_quantity integer DEFAULT 32000 NOT NULL,
    total_buy_energy_quantity bigint DEFAULT '0'::bigint NOT NULL,
    total_used_trx numeric(14,2) DEFAULT 0.00 NOT NULL,
    total_buy_quantity integer DEFAULT 0 NOT NULL,
    is_buy character(1) DEFAULT 'N'::bpchar NOT NULL,
    is_notice character(1) DEFAULT 'N'::bpchar NOT NULL,
    is_notice_admin character(1) DEFAULT 'N'::bpchar NOT NULL,
    last_buy_time timestamp without time zone,
    last_used_trx numeric(14,2) DEFAULT NULL::numeric,
    comments character varying(2000) DEFAULT NULL::character varying,
    create_by integer NOT NULL,
    create_time timestamp without time zone NOT NULL,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_energy_ai_trusteeship_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_ai_trusteeship_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_ai_trusteeship_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_ai_trusteeship_rid_seq OWNED BY public.t_energy_ai_trusteeship.rid;


--
-- Name: t_energy_platform; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_platform (
    rid integer NOT NULL,
    poll_group character(1) NOT NULL,
    platform_name smallint NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    platform_uid character varying(100) DEFAULT NULL::character varying,
    permission_id integer DEFAULT 0 NOT NULL,
    platform_balance numeric(28,6) DEFAULT '0'::numeric NOT NULL,
    alert_platform_balance numeric(28,6) DEFAULT '0'::numeric NOT NULL,
    platform_apikey character varying(3000) DEFAULT NULL::character varying,
    tg_notice_obj character varying(200) DEFAULT NULL::character varying,
    tg_notice_bot_rid integer,
    seq_sn integer DEFAULT 0 NOT NULL,
    last_alert_time timestamp without time zone,
    comments character varying(3000) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_energy_platform_bot; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_platform_bot (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    poll_group character(1) NOT NULL,
    tg_admin_uid character varying(500) DEFAULT NULL::character varying,
    status smallint DEFAULT '1'::smallint NOT NULL,
    receive_wallet character varying(100) DEFAULT NULL::character varying,
    get_tx_time timestamp without time zone,
    tg_notice_obj_receive character varying(200) DEFAULT NULL::character varying,
    tg_notice_obj_send character varying(200) DEFAULT NULL::character varying,
    comments character varying(255) DEFAULT NULL::character varying,
    is_open_ai_trusteeship character(1) DEFAULT 'N'::bpchar NOT NULL,
    trx_price_energy_32000 integer,
    trx_price_energy_65000 integer,
    per_energy_day integer,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_energy_platform_bot_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_platform_bot_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_platform_bot_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_platform_bot_rid_seq OWNED BY public.t_energy_platform_bot.rid;


--
-- Name: t_energy_platform_order; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_platform_order (
    rid integer NOT NULL,
    energy_platform_rid integer NOT NULL,
    energy_platform_bot_rid integer NOT NULL,
    platform_name smallint NOT NULL,
    platform_uid character varying(50) NOT NULL,
    source_type smallint DEFAULT '1'::smallint NOT NULL,
    receive_address character varying(50) NOT NULL,
    platform_order_id character varying(100) NOT NULL,
    energy_amount integer NOT NULL,
    energy_day smallint NOT NULL,
    energy_time timestamp without time zone NOT NULL,
    recovery_status smallint DEFAULT '1'::smallint NOT NULL,
    recovery_time timestamp without time zone,
    use_trx numeric(14,6) DEFAULT NULL::numeric
);


--
-- Name: t_energy_platform_order_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_platform_order_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_platform_order_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_platform_order_rid_seq OWNED BY public.t_energy_platform_order.rid;


--
-- Name: t_energy_platform_package; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_platform_package (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    package_type smallint DEFAULT '1'::smallint NOT NULL,
    package_name character varying(50) NOT NULL,
    energy_amount integer NOT NULL,
    energy_day smallint NOT NULL,
    trx_price numeric(14,2) DEFAULT 0.00 NOT NULL,
    usdt_price numeric(14,2) DEFAULT 0.00 NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    seq_sn integer DEFAULT 0 NOT NULL,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone,
    callback_data character varying(50) NOT NULL,
    show_notes character varying(1000) DEFAULT NULL::character varying,
    package_pic character varying(200) DEFAULT NULL::character varying,
    agent_trx_price numeric(14,2) DEFAULT 0.00 NOT NULL
);


--
-- Name: t_energy_platform_package_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_platform_package_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_platform_package_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_platform_package_rid_seq OWNED BY public.t_energy_platform_package.rid;


--
-- Name: t_energy_platform_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_platform_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_platform_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_platform_rid_seq OWNED BY public.t_energy_platform.rid;


--
-- Name: t_energy_quick_order; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_quick_order (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    tg_uid character varying(100) NOT NULL,
    wallet_addr character varying(100) NOT NULL,
    quick_order_amount bigint DEFAULT 0 NOT NULL,
    quick_order_status smallint DEFAULT 0 NOT NULL,
    create_time timestamp without time zone,
    update_time timestamp without time zone
);


--
-- Name: t_energy_quick_order_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_quick_order_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_quick_order_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_quick_order_rid_seq OWNED BY public.t_energy_quick_order.rid;


--
-- Name: t_energy_special; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_special (
    rid integer NOT NULL,
    bot_rid integer,
    tg_uid bigint,
    wallet_addr character varying(50) NOT NULL,
    wallet_energy bigint DEFAULT 0 NOT NULL,
    max_energy bigint DEFAULT 0 NOT NULL,
    send_energy bigint DEFAULT 0 NOT NULL,
    per_energy integer DEFAULT 0 NOT NULL,
    less_than_energy integer DEFAULT 0 NOT NULL,
    status smallint DEFAULT 0 NOT NULL,
    total_usdt_recharge numeric(14,2) DEFAULT 0 NOT NULL,
    total_trx_recharge numeric(14,2) DEFAULT 0 NOT NULL,
    seq_sn integer DEFAULT 0 NOT NULL,
    comments character varying(200) DEFAULT NULL::character varying
);


--
-- Name: t_energy_special_list; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_special_list (
    rid integer NOT NULL,
    bot_rid integer,
    tg_uid bigint,
    wallet_addr character varying(50) NOT NULL,
    send_wallet_addr character varying(50) NOT NULL,
    before_energy bigint,
    daili_energy integer,
    daili_hash character varying(100) DEFAULT NULL::character varying,
    daili_trx numeric(18,6) DEFAULT NULL::numeric,
    status smallint DEFAULT 1 NOT NULL,
    daili_time timestamp without time zone,
    huishou_time timestamp without time zone,
    huishou_hash character varying(100) DEFAULT NULL::character varying
);


--
-- Name: t_energy_special_list_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_special_list_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_special_list_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_special_list_rid_seq OWNED BY public.t_energy_special_list.rid;


--
-- Name: t_energy_special_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_special_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_special_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_special_rid_seq OWNED BY public.t_energy_special.rid;


--
-- Name: t_energy_third_part; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_third_part (
    rid integer NOT NULL,
    order_type smallint NOT NULL,
    tg_uid bigint NOT NULL,
    platform_rid integer NOT NULL,
    bot_rid integer NOT NULL,
    cishu_energy integer NOT NULL,
    wallet_addr character varying(50) NOT NULL,
    before_trx numeric(16,6) NOT NULL,
    change_trx numeric(16,6) NOT NULL,
    after_trx numeric(16,6) NOT NULL,
    order_time timestamp without time zone NOT NULL,
    process_status smallint DEFAULT 0 NOT NULL,
    process_time timestamp without time zone,
    process_comments character varying(2000) DEFAULT NULL::character varying
);


--
-- Name: t_energy_third_part_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_third_part_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_third_part_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_third_part_rid_seq OWNED BY public.t_energy_third_part.rid;


--
-- Name: t_energy_wallet_trade_list; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_energy_wallet_trade_list (
    rid integer NOT NULL,
    tx_hash character varying(100) NOT NULL,
    transferfrom_address character varying(200) NOT NULL,
    transferto_address character varying(200) NOT NULL,
    coin_name character varying(20) NOT NULL,
    amount character varying(100) NOT NULL,
    "timestamp" character varying(20) NOT NULL,
    process_status smallint DEFAULT '0'::smallint NOT NULL,
    process_time timestamp without time zone,
    process_comments character varying(2000) DEFAULT NULL::character varying,
    get_time timestamp without time zone,
    energy_platform_rid integer,
    energy_platform_bot_rid integer,
    platform_order_rid integer,
    energy_package_rid integer,
    tg_notice_status_receive character(1) DEFAULT 'N'::bpchar NOT NULL,
    tg_notice_status_send character(1) DEFAULT 'N'::bpchar NOT NULL
);


--
-- Name: t_energy_wallet_trade_list_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_energy_wallet_trade_list_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_energy_wallet_trade_list_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_energy_wallet_trade_list_rid_seq OWNED BY public.t_energy_wallet_trade_list.rid;


--
-- Name: t_fms_recharge_order; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_fms_recharge_order (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    recharge_tg_uid character varying(100) NOT NULL,
    recharge_tg_username character varying(100) DEFAULT NULL::character varying,
    recharge_coin_name character varying(50) NOT NULL,
    recharge_pay_price numeric(12,4) NOT NULL,
    need_pay_price numeric(12,4) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    comments character varying(255) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone,
    expire_time timestamp without time zone NOT NULL,
    cancel_time timestamp without time zone,
    complete_time timestamp without time zone,
    tx_hash character varying(100) DEFAULT NULL::character varying
);


--
-- Name: t_fms_recharge_order_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_fms_recharge_order_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_fms_recharge_order_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_fms_recharge_order_rid_seq OWNED BY public.t_fms_recharge_order.rid;


--
-- Name: t_fms_wallet_trade_list; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_fms_wallet_trade_list (
    rid integer NOT NULL,
    tx_hash character varying(100) NOT NULL,
    transferfrom_address character varying(200) NOT NULL,
    transferto_address character varying(200) NOT NULL,
    coin_name character varying(20) NOT NULL,
    amount character varying(100) NOT NULL,
    "timestamp" character varying(20) NOT NULL,
    process_status smallint DEFAULT '0'::smallint NOT NULL,
    process_time timestamp without time zone,
    process_comments character varying(255) DEFAULT NULL::character varying,
    get_time timestamp without time zone,
    tg_notice_status_receive character(1) DEFAULT 'N'::bpchar NOT NULL,
    tg_notice_status_send character(1) DEFAULT 'N'::bpchar NOT NULL,
    recharge_order_rid integer
);


--
-- Name: t_fms_wallet_trade_list_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_fms_wallet_trade_list_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_fms_wallet_trade_list_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_fms_wallet_trade_list_rid_seq OWNED BY public.t_fms_wallet_trade_list.rid;


--
-- Name: t_model_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: t_model_has_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: t_monitor_bot; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_monitor_bot (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    price_usdt_5 numeric(16,4) DEFAULT 0.0000 NOT NULL,
    price_usdt_10 numeric(16,4) DEFAULT 0.0000 NOT NULL,
    price_usdt_20 numeric(16,4) DEFAULT 0.0000 NOT NULL,
    price_usdt_50 numeric(16,4) DEFAULT 0.0000 NOT NULL,
    price_usdt_100 numeric(16,4) DEFAULT 0.0000 NOT NULL,
    price_usdt_200 numeric(16,4) DEFAULT 0.0000 NOT NULL,
    comments character varying(3000) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_monitor_bot_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_monitor_bot_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_monitor_bot_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_monitor_bot_rid_seq OWNED BY public.t_monitor_bot.rid;


--
-- Name: t_monitor_wallet; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_monitor_wallet (
    rid integer NOT NULL,
    chain_type character varying(10) NOT NULL,
    monitor_wallet character varying(200) NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    bot_rid integer,
    tg_notice_obj character varying(200) DEFAULT NULL::character varying,
    balance_alert numeric(14,2) DEFAULT 0.00 NOT NULL,
    balance_amount numeric(14,2) DEFAULT 0.00 NOT NULL,
    comments character varying(100) DEFAULT NULL::character varying,
    create_time timestamp without time zone,
    update_time timestamp without time zone
);


--
-- Name: t_monitor_wallet_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_monitor_wallet_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_monitor_wallet_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_monitor_wallet_rid_seq OWNED BY public.t_monitor_wallet.rid;


--
-- Name: t_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) DEFAULT 'web'::character varying NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    route character varying(255) DEFAULT NULL::character varying,
    pid integer
);


--
-- Name: t_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_permissions_id_seq OWNED BY public.t_permissions.id;


--
-- Name: t_premium_platform; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_premium_platform (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    platform_name smallint NOT NULL,
    tg_admin_uid character varying(500) DEFAULT NULL::character varying,
    platform_hash character varying(500) DEFAULT NULL::character varying,
    platform_cookie character varying(3000) DEFAULT NULL::character varying,
    platform_phrase character varying(3000) DEFAULT NULL::character varying,
    status smallint DEFAULT '1'::smallint NOT NULL,
    receive_wallet character varying(100) DEFAULT NULL::character varying,
    get_tx_time timestamp without time zone,
    tg_notice_obj_receive character varying(200) DEFAULT NULL::character varying,
    tg_notice_obj_send character varying(200) DEFAULT NULL::character varying,
    comments character varying(255) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_premium_platform_order; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_premium_platform_order (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    premium_platform_rid integer NOT NULL,
    source_type smallint DEFAULT '1'::smallint NOT NULL,
    buy_tg_uid character varying(100) NOT NULL,
    buy_tg_username character varying(100) DEFAULT NULL::character varying,
    premium_tg_username character varying(100) NOT NULL,
    need_pay_usdt numeric(12,4) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    premium_platform_package_rid integer NOT NULL,
    premium_package_month integer NOT NULL,
    comments character varying(255) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone,
    expire_time timestamp without time zone NOT NULL,
    cancel_time timestamp without time zone,
    complete_time timestamp without time zone,
    recipient character varying(1000) DEFAULT NULL::character varying,
    tx_hash character varying(200) DEFAULT NULL::character varying
);


--
-- Name: t_premium_platform_order_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_premium_platform_order_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_premium_platform_order_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_premium_platform_order_rid_seq OWNED BY public.t_premium_platform_order.rid;


--
-- Name: t_premium_platform_package; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_premium_platform_package (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    premium_platform_rid integer NOT NULL,
    package_name character varying(50) NOT NULL,
    package_month integer NOT NULL,
    usdt_price numeric(14,2) NOT NULL,
    callback_data character varying(50) NOT NULL,
    seq_sn integer DEFAULT 0 NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    show_notes character varying(1000) DEFAULT NULL::character varying,
    package_pic character varying(200) DEFAULT NULL::character varying,
    comments character varying(255) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_premium_platform_package_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_premium_platform_package_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_premium_platform_package_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_premium_platform_package_rid_seq OWNED BY public.t_premium_platform_package.rid;


--
-- Name: t_premium_platform_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_premium_platform_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_premium_platform_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_premium_platform_rid_seq OWNED BY public.t_premium_platform.rid;


--
-- Name: t_premium_wallet_trade_list; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_premium_wallet_trade_list (
    rid integer NOT NULL,
    tx_hash character varying(100) NOT NULL,
    transferfrom_address character varying(200) NOT NULL,
    transferto_address character varying(200) NOT NULL,
    coin_name character varying(20) NOT NULL,
    amount character varying(100) NOT NULL,
    "timestamp" character varying(20) NOT NULL,
    process_status smallint DEFAULT '0'::smallint NOT NULL,
    process_time timestamp without time zone,
    process_comments character varying(255) DEFAULT NULL::character varying,
    get_time timestamp without time zone,
    tg_notice_status_receive character(1) DEFAULT 'N'::bpchar NOT NULL,
    tg_notice_status_send character(1) DEFAULT 'N'::bpchar NOT NULL,
    platform_order_rid integer,
    premium_package_rid integer,
    premium_platform_rid integer
);


--
-- Name: t_premium_wallet_trade_list_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_premium_wallet_trade_list_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_premium_wallet_trade_list_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_premium_wallet_trade_list_rid_seq OWNED BY public.t_premium_wallet_trade_list.rid;


--
-- Name: t_role_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


--
-- Name: t_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) DEFAULT 'web'::character varying NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


--
-- Name: t_roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_roles_id_seq OWNED BY public.t_roles.id;


--
-- Name: t_shop_goods; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_shop_goods (
    rid integer NOT NULL,
    goods_name character varying(50) NOT NULL,
    goods_type smallint NOT NULL,
    goods_usdt_price numeric(16,4) DEFAULT 0.0000 NOT NULL,
    goods_trx_price numeric(16,4) DEFAULT 0.0000 NOT NULL,
    show_notes character varying(1000) DEFAULT NULL::character varying,
    status smallint DEFAULT '1'::smallint NOT NULL,
    seq_sn integer DEFAULT 0 NOT NULL,
    comments character varying(3000) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_shop_goods_bot; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_shop_goods_bot (
    rid integer NOT NULL,
    goods_rid integer NOT NULL,
    bot_rid integer NOT NULL,
    goods_usdt_discount numeric(4,2) DEFAULT 1.00 NOT NULL,
    goods_trx_discount numeric(4,2) DEFAULT 1.00 NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    comments character varying(3000) DEFAULT NULL::character varying,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_shop_goods_bot_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_shop_goods_bot_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_shop_goods_bot_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_shop_goods_bot_rid_seq OWNED BY public.t_shop_goods_bot.rid;


--
-- Name: t_shop_goods_cdkey; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_shop_goods_cdkey (
    rid integer NOT NULL,
    goods_rid integer NOT NULL,
    cdkey_no character varying(100) NOT NULL,
    cdkey_pwd character varying(2000) NOT NULL,
    cdkey_usdt_price numeric(16,4) DEFAULT 0.0000 NOT NULL,
    cdkey_trx_price numeric(16,4) DEFAULT 0.0000 NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    seq_sn integer DEFAULT 0 NOT NULL,
    create_time timestamp without time zone NOT NULL,
    update_time timestamp without time zone
);


--
-- Name: t_shop_goods_cdkey_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_shop_goods_cdkey_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_shop_goods_cdkey_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_shop_goods_cdkey_rid_seq OWNED BY public.t_shop_goods_cdkey.rid;


--
-- Name: t_shop_goods_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_shop_goods_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_shop_goods_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_shop_goods_rid_seq OWNED BY public.t_shop_goods.rid;


--
-- Name: t_shop_order; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_shop_order (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    tg_uid character varying(50) NOT NULL,
    tg_username character varying(100) DEFAULT NULL::character varying,
    cdkey_no character varying(100) DEFAULT NULL::character varying,
    cdkey_pwd character varying(2000) DEFAULT NULL::character varying,
    pay_type smallint NOT NULL,
    pay_price character varying(50) DEFAULT NULL::character varying,
    comments character varying(3000) DEFAULT NULL::character varying,
    pay_time timestamp without time zone
);


--
-- Name: t_shop_order_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_shop_order_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_shop_order_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_shop_order_rid_seq OWNED BY public.t_shop_order.rid;


--
-- Name: t_sys_admin_opt_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_sys_admin_opt_log (
    rid integer NOT NULL,
    admin_id integer NOT NULL,
    opt_module character varying(200) DEFAULT NULL::character varying,
    opt_ref_sn character varying(100) DEFAULT NULL::character varying,
    opt_time timestamp without time zone,
    opt_content text
);


--
-- Name: t_sys_admin_opt_log_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_sys_admin_opt_log_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_sys_admin_opt_log_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_sys_admin_opt_log_rid_seq OWNED BY public.t_sys_admin_opt_log.rid;


--
-- Name: t_sys_config; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_sys_config (
    rid integer NOT NULL,
    config_key character varying(50) NOT NULL,
    config_val character varying(500) NOT NULL,
    comments character varying(255) NOT NULL,
    create_by character varying(50) DEFAULT NULL::character varying,
    create_time timestamp without time zone,
    update_by character varying(50) DEFAULT NULL::character varying,
    update_time timestamp without time zone
);


--
-- Name: t_sys_config_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_sys_config_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_sys_config_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_sys_config_rid_seq OWNED BY public.t_sys_config.rid;


--
-- Name: t_sys_data_dictionary; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_sys_data_dictionary (
    rid integer NOT NULL,
    dic_key character varying(100) NOT NULL,
    dic_value character varying(500) NOT NULL,
    dic_name character varying(200) DEFAULT NULL::character varying,
    create_time timestamp without time zone,
    update_time timestamp without time zone
);


--
-- Name: t_sys_data_dictionary_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_sys_data_dictionary_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_sys_data_dictionary_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_sys_data_dictionary_rid_seq OWNED BY public.t_sys_data_dictionary.rid;


--
-- Name: t_telegram_bot; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot (
    rid integer NOT NULL,
    bot_token character varying(100) NOT NULL,
    bot_admin_username character varying(100) DEFAULT '-'::character varying,
    bot_firstname character varying(100) DEFAULT NULL::character varying,
    bot_username character varying(100) DEFAULT NULL::character varying,
    comments character varying(255) DEFAULT NULL::character varying,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone,
    recharge_wallet_addr character varying(100) DEFAULT NULL::character varying,
    get_tx_time timestamp without time zone,
    last_update_id bigint DEFAULT 0
);


--
-- Name: t_telegram_bot_ad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_ad (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    notice_cycle smallint DEFAULT '1'::smallint NOT NULL,
    notice_obj character varying(200) NOT NULL,
    notice_photo character varying(500) DEFAULT NULL::character varying,
    notice_ad character varying(2000) NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    last_notice_time timestamp without time zone,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_telegram_bot_ad_keyboard; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_ad_keyboard (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    ad_rid integer NOT NULL,
    keyboard_rid integer NOT NULL,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_telegram_bot_ad_keyboard_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_ad_keyboard_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_ad_keyboard_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_ad_keyboard_rid_seq OWNED BY public.t_telegram_bot_ad_keyboard.rid;


--
-- Name: t_telegram_bot_ad_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_ad_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_ad_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_ad_rid_seq OWNED BY public.t_telegram_bot_ad.rid;


--
-- Name: t_telegram_bot_command; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_command (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    command character varying(50) NOT NULL,
    description character varying(100) NOT NULL,
    command_type smallint DEFAULT '1'::smallint NOT NULL,
    seq_sn integer DEFAULT 0 NOT NULL,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_telegram_bot_command_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_command_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_command_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_command_rid_seq OWNED BY public.t_telegram_bot_command.rid;


--
-- Name: t_telegram_bot_group; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_group (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    group_type character varying(50) NOT NULL,
    tg_groupid bigint NOT NULL,
    tg_groupusername character varying(200) DEFAULT NULL::character varying,
    tg_groupnickname character varying(200) DEFAULT NULL::character varying,
    status smallint DEFAULT 1 NOT NULL,
    is_admin character varying(1) DEFAULT 'N'::character varying,
    first_time timestamp without time zone,
    last_time timestamp without time zone,
    stop_time timestamp without time zone,
    follow_count integer DEFAULT 0
);


--
-- Name: t_telegram_bot_group_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_group_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_group_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_group_rid_seq OWNED BY public.t_telegram_bot_group.rid;


--
-- Name: t_telegram_bot_keyboard; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_keyboard (
    rid integer NOT NULL,
    keyboard_type smallint DEFAULT '1'::smallint NOT NULL,
    keyboard_name character varying(20) NOT NULL,
    inline_type smallint DEFAULT '0'::smallint NOT NULL,
    keyboard_value character varying(500) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone,
    seq_sn integer DEFAULT 0 NOT NULL
);


--
-- Name: t_telegram_bot_keyboard_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_keyboard_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_keyboard_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_keyboard_rid_seq OWNED BY public.t_telegram_bot_keyboard.rid;


--
-- Name: t_telegram_bot_keyreply; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_keyreply (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    key_type smallint NOT NULL,
    monitor_word character varying(500) NOT NULL,
    reply_photo character varying(300) DEFAULT NULL::character varying,
    reply_content character varying(2000) NOT NULL,
    opt_type integer NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_telegram_bot_keyreply_keyboard; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_keyreply_keyboard (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    keyreply_rid integer NOT NULL,
    keyboard_rid integer NOT NULL,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_telegram_bot_keyreply_keyboard_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_keyreply_keyboard_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_keyreply_keyboard_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_keyreply_keyboard_rid_seq OWNED BY public.t_telegram_bot_keyreply_keyboard.rid;


--
-- Name: t_telegram_bot_keyreply_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_keyreply_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_keyreply_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_keyreply_rid_seq OWNED BY public.t_telegram_bot_keyreply.rid;


--
-- Name: t_telegram_bot_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_rid_seq OWNED BY public.t_telegram_bot.rid;


--
-- Name: t_telegram_bot_user; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_telegram_bot_user (
    rid integer NOT NULL,
    bot_rid integer NOT NULL,
    tg_uid bigint NOT NULL,
    tg_username character varying(200) DEFAULT NULL::character varying,
    tg_nickname character varying(200) DEFAULT NULL::character varying,
    status smallint DEFAULT 1 NOT NULL,
    bind_trc_wallet_addr character varying(200) DEFAULT NULL::character varying,
    first_time timestamp without time zone,
    last_time timestamp without time zone,
    stop_time timestamp without time zone
);


--
-- Name: t_telegram_bot_user_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_telegram_bot_user_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_telegram_bot_user_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_telegram_bot_user_rid_seq OWNED BY public.t_telegram_bot_user.rid;


--
-- Name: t_transit_user_wallet; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_transit_user_wallet (
    rid integer NOT NULL,
    chain_type character varying(10) NOT NULL,
    wallet_addr character varying(200) NOT NULL,
    total_transit_usdt character varying(200) DEFAULT '0'::character varying NOT NULL,
    total_transit_sxf character varying(200) DEFAULT '0'::character varying NOT NULL,
    total_yuzhi_sxf character varying(200) DEFAULT '0'::character varying NOT NULL,
    need_feedback_sxf character varying(200) DEFAULT '0'::character varying NOT NULL,
    send_feedback_sxf character varying(200) DEFAULT '0'::character varying NOT NULL,
    last_transit_time timestamp without time zone,
    last_yuzhi_time timestamp without time zone
);


--
-- Name: t_transit_user_wallet_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_transit_user_wallet_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_transit_user_wallet_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_transit_user_wallet_rid_seq OWNED BY public.t_transit_user_wallet.rid;


--
-- Name: t_transit_wallet; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_transit_wallet (
    rid integer NOT NULL,
    chain_type character varying(10) NOT NULL,
    receive_wallet character varying(200) NOT NULL,
    send_wallet character varying(200) NOT NULL,
    send_wallet_privatekey character varying(2000) DEFAULT NULL::character varying,
    show_notes character varying(500) DEFAULT NULL::character varying,
    status smallint DEFAULT '1'::smallint NOT NULL,
    tg_notice_obj_receive character varying(200) DEFAULT NULL::character varying,
    tg_notice_obj_send character varying(200) DEFAULT NULL::character varying,
    get_tx_time timestamp without time zone,
    create_time timestamp without time zone,
    update_time timestamp without time zone,
    bot_rid integer,
    auto_stock_min_trx integer DEFAULT 0 NOT NULL,
    auto_stock_per_usdt integer DEFAULT 0 NOT NULL
);


--
-- Name: t_transit_wallet_black; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_transit_wallet_black (
    rid integer NOT NULL,
    chain_type character varying(10) NOT NULL,
    black_wallet character varying(200) NOT NULL,
    comments character varying(255) DEFAULT NULL::character varying,
    create_time timestamp without time zone,
    update_time timestamp without time zone
);


--
-- Name: t_transit_wallet_black_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_transit_wallet_black_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_transit_wallet_black_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_transit_wallet_black_rid_seq OWNED BY public.t_transit_wallet_black.rid;


--
-- Name: t_transit_wallet_coin; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_transit_wallet_coin (
    rid integer NOT NULL,
    transit_wallet_id integer NOT NULL,
    in_coin_name character varying(20) NOT NULL,
    out_coin_name character varying(20) NOT NULL,
    is_realtime_rate smallint DEFAULT '1'::smallint NOT NULL,
    profit_rate numeric(4,2) DEFAULT 1.00 NOT NULL,
    exchange_rate numeric(8,2) DEFAULT 0.00 NOT NULL,
    kou_out_amount numeric(8,2) DEFAULT 0.00 NOT NULL,
    min_transit_amount integer NOT NULL,
    max_transit_amount integer NOT NULL,
    comments character varying(255) DEFAULT NULL::character varying,
    create_by integer,
    create_time timestamp without time zone,
    update_by integer,
    update_time timestamp without time zone
);


--
-- Name: t_transit_wallet_coin_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_transit_wallet_coin_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_transit_wallet_coin_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_transit_wallet_coin_rid_seq OWNED BY public.t_transit_wallet_coin.rid;


--
-- Name: t_transit_wallet_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_transit_wallet_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_transit_wallet_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_transit_wallet_rid_seq OWNED BY public.t_transit_wallet.rid;


--
-- Name: t_transit_wallet_trade_list; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.t_transit_wallet_trade_list (
    rid integer NOT NULL,
    tx_hash character varying(100) NOT NULL,
    transferfrom_address character varying(200) NOT NULL,
    transferto_address character varying(200) NOT NULL,
    coin_name character varying(20) NOT NULL,
    amount character varying(100) NOT NULL,
    "timestamp" character varying(20) NOT NULL,
    process_status smallint DEFAULT '0'::smallint NOT NULL,
    process_time timestamp without time zone,
    process_comments character varying(255) DEFAULT NULL::character varying,
    get_time timestamp without time zone,
    sendback_address character varying(200) DEFAULT NULL::character varying,
    sendback_amount character varying(100) DEFAULT '0'::character varying NOT NULL,
    sendback_time timestamp without time zone,
    sendback_coin_name character varying(20) DEFAULT NULL::character varying,
    sendback_tx_hash character varying(100) DEFAULT NULL::character varying,
    sendback_contract_ret character varying(20) DEFAULT NULL::character varying,
    tg_notice_status_receive character(1) DEFAULT 'N'::bpchar NOT NULL,
    tg_notice_status_send character(1) DEFAULT 'N'::bpchar NOT NULL,
    current_exchange_rate numeric(8,2) DEFAULT 0.00 NOT NULL,
    current_huan_yuzhi_amount numeric(14,2) DEFAULT 0.00 NOT NULL
);


--
-- Name: t_transit_wallet_trade_list_rid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.t_transit_wallet_trade_list_rid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_transit_wallet_trade_list_rid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.t_transit_wallet_trade_list_rid_seq OWNED BY public.t_transit_wallet_trade_list.rid;


--
-- Name: t_admin id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_admin ALTER COLUMN id SET DEFAULT nextval('public.t_admin_id_seq'::regclass);


--
-- Name: t_admin_login_log rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_admin_login_log ALTER COLUMN rid SET DEFAULT nextval('public.t_admin_login_log_rid_seq'::regclass);


--
-- Name: t_collection_wallet rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_collection_wallet ALTER COLUMN rid SET DEFAULT nextval('public.t_collection_wallet_rid_seq'::regclass);


--
-- Name: t_collection_wallet_list rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_collection_wallet_list ALTER COLUMN rid SET DEFAULT nextval('public.t_collection_wallet_list_rid_seq'::regclass);


--
-- Name: t_energy_ai_bishu rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_ai_bishu ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_ai_bishu_rid_seq'::regclass);


--
-- Name: t_energy_ai_trusteeship rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_ai_trusteeship ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_ai_trusteeship_rid_seq'::regclass);


--
-- Name: t_energy_platform rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_platform_rid_seq'::regclass);


--
-- Name: t_energy_platform_bot rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform_bot ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_platform_bot_rid_seq'::regclass);


--
-- Name: t_energy_platform_order rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform_order ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_platform_order_rid_seq'::regclass);


--
-- Name: t_energy_platform_package rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform_package ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_platform_package_rid_seq'::regclass);


--
-- Name: t_energy_quick_order rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_quick_order ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_quick_order_rid_seq'::regclass);


--
-- Name: t_energy_special rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_special ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_special_rid_seq'::regclass);


--
-- Name: t_energy_special_list rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_special_list ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_special_list_rid_seq'::regclass);


--
-- Name: t_energy_third_part rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_third_part ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_third_part_rid_seq'::regclass);


--
-- Name: t_energy_wallet_trade_list rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_wallet_trade_list ALTER COLUMN rid SET DEFAULT nextval('public.t_energy_wallet_trade_list_rid_seq'::regclass);


--
-- Name: t_fms_recharge_order rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_fms_recharge_order ALTER COLUMN rid SET DEFAULT nextval('public.t_fms_recharge_order_rid_seq'::regclass);


--
-- Name: t_fms_wallet_trade_list rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_fms_wallet_trade_list ALTER COLUMN rid SET DEFAULT nextval('public.t_fms_wallet_trade_list_rid_seq'::regclass);


--
-- Name: t_monitor_bot rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_monitor_bot ALTER COLUMN rid SET DEFAULT nextval('public.t_monitor_bot_rid_seq'::regclass);


--
-- Name: t_monitor_wallet rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_monitor_wallet ALTER COLUMN rid SET DEFAULT nextval('public.t_monitor_wallet_rid_seq'::regclass);


--
-- Name: t_permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_permissions ALTER COLUMN id SET DEFAULT nextval('public.t_permissions_id_seq'::regclass);


--
-- Name: t_premium_platform rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_platform ALTER COLUMN rid SET DEFAULT nextval('public.t_premium_platform_rid_seq'::regclass);


--
-- Name: t_premium_platform_order rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_platform_order ALTER COLUMN rid SET DEFAULT nextval('public.t_premium_platform_order_rid_seq'::regclass);


--
-- Name: t_premium_platform_package rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_platform_package ALTER COLUMN rid SET DEFAULT nextval('public.t_premium_platform_package_rid_seq'::regclass);


--
-- Name: t_premium_wallet_trade_list rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_wallet_trade_list ALTER COLUMN rid SET DEFAULT nextval('public.t_premium_wallet_trade_list_rid_seq'::regclass);


--
-- Name: t_roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_roles ALTER COLUMN id SET DEFAULT nextval('public.t_roles_id_seq'::regclass);


--
-- Name: t_shop_goods rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_goods ALTER COLUMN rid SET DEFAULT nextval('public.t_shop_goods_rid_seq'::regclass);


--
-- Name: t_shop_goods_bot rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_goods_bot ALTER COLUMN rid SET DEFAULT nextval('public.t_shop_goods_bot_rid_seq'::regclass);


--
-- Name: t_shop_goods_cdkey rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_goods_cdkey ALTER COLUMN rid SET DEFAULT nextval('public.t_shop_goods_cdkey_rid_seq'::regclass);


--
-- Name: t_shop_order rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_order ALTER COLUMN rid SET DEFAULT nextval('public.t_shop_order_rid_seq'::regclass);


--
-- Name: t_sys_admin_opt_log rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_sys_admin_opt_log ALTER COLUMN rid SET DEFAULT nextval('public.t_sys_admin_opt_log_rid_seq'::regclass);


--
-- Name: t_sys_config rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_sys_config ALTER COLUMN rid SET DEFAULT nextval('public.t_sys_config_rid_seq'::regclass);


--
-- Name: t_sys_data_dictionary rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_sys_data_dictionary ALTER COLUMN rid SET DEFAULT nextval('public.t_sys_data_dictionary_rid_seq'::regclass);


--
-- Name: t_telegram_bot rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_rid_seq'::regclass);


--
-- Name: t_telegram_bot_ad rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_ad ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_ad_rid_seq'::regclass);


--
-- Name: t_telegram_bot_ad_keyboard rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_ad_keyboard ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_ad_keyboard_rid_seq'::regclass);


--
-- Name: t_telegram_bot_command rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_command ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_command_rid_seq'::regclass);


--
-- Name: t_telegram_bot_group rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_group ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_group_rid_seq'::regclass);


--
-- Name: t_telegram_bot_keyboard rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_keyboard ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_keyboard_rid_seq'::regclass);


--
-- Name: t_telegram_bot_keyreply rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_keyreply ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_keyreply_rid_seq'::regclass);


--
-- Name: t_telegram_bot_keyreply_keyboard rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_keyreply_keyboard ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_keyreply_keyboard_rid_seq'::regclass);


--
-- Name: t_telegram_bot_user rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_user ALTER COLUMN rid SET DEFAULT nextval('public.t_telegram_bot_user_rid_seq'::regclass);


--
-- Name: t_transit_user_wallet rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_user_wallet ALTER COLUMN rid SET DEFAULT nextval('public.t_transit_user_wallet_rid_seq'::regclass);


--
-- Name: t_transit_wallet rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet ALTER COLUMN rid SET DEFAULT nextval('public.t_transit_wallet_rid_seq'::regclass);


--
-- Name: t_transit_wallet_black rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet_black ALTER COLUMN rid SET DEFAULT nextval('public.t_transit_wallet_black_rid_seq'::regclass);


--
-- Name: t_transit_wallet_coin rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet_coin ALTER COLUMN rid SET DEFAULT nextval('public.t_transit_wallet_coin_rid_seq'::regclass);


--
-- Name: t_transit_wallet_trade_list rid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet_trade_list ALTER COLUMN rid SET DEFAULT nextval('public.t_transit_wallet_trade_list_rid_seq'::regclass);


--
-- Data for Name: t_admin; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_admin VALUES (1, 'trxadmin', '$2y$10$t6LrogWBLp5LQ7i2KWOrDuNSIEyhVt5YpMJdBiWrLQBZ0vVGBm4v.', '', 1, NULL, NULL, '2023-12-02 16:28:30', '');


--
-- Data for Name: t_admin_login_log; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_admin_login_log VALUES (1, 'trxadmin', '192.168.65.1', '2026-02-22 16:26:19');
INSERT INTO public.t_admin_login_log VALUES (2, 'trxadmin', '192.168.65.1', '2026-02-22 16:26:50');


--
-- Data for Name: t_collection_wallet; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_collection_wallet_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_ai_bishu; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_ai_trusteeship; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_platform; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_energy_platform VALUES (1, 'A', 3, 1, 'TBwHofgkkkttttttttttt', 0, 233694.000000, 0.000000, NULL, '6666666', 1, 98, '2023-11-25 14:23:00', '', '2023-11-25 13:06:13', '2023-11-30 17:11:46');
INSERT INTO public.t_energy_platform VALUES (2, 'A', 2, 1, '-', 0, 864.100000, 0.000000, NULL, '6666666', 1, 1, '2023-11-25 14:23:06', '', '2023-11-01 13:06:41', '2023-11-30 19:25:43');
INSERT INTO public.t_energy_platform VALUES (3, 'A', 1, 1, '3333', 0, 910.426948, 0.000000, NULL, '6666666', 1, 90, NULL, '', '2023-11-25 15:03:04', '2023-11-30 22:02:38');
INSERT INTO public.t_energy_platform VALUES (4, 'A', 4, 1, 'user', 0, 36.360000, 0.000000, NULL, '6666666', 1, 999, NULL, '', '2023-11-27 15:16:34', NULL);


--
-- Data for Name: t_energy_platform_bot; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_energy_platform_bot VALUES (3, 1, 'A', '6666666', 0, 'tttttttttttttttt', '2023-11-25 00:00:00', '6666666', '-111111', '', 'N', 6, 12, 1, '2023-11-25 02:06:35', '2023-12-02 11:36:36');


--
-- Data for Name: t_energy_platform_order; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_platform_package; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_energy_platform_package VALUES (1, 1, 1, '免费1笔|1小时(对方有U)', 32000, 0, 3.00, 0.50, 0, 99, NULL, '2023-08-16 22:53:45', NULL, '2024-02-25 16:45:59', 'energy_bc7edcf8e85bdffadac37e41f2feb369', '付款成功将获得<b>1笔</b>免费USDT转账手续费(60分钟内使用)\n能量数量: <b>32000</b>\n对比节省：<b>10.2559 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (2, 1, 1, '免费1笔|1小时(对方无U)', 65000, 0, 6.00, 1.00, 0, 98, NULL, '2023-08-16 23:27:14', NULL, '2024-02-25 16:46:23', 'energy_bc7edcf8e85bdffadac37e41f2feb364', '付款成功将获得<b>1笔/1小时</b>免费USDT转账手续费(1小时内使用，转给无USDT地址)\n能量数量: <b>65000</b>\n对比节省: <b>20.2677 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (3, 1, 1, '3笔|1小时(对方有U)', 96000, 0, 9.00, 1.50, 0, 97, NULL, '2023-08-16 23:31:54', NULL, '2024-02-25 16:47:32', 'energy_bc7edcf8e85bdffadac37e41f2feb362', '付款成功将获得<b>3</b>笔免费USDT转账手续费(1个小时内使用)\n能量数量: <b>96000</b>\n对比节省：<b>33 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (4, 1, 1, '4笔|1小时(对方有U)', 128000, 0, 12.00, 2.00, 0, 96, NULL, '2023-08-16 23:34:19', NULL, '2024-02-25 16:48:09', 'energy_bc7edcf8e85bdffadac37e41f2feb361', '付款成功将获得<b>4笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>128000</b>\n对比节省: <b>44 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (5, 1, 1, '5笔|1小时(对方有U)', 160000, 0, 15.00, 2.50, 0, 95, NULL, '2023-08-17 00:28:58', NULL, '2024-02-25 16:49:01', 'energy_2c1ec34ee36b27b35403e4f9f18ee5ff', '付款成功将获得<b>5笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>160000</b>\n对比节省: <b>55 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (6, 1, 1, '6笔|1小时(对方有U)', 192000, 0, 18.00, 3.00, 0, 94, NULL, '2023-08-19 00:42:04', NULL, '2024-02-25 16:49:35', 'energy_481639fd1b584be1e5dac45630e3f736', '付款成功将获得<b>6笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>192000</b>\n对比节省: <b>66 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (7, 1, 1, '7笔|1小时(对方有U)', 224000, 0, 21.00, 3.50, 0, 93, NULL, '2023-08-19 00:44:29', NULL, '2024-02-25 16:50:02', 'energy_bacf03cebf12a8ac5a428ee7fe90d7cb', '付款成功将获得<b>7笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>224000</b>\n对比节省：<b>77 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (8, 1, 1, '8笔|1小时(对方有U)', 256000, 0, 24.00, 4.00, 0, 92, NULL, '2023-08-19 00:45:57', NULL, '2024-02-25 16:50:33', 'energy_c70b1fbe610b9ef365181cb3c22804be', '付款成功将获得<b>8笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>256000</b>\n对比节省: <b>88 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (9, 1, 1, '9笔|1小时(对方有U)', 288000, 0, 27.00, 4.50, 0, 91, NULL, '2023-08-19 00:47:25', NULL, '2024-02-25 16:51:04', 'energy_d5e8ca3fb78447942819d507ca3e55f1', '付款成功将获得<b>9笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>288000</b>\n对比节省：<b>99 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (10, 1, 1, '10笔|1小时(对方有U)', 320000, 0, 30.00, 5.00, 0, 90, NULL, '2023-08-19 00:48:48', NULL, '2024-02-25 16:51:59', 'energy_fe4a5f5119c39f7089ee412749b080aa', '付款成功将获得<b>10笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>320000</b>\n对比节省: <b>110 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (11, 1, 1, '20笔|1小时(对方有U)', 640000, 0, 60.00, 10.00, 0, 89, NULL, '2023-08-19 00:52:47', NULL, '2024-02-25 16:53:45', 'energy_2e4d7f44481ee72fdd824058bdee5dea', '付款成功将获得<b>20笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>640000</b>\n对比节省: <b>240 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);
INSERT INTO public.t_energy_platform_package VALUES (12, 1, 1, '50笔|1小时(对方有U)', 1600000, 0, 150.00, 25.00, 0, 88, NULL, '2023-08-19 00:54:29', NULL, '2024-02-25 16:54:57', 'energy_f632ffe6a47ae7e778c897fda0c3ae0f', '付款成功将获得<b>50笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>320000</b>\n对比节省: <b>580 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL, 0.00);


--
-- Data for Name: t_energy_quick_order; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_special; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_special_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_third_part; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_energy_wallet_trade_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_fms_recharge_order; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_fms_wallet_trade_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_model_has_permissions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_model_has_roles; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_model_has_roles VALUES (1, 'App\\Models\\Admin\\Admin', 1);
INSERT INTO public.t_model_has_roles VALUES (1, 'App\Models\Admin\Admin', 1);


--
-- Data for Name: t_monitor_bot; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_monitor_bot VALUES (2, 1, 0, 5.0000, 8.0000, 13.0000, 24.0000, 38.0000, 56.0000, '', '2023-11-25 19:54:54', '2023-11-25 23:44:54');


--
-- Data for Name: t_monitor_wallet; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_permissions; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_permissions VALUES (5, '主页', 'web', '2020-09-22 08:33:50', '2020-09-22 08:33:50', '', 0);
INSERT INTO public.t_permissions VALUES (6, '主页列表', 'web', '2020-09-22 08:37:02', '2020-09-22 08:37:02', 'admin.home', 5);
INSERT INTO public.t_permissions VALUES (7, '系统管理', 'web', '2020-09-22 08:37:37', '2020-09-22 08:37:37', '', 0);
INSERT INTO public.t_permissions VALUES (8, '管理员管理', 'web', '2020-09-22 08:38:14', '2020-09-22 08:38:14', 'admin.system.admin.index', 7);
INSERT INTO public.t_permissions VALUES (9, '权限管理', 'web', '2020-09-22 08:38:23', '2020-09-22 08:38:23', 'admin.system.permission.index', 7);
INSERT INTO public.t_permissions VALUES (10, '角色管理', 'web', '2020-09-22 08:38:34', '2020-09-22 08:38:34', 'admin.system.role.index', 7);
INSERT INTO public.t_permissions VALUES (19, '系统设置', 'web', '2021-07-09 05:42:29', '2021-07-09 05:42:29', '', 0);
INSERT INTO public.t_permissions VALUES (20, '配置信息', 'web', '2021-07-09 05:42:47', '2021-07-09 05:42:47', 'admin.setting.config.index', 19);
INSERT INTO public.t_permissions VALUES (11, '添加管理员', 'web', '2020-09-22 08:39:33', '2020-09-22 08:39:33', 'admin.system.admin.add', 8);
INSERT INTO public.t_permissions VALUES (12, '修改管理员状态', 'web', '2020-09-22 09:23:59', '2020-09-22 09:23:59', 'admin.system.admin.change_status', 8);
INSERT INTO public.t_permissions VALUES (13, '修改管理员资料', 'web', '2020-09-22 09:24:12', '2020-09-22 09:24:12', 'admin.system.admin.update', 8);
INSERT INTO public.t_permissions VALUES (14, '删除管理员', 'web', '2020-09-22 09:24:21', '2020-09-22 09:24:21', 'admin.system.admin.delete', 8);
INSERT INTO public.t_permissions VALUES (15, '添加角色', 'web', '2020-09-22 09:25:14', '2020-09-22 09:25:14', 'admin.system.role.add', 10);
INSERT INTO public.t_permissions VALUES (16, '编辑角色权限', 'web', '2020-09-22 09:25:23', '2020-09-22 09:25:23', 'admin.system.role.show_permissions', 10);
INSERT INTO public.t_permissions VALUES (17, '修改角色名称', 'web', '2020-09-22 09:25:30', '2020-09-22 09:25:30', 'admin.system.role.update', 10);
INSERT INTO public.t_permissions VALUES (18, '删除角色', 'web', '2020-09-22 09:25:38', '2020-09-22 09:25:38', 'admin.system.role.del', 10);
INSERT INTO public.t_permissions VALUES (21, '搜索查询_配置信息', 'web', '2021-07-09 05:42:55', '2021-07-09 05:42:55', 'admin.search', 20);
INSERT INTO public.t_permissions VALUES (22, '数据字典', 'web', '2021-07-09 05:43:06', '2021-07-09 05:43:06', 'admin.setting.dictionary.index', 19);
INSERT INTO public.t_permissions VALUES (23, '添加数据字典', 'web', '2021-07-09 05:43:27', '2021-07-09 05:43:27', 'admin.setting.dictionary.store', 22);
INSERT INTO public.t_permissions VALUES (24, '编辑数据字典', 'web', '2021-07-09 05:43:38', '2021-07-09 05:43:38', 'admin.setting.dictionary.update', 22);
INSERT INTO public.t_permissions VALUES (25, '删除数据字典', 'web', '2021-07-09 05:43:49', '2021-07-09 05:43:49', 'admin.setting.dictionary.delete', 22);
INSERT INTO public.t_permissions VALUES (26, '搜索查询_数据字典', 'web', '2021-07-09 05:43:56', '2021-07-09 05:43:56', 'admin.search', 22);
INSERT INTO public.t_permissions VALUES (27, '应用升级', 'web', '2021-07-09 05:44:09', '2021-07-09 05:44:09', 'admin.setting.app_version.index', 19);
INSERT INTO public.t_permissions VALUES (28, '升级应用升级', 'web', '2021-07-09 05:44:25', '2021-07-09 05:44:25', 'admin.setting.app_version.store', 27);
INSERT INTO public.t_permissions VALUES (29, '编辑应用升级', 'web', '2021-07-09 05:44:36', '2021-07-09 05:44:36', 'admin.setting.app_version.edit', 27);
INSERT INTO public.t_permissions VALUES (30, '搜索查询_应用升级', 'web', '2021-07-09 05:44:53', '2021-07-09 05:44:53', 'admin.search', 27);
INSERT INTO public.t_permissions VALUES (31, '机器人管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (32, '群组用户', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (33, '闪兑管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (34, '能量管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (35, '会员管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (36, '监控管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (37, '归集管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);
INSERT INTO public.t_permissions VALUES (38, '商城管理', 'web', '2026-02-22 06:44:53.057733', '2026-02-22 06:44:53.057733', '', 0);


--
-- Data for Name: t_premium_platform; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_premium_platform VALUES (1, 1, 1, '6666666', '23deweww', NULL, NULL, 0, 'ttttttttttttttt', '2023-11-21 00:00:00', '6666666', '-1111111', '', '2023-11-21 23:45:17', NULL);


--
-- Data for Name: t_premium_platform_order; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_premium_platform_package; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_premium_platform_package VALUES (1, 1, 1, '3个月 价格 15 USDT', 3, 15.00, 'premium_aee6034741a76c9c947844f196fc58e3', 1, 0, '', NULL, NULL, '2023-11-21 23:46:14', NULL);
INSERT INTO public.t_premium_platform_package VALUES (2, 1, 1, '6个月 价格 25 USDT', 6, 25.00, 'premium_310fe79585da99e1b3edd1393ff6a36a', 2, 0, '', NULL, NULL, '2023-11-21 23:46:31', NULL);
INSERT INTO public.t_premium_platform_package VALUES (3, 1, 1, '12个月 价格 45 USDT', 12, 45.00, 'premium_a21a27e202cdc317de8466ef7250a6f1', 3, 0, '', NULL, NULL, '2023-11-21 23:46:44', NULL);


--
-- Data for Name: t_premium_wallet_trade_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_role_has_permissions; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_role_has_permissions VALUES (5, 1);
INSERT INTO public.t_role_has_permissions VALUES (6, 1);
INSERT INTO public.t_role_has_permissions VALUES (7, 1);
INSERT INTO public.t_role_has_permissions VALUES (8, 1);
INSERT INTO public.t_role_has_permissions VALUES (9, 1);
INSERT INTO public.t_role_has_permissions VALUES (10, 1);
INSERT INTO public.t_role_has_permissions VALUES (19, 1);
INSERT INTO public.t_role_has_permissions VALUES (20, 1);
INSERT INTO public.t_role_has_permissions VALUES (11, 1);
INSERT INTO public.t_role_has_permissions VALUES (12, 1);
INSERT INTO public.t_role_has_permissions VALUES (13, 1);
INSERT INTO public.t_role_has_permissions VALUES (14, 1);
INSERT INTO public.t_role_has_permissions VALUES (15, 1);
INSERT INTO public.t_role_has_permissions VALUES (16, 1);
INSERT INTO public.t_role_has_permissions VALUES (17, 1);
INSERT INTO public.t_role_has_permissions VALUES (18, 1);
INSERT INTO public.t_role_has_permissions VALUES (21, 1);
INSERT INTO public.t_role_has_permissions VALUES (22, 1);
INSERT INTO public.t_role_has_permissions VALUES (23, 1);
INSERT INTO public.t_role_has_permissions VALUES (24, 1);
INSERT INTO public.t_role_has_permissions VALUES (25, 1);
INSERT INTO public.t_role_has_permissions VALUES (26, 1);
INSERT INTO public.t_role_has_permissions VALUES (27, 1);
INSERT INTO public.t_role_has_permissions VALUES (28, 1);
INSERT INTO public.t_role_has_permissions VALUES (29, 1);
INSERT INTO public.t_role_has_permissions VALUES (30, 1);
INSERT INTO public.t_role_has_permissions VALUES (31, 1);
INSERT INTO public.t_role_has_permissions VALUES (32, 1);
INSERT INTO public.t_role_has_permissions VALUES (33, 1);
INSERT INTO public.t_role_has_permissions VALUES (34, 1);
INSERT INTO public.t_role_has_permissions VALUES (35, 1);
INSERT INTO public.t_role_has_permissions VALUES (36, 1);
INSERT INTO public.t_role_has_permissions VALUES (37, 1);
INSERT INTO public.t_role_has_permissions VALUES (38, 1);


--
-- Data for Name: t_roles; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_roles VALUES (1, '超级管理员', 'web', '2026-02-22 06:41:09.980014', '2026-02-22 06:41:09.980014');


--
-- Data for Name: t_shop_goods; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_shop_goods_bot; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_shop_goods_cdkey; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_shop_order; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_sys_admin_opt_log; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_sys_config; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_sys_config VALUES (1, 'job_url', '{"url":"http:\/\/tgbot-job:9503"}', '任务域名url', '1', '2022-05-05 12:55:54', '1', '2022-05-05 12:55:54');
INSERT INTO public.t_sys_config VALUES (2, 'ton_url', '{"url":"http:\/\/host.docker.internal:4444\/api\/premium"}', 'ton支付接口url(不需要开通tg会员,用不到这个接口)', '1', '2022-05-05 12:55:54', '1', '2022-05-05 12:55:54');
INSERT INTO public.t_sys_config VALUES (3, 'api_web_url', '{"url":"http:\/\/host.docker.internal:4444\/"}', 'API连接url', '1', '2022-05-05 12:55:54', '1', '2022-05-05 12:55:54');


--
-- Data for Name: t_sys_data_dictionary; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_telegram_bot; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot VALUES (1, '6666666:AAHOcqAPQuqtO3', '@aaaa', 'TRX 能量 会员 靓号 24小时营业', 'pri_bot', 'own-01', NULL, '2023-11-21 23:03:34', NULL, '2023-11-21 23:07:56', 'ttttttttt', '2023-11-21 00:00:00');


--
-- Data for Name: t_telegram_bot_ad; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot_ad VALUES (1, 1, 5, '-111111', NULL, '✅24小时兑换地址： <code>${trxusdtwallet}</code> (点击自动复制) \n\n实时汇率：\n10 USDT = ${trx10usdtrate} TRX\n100 USDT = ${trx100usdtrate} TRX\n1000 USDT = ${trx1000usdtrate} TRX\n\n❌请勿从交易所直接提现到机器人账户！！\n${trxusdtshownotes}\n✅只支持1 USDT及其以上的金额兑换，若转入1 USDT以下金额，将无法退还！！！\n✅另有波场靓号出售，选号咨询客服！\n\n联系客服：${tgbotadmin}\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 0, '2023-12-02 00:52:00', NULL, '2023-11-22 00:19:22', NULL, '2023-11-22 00:52:14');


--
-- Data for Name: t_telegram_bot_ad_keyboard; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot_ad_keyboard VALUES (1, 1, 1, 5, NULL, '2023-11-22 00:49:14', NULL, NULL);
INSERT INTO public.t_telegram_bot_ad_keyboard VALUES (2, 1, 1, 6, NULL, '2023-11-22 00:49:14', NULL, NULL);
INSERT INTO public.t_telegram_bot_ad_keyboard VALUES (3, 1, 1, 7, NULL, '2023-11-22 00:49:14', NULL, NULL);
INSERT INTO public.t_telegram_bot_ad_keyboard VALUES (4, 1, 1, 8, NULL, '2023-11-22 00:49:14', NULL, NULL);


--
-- Data for Name: t_telegram_bot_command; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot_command VALUES (1, 1, 'start', '开始使用', 1, 99, NULL, '2023-11-21 23:20:47', NULL, NULL);
INSERT INTO public.t_telegram_bot_command VALUES (2, 1, 'trx', 'USDT兑换TRX', 1, 98, NULL, '2023-11-21 23:21:38', NULL, NULL);
INSERT INTO public.t_telegram_bot_command VALUES (3, 1, 'buyenergy', '租用能量', 1, 97, NULL, '2023-11-21 23:22:03', NULL, NULL);
INSERT INTO public.t_telegram_bot_command VALUES (4, 1, 'buypremium', '购买会员', 2, 96, NULL, '2023-11-21 23:22:46', NULL, NULL);
INSERT INTO public.t_telegram_bot_command VALUES (5, 1, 'z0', '查询欧意价格', 1, 95, NULL, '2023-11-21 23:23:06', NULL, NULL);


--
-- Data for Name: t_telegram_bot_group; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_telegram_bot_keyboard; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot_keyboard VALUES (1, 1, '🙎联系客服', 0, '-', 0, NULL, '2023-11-21 23:25:10', NULL, '2023-11-27 18:22:34', 80);
INSERT INTO public.t_telegram_bot_keyboard VALUES (2, 1, '💹闪兑TRX', 0, '-', 0, NULL, '2023-11-21 23:25:49', NULL, '2024-01-10 18:40:44', 98);
INSERT INTO public.t_telegram_bot_keyboard VALUES (3, 1, '🔋闪租能量', 0, '-', 0, NULL, '2023-11-21 23:26:09', NULL, '2024-01-10 17:40:02', 97);
INSERT INTO public.t_telegram_bot_keyboard VALUES (4, 1, '👑购买会员', 0, '-', 0, NULL, '2023-11-21 23:26:22', NULL, '2023-11-23 23:33:18', 96);
INSERT INTO public.t_telegram_bot_keyboard VALUES (5, 2, '💎TRX兑换', 2, '兑换', 0, NULL, '2023-11-22 00:47:13', NULL, NULL, 99);
INSERT INTO public.t_telegram_bot_keyboard VALUES (6, 2, '🔋购买能量', 2, '购买能量', 0, NULL, '2023-11-22 00:47:43', NULL, NULL, 98);
INSERT INTO public.t_telegram_bot_keyboard VALUES (7, 2, '🛎开通会员', 1, 'https://t.me/aaaa', 0, NULL, '2023-11-22 00:48:27', NULL, '2023-11-22 00:51:10', 97);
INSERT INTO public.t_telegram_bot_keyboard VALUES (8, 2, '👳‍♀️联系老板', 1, 'https://t.me/aa', 0, NULL, '2023-11-22 00:49:06', NULL, '2023-11-27 18:20:31', 92);
INSERT INTO public.t_telegram_bot_keyboard VALUES (9, 1, '⚡️我要充值', 0, '-', 1, NULL, '2023-11-23 23:23:48', NULL, '2023-11-23 23:31:46', 94);
INSERT INTO public.t_telegram_bot_keyboard VALUES (10, 1, '👁钱包监控', 0, '-', 0, NULL, '2023-11-25 18:10:30', NULL, NULL, 93);
INSERT INTO public.t_telegram_bot_keyboard VALUES (11, 1, '🔠购买靓号', 0, '-', 0, NULL, '2023-11-26 19:23:39', NULL, '2023-11-27 18:21:59', 95);
INSERT INTO public.t_telegram_bot_keyboard VALUES (12, 1, '👨‍💼个人中心', 0, '-', 0, NULL, '2023-11-26 23:22:34', NULL, NULL, 91);
INSERT INTO public.t_telegram_bot_keyboard VALUES (13, 1, '🏧欧意汇率', 0, '-', 1, NULL, '2023-11-26 23:56:58', NULL, NULL, 90);
INSERT INTO public.t_telegram_bot_keyboard VALUES (14, 1, '🖌笔数套餐', 0, '-', 0, NULL, '2024-01-10 17:31:56', NULL, '2024-01-10 17:39:12', 97);
INSERT INTO public.t_telegram_bot_keyboard VALUES (15, 1, '❇️智能托管', 0, '-', 0, NULL, '2024-01-10 18:31:25', NULL, NULL, 97);


--
-- Data for Name: t_telegram_bot_keyreply; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot_keyreply VALUES (1, 1, 2, '-', '', '24小时自动兑换地址:\n <code>${trxusdtwallet}</code>(点击自动复制) \n\n✅进U即兑,全自动返TRX,1U起兑,24小时全自动\n${trxusdtshownotes}\n❌请勿使用交易所或中心化钱包转账\n✅如有老板需要用交易所转账,提前联系群老板:  ${tgbotadmin}\n✅24小时兑换机器人：${tgbotname}\n\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 1, 0, NULL, '2023-11-21 23:28:35', NULL, '2023-12-02 20:17:01');
INSERT INTO public.t_telegram_bot_keyreply VALUES (2, 1, 1, '💹闪兑TRX,闪兑TRX,USDT兑TRX,兑换,USDT,usdt,TRX,trx,闪兑,地址,换trx,换TRX,开始,jusdt,/trx,TRX兑换,eth兑换,预支点TRX,ETH,eth,btc,bnb,汇率,价格,1,2,3,4,5,6,7,10,100,50,20,30,200,100,帮助,菜单,help,menu,/start', '', '➖➖➖➖➖➖➖➖➖➖➖➖\n24小时自动兑换地址:\n <code>${trxusdtwallet}</code> (点击自动复制) \n➖➖➖➖➖➖➖➖➖➖➖➖\n当前汇率：\n1 USDT = ${trxusdtrate} TRX\n10 USDT = ${trx10usdtrate} TRX\n100 USDT = ${trx100usdtrate} TRX\n1000 USDT = ${trx1000usdtrate} TRX\n➖➖➖➖➖➖➖➖➖➖➖➖\n✅进U即兑,全自动返TRX,1U起兑\n${trxusdtshownotes}\n❌请勿使用交易所或中心化钱包转账\n✅如有老板需要用交易所转账,提前联系群老板:  ${tgbotadmin}\n➖➖➖➖➖➖➖➖➖➖➖➖\n✅24小时兑换机器人：${tgbotname}\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 1, 0, NULL, '2023-11-21 23:29:56', NULL, '2024-01-10 18:57:39');
INSERT INTO public.t_telegram_bot_keyreply VALUES (3, 1, 1, '联系客服,客服', '', '✅联系老板：${tgbotadmin}\n✅24小时兑换机器人：${tgbotname}\n\n✅USDT兑换TRX：/trx\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 1, 0, NULL, '2023-11-21 23:33:13', NULL, '2023-11-23 23:34:17');
INSERT INTO public.t_telegram_bot_keyreply VALUES (4, 1, 1, '转账手续费', '', 'TRX转账：268 带宽\r\nUSDT转账：\r\n对方有U：345 带宽、31895 能量\r\n对方无U：345 带宽、64895 能量\r\n\r\n燃烧价值\r\n1 TRX = 1000 带宽\r\n13.3959 TRX = 31895 能量\r\n27.2559 TRX = 64895 能量', 1, 0, NULL, '2023-11-21 23:33:43', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply VALUES (5, 1, 1, '开通会员,购买会员,续费会员,/buypremium', '', '🔐<b>开通/续费 Telegram Premium会员</b>\n\n\n<b>根据下列菜单，选择开通会员月份，可多次重复购买</b>', 4, 0, NULL, '2023-11-21 23:34:14', NULL, '2023-11-21 23:39:59');
INSERT INTO public.t_telegram_bot_keyreply VALUES (6, 1, 1, '能量,购买能量,租用能量,代理能量,买能量,/buyenergy,🔋租用能量,🔋闪租能量,闪租能量', '', '🔋租用能量，转账无需TRX消耗，0手续费！\n\n以下数据以USDT单笔转账为例\n波场实时消耗：31895 能量（对方地址有U）\n官方手续费：≈ 13.3959 TRX\n租用能量最低费用：= <b>4.14 TRX</b>\n最高节约手续费：9.2559 TRX\n节省手续费最高约 80%\n\n<b>注意：如果对方地址没U，转账一笔需要 64895 能量</b>\n<b>根据下列菜单，选择适合自己的套餐，可多次重复购买</b>', 3, 0, NULL, '2023-11-21 23:34:48', NULL, '2024-01-10 18:57:57');
INSERT INTO public.t_telegram_bot_keyreply VALUES (7, 1, 1, '查ID,查id', '', '-', 2, 0, NULL, '2023-11-21 23:41:12', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply VALUES (8, 1, 1, '⚡️我要充值', '', '请在下方选择您要充值的币种', 5, 0, NULL, '2023-11-23 23:24:17', NULL, '2023-11-23 23:31:39');
INSERT INTO public.t_telegram_bot_keyreply VALUES (9, 1, 1, '钱包监控', '', '监控波场TRC链，USDT,TRX,授权,多签,代理能量', 6, 0, NULL, '2023-11-25 18:12:30', NULL, '2023-11-25 18:17:18');
INSERT INTO public.t_telegram_bot_keyreply VALUES (10, 1, 1, '购买靓号', '', '请选择分类，然后再选择您心仪的商品\n使用<b>TRX余额</b>或者<b>USDT余额</b>支付,可点击下方充值', 7, 0, NULL, '2023-11-26 19:24:25', NULL, '2023-11-26 22:28:10');
INSERT INTO public.t_telegram_bot_keyreply VALUES (11, 1, 1, '个人中心,👨‍💼个人中心', '', '欢迎使用本机器人，您可以使用本机器人功能：\n✅U闪兑TRX：/trx\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 8, 0, NULL, '2023-11-26 23:22:52', NULL, '2023-11-26 23:26:46');
INSERT INTO public.t_telegram_bot_keyreply VALUES (12, 1, 1, '🏧欧意汇率,欧意汇率,z0,Z0,z1,Z1,z2,Z2,z3,Z3,/z0', '', '-', 9, 0, NULL, '2023-11-26 23:57:24', NULL, '2023-11-26 23:58:09');
INSERT INTO public.t_telegram_bot_keyreply VALUES (13, 1, 1, '笔数套餐,🖌笔数套餐', '', '--', 10, 0, NULL, '2024-01-10 18:54:10', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply VALUES (14, 1, 1, '❇️智能托管,智能托管', '', '--', 11, 0, NULL, '2024-01-10 18:54:26', NULL, NULL);


--
-- Data for Name: t_telegram_bot_keyreply_keyboard; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (571, 1, 1, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (572, 1, 1, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (573, 1, 1, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (574, 1, 1, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (575, 1, 1, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (576, 1, 1, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (577, 1, 1, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (578, 1, 1, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (579, 1, 1, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (580, 1, 1, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (581, 1, 1, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (586, 1, 2, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (587, 1, 2, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (588, 1, 2, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (589, 1, 2, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (590, 1, 2, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (591, 1, 2, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (592, 1, 2, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (593, 1, 2, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (594, 1, 2, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (595, 1, 2, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (596, 1, 2, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (601, 1, 3, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (602, 1, 3, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (603, 1, 3, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (604, 1, 3, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (605, 1, 3, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (606, 1, 3, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (607, 1, 3, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (608, 1, 3, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (609, 1, 3, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (610, 1, 3, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (611, 1, 3, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (616, 1, 4, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (617, 1, 4, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (618, 1, 4, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (619, 1, 4, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (620, 1, 4, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (621, 1, 4, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (622, 1, 4, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (623, 1, 4, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (624, 1, 4, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (625, 1, 4, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (626, 1, 4, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (631, 1, 5, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (632, 1, 5, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (633, 1, 5, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (634, 1, 5, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (635, 1, 5, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (636, 1, 5, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (637, 1, 5, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (638, 1, 5, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (639, 1, 5, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (640, 1, 5, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (641, 1, 5, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (646, 1, 6, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (647, 1, 6, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (648, 1, 6, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (649, 1, 6, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (650, 1, 6, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (651, 1, 6, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (652, 1, 6, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (653, 1, 6, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (654, 1, 6, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (655, 1, 6, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (656, 1, 6, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (661, 1, 7, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (662, 1, 7, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (663, 1, 7, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (664, 1, 7, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (665, 1, 7, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (666, 1, 7, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (667, 1, 7, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (668, 1, 7, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (669, 1, 7, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (670, 1, 7, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (671, 1, 7, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (676, 1, 8, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (677, 1, 8, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (678, 1, 8, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (679, 1, 8, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (680, 1, 8, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (681, 1, 8, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (682, 1, 8, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (683, 1, 8, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (684, 1, 8, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (685, 1, 8, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (686, 1, 8, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (691, 1, 9, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (692, 1, 9, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (693, 1, 9, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (694, 1, 9, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (695, 1, 9, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (696, 1, 9, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (697, 1, 9, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (698, 1, 9, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (699, 1, 9, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (700, 1, 9, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (701, 1, 9, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (706, 1, 10, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (707, 1, 10, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (708, 1, 10, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (709, 1, 10, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (710, 1, 10, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (711, 1, 10, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (712, 1, 10, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (713, 1, 10, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (714, 1, 10, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (715, 1, 10, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (716, 1, 10, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (721, 1, 11, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (722, 1, 11, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (723, 1, 11, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (724, 1, 11, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (725, 1, 11, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (726, 1, 11, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (727, 1, 11, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (728, 1, 11, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (729, 1, 11, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (730, 1, 11, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (731, 1, 11, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (736, 1, 12, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (737, 1, 12, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (738, 1, 12, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (739, 1, 12, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (740, 1, 12, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (741, 1, 12, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (742, 1, 12, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (743, 1, 12, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (744, 1, 12, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (745, 1, 12, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (746, 1, 12, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (751, 1, 13, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (752, 1, 13, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (753, 1, 13, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (754, 1, 13, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (755, 1, 13, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (756, 1, 13, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (757, 1, 13, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (758, 1, 13, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (759, 1, 13, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (760, 1, 13, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (761, 1, 13, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (766, 1, 14, 1, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (767, 1, 14, 2, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (768, 1, 14, 3, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (769, 1, 14, 4, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (770, 1, 14, 9, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (771, 1, 14, 10, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (772, 1, 14, 11, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (773, 1, 14, 12, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (774, 1, 14, 13, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (775, 1, 14, 14, NULL, '2024-01-10 18:54:34', NULL, NULL);
INSERT INTO public.t_telegram_bot_keyreply_keyboard VALUES (776, 1, 14, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);


--
-- Data for Name: t_telegram_bot_user; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_transit_user_wallet; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: t_transit_wallet; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_transit_wallet VALUES (1, 'trc', 'tttttt', 'tttttt', NULL, '✅请认准靓号 TWxm1pW 开头 8个U 结尾', 0, '6666666', '-111111', '2023-11-21 00:00:00', '2023-11-21 23:37:38', '2023-11-22 00:18:53', 1, 0, 0);


--
-- Data for Name: t_transit_wallet_black; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_transit_wallet_black VALUES (4, 'trc', 'TWd4WrZ9wn84f5x1hZhL4DHvk738ns5jwb', '币安', NULL, NULL);
INSERT INTO public.t_transit_wallet_black VALUES (5, 'trc', 'TMuA6YqfCeX8EhbfYEg5y7S4DqzSJireY9', '币安', '2023-02-11 13:29:31', '2023-02-11 13:29:42');
INSERT INTO public.t_transit_wallet_black VALUES (6, 'trc', 'TT1DyeqXaaJkt6UhVYFWUXBXknaXnBudTK', '币安', '2023-02-11 13:30:08', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (7, 'trc', 'TJCo98saj6WND61g1uuKwJ9GMWMT9WkJFo', '币安', '2023-02-11 13:30:36', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (8, 'trc', 'TV6MuMXfmLbBqPZvBHdwFsDnQeVfnmiuSi', '币安', '2023-02-11 13:30:51', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (9, 'trc', 'TDToUxX8sH4z6moQpK3ZLAN24eupu2ivA4', NULL, '2023-02-11 13:31:07', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (10, 'trc', 'TRYL7PKCG4b4xRCM554Q5J6o8f1UjUmfnY', 'Kucoin-Cold', '2023-02-11 13:31:27', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (11, 'trc', 'TB1WQmj63bHV9Qmuhp39WABzutphMAetSc', NULL, '2023-02-11 13:31:41', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (12, 'trc', 'TNiq9AXBp9EjUqhDhrwrfvAA8U3GUQZH81', NULL, '2023-02-11 13:31:49', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (13, 'trc', 'TKHuVq1oKVruCGLvqVexFs6dawKv6fQgFs', NULL, '2023-02-11 13:32:02', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (14, 'trc', 'TMmhxjhqPbUwgzfV3eV94T398Qk1khE32v', NULL, '2023-02-11 13:32:11', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (15, 'trc', 'TMhJviFWiaxvqKLdng9dmsi1H5H5yTGEeu', NULL, '2023-02-11 13:32:24', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (16, 'trc', 'TTd9qHyjqiUkfTxe3gotbuTMpjU8LEbpkN', 'Kraken', '2023-02-11 13:32:40', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (17, 'trc', 'TTiDLWE6fZK8okMJv6ijg42yrH6W2pjSr9', NULL, '2023-02-11 13:32:50', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (18, 'trc', 'TJYM8UnYvZ8iM5PjuHTYsDYXhY1YZBeKeX', NULL, '2023-02-11 13:32:57', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (19, 'trc', 'TQeNNo5zVarhdKm5EiJSekfNXg6H1tRN4n', NULL, '2023-02-11 13:33:04', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (20, 'trc', 'TJbHp48Shg4tTD5x6fKkU7PodggL5mjcJP', NULL, '2023-02-11 13:33:12', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (21, 'trc', 'TWGZbjofbTLY3UCjCV4yiLkRg89zLqwRgi', NULL, '2023-02-11 13:33:19', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (22, 'trc', 'TM1zzNDZD2DPASbKcgdVoTYhfmYgtfwx9R', 'okx', '2023-02-11 13:33:35', '2023-02-11 13:34:05');
INSERT INTO public.t_transit_wallet_black VALUES (23, 'trc', 'TBA6CypYJizwA9XdC7Ubgc5F1bxrQ7SqPt', 'gate', '2023-02-11 13:34:32', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (24, 'trc', 'TNaRAoLUyYEV2uF7GUrzSjRQTU8v5ZJ5VR', 'huobi', '2023-02-11 13:34:49', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (25, 'trc', 'TNXoiAJ3dct8Fjg4M9fkLFh9S2v9TXc32G', NULL, '2023-02-11 13:35:09', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (26, 'trc', 'TJDENsfBJs4RFETt1X1W8wMDc8M5XnJhCe', NULL, '2023-02-11 13:35:23', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (27, 'trc', 'TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS', NULL, '2023-02-11 13:35:37', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (29, 'trc', 'TPF3KHqPbCFQL2UDrHr4LuHoYU9XPfzYLo', NULL, '2023-04-30 19:25:22', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (30, 'trc', 'TKk9y2F5oFnnjSiH53fUhvRC55joFwS8c9', NULL, '2023-05-23 13:18:09', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (31, 'trc', 'TX3xNEmn9S5c77qeNnQhsfgcrbgqYo9Xcc', NULL, '2023-10-06 15:34:50', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (32, 'trc', 'TCz47XgC9TjCeF4UzfB6qZbM9LTF9s1tG7', '欧意', '2023-10-06 20:14:42', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (33, 'trc', 'TAzsQ9Gx8eqFNFSKbeXrbi45CuVPHzA8wr', NULL, '2023-10-09 00:14:18', NULL);
INSERT INTO public.t_transit_wallet_black VALUES (34, 'trc', 'TSaRZDiBPD8Rd5vrvX8a4zgunHczM9mj8S', NULL, '2023-10-09 00:15:05', NULL);


--
-- Data for Name: t_transit_wallet_coin; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.t_transit_wallet_coin VALUES (1, 1, 'usdt', 'trx', 3, 0.10, 8.76, 0.00, 1, 200, NULL, NULL, '2023-11-21 23:38:02', NULL, '2023-11-22 00:53:41');


--
-- Data for Name: t_transit_wallet_trade_list; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Name: t_admin_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_admin_id_seq', 1, false);


--
-- Name: t_admin_login_log_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_admin_login_log_rid_seq', 2, true);


--
-- Name: t_collection_wallet_list_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_collection_wallet_list_rid_seq', 1, false);


--
-- Name: t_collection_wallet_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_collection_wallet_rid_seq', 1, false);


--
-- Name: t_energy_ai_bishu_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_ai_bishu_rid_seq', 1, false);


--
-- Name: t_energy_ai_trusteeship_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_ai_trusteeship_rid_seq', 1, false);


--
-- Name: t_energy_platform_bot_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_platform_bot_rid_seq', 1, false);


--
-- Name: t_energy_platform_order_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_platform_order_rid_seq', 1, false);


--
-- Name: t_energy_platform_package_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_platform_package_rid_seq', 12, true);


--
-- Name: t_energy_platform_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_platform_rid_seq', 4, true);


--
-- Name: t_energy_quick_order_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_quick_order_rid_seq', 1, false);


--
-- Name: t_energy_special_list_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_special_list_rid_seq', 1, false);


--
-- Name: t_energy_special_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_special_rid_seq', 1, false);


--
-- Name: t_energy_third_part_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_third_part_rid_seq', 1, false);


--
-- Name: t_energy_wallet_trade_list_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_energy_wallet_trade_list_rid_seq', 1, false);


--
-- Name: t_fms_recharge_order_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_fms_recharge_order_rid_seq', 1, false);


--
-- Name: t_fms_wallet_trade_list_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_fms_wallet_trade_list_rid_seq', 1, false);


--
-- Name: t_monitor_bot_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_monitor_bot_rid_seq', 1, false);


--
-- Name: t_monitor_wallet_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_monitor_wallet_rid_seq', 1, false);


--
-- Name: t_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_permissions_id_seq', 1, false);


--
-- Name: t_premium_platform_order_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_premium_platform_order_rid_seq', 1, false);


--
-- Name: t_premium_platform_package_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_premium_platform_package_rid_seq', 1, false);


--
-- Name: t_premium_platform_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_premium_platform_rid_seq', 1, false);


--
-- Name: t_premium_wallet_trade_list_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_premium_wallet_trade_list_rid_seq', 1, false);


--
-- Name: t_roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_roles_id_seq', 1, false);


--
-- Name: t_shop_goods_bot_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_shop_goods_bot_rid_seq', 1, false);


--
-- Name: t_shop_goods_cdkey_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_shop_goods_cdkey_rid_seq', 1, false);


--
-- Name: t_shop_goods_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_shop_goods_rid_seq', 1, false);


--
-- Name: t_shop_order_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_shop_order_rid_seq', 1, false);


--
-- Name: t_sys_admin_opt_log_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_sys_admin_opt_log_rid_seq', 1, false);


--
-- Name: t_sys_config_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_sys_config_rid_seq', 3, true);


--
-- Name: t_sys_data_dictionary_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_sys_data_dictionary_rid_seq', 1, false);


--
-- Name: t_telegram_bot_ad_keyboard_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_ad_keyboard_rid_seq', 1, false);


--
-- Name: t_telegram_bot_ad_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_ad_rid_seq', 1, false);


--
-- Name: t_telegram_bot_command_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_command_rid_seq', 1, false);


--
-- Name: t_telegram_bot_group_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_group_rid_seq', 1, false);


--
-- Name: t_telegram_bot_keyboard_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_keyboard_rid_seq', 1, false);


--
-- Name: t_telegram_bot_keyreply_keyboard_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_keyreply_keyboard_rid_seq', 1, false);


--
-- Name: t_telegram_bot_keyreply_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_keyreply_rid_seq', 1, false);


--
-- Name: t_telegram_bot_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_rid_seq', 1, false);


--
-- Name: t_telegram_bot_user_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_telegram_bot_user_rid_seq', 1, false);


--
-- Name: t_transit_user_wallet_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_transit_user_wallet_rid_seq', 1, false);


--
-- Name: t_transit_wallet_black_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_transit_wallet_black_rid_seq', 1, false);


--
-- Name: t_transit_wallet_coin_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_transit_wallet_coin_rid_seq', 1, false);


--
-- Name: t_transit_wallet_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_transit_wallet_rid_seq', 1, false);


--
-- Name: t_transit_wallet_trade_list_rid_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.t_transit_wallet_trade_list_rid_seq', 1, false);


--
-- Name: t_admin_login_log t_admin_login_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_admin_login_log
    ADD CONSTRAINT t_admin_login_log_pkey PRIMARY KEY (rid);


--
-- Name: t_admin t_admin_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_admin
    ADD CONSTRAINT t_admin_pkey PRIMARY KEY (id);


--
-- Name: t_collection_wallet_list t_collection_wallet_list_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_collection_wallet_list
    ADD CONSTRAINT t_collection_wallet_list_pkey PRIMARY KEY (rid);


--
-- Name: t_collection_wallet t_collection_wallet_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_collection_wallet
    ADD CONSTRAINT t_collection_wallet_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_ai_bishu t_energy_ai_bishu_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_ai_bishu
    ADD CONSTRAINT t_energy_ai_bishu_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_ai_trusteeship t_energy_ai_trusteeship_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_ai_trusteeship
    ADD CONSTRAINT t_energy_ai_trusteeship_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_platform_bot t_energy_platform_bot_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform_bot
    ADD CONSTRAINT t_energy_platform_bot_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_platform_order t_energy_platform_order_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform_order
    ADD CONSTRAINT t_energy_platform_order_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_platform_package t_energy_platform_package_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform_package
    ADD CONSTRAINT t_energy_platform_package_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_platform t_energy_platform_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_platform
    ADD CONSTRAINT t_energy_platform_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_quick_order t_energy_quick_order_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_quick_order
    ADD CONSTRAINT t_energy_quick_order_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_special_list t_energy_special_list_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_special_list
    ADD CONSTRAINT t_energy_special_list_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_special t_energy_special_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_special
    ADD CONSTRAINT t_energy_special_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_third_part t_energy_third_part_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_third_part
    ADD CONSTRAINT t_energy_third_part_pkey PRIMARY KEY (rid);


--
-- Name: t_energy_wallet_trade_list t_energy_wallet_trade_list_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_energy_wallet_trade_list
    ADD CONSTRAINT t_energy_wallet_trade_list_pkey PRIMARY KEY (rid);


--
-- Name: t_fms_recharge_order t_fms_recharge_order_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_fms_recharge_order
    ADD CONSTRAINT t_fms_recharge_order_pkey PRIMARY KEY (rid);


--
-- Name: t_fms_wallet_trade_list t_fms_wallet_trade_list_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_fms_wallet_trade_list
    ADD CONSTRAINT t_fms_wallet_trade_list_pkey PRIMARY KEY (rid);


--
-- Name: t_model_has_permissions t_model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_model_has_permissions
    ADD CONSTRAINT t_model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: t_model_has_roles t_model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_model_has_roles
    ADD CONSTRAINT t_model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: t_monitor_bot t_monitor_bot_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_monitor_bot
    ADD CONSTRAINT t_monitor_bot_pkey PRIMARY KEY (rid);


--
-- Name: t_monitor_wallet t_monitor_wallet_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_monitor_wallet
    ADD CONSTRAINT t_monitor_wallet_pkey PRIMARY KEY (rid);


--
-- Name: t_permissions t_permissions_name_guard_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_permissions
    ADD CONSTRAINT t_permissions_name_guard_name_key UNIQUE (name, guard_name);


--
-- Name: t_permissions t_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_permissions
    ADD CONSTRAINT t_permissions_pkey PRIMARY KEY (id);


--
-- Name: t_premium_platform_order t_premium_platform_order_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_platform_order
    ADD CONSTRAINT t_premium_platform_order_pkey PRIMARY KEY (rid);


--
-- Name: t_premium_platform_package t_premium_platform_package_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_platform_package
    ADD CONSTRAINT t_premium_platform_package_pkey PRIMARY KEY (rid);


--
-- Name: t_premium_platform t_premium_platform_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_platform
    ADD CONSTRAINT t_premium_platform_pkey PRIMARY KEY (rid);


--
-- Name: t_premium_wallet_trade_list t_premium_wallet_trade_list_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_premium_wallet_trade_list
    ADD CONSTRAINT t_premium_wallet_trade_list_pkey PRIMARY KEY (rid);


--
-- Name: t_role_has_permissions t_role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_role_has_permissions
    ADD CONSTRAINT t_role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: t_roles t_roles_name_guard_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_roles
    ADD CONSTRAINT t_roles_name_guard_name_key UNIQUE (name, guard_name);


--
-- Name: t_roles t_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_roles
    ADD CONSTRAINT t_roles_pkey PRIMARY KEY (id);


--
-- Name: t_shop_goods_bot t_shop_goods_bot_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_goods_bot
    ADD CONSTRAINT t_shop_goods_bot_pkey PRIMARY KEY (rid);


--
-- Name: t_shop_goods_cdkey t_shop_goods_cdkey_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_goods_cdkey
    ADD CONSTRAINT t_shop_goods_cdkey_pkey PRIMARY KEY (rid);


--
-- Name: t_shop_goods t_shop_goods_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_goods
    ADD CONSTRAINT t_shop_goods_pkey PRIMARY KEY (rid);


--
-- Name: t_shop_order t_shop_order_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_shop_order
    ADD CONSTRAINT t_shop_order_pkey PRIMARY KEY (rid);


--
-- Name: t_sys_admin_opt_log t_sys_admin_opt_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_sys_admin_opt_log
    ADD CONSTRAINT t_sys_admin_opt_log_pkey PRIMARY KEY (rid);


--
-- Name: t_sys_config t_sys_config_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_sys_config
    ADD CONSTRAINT t_sys_config_pkey PRIMARY KEY (rid);


--
-- Name: t_sys_data_dictionary t_sys_data_dictionary_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_sys_data_dictionary
    ADD CONSTRAINT t_sys_data_dictionary_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_ad_keyboard t_telegram_bot_ad_keyboard_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_ad_keyboard
    ADD CONSTRAINT t_telegram_bot_ad_keyboard_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_ad t_telegram_bot_ad_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_ad
    ADD CONSTRAINT t_telegram_bot_ad_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_command t_telegram_bot_command_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_command
    ADD CONSTRAINT t_telegram_bot_command_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_group t_telegram_bot_group_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_group
    ADD CONSTRAINT t_telegram_bot_group_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_keyboard t_telegram_bot_keyboard_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_keyboard
    ADD CONSTRAINT t_telegram_bot_keyboard_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_keyreply_keyboard t_telegram_bot_keyreply_keyboard_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_keyreply_keyboard
    ADD CONSTRAINT t_telegram_bot_keyreply_keyboard_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_keyreply t_telegram_bot_keyreply_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_keyreply
    ADD CONSTRAINT t_telegram_bot_keyreply_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot t_telegram_bot_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot
    ADD CONSTRAINT t_telegram_bot_pkey PRIMARY KEY (rid);


--
-- Name: t_telegram_bot_user t_telegram_bot_user_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_telegram_bot_user
    ADD CONSTRAINT t_telegram_bot_user_pkey PRIMARY KEY (rid);


--
-- Name: t_transit_user_wallet t_transit_user_wallet_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_user_wallet
    ADD CONSTRAINT t_transit_user_wallet_pkey PRIMARY KEY (rid);


--
-- Name: t_transit_wallet_black t_transit_wallet_black_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet_black
    ADD CONSTRAINT t_transit_wallet_black_pkey PRIMARY KEY (rid);


--
-- Name: t_transit_wallet_coin t_transit_wallet_coin_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet_coin
    ADD CONSTRAINT t_transit_wallet_coin_pkey PRIMARY KEY (rid);


--
-- Name: t_transit_wallet t_transit_wallet_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet
    ADD CONSTRAINT t_transit_wallet_pkey PRIMARY KEY (rid);


--
-- Name: t_transit_wallet_trade_list t_transit_wallet_trade_list_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.t_transit_wallet_trade_list
    ADD CONSTRAINT t_transit_wallet_trade_list_pkey PRIMARY KEY (rid);


--
-- Name: idx_energy_third_part_1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_energy_third_part_1 ON public.t_energy_third_part USING btree (tg_uid);


--
-- PostgreSQL database dump complete
--

\unrestrict 8ju2b8gnQVtGC2EErNdMWUaKlKS6g46lbaOrvSPadhKlauUJmy9xgQjdNZ70n4J

