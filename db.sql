system_express_keywordSET FOREIGN_KEY_CHECKS=0;


-- ----------------------------
-- Table structure for `partner_withdraw`
-- ----------------------------
DROP TABLE IF EXISTS `partner_withdraw`;
CREATE TABLE `partner_withdraw` (
  `id` mediumint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `real_money` double(8,2) NOT NULL DEFAULT '0.00' COMMENT '扣除手续费后的钱',
  `ids` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '订单id集合',
  `apply_money` double(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '提现申请金额',
  `remark` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 待审批 1 审批通过 2拒绝',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL COMMENT 'update_time',
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of partner_withdraw
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_after_info`
-- ----------------------------
DROP TABLE IF EXISTS `shop_after_info`;
CREATE TABLE `shop_after_info` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `after_phone` varchar(25) DEFAULT '0' COMMENT '退款电话',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '省',
  `province_code` int(8) NOT NULL COMMENT '省行政区代码',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '市',
  `city_code` int(8) NOT NULL COMMENT '市行政区代码',
  `area` varchar(20) NOT NULL DEFAULT '' COMMENT '区',
  `area_code` int(8) NOT NULL COMMENT '区、县行政区代码',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '具体地址',
  `after_addr` varchar(256) DEFAULT NULL COMMENT '退款地址',
  `store_address` varchar(255) NOT NULL DEFAULT '' COMMENT '门店地址',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0=无效 1=有效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='商户退款信息表';


-- ----------------------------
-- Table structure for `shop_agent_user`
-- ----------------------------
DROP TABLE IF EXISTS `shop_agent_user`;
CREATE TABLE `shop_agent_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '代理商名称（代理商等级）',
  `background_color` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '背景色',
  `icon` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '图标',
  `background_image` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '背景图',
  `fan_number` int(11) NOT NULL DEFAULT '0' COMMENT '直推粉丝数',
  `secondhand_fan_number` int(11) NOT NULL DEFAULT '0' COMMENT '非直推粉丝数',
  `fan_number_buy` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '直推粉丝消费额',
  `self_buy` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '自购省钱',
  `first_back` decimal(5,2) NOT NULL COMMENT '一级分佣',
  `second_back` decimal(5,2) NOT NULL COMMENT '二级分佣',
  `third_back` decimal(5,2) NOT NULL COMMENT '三级级分佣',
  `remark` text CHARACTER SET utf8 COMMENT '权益说明',
  `status` tinyint(1) NOT NULL COMMENT '0 关闭 1 开启',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='代理商';

-- ----------------------------
-- Table structure for `shop_assemble`
-- ----------------------------
DROP TABLE IF EXISTS `shop_assemble`;
CREATE TABLE `shop_assemble` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `supplier_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `is_self` tinyint(1) NOT NULL DEFAULT '0' COMMENT '单独购买 0=不开启 1=开启',
  `older_with_newer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '老带新(团长必须已经购买过，团员必须没有购买过的新客) 1=开启 0=不开启',
  `is_automatic` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否自动拼成(最后5分钟如没满团自动虚拟成团) 1=开启 0=不开启',
  `is_leader_discount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否团长优惠(开团的人非团购的团长) 1=开启 0=不开启',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=多人团 2=阶梯团',
  `number` int(5) NOT NULL DEFAULT '2' COMMENT '拼团人数(阶梯团取最大阶梯人数)，最大99999',
  `property` text NOT NULL COMMENT '属性信息（json：2人 => 属性1 property1_name 属性2 property2_name 拼团价格 price 团长的优惠价 tuan_price)',
  `min_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '拼团的最低价格',
  `group_price_discount` varchar(255) NOT NULL DEFAULT '' COMMENT '阶梯团时候使用拼团折扣率',
  `is_show` tinyint(1) DEFAULT '0' COMMENT '是否显示 1=显示 0=不显示',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品-拼团';

-- ----------------------------
-- Table structure for `shop_assemble_access`
-- ----------------------------
DROP TABLE IF EXISTS `shop_assemble_access`;
CREATE TABLE `shop_assemble_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `uid` int(10) NOT NULL COMMENT '用户uid',
  `leader_id` int(10) NOT NULL DEFAULT '0' COMMENT 'pid 如果是团长 则=0',
  `order_sn` varchar(24) NOT NULL DEFAULT '' COMMENT '订单编号',
  `is_leader` tinyint(1) NOT NULL COMMENT '是否团长(开团的人非团购的团长) 1=是 0=否',
  `type` tinyint(1) NOT NULL COMMENT '1=多人团 2=阶梯团',
  `expire_time` int(10) NOT NULL COMMENT '到期时间(最迟成团时间，开团时间24小时内),如开启虚拟成团，则自动成团，否则按订单号退款',
  `number` int(5) NOT NULL COMMENT '拼团人数(满人数则成团)',
  `price` decimal(10,2) NOT NULL COMMENT '拼团的价格 如果开启团长优惠且是团长，则团长优惠价，否则普通团员价格',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品-拼团记录表';

-- ----------------------------
-- Records of shop_assemble_access
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_assemble_record`
-- ----------------------------
DROP TABLE IF EXISTS `shop_assemble_record`;
CREATE TABLE `shop_assemble_record` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `key` varchar(8) NOT NULL,
  `merchant_id` int(8) NOT NULL COMMENT '商家ID',
  `supplier_id` int(9) NOT NULL DEFAULT '0',
  `goods_id` int(8) NOT NULL COMMENT '商品ID',
  `name` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '商品名称',
  `status` int(8) NOT NULL DEFAULT '0' COMMENT '状态 0关闭 1开启',
  `time` varchar(12) NOT NULL COMMENT '状态更改时间',
  `create_time` varchar(12) NOT NULL,
  `update_time` varchar(12) NOT NULL,
  `delete_time` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;



-- ----------------------------
-- Table structure for `shop_attribute`
-- ----------------------------
DROP TABLE IF EXISTS `shop_attribute`;
CREATE TABLE `shop_attribute` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '类目名称',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `category_id` int(10) NOT NULL COMMENT '类目id',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首页显示 1显示 0隐藏',
  `sort` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='属性表';

-- ----------------------------
-- Records of shop_attribute
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `shop_auth_group`;
CREATE TABLE `shop_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `title` char(100) NOT NULL DEFAULT '' COMMENT '权限组名称',
  `rules` text COMMENT '权限规则',
  `is_kefu` tinyint(1) DEFAULT '0' COMMENT '是否客服 0不是 1是',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1可用0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=535 DEFAULT CHARSET=utf8 COMMENT='电商权限组表';


-- ----------------------------
-- Table structure for `shop_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `shop_auth_group_access`;
CREATE TABLE `shop_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `group_ids` varchar(52) NOT NULL DEFAULT '' COMMENT '权限组ids',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  UNIQUE KEY `uid_group_id` (`uid`,`group_ids`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_ids`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限对应表';


-- ----------------------------
-- Table structure for `shop_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `shop_auth_rule`;
CREATE TABLE `shop_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父id',
  `name` char(80) NOT NULL COMMENT '权限名称',
  `title` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '权限标题',
  `rule_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0操作 1页面 2模块',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1可用0禁用',
  `condition` char(100) NOT NULL COMMENT '触发条件',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `menu_url` varchar(50) DEFAULT NULL COMMENT '菜单路由地址',
  `menu_name` varchar(50) DEFAULT NULL COMMENT '菜单名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of shop_auth_rule
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_balance_ratio`
-- ----------------------------
DROP TABLE IF EXISTS `shop_balance_ratio`;
CREATE TABLE `shop_balance_ratio` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `money` decimal(8,2) NOT NULL COMMENT '充值金额',
  `remain_money` decimal(8,2) DEFAULT '0.00' COMMENT '到账金额',
  `remarks` varchar(128) NOT NULL COMMENT '备注',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0=通用 1=微信 2=支付宝 3=银行卡',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1=正常,0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='电商-充值配置表';


-- ----------------------------
-- Table structure for `shop_banner`
-- ----------------------------
DROP TABLE IF EXISTS `shop_banner`;
CREATE TABLE `shop_banner` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(50) NOT NULL COMMENT '横幅名称',
  `pic_url` varchar(255) NOT NULL COMMENT '横幅图片',
  `jump_url` varchar(255) NOT NULL COMMENT '跳转链接',
  `type` tinyint(1) NOT NULL COMMENT '类型 1微信端 2小程序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='商户-横幅表';

-- ----------------------------
-- Records of shop_banner
-- ----------------------------

INSERT INTO `shop_banner` VALUES ('29', 'ccvWPn', '13', '1', 'https://imgs.juanpao.com/shop%2Fbanner%2F2019%2F07%2F16%2F15632607535d2d775108543.jpeg', '1', '1', '1', '1563260753', null, null);

-- ----------------------------
-- Table structure for `shop_bargain_info`
-- ----------------------------
DROP TABLE IF EXISTS `shop_bargain_info`;
CREATE TABLE `shop_bargain_info` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `supplier_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `stock_id` int(11) NOT NULL DEFAULT '0',
  `goods_price` int(11) NOT NULL COMMENT '当前商品价格',
  `price` int(11) NOT NULL COMMENT '砍的价格',
  `user_id` int(8) NOT NULL COMMENT '砍价 人',
  `is_promoter` int(1) NOT NULL DEFAULT '0' COMMENT '是否发起者 0否 1是',
  `promoter_sn` varchar(20) NOT NULL,
  `promoter_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '发起人id',
  `end_time` varchar(20) NOT NULL COMMENT '结束时间',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0砍价结束 1砍价进行中',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='商品砍价记录表';

-- ----------------------------
-- Records of shop_bargain_info
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_bargain_record`
-- ----------------------------
DROP TABLE IF EXISTS `shop_bargain_record`;
CREATE TABLE `shop_bargain_record` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `supplier_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `is_bargain` tinyint(1) NOT NULL COMMENT '是否开启砍价 0不开启 1开启',
  `bargain_start_time` int(11) NOT NULL COMMENT '砍价活动开始时间',
  `bargain_end_time` int(11) NOT NULL COMMENT '砍价活动结束时间',
  `is_buy_alone` tinyint(1) NOT NULL COMMENT '是否支持单独购买 0不开启 1开启',
  `fictitious_initiate_bargain` int(10) NOT NULL COMMENT '虚拟发起砍价人数量',
  `fictitious_help_bargain` int(10) NOT NULL COMMENT '虚拟帮砍人数量',
  `bargain_price` decimal(10,2) NOT NULL COMMENT '砍价最低价（必须大于0）',
  `help_number` int(10) NOT NULL COMMENT '好友帮砍次数',
  `bargain_limit_time` int(11) NOT NULL COMMENT '砍价时间限制（小时）',
  `bargain_rule` text COMMENT '砍价规则',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='商品砍价活动记录表';

-- ----------------------------
-- Records of shop_bargain_record
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_cashback`
-- ----------------------------
DROP TABLE IF EXISTS `shop_cashback`;
CREATE TABLE `shop_cashback` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `key` varchar(8) NOT NULL,
  `goods_id` int(8) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of shop_cashback
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_category`
-- ----------------------------
DROP TABLE IF EXISTS `shop_category`;
CREATE TABLE `shop_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '类目名称',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `parent_id` int(11) DEFAULT '0' COMMENT '父类',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首页显示 1显示 0隐藏',
  `sort` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='商品类目表';

-- ----------------------------
-- Records of shop_category
-- ----------------------------
INSERT INTO `shop_category` VALUES ('16', '服装鞋包', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495565c7f5854304f3.png', '', '0', '0', '1', '1551255756', '1551849556', null);
INSERT INTO `shop_category` VALUES ('17', '海淘', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495615c7f58593238f.png', '', '0', '0', '1', '1551255779', '1551849561', null);
INSERT INTO `shop_category` VALUES ('18', '家居家纺', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15517483425c7dccf65c8dd.png', '', '0', '0', '1', '1551748342', null, null);
INSERT INTO `shop_category` VALUES ('19', '美妆洗护', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518347835c7f1e9fc3143.png', '', '0', '0', '1', '1551748363', '1551834784', null);
INSERT INTO `shop_category` VALUES ('20', '母婴玩具', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518348285c7f1ecc6b6b1.png', '', '0', '0', '1', '1551834828', null, null);
INSERT INTO `shop_category` VALUES ('21', '汽车美容', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518348955c7f1f0fad856.png', '', '0', '0', '1', '1551834895', null, null);
INSERT INTO `shop_category` VALUES ('22', '数码家电', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495655c7f585dd0eeb.png', '', '0', '0', '1', '1551834920', '1551849565', null);
INSERT INTO `shop_category` VALUES ('23', '保健品', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518349335c7f1f35cf777.png', '', '0', '0', '1', '1551834934', null, null);
INSERT INTO `shop_category` VALUES ('24', '珠宝饰品', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518349485c7f1f4412d1a.png', '', '0', '0', '1', '1551834948', null, null);
INSERT INTO `shop_category` VALUES ('25', '眼镜手表', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518349565c7f1f4c52be9.png', '', '0', '0', '1', '1551834956', null, null);
INSERT INTO `shop_category` VALUES ('26', '运动户外', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518349995c7f1f7795c74.png', '', '0', '0', '1', '1551834999', null, null);
INSERT INTO `shop_category` VALUES ('27', '鲜花礼品', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495355c7f583f14a3a.png', '', '0', '0', '1', '1551835082', '1551849535', null);
INSERT INTO `shop_category` VALUES ('28', '家居家纺', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495395c7f5843cf36d.png', '', '0', '0', '1', '1551835114', '1551849539', null);
INSERT INTO `shop_category` VALUES ('29', '办公文具', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518351315c7f1ffbd4370.png', '', '0', '0', '1', '1551835132', null, null);
INSERT INTO `shop_category` VALUES ('30', '装修建材', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495445c7f58489b515.png', '', '0', '0', '1', '1551835156', '1551849544', null);
INSERT INTO `shop_category` VALUES ('31', '水果蔬菜', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518495495c7f584d36415.png', '', '0', '0', '1', '1551835184', '1551849549', null);
INSERT INTO `shop_category` VALUES ('32', '日用百货', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518352535c7f20750d1ec.png', '', '0', '0', '1', '1551835253', null, null);
INSERT INTO `shop_category` VALUES ('33', '食品酒水', '', '0', '0', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/admin%2Fshop%2Fcategory%2F15518352695c7f208535153.png', '', '0', '0', '1', '1551835269', null, null);

-- ----------------------------
-- Table structure for `shop_dianwoda_account`
-- ----------------------------
DROP TABLE IF EXISTS `shop_dianwoda_account`;
CREATE TABLE `shop_dianwoda_account` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `appkey` char(50) NOT NULL COMMENT '账户appkey',
  `appsecret` char(50) NOT NULL COMMENT '账户appsecret',
  `accesstoken` char(50) NOT NULL COMMENT '账户accesstoken',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='商户点我达账号信息表';

-- ----------------------------
-- Records of shop_dianwoda_account
-- ----------------------------
INSERT INTO `shop_dianwoda_account` VALUES ('1', 'ccvWPn', '13', '', '', '', '1582981594', '1583139840', null);

-- ----------------------------
-- Table structure for `shop_dianwoda_order`
-- ----------------------------
DROP TABLE IF EXISTS `shop_dianwoda_order`;
CREATE TABLE `shop_dianwoda_order` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `order_sn` varchar(24) NOT NULL DEFAULT '' COMMENT '订单编码',
  `dwd_order_id` varchar(50) NOT NULL DEFAULT '' COMMENT '点我达订单编号',
  `total_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '预估运费',
  `price` decimal(8,2) DEFAULT '0.00' COMMENT '补贴金额（单位：分）',
  `skycon` varchar(50) DEFAULT NULL COMMENT '天气标签',
  `distance` int(11) DEFAULT NULL COMMENT '高德步行路径距离（单位：米）',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='商户点我达订单表';



-- ----------------------------
-- Table structure for `shop_distribution_access`
-- ----------------------------
DROP TABLE IF EXISTS `shop_distribution_access`;
CREATE TABLE `shop_distribution_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `uid` int(10) NOT NULL,
  `balance_sn` varchar(18) DEFAULT NULL COMMENT '提现单号',
  `order_sn` varchar(18) NOT NULL DEFAULT '' COMMENT '订单编号',
  `money` decimal(8,2) NOT NULL COMMENT '金额 正数增加 负数减少',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '佣金来源 1=下线提佣 2=股权分佣 3=自购提佣',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='分销会员佣金记录表';


-- ----------------------------
-- Table structure for `shop_diy_config`
-- ----------------------------
DROP TABLE IF EXISTS `shop_diy_config`;
CREATE TABLE `shop_diy_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `system_diy_config_id` int(11) NOT NULL COMMENT '应用id',
  `value` text NOT NULL COMMENT '值 html格式',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=数值 2=字符串 3=数组，目前只有2',
  `status` tinyint(1) NOT NULL COMMENT '状态 1可用 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COMMENT='商户自定义配置表';

INSERT INTO `shop_diy_config` VALUES ('19', 'ccvWPn', '13', '5', '<p>用户协议</p>', '2', '1', '1562837661', '1562837661', null);
INSERT INTO `shop_diy_config` VALUES ('20', 'ccvWPn', '13', '6', '<p>隐私政策</p>', '2', '1', '1562837673', '1562837673', null);
INSERT INTO `shop_diy_config` VALUES ('21', 'ccvWPn', '13', '7', '<p><span style=\"color: rgb(102, 102, 102); font-family: 思源字体, sans-serif; font-size: 14px; background-color: rgb(255, 255, 255);\">资质</span></p>', '2', '1', '1562837678', '1564726739', null);
INSERT INTO `shop_diy_config` VALUES ('23', 'ccvWPn', '13', '2', '<p>申请团长</p>', '2', '1', '1562942539', '1583191920', null);
INSERT INTO `shop_diy_config` VALUES ('25', 'ccvWPn', '13', '1', '<p>申请门店</p>', '2', '1', '1563348998', '1583191931', null);



-- ----------------------------
-- Table structure for `shop_diy_express_template`
-- ----------------------------
DROP TABLE IF EXISTS `shop_diy_express_template`;
CREATE TABLE `shop_diy_express_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `system_express_template_id` int(10) NOT NULL COMMENT '系统模板id',
  `keywrod_info` text NOT NULL COMMENT '选中信息',
  `info` longtext NOT NULL COMMENT '设计信息(html)',
  `width` float(5,2) NOT NULL COMMENT '宽度',
  `height` float(5,2) NOT NULL COMMENT '高度',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `admin_remark` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='店铺diy快递模板表';

-- ----------------------------
-- Records of shop_diy_express_template
-- ----------------------------
INSERT INTO `shop_diy_express_template` VALUES ('1', '000546', '225', '3', '[{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"16\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"label\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]}]', '<div class=\"edit-box\" style=\"width: 3482px; height: 3510px;\">\n					<div class=\"item item-table\" data-name=\"name-table\" style=\"left: 0px; top: 113px; position: absolute; cursor: move; z-index: 0; width: 909.409px; height: 114px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"label\" style=\"border:1px solid black;padding:10px;\">标签</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19 item-active\" id=\"item-active\" data-name=\"19\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; font-size: 19px; z-index: 0; left: 349px; top: 25px; width: 240px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '999.99', '999.99', null, '1', null, '1566874463', '1566874861', null);
INSERT INTO `shop_diy_express_template` VALUES ('2', '000546', '225', '4', '[{\"id\":1,\"name\":\"商品信息\",\"type\":0,\"child\":[{\"id\":\"43\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"short_name\"},{\"id\":\"44\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"property\"},{\"id\":\"3\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"number\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"65\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"route\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"},{\"id\":\"18\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_name\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"}]}]', '<div class=\"edit-box\" style=\"width: 2482px; height: 3510px;\">\n					<div class=\"item 43\" id=\"item-active\" data-name=\"43\" data-englishname=\"short_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 45px; top: 12px; width: 369px; height: 42.5092px; font-size: 17px;\"><span>短标题:$short_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 44\" id=\"item-active\" data-name=\"44\" data-englishname=\"property\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 44px; top: 65px; font-size: 17px;\"><span>规格:$property</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 3\" id=\"item-active\" data-name=\"3\" data-englishname=\"number\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 330px; top: 69px; font-size: 17px;\"><span>数量:$number</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 108px; position: absolute; cursor: move; width: 901.818px; height: 124px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"route\" style=\"border:1px solid black;padding:10px;\">路线</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th><th data-englishname=\"leader_name\" style=\"border:1px solid black;padding:10px;\">团长姓名</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '999.99', '999.99', null, '1', null, '1566874870', '1566875164', null);
INSERT INTO `shop_diy_express_template` VALUES ('3', '000546', '225', '1', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"37\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_uid\"},{\"id\":\"35\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_name\"},{\"id\":\"34\",\"name\":\"团长昵称\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_nickname\"},{\"id\":\"36\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_phone\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"},{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"},{\"id\":\"40\",\"name\":\"配送方式\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"express_type\"},{\"id\":\"39\",\"name\":\"团长城市\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_city\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"16\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"label\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 982px;\">\n					<div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; height: 26px; z-index: 0; left: 0px; top: 21px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 43px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 62px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 37\" id=\"item-active\" data-name=\"37\" data-englishname=\"leader_uid\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 0px;\"><span>团长ID:$leader_uid</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 35\" id=\"item-active\" data-name=\"35\" data-englishname=\"leader_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 22px;\"><span>团长姓名:$leader_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 34\" id=\"item-active\" data-name=\"34\" data-englishname=\"leader_nickname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 42px;\"><span>团长昵称:$leader_nickname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 36\" id=\"item-active\" data-name=\"36\" data-englishname=\"leader_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 65px;\"><span>团长电话:$leader_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" data-englishname=\"leader_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 86px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" data-englishname=\"leader_area_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 91px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 40\" id=\"item-active\" data-name=\"40\" data-englishname=\"express_type\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 115px;\"><span>配送方式:$express_type</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 39\" id=\"item-active\" data-name=\"39\" data-englishname=\"leader_city\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 114px;\"><span>团长城市:$leader_city</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 143px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"label\" style=\"border:1px solid black;padding:10px;\">标签</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '980.00', null, '1', null, '1566875176', '1566875176', null);
INSERT INTO `shop_diy_express_template` VALUES ('4', 'jqXkVh', '108', '2', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"9\",\"name\":\"买家电话\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"phone\"},{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"},{\"id\":\"26\",\"name\":\"买家地址\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"address\"},{\"id\":\"31\",\"name\":\"买家留言\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"remark\"},{\"id\":\"29\",\"name\":\"实付金额\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"payment_money\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"40\",\"name\":\"配送方式\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"express_type\"},{\"id\":\"35\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_name\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"},{\"id\":\"36\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_phone\"},{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]}]', '<div class=\"edit-box\" style=\"width: 602px; height: 802px;\">\n					<div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 198px; top: 3px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 9\" id=\"item-active\" data-name=\"9\" data-englishname=\"phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 4px; top: 57px;\"><span>买家电话:$phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 6\" id=\"item-active\" data-name=\"6\" data-englishname=\"name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 5px; top: 34px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 26\" id=\"item-active\" data-name=\"26\" data-englishname=\"address\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 3px; top: 79px;\"><span>买家地址:$address</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 40\" id=\"item-active\" data-name=\"40\" data-englishname=\"express_type\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 398px; top: 73px;\"><span>配送方式:$express_type</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 31\" id=\"item-active\" data-name=\"31\" data-englishname=\"remark\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 2px; top: 104px;\"><span>买家留言:$remark</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 35\" id=\"item-active\" data-name=\"35\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 398px; top: 27px;\"><span>团长姓名:$leader_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 397px; top: 118px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 36\" id=\"item-active\" data-name=\"36\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 398px; top: 50px;\"><span>团长电话:$leader_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 1px; top: 193px; position: absolute; cursor: move; width: 591px; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 29\" id=\"item-active\" data-name=\"29\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 1px; top: 127px;\"><span>实付金额:$payment_money</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 397px; top: 96px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '600.00', '800.00', null, '1', null, '1566886237', '1569051787', null);
INSERT INTO `shop_diy_express_template` VALUES ('5', 'jqXkVh', '108', '5', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"},{\"id\":\"9\",\"name\":\"买家电话\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"phone\"},{\"id\":\"31\",\"name\":\"买家留言\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"remark\"},{\"id\":\"29\",\"name\":\"实付金额\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"payment_money\"},{\"id\":\"26\",\"name\":\"买家地址\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"address\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"35\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_name\"},{\"id\":\"36\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_phone\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"},{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]}]', '<div class=\"edit-box\" style=\"width: 602px; height: 682px;\">\n					<div class=\"item 19\" id=\"item-active\" data-name=\"19\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 215px; top: 10px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 6\" id=\"item-active\" data-name=\"6\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 16px; top: 54px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 9\" id=\"item-active\" data-name=\"9\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 17px; top: 73px;\"><span>买家电话:$phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 31\" id=\"item-active\" data-name=\"31\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 17px; top: 113px;\"><span>买家留言:$remark</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 29\" id=\"item-active\" data-name=\"29\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 17px; top: 132px;\"><span>实付金额:$payment_money</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 26\" id=\"item-active\" data-name=\"26\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 17px; top: 93px;\"><span>买家地址:$address</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 35\" id=\"item-active\" data-name=\"35\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 266px; top: 53px;\"><span>团长姓名:$leader_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 36\" id=\"item-active\" data-name=\"36\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 267px; top: 93px;\"><span>团长电话:$leader_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 267px; top: 113px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 266px; top: 73px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 3px; top: 157px; position: absolute; cursor: move; width: 595px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '600.00', '680.00', null, '1', null, '1566887706', '1566906796', null);
INSERT INTO `shop_diy_express_template` VALUES ('6', 'jqXkVh', '108', '4', '[{\"id\":1,\"name\":\"商品信息\",\"type\":0,\"child\":[{\"id\":\"45\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_id\"},{\"id\":\"46\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_code\"},{\"id\":\"1\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goodsname\"},{\"id\":\"43\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"short_name\"},{\"id\":\"42\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"label\"},{\"id\":\"44\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"property\"},{\"id\":\"3\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"number\"},{\"id\":\"8\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"price\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"65\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"route\"},{\"id\":\"61\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_uid\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"},{\"id\":\"18\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_name\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"}]}]', '<div class=\"edit-box\" style=\"width: 602px; height: 682px;\">\n					<div class=\"item 45\" id=\"item-active\" data-name=\"45\" data-englishname=\"goods_id\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商品ID:$goods_id</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 46\" id=\"item-active\" data-name=\"46\" data-englishname=\"goods_code\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 52px;\"><span>货号:$goods_code</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 1\" id=\"item-active\" data-name=\"1\" data-englishname=\"goodsname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 175px; top: 0px;\"><span>商品名称:$goodsname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 43\" id=\"item-active\" data-name=\"43\" data-englishname=\"short_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 177px; top: 26px;\"><span>短标题:$short_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 42\" id=\"item-active\" data-name=\"42\" data-englishname=\"label\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 27px;\"><span>标签:$label</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 44\" id=\"item-active\" data-name=\"44\" data-englishname=\"property\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 176px; top: 54px;\"><span>规格:$property</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 3\" id=\"item-active\" data-name=\"3\" data-englishname=\"number\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 80px;\"><span>数量:$number</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 8\" id=\"item-active\" data-name=\"8\" data-englishname=\"price\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 80px;\"><span>单价:$price</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 169px; position: absolute; cursor: move; z-index: 0; width: 593px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"route\" style=\"border:1px solid black;padding:10px;\">路线</th><th data-englishname=\"leader_uid\" style=\"border:1px solid black;padding:10px;\">团长ID</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th><th data-englishname=\"leader_name\" style=\"border:1px solid black;padding:10px;\">团长姓名</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '600.00', '680.00', null, '1', null, '1566888072', '1566906103', null);
INSERT INTO `shop_diy_express_template` VALUES ('7', 'ccvWPn', '13', '2', '[{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"9\",\"name\":\"买家电话\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"phone\"},{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"},{\"id\":\"33\",\"name\":\"买家区域\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"buyer_area\"},{\"id\":\"26\",\"name\":\"买家地址\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"address\"},{\"id\":\"31\",\"name\":\"买家留言\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"remark\"},{\"id\":\"29\",\"name\":\"实付金额\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"payment_money\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]}]', '<div class=\"edit-box\" style=\"width: 752px; height: 796px;\">\n					<div class=\"item 9\" id=\"item-active\" data-name=\"9\" data-englishname=\"phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 433px; top: 66px; font-size: 16px;\"><span>买家电话:$phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 6\" id=\"item-active\" data-name=\"6\" data-englishname=\"name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 77px; top: 70px; font-size: 16px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 33\" id=\"item-active\" data-name=\"33\" data-englishname=\"buyer_area\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 71px; top: 123px; height: 26px; font-size: 16px;\"><span>买家区域:$buyer_area</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 26\" id=\"item-active\" data-name=\"26\" data-englishname=\"address\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 71px; top: 182px; font-size: 16px;\"><span>买家地址:$address</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 31\" id=\"item-active\" data-name=\"31\" data-englishname=\"remark\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 439px; top: 126px; height: 26px; font-size: 16px;\"><span>买家留言:$remark</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 29\" id=\"item-active\" data-name=\"29\" data-englishname=\"payment_money\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 444px; top: 181px; font-size: 16px;\"><span>实付金额:$payment_money</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 21px; top: 228px; position: absolute; cursor: move; z-index: 0; width: 709px; height: 94px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19\" id=\"item-active\" data-name=\"19\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 262px; top: 16px; font-family: 微软雅黑; font-size: 20px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '750.00', '794.00', null, '1', null, '1567145039', '1583558542', null);
INSERT INTO `shop_diy_express_template` VALUES ('8', '000613', '301', '5', '[]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					</div>', '378.00', '680.00', null, '1', null, '1567581994', '1567581994', null);
INSERT INTO `shop_diy_express_template` VALUES ('9', 'jqXkVh', '108', '3', '[{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"16\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"label\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item item-table\" data-name=\"name-table\" style=\"left: 0px; top: 48px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"label\" style=\"border:1px solid black;padding:10px;\">标签</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19 item-active\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 94px; top: 11px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '680.00', null, '1', null, '1567758662', '1567758662', null);
INSERT INTO `shop_diy_express_template` VALUES ('10', '000619', '308', '2', '[{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]}]', '<div class=\"edit-box\" style=\"width: 360px; height: 679px;\">\n					<div class=\"item 6\" id=\"item-active\" data-name=\"6\" data-englishname=\"name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 67px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 27px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 0px; top: 95px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 46px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19\" id=\"item-active\" data-name=\"19\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 61px; top: 6px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '358.00', '677.00', null, '1', null, '1567925394', '1567928129', null);
INSERT INTO `shop_diy_express_template` VALUES ('11', '000619', '308', '4', '[{\"id\":1,\"name\":\"商品信息\",\"type\":0,\"child\":[{\"id\":\"45\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_id\"},{\"id\":\"46\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_code\"},{\"id\":\"1\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goodsname\"},{\"id\":\"43\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"short_name\"},{\"id\":\"42\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"label\"},{\"id\":\"44\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"property\"},{\"id\":\"3\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"number\"},{\"id\":\"8\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"price\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"65\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"route\"},{\"id\":\"61\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_uid\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"},{\"id\":\"18\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_name\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 45\" id=\"item-active\" data-name=\"45\" data-englishname=\"goods_id\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商品ID:$goods_id</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 46\" id=\"item-active\" data-name=\"46\" data-englishname=\"goods_code\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 52px;\"><span>货号:$goods_code</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 1\" id=\"item-active\" data-name=\"1\" data-englishname=\"goodsname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 175px; top: 0px;\"><span>商品名称:$goodsname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 43\" id=\"item-active\" data-name=\"43\" data-englishname=\"short_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 177px; top: 26px;\"><span>短标题:$short_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 42\" id=\"item-active\" data-name=\"42\" data-englishname=\"label\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 27px;\"><span>标签:$label</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 44\" id=\"item-active\" data-name=\"44\" data-englishname=\"property\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 176px; top: 54px;\"><span>规格:$property</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 3\" id=\"item-active\" data-name=\"3\" data-englishname=\"number\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 80px;\"><span>数量:$number</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 8\" id=\"item-active\" data-name=\"8\" data-englishname=\"price\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 80px;\"><span>单价:$price</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 108px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"route\" style=\"border:1px solid black;padding:10px;\">路线</th><th data-englishname=\"leader_uid\" style=\"border:1px solid black;padding:10px;\">团长ID</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th><th data-englishname=\"leader_name\" style=\"border:1px solid black;padding:10px;\">团长姓名</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '680.00', null, '1', null, '1567925456', '1567925456', '1567925470');
INSERT INTO `shop_diy_express_template` VALUES ('12', '000619', '308', '1', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"35\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_name\"},{\"id\":\"34\",\"name\":\"团长昵称\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_nickname\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"},{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]}]', '<div class=\"edit-box\" style=\"width: 355px; height: 982px;\">\n					<div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 61px; top: 0px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 35\" id=\"item-active\" data-name=\"35\" data-englishname=\"leader_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 2px; top: 17px;\"><span>团长姓名:$leader_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 34\" id=\"item-active\" data-name=\"34\" data-englishname=\"leader_nickname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 176px; top: 20px;\"><span>团长昵称:$leader_nickname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" data-englishname=\"leader_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 55px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" data-englishname=\"leader_area_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 36px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 2px; top: 82px; position: absolute; cursor: move;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '353.00', '980.00', null, '1', null, '1567927066', '1567927787', null);
INSERT INTO `shop_diy_express_template` VALUES ('13', '000619', '308', '3', '[{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"}]}]', '<div class=\"edit-box\" style=\"width: 353px; height: 668px;\">\n					<div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 80px; top: 0px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 8px; top: 41px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 75px; top: 19px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '351.00', '666.00', null, '1', null, '1567927069', '1567928564', null);
INSERT INTO `shop_diy_express_template` VALUES ('14', '000619', '308', '5', '[{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"5\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"name\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 132px; position: absolute; cursor: move;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"name\" style=\"border:1px solid black;padding:10px;\">买家姓名</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '680.00', null, '1', null, '1567929031', '1567929287', null);
INSERT INTO `shop_diy_express_template` VALUES ('15', '000609', '296', '5', '[]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					</div>', '378.00', '680.00', null, '1', null, '1568210751', '1568210751', null);
INSERT INTO `shop_diy_express_template` VALUES ('16', '000808', '448', '1', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"37\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_uid\"},{\"id\":\"35\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_name\"},{\"id\":\"34\",\"name\":\"团长昵称\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_nickname\"},{\"id\":\"36\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_phone\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"},{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"},{\"id\":\"40\",\"name\":\"配送方式\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"express_type\"},{\"id\":\"39\",\"name\":\"团长城市\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_city\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 982px;\">\n					<div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; height: 26px; z-index: 0; left: 0px; top: 21px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 43px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 62px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 37\" id=\"item-active\" data-name=\"37\" data-englishname=\"leader_uid\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 0px;\"><span>团长ID:$leader_uid</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 35\" id=\"item-active\" data-name=\"35\" data-englishname=\"leader_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 22px;\"><span>团长姓名:$leader_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 34\" id=\"item-active\" data-name=\"34\" data-englishname=\"leader_nickname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 42px;\"><span>团长昵称:$leader_nickname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 36\" id=\"item-active\" data-name=\"36\" data-englishname=\"leader_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 65px;\"><span>团长电话:$leader_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" data-englishname=\"leader_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 86px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" data-englishname=\"leader_area_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 91px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 40\" id=\"item-active\" data-name=\"40\" data-englishname=\"express_type\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 115px;\"><span>配送方式:$express_type</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 39\" id=\"item-active\" data-name=\"39\" data-englishname=\"leader_city\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 114px;\"><span>团长城市:$leader_city</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 137px; position: absolute; cursor: move; z-index: 0; width: 377px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '980.00', null, '1', null, '1572094938', '1572346755', null);
INSERT INTO `shop_diy_express_template` VALUES ('17', '000800', '441', '4', '[{\"id\":1,\"name\":\"商品信息\",\"type\":0,\"child\":[{\"id\":\"45\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_id\"},{\"id\":\"46\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_code\"},{\"id\":\"1\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goodsname\"},{\"id\":\"43\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"short_name\"},{\"id\":\"42\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"label\"},{\"id\":\"44\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"property\"},{\"id\":\"3\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"number\"},{\"id\":\"8\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"price\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"65\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"route\"},{\"id\":\"61\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_uid\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"},{\"id\":\"18\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_name\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 45\" id=\"item-active\" data-name=\"45\" data-englishname=\"goods_id\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商品ID:$goods_id</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 46\" id=\"item-active\" data-name=\"46\" data-englishname=\"goods_code\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 52px;\"><span>货号:$goods_code</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 1\" id=\"item-active\" data-name=\"1\" data-englishname=\"goodsname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 175px; top: 0px;\"><span>商品名称:$goodsname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 43\" id=\"item-active\" data-name=\"43\" data-englishname=\"short_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 177px; top: 26px;\"><span>短标题:$short_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 42\" id=\"item-active\" data-name=\"42\" data-englishname=\"label\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 27px;\"><span>标签:$label</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 44\" id=\"item-active\" data-name=\"44\" data-englishname=\"property\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 176px; top: 54px;\"><span>规格:$property</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 3\" id=\"item-active\" data-name=\"3\" data-englishname=\"number\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 80px;\"><span>数量:$number</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 8\" id=\"item-active\" data-name=\"8\" data-englishname=\"price\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 80px;\"><span>单价:$price</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 108px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"route\" style=\"border:1px solid black;padding:10px;\">路线</th><th data-englishname=\"leader_uid\" style=\"border:1px solid black;padding:10px;\">团长ID</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th><th data-englishname=\"leader_name\" style=\"border:1px solid black;padding:10px;\">团长姓名</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '680.00', null, '1', null, '1573551801', '1573551801', null);
INSERT INTO `shop_diy_express_template` VALUES ('18', '000800', '441', '2', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"},{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"}]},{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"9\",\"name\":\"买家电话\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"phone\"},{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"},{\"id\":\"33\",\"name\":\"买家区域\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"buyer_area\"},{\"id\":\"32\",\"name\":\"买家城市\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"buyer_city\"},{\"id\":\"26\",\"name\":\"买家地址\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"address\"},{\"id\":\"31\",\"name\":\"买家留言\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"remark\"},{\"id\":\"29\",\"name\":\"实付金额\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"payment_money\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"40\",\"name\":\"配送方式\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"express_type\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 104px; top: 4px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 24px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 25px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 47px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 9\" id=\"item-active\" data-name=\"9\" data-englishname=\"phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 69px;\"><span>买家电话:$phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 6\" id=\"item-active\" data-name=\"6\" data-englishname=\"name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 68px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 33\" id=\"item-active\" data-name=\"33\" data-englishname=\"buyer_area\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 92px;\"><span>买家区域:$buyer_area</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 32\" id=\"item-active\" data-name=\"32\" data-englishname=\"buyer_city\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 171px; top: 91px;\"><span>买家城市:$buyer_city</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 26\" id=\"item-active\" data-name=\"26\" data-englishname=\"address\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 116px;\"><span>买家地址:$address</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 40\" id=\"item-active\" data-name=\"40\" data-englishname=\"express_type\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 171px; top: 117px;\"><span>配送方式:$express_type</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 31\" id=\"item-active\" data-name=\"31\" data-englishname=\"remark\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 137px;\"><span>买家留言:$remark</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 29\" id=\"item-active\" data-name=\"29\" data-englishname=\"payment_money\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 169px; top: 136px;\"><span>实付金额:$payment_money</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 4px; top: 171px; position: absolute; cursor: move; z-index: 0; width: 369px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '378.00', '680.00', null, '1', null, '1573551809', '1573551809', null);
INSERT INTO `shop_diy_express_template` VALUES ('19', '000847', '497', '2', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"},{\"id\":\"33\",\"name\":\"买家区域\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"buyer_area\"},{\"id\":\"29\",\"name\":\"实付金额\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"payment_money\"},{\"id\":\"26\",\"name\":\"买家地址\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"address\"},{\"id\":\"9\",\"name\":\"买家电话\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"phone\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"}]}]', '<div class=\"edit-box\" style=\"width: 212px; height: 299px;\">\n					<div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 24px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 25px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 47px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 6\" id=\"item-active\" data-name=\"6\" data-englishname=\"name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 68px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 33\" id=\"item-active\" data-name=\"33\" data-englishname=\"buyer_area\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 92px;\"><span>买家区域:$buyer_area</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 29\" id=\"item-active\" data-name=\"29\" data-englishname=\"payment_money\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 169px; top: 136px;\"><span>实付金额:$payment_money</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 26\" id=\"item-active\" data-name=\"26\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move;\"><span>买家地址:$address</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 9\" id=\"item-active\" data-name=\"9\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move;\"><span>买家电话:$phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 4px; top: 171px; position: absolute; cursor: move;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19\" id=\"item-active\" data-name=\"19\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '210.00', '297.00', null, '1', null, '1576826768', '1576828986', '1576829087');
INSERT INTO `shop_diy_express_template` VALUES ('20', '000847', '497', '4', '[{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"},{\"id\":\"59\",\"name\":\"团长昵称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_nickname\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"}]},{\"id\":1,\"name\":\"商品信息\",\"type\":0,\"child\":[{\"id\":\"45\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_id\"},{\"id\":\"44\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"property\"},{\"id\":\"8\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"price\"},{\"id\":\"3\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"number\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"小程序名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"}]}]', '<div class=\"edit-box\" style=\"width: 212px; height: 299px;\">\n					<div class=\"item item-table\" data-name=\"name-table\" style=\"left: 0px; top: 190px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th><th data-englishname=\"leader_nickname\" style=\"border:1px solid black;padding:10px;\">团长昵称</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 45\" id=\"item-active\" data-name=\"45\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 4px; top: 27px;\"><span>商品ID:$goods_id</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 44\" id=\"item-active\" data-name=\"44\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 4px; top: 69px;\"><span>规格:$property</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 8\" id=\"item-active\" data-name=\"8\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 117px; top: 25px;\"><span>单价:$price</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 3\" id=\"item-active\" data-name=\"3\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 113px; top: 73px;\"><span>数量:$number</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19\" id=\"item-active\" data-name=\"19\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 29px; top: 0px;\"><span>小程序名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 1px; top: 130px; height: 28px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 5px; top: 102px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 70\" id=\"item-active\" data-name=\"70\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 3px; top: 164px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', '210.00', '297.00', null, '1', null, '1576829099', '1576839306', null);

-- ----------------------------
-- Table structure for `shop_electronics`
-- ----------------------------
DROP TABLE IF EXISTS `shop_electronics`;
CREATE TABLE `shop_electronics` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `express_id` int(11) NOT NULL COMMENT '快递公司id',
  `customer_name` varchar(255) DEFAULT NULL COMMENT '电子面单客户账号',
  `customer_pwd` varchar(255) DEFAULT NULL COMMENT '电子面单密码',
  `month_code` varchar(255) DEFAULT NULL COMMENT '月结编码',
  `dot_code` varchar(255) DEFAULT NULL COMMENT '网点编码',
  `dot_name` varchar(255) NOT NULL COMMENT '网点名称',
  `company` varchar(255) DEFAULT NULL COMMENT '发件人公司',
  `name` varchar(255) DEFAULT NULL COMMENT '发件人名称',
  `tel` varchar(255) DEFAULT NULL COMMENT '发件人电话',
  `phone` int(11) DEFAULT NULL COMMENT '发件人手机',
  `post_code` char(6) DEFAULT NULL COMMENT '发件人邮编',
  `addr` varchar(255) DEFAULT NULL COMMENT '发件人详细地址',
  `province_code` char(6) DEFAULT NULL COMMENT '省编码',
  `province_name` varchar(255) DEFAULT NULL COMMENT '省名称',
  `city_code` char(6) DEFAULT NULL COMMENT '市编码',
  `city_name` varchar(255) DEFAULT NULL COMMENT '市名称',
  `area_code` char(6) DEFAULT NULL COMMENT '区编码',
  `area_name` varchar(255) DEFAULT NULL COMMENT '区名称',
  `towing_goods` varchar(255) DEFAULT NULL COMMENT '托寄物',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COMMENT='商城-电子面单';


-- ----------------------------
-- Table structure for `shop_express`
-- ----------------------------
DROP TABLE IF EXISTS `shop_express`;
CREATE TABLE `shop_express` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `system_express_id` int(10) NOT NULL COMMENT '快递id',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `remarks` varchar(50) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='商户-快递表';

-- ----------------------------
-- Records of shop_express
-- ----------------------------

INSERT INTO `shop_express` VALUES ('35', 'ccvWPn', '13', '8', '0', '', '1', '1562844562', null, null);

-- ----------------------------
-- Table structure for `shop_express_template`
-- ----------------------------
DROP TABLE IF EXISTS `shop_express_template`;
CREATE TABLE `shop_express_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(50) NOT NULL COMMENT '模板名称',
  `type` tinyint(1) NOT NULL COMMENT '类型 1记件 2记重 3距离',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8 COMMENT='商户-快递模板表';

-- ----------------------------
-- Records of shop_express_template
-- ----------------------------

INSERT INTO `shop_express_template` VALUES ('19', 'ccvWPn', '13', '全国统一运费', '1', '1', '1558581943', '1582781407', null);
INSERT INTO `shop_express_template` VALUES ('223', 'ccvWPn', '13', '222', '3', '0', '1582218502', '1582781407', null);
INSERT INTO `shop_express_template` VALUES ('224', 'ccvWPn', '13', '按照距离', '3', '0', '1582420584', '1582781407', null);

-- ----------------------------
-- Table structure for `shop_express_template_details`
-- ----------------------------
DROP TABLE IF EXISTS `shop_express_template_details`;
CREATE TABLE `shop_express_template_details` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `shop_express_template_id` int(11) NOT NULL DEFAULT '0' COMMENT '快递模板id',
  `names` text NOT NULL COMMENT '城市名称,逗号分割',
  `first_num` int(10) NOT NULL COMMENT '首个(重)数量',
  `first_price` decimal(10,2) NOT NULL COMMENT '首个(重)价格',
  `expand_num` int(10) NOT NULL COMMENT '续个(重)数量',
  `expand_price` decimal(10,2) NOT NULL COMMENT '续个(重)价格',
  `distance` text NOT NULL COMMENT '距离价格',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=381 DEFAULT CHARSET=utf8 COMMENT='商户-快递模板详情表';

-- ----------------------------
-- Records of shop_express_template_details
-- ----------------------------
INSERT INTO `shop_express_template_details` VALUES ('345', 'ccvWPn', '13', '208', '', '0', '0.00', '0', '0.00', '{\"start_number\":[\"0\"],\"end_number\":[\"2\"],\"freight\":[\"3\"]}', '1', '1575338084', null, null);
INSERT INTO `shop_express_template_details` VALUES ('374', 'ccvWPn', '13', '19', '全国统一运费', '1', '0.00', '1', '1.00', '', '1', '1576721823', null, null);
INSERT INTO `shop_express_template_details` VALUES ('377', 'ccvWPn', '13', '223', '', '0', '0.00', '0', '0.00', '{\"start_number\":[\"0\"],\"end_number\":[\"5\"],\"freight\":[\"5\"]}', '1', '1582218502', null, null);
INSERT INTO `shop_express_template_details` VALUES ('380', 'ccvWPn', '13', '224', '', '0', '0.00', '0', '0.00', '{\"start_number\":[\"0\",\"5\"],\"end_number\":[\"5\",\"1200\"],\"freight\":[\"5\",\"8\"]}', '1', '1582762747', null, null);

-- ----------------------------
-- Table structure for `shop_flash_sale`
-- ----------------------------
DROP TABLE IF EXISTS `shop_flash_sale`;
CREATE TABLE `shop_flash_sale` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `supplier_id` int(11) NOT NULL,
  `is_top` tinyint(1) NOT NULL COMMENT '是否推荐 1=推荐 0=不推荐',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `flash_sale_group_id` int(11) NOT NULL COMMENT '秒杀组id',
  `name` varchar(255) NOT NULL COMMENT '商品名称',
  `property` text NOT NULL COMMENT '属性信息（json：属性1 property1_name 属性2 property2_name 规格的秒杀数量 stocks 规格的秒杀价 flash_price)',
  `flash_price` decimal(10,2) NOT NULL COMMENT '秒杀的最低价格',
  `stocks` int(10) NOT NULL COMMENT '秒杀商品的总库存',
  `pic_url` varchar(255) NOT NULL COMMENT '商品图片',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品-秒杀表';

-- ----------------------------
-- Records of shop_flash_sale
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_flash_sale_group`
-- ----------------------------
DROP TABLE IF EXISTS `shop_flash_sale_group`;
CREATE TABLE `shop_flash_sale_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `supplier_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL COMMENT '秒杀名称',
  `detail_info` varchar(512) NOT NULL DEFAULT '' COMMENT '描述',
  `start_time` int(11) NOT NULL COMMENT '开始售卖时间',
  `end_time` int(11) NOT NULL COMMENT '结束售卖时间',
  `send_time` int(11) NOT NULL COMMENT '预计发货时间',
  `goods_ids` varchar(256) NOT NULL COMMENT '商品ids，逗号分割',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品-秒杀组表';

-- ----------------------------
-- Records of shop_flash_sale_group
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_goods`
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods`;
CREATE TABLE `shop_goods` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `name` varchar(128) NOT NULL COMMENT '商品名称',
  `code` varchar(52) DEFAULT NULL COMMENT '商品编码',
  `pic_urls` text NOT NULL COMMENT '商品图片',
  `video_url` varchar(256) NOT NULL DEFAULT '' COMMENT '视频地址',
  `video_pic_url` varchar(256) NOT NULL DEFAULT '' COMMENT '视频图片地址',
  `video_id` varchar(32) NOT NULL DEFAULT '' COMMENT '视频id(回调使用)',
  `video_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0=转码中 1=转码成功',
  `assemble_price` decimal(10,2) NOT NULL COMMENT '拼团最低价',
  `price` decimal(10,2) NOT NULL COMMENT '最低价',
  `weight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '重量',
  `line_price` decimal(10,2) NOT NULL COMMENT '划线价',
  `stocks` int(10) NOT NULL COMMENT '总库存',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '类目id',
  `m_category_id` int(10) NOT NULL DEFAULT '0' COMMENT '分组id',
  `property1` varchar(256) DEFAULT NULL COMMENT '第一个属性',
  `property2` varchar(256) DEFAULT NULL COMMENT '第二个属性',
  `stock_type` int(1) NOT NULL COMMENT '单双规格',
  `have_stock_type` int(1) NOT NULL DEFAULT '0' COMMENT '是否有规格',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `shop_express_template_id` int(10) NOT NULL COMMENT '运费模板id',
  `type` tinyint(1) unsigned NOT NULL COMMENT '类型 1=实物 2=虚拟 3=服务',
  `is_flash_sale` tinyint(1) DEFAULT '0',
  `service_goods_is_ship` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品是服务时选择是否自动发货，0不自动 1 自动',
  `band_self_leader_id` int(10) DEFAULT '0' COMMENT '绑定自提点id',
  `detail_info` text COMMENT '详细说明(富文本)',
  `simple_info` varchar(256) DEFAULT NULL COMMENT '简单说明',
  `label` varchar(256) DEFAULT NULL COMMENT '标签',
  `short_name` varchar(256) DEFAULT NULL COMMENT '短标题',
  `supplier_id` int(10) NOT NULL DEFAULT '0' COMMENT '供应商id，0为系统',
  `supplier_money` decimal(8,2) DEFAULT '0.00' COMMENT '供应商价格',
  `is_check` tinyint(1) DEFAULT '0' COMMENT '审核状态 0=审核中 1=审核成功 2=审核失败',
  `city_group_id` text COMMENT '城市组id，0为不限制',
  `commission_is_open` tinyint(1) DEFAULT '0' COMMENT '是否开启单独佣金设置 1 是 0 否',
  `commission_leader_ratio` double(5,2) DEFAULT '0.00' COMMENT '团长佣金',
  `commission_selfleader_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '自提点佣金',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐 1显示 0隐藏',
  `is_limit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否限量 0=不开启 1=开启',
  `limit_number` int(11) DEFAULT '0' COMMENT '0=不限量',
  `sales_number` int(10) NOT NULL DEFAULT '0' COMMENT '虚拟销量 实际销量=虚拟销量+真实销量',
  `unit` varchar(30) NOT NULL COMMENT 'unit',
  `attribute` varchar(255) NOT NULL COMMENT '属性',
  `look` tinyint(10) NOT NULL DEFAULT '0' COMMENT '查看量',
  `is_open_assemble` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启拼团 0不开启 1开启',
  `regimental_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否团长专属 1是 0否',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效[下架]',
  `start_type` tinyint(1) DEFAULT '1' COMMENT '商家时间类型 1立即上架 2自定义 3暂不售卖',
  `start_time` int(11) DEFAULT NULL COMMENT '开始售卖时间',
  `end_time` int(11) DEFAULT NULL COMMENT '结束售卖时间',
  `take_goods_time` int(11) DEFAULT NULL COMMENT '提货时间',
  `is_bargain` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启砍价 0不开启 1开启',
  `bargain_start_time` int(11) DEFAULT NULL COMMENT '砍价活动开始时间',
  `bargain_end_time` int(11) DEFAULT NULL COMMENT '砍价活动结束时间',
  `is_buy_alone` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持单独购买 0不开启 1开启',
  `fictitious_initiate_bargain` int(10) NOT NULL COMMENT '虚拟发起砍价人数量',
  `fictitious_help_bargain` int(10) NOT NULL COMMENT '虚拟帮砍人数量',
  `bargain_price` decimal(10,2) NOT NULL COMMENT '砍价最低价（必须大于0）',
  `help_number` int(10) NOT NULL COMMENT '好友帮砍次数',
  `bargain_limit_time` int(11) NOT NULL COMMENT '砍价时间限制（小时）',
  `bargain_rule` text COMMENT '砍价规则',
  `is_recruits` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否参加新人专享 0未参加 1参加',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `distribution` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '分销佣金',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商品表';



-- ----------------------------
-- Table structure for `shop_goods_city_group`
-- ----------------------------
DROP TABLE IF EXISTS `shop_goods_city_group`;
CREATE TABLE `shop_goods_city_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(128) NOT NULL COMMENT '商品城市组名称',
  `city_codes` text COMMENT '城市编码',
  `area_codes` text COMMENT '县区编码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品-城市组表';

-- ----------------------------
-- Records of shop_goods_city_group
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_incoming`
-- ----------------------------
DROP TABLE IF EXISTS `shop_incoming`;
CREATE TABLE `shop_incoming` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `code` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '入库编码',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `operator` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '操作人',
  `number` int(10) NOT NULL DEFAULT '0' COMMENT '入库总数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '插入时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COMMENT='入库表';

-- ----------------------------
-- Records of shop_incoming
-- ----------------------------
INSERT INTO `shop_incoming` VALUES ('19', 'ccvWPn', '13', 'RK-1951576045096', '1', '', '3', '1', '1576045095', '1576045095', null);
INSERT INTO `shop_incoming` VALUES ('24', 'ccvWPn', '13', 'RK-8701576045645', '1', '', '15', '1', '1576045645', '1576045645', null);
INSERT INTO `shop_incoming` VALUES ('25', 'ccvWPn', '13', 'RK-1271576045706', '1', '', '6', '1', '1576045705', '1576045705', null);
INSERT INTO `shop_incoming` VALUES ('26', 'ccvWPn', '13', 'RK-2021578386088', '1', '', '6', '1', '1578386088', '1578386088', null);
INSERT INTO `shop_incoming` VALUES ('27', 'ccvWPn', '13', 'RK-3221578551940', '5', '', '2999994', '1', '1578551939', '1578551939', null);
INSERT INTO `shop_incoming` VALUES ('28', 'ccvWPn', '13', 'RK-8391578561169', '5', '', '100', '1', '1578561169', '1578561169', null);
INSERT INTO `shop_incoming` VALUES ('29', 'ccvWPn', '13', 'RK-7501578566759', '5', '', '10', '1', '1578566759', '1578566759', null);
INSERT INTO `shop_incoming` VALUES ('30', 'ccvWPn', '13', 'RK-2721578566839', '5', '', '10', '1', '1578566839', '1578566839', null);
INSERT INTO `shop_incoming` VALUES ('31', 'ccvWPn', '13', 'RK-8001578621919', '5', '', '100', '1', '1578621919', '1578621919', null);

-- ----------------------------
-- Table structure for `shop_incoming_detail`
-- ----------------------------
DROP TABLE IF EXISTS `shop_incoming_detail`;
CREATE TABLE `shop_incoming_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `incoming_code` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '入库单号',
  `incoming_id` int(11) NOT NULL DEFAULT '0' COMMENT '入库id',
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `stock_id` int(11) NOT NULL DEFAULT '0' COMMENT '规格id',
  `number` int(10) NOT NULL DEFAULT '0' COMMENT '入库商品数量',
  `status` tinyint(1) DEFAULT '1' COMMENT '0 无效 1有效',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='入库详情';

-- ----------------------------
-- Records of shop_incoming_detail
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_inventory`
-- ----------------------------
DROP TABLE IF EXISTS `shop_inventory`;
CREATE TABLE `shop_inventory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `code` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '盘点单号',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `operator` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '操作人',
  `new_number` int(10) NOT NULL DEFAULT '0' COMMENT '盘点后商品数量',
  `old_number` int(10) NOT NULL DEFAULT '0' COMMENT '盘点前商品数量',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '插入时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='盘点表';

-- ----------------------------
-- Records of shop_inventory
-- ----------------------------
INSERT INTO `shop_inventory` VALUES ('1', 'ccvWPn', '13', 'PD-6531583814700', '6', '', '100', '0', '1', '1583814700', '1583814700', null);

-- ----------------------------
-- Table structure for `shop_inventory_detail`
-- ----------------------------
DROP TABLE IF EXISTS `shop_inventory_detail`;
CREATE TABLE `shop_inventory_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_code` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '盘点单号',
  `inventory_id` int(11) NOT NULL DEFAULT '0' COMMENT '盘点id',
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `stock_id` int(11) NOT NULL DEFAULT '0' COMMENT '规格id',
  `old_number` int(10) NOT NULL DEFAULT '0' COMMENT '原商品数量',
  `new_number` int(10) NOT NULL DEFAULT '0' COMMENT '新商品数量',
  `remark` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '0 无效 1有效',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='盘点详情';

-- ----------------------------
-- Records of shop_inventory_detail
-- ----------------------------
INSERT INTO `shop_inventory_detail` VALUES ('1', 'PD-6531583814700', '1', 'ccvWPn', '6', '13', '35', '135', '0', '100', '', '1', '1583814700', '1583814700', null);

-- ----------------------------
-- Table structure for `shop_leader_level`
-- ----------------------------
DROP TABLE IF EXISTS `shop_leader_level`;
CREATE TABLE `shop_leader_level` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(32) NOT NULL COMMENT '等级名称',
  `min_exp` int(10) NOT NULL COMMENT '等级经验',
  `reward_ratio` double(4,2) DEFAULT '0.00' COMMENT '奖励比例',
  `type` int(2) NOT NULL DEFAULT '1' COMMENT '1推客 2团长',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COMMENT='团长等级表';

-- ----------------------------
-- Records of shop_leader_level
-- ----------------------------
INSERT INTO `shop_leader_level` VALUES ('1', 'ccvWPn', '13', '金牌推客', '100', '1.00', '1', '1', '1560302349', '1571967567', null);
INSERT INTO `shop_leader_level` VALUES ('26', 'ccvWPn', '13', '一级团长', '100', '1.00', '2', '1', '1571967599', '1571967599', null);
INSERT INTO `shop_leader_level` VALUES ('27', 'ccvWPn', '13', '二级团长', '10000', '2.00', '2', '1', '1571967634', '1571967634', null);
INSERT INTO `shop_leader_level` VALUES ('28', 'ccvWPn', '13', '三级团长', '100000', '3.00', '2', '1', '1571967715', '1571967715', null);

-- ----------------------------
-- Table structure for `shop_lucky_voucher`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lucky_voucher`;
CREATE TABLE `shop_lucky_voucher` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `order_sn` varchar(32) DEFAULT NULL COMMENT '订单号',
  `lucky_number` tinyint(2) NOT NULL DEFAULT '1' COMMENT '幸运数字',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态 0无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='抵用券-拼手气表';



-- ----------------------------
-- Table structure for `shop_marchant_category`
-- ----------------------------
DROP TABLE IF EXISTS `shop_marchant_category`;
CREATE TABLE `shop_marchant_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '类目名称',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父类',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `img_url` varchar(255) NOT NULL COMMENT '分类海报',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首页显示 1显示 0隐藏',
  `sort` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1015 DEFAULT CHARSET=utf8 COMMENT='店铺分组表';



-- ----------------------------
-- Table structure for `shop_operator_user`
-- ----------------------------
DROP TABLE IF EXISTS `shop_operator_user`;
CREATE TABLE `shop_operator_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '运营商名称（运营商等级）',
  `background_color` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '背景色',
  `icon` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '图标',
  `background_image` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '背景图',
  `fan_number` int(11) NOT NULL DEFAULT '0' COMMENT '直推粉丝数',
  `secondhand_fan_number` int(11) NOT NULL DEFAULT '0' COMMENT '非直推粉丝数',
  `fan_number_buy` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '直推粉丝消费额',
  `self_buy` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '自购省钱',
  `team_back` decimal(5,2) NOT NULL COMMENT '团队分佣',
  `same_level_back` decimal(5,2) NOT NULL COMMENT '平级分佣',
  `equity_back` decimal(5,2) NOT NULL COMMENT '股权分佣',
  `remark` text CHARACTER SET utf8 COMMENT '权益说明',
  `status` tinyint(1) NOT NULL COMMENT '0 关闭 1 开启',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='运营商';



-- ----------------------------
-- Table structure for `shop_order`
-- ----------------------------
DROP TABLE IF EXISTS `shop_order`;
CREATE TABLE `shop_order` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `order_group_sn` varchar(24) NOT NULL DEFAULT '' COMMENT '订单主表sn号',
  `name` varchar(256) NOT NULL COMMENT '商品名称',
  `pic_url` varchar(256) NOT NULL COMMENT '商品图片',
  `goods_id` int(10) NOT NULL COMMENT '商品id',
  `is_flash_sale` tinyint(1) DEFAULT '0' COMMENT '是否秒杀 0不是 1=是',
  `is_assemble` tinyint(1) DEFAULT '0' COMMENT '是否拼团 0=否 1=是',
  `estimated_time` int(10) NOT NULL DEFAULT '0' COMMENT '预约时间',
  `property1_name` varchar(256) DEFAULT NULL COMMENT '第一个属性名称',
  `property2_name` varchar(256) DEFAULT NULL COMMENT '第二个属性名称',
  `stock_id` varchar(256) NOT NULL DEFAULT '' COMMENT '库存id',
  `number` int(10) NOT NULL COMMENT '商品数量',
  `total_price` decimal(8,2) DEFAULT '0.00' COMMENT '子订单总额',
  `price` decimal(8,2) DEFAULT NULL COMMENT '商品单价',
  `express_price` decimal(8,2) DEFAULT '0.00' COMMENT '运费价格',
  `confirm_time` int(10) DEFAULT '0' COMMENT '确认时间',
  `finish_time` int(10) DEFAULT '0' COMMENT '完成时间',
  `send_out_time` int(10) DEFAULT '0' COMMENT '发货时间',
  `payment_money` decimal(8,2) DEFAULT '0.00' COMMENT '子付款总额(减去优惠)',
  `express_id` int(10) DEFAULT NULL COMMENT '快递id',
  `express_number` varchar(52) DEFAULT NULL COMMENT '快递单号',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '0' COMMENT '0=未发货 1=已发货 2=已退款 3=售后中 4=已完成',
  `admin_remark` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='商品订单表';


-- ----------------------------
-- Table structure for `shop_order_group`
-- ----------------------------
DROP TABLE IF EXISTS `shop_order_group`;
CREATE TABLE `shop_order_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `transaction_order_sn` varchar(24) NOT NULL COMMENT '总订单号',
  `order_sn` varchar(24) NOT NULL DEFAULT '' COMMENT '订单编码',
  `goodsname` varchar(256) DEFAULT NULL,
  `user_contact_id` int(11) NOT NULL COMMENT '用户信息id',
  `total_price` decimal(8,2) DEFAULT '0.00' COMMENT '订单总额',
  `express_price` double(8,2) DEFAULT '0.00' COMMENT '快递费用',
  `payment_money` decimal(8,2) DEFAULT '0.00' COMMENT '付款总额(减去优惠)',
  `reduction_achieve` decimal(8,2) DEFAULT '0.00' COMMENT '满减',
  `voucher_id` int(10) DEFAULT '0' COMMENT '优惠券id',
  `is_tuan` tinyint(1) DEFAULT '0' COMMENT '是否社区团购 1=是 0=否',
  `express_type` tinyint(1) DEFAULT '0' COMMENT '发货方式 0=快递 1=自提 2=团长送货',
  `leader_uid` int(10) DEFAULT '0' COMMENT '团长的uid',
  `leader_self_uid` int(10) DEFAULT '0' COMMENT '自提点团长的uid',
  `tuan_status` tinyint(1) DEFAULT '0' COMMENT '社区团购状态 0=未发货(非社区团购无需处理)  1=已发货 2=已收到货',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `supplier_id` int(8) DEFAULT '0' COMMENT '供应商id',
  `address` varchar(256) DEFAULT NULL COMMENT '收货地址',
  `phone` varchar(25) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `after_sale` tinyint(1) DEFAULT '-1' COMMENT '退款状态 -1=未售后 0=退款中 1=同意退款 2=拒绝退款',
  `after_type` tinyint(1) DEFAULT '0' COMMENT '退款类型 1=退款退货 2=只退款',
  `after_phone` varchar(25) DEFAULT '0' COMMENT '退款电话',
  `after_addr` varchar(256) DEFAULT NULL COMMENT '退款地址',
  `after_remark` varchar(256) DEFAULT '' COMMENT '退款理由(用户)',
  `after_imgs` varchar(512) DEFAULT NULL COMMENT '退款图片(用户)',
  `after_express_number` varchar(52) DEFAULT NULL COMMENT '退款单号',
  `after_admin_imgs` varchar(512) DEFAULT NULL COMMENT '拒绝图片(商户)',
  `after_admin_remark` varchar(256) DEFAULT '' COMMENT '拒绝理由(管理)',
  `order_type` int(1) DEFAULT '1' COMMENT '1 微信公众号支付  2 小程序支付 3 余额支付',
  `is_print` int(1) NOT NULL DEFAULT '0' COMMENT '1已打印 0未打印',
  `print_time` int(11) DEFAULT '0',
  `is_sent_print` int(1) NOT NULL DEFAULT '0',
  `send_print_time` int(11) DEFAULT '0',
  `estimated_service_time` varchar(255) NOT NULL,
  `is_bargain` int(1) NOT NULL DEFAULT '0',
  `is_assemble` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否拼团 0=否 1=是',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0=待付款 1=待发货 2=已取消(24小时未支付) 3=已发货 4=已退款 5=退款中 6=待评价 7=已完成(评价后)  8=已删除  9一键退款  11=拼团中',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `admin_remark` varchar(255) DEFAULT '' COMMENT '管理员备注',
  `service_goods_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是服务商品订单0 不是1 是',
  `refund` text COMMENT '退款信息',
  `is_send_message` int(11) DEFAULT '0',
  `is_partner_withdraw` tinyint(1) NOT NULL DEFAULT '0' COMMENT '不是合伙人订单忽略此参数 0 待提现 1 已申请提现 2 提现完成',
  `reduction_max_money` float(11,2) NOT NULL,
  `reduction_min_money` float(11,2) NOT NULL,
  `commission` float(11,2) NOT NULL COMMENT '分销总佣金',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COMMENT='商品订单主表';


-- ----------------------------
-- Table structure for `shop_outbound`
-- ----------------------------
DROP TABLE IF EXISTS `shop_outbound`;
CREATE TABLE `shop_outbound` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `code` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '出库编码',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `operator` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '操作人',
  `number` int(10) NOT NULL DEFAULT '0' COMMENT '出库总数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '插入时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='出库表';

-- ----------------------------
-- Records of shop_outbound
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_outbound_detail`
-- ----------------------------
DROP TABLE IF EXISTS `shop_outbound_detail`;
CREATE TABLE `shop_outbound_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `outbound_code` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '出库单号',
  `outbound_id` int(11) NOT NULL DEFAULT '0' COMMENT '出库id',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `stock_id` int(11) NOT NULL DEFAULT '0' COMMENT '规格id',
  `number` int(10) NOT NULL DEFAULT '0' COMMENT '出库商品数量',
  `status` tinyint(1) DEFAULT '1' COMMENT '0 无效 1有效',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='出库详情';

-- ----------------------------
-- Records of shop_outbound_detail
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_picture`
-- ----------------------------
DROP TABLE IF EXISTS `shop_picture`;
CREATE TABLE `shop_picture` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `picture_group_id` int(10) NOT NULL DEFAULT '0' COMMENT '图片分组id，默认0 =未分组',
  `name` varchar(52) NOT NULL DEFAULT '' COMMENT '名称',
  `width` double(8,2) NOT NULL COMMENT '宽度',
  `height` double(8,2) NOT NULL COMMENT '高度',
  `pic_url` varchar(128) NOT NULL COMMENT '图片地址',
  `md5` varchar(32) NOT NULL COMMENT '图片md5值，防止重复',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='电商-图片表';


-- ----------------------------
-- Table structure for `shop_picture_group`
-- ----------------------------
DROP TABLE IF EXISTS `shop_picture_group`;
CREATE TABLE `shop_picture_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL COMMENT '图片分组名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='电商-图片分组表';

-- ----------------------------
-- Records of shop_picture_group
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_poster`
-- ----------------------------
DROP TABLE IF EXISTS `shop_poster`;
CREATE TABLE `shop_poster` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(52) NOT NULL DEFAULT '' COMMENT '名称',
  `type` int(1) NOT NULL COMMENT '0 是首页 1 详情页',
  `pic_url` varchar(128) NOT NULL COMMENT '图片地址',
  `path` varchar(128) NOT NULL COMMENT '图片路径',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='商城生成海报表';



-- ----------------------------
-- Table structure for `shop_property`
-- ----------------------------
DROP TABLE IF EXISTS `shop_property`;
CREATE TABLE `shop_property` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `name` varchar(52) NOT NULL COMMENT '属性名称',
  `pic_url` varchar(52) NOT NULL COMMENT '属性图片',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '父类id',
  `index` tinyint(1) NOT NULL COMMENT '第几个属性 index = 1或者2',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品属性表';

-- ----------------------------
-- Records of shop_property
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_recharge_balance_access`
-- ----------------------------
DROP TABLE IF EXISTS `shop_recharge_balance_access`;
CREATE TABLE `shop_recharge_balance_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(10) NOT NULL COMMENT '会员id',
  `money` decimal(8,2) NOT NULL COMMENT '支付金额',
  `remain_money` decimal(8,2) NOT NULL COMMENT '到账金额',
  `pay_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '购买类型  1=微信 2=支付宝 5=扫呗',
  `pay_sn` varchar(32) NOT NULL DEFAULT '' COMMENT '支付流水号',
  `transaction_id` varchar(52) DEFAULT NULL COMMENT '第三方支付流水号',
  `status` tinyint(1) DEFAULT '0' COMMENT '用户状态 1=已支付 0=未支付',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COMMENT='电商充值记录表';

-- ----------------------------
-- Records of shop_recharge_balance_access
-- ----------------------------
INSERT INTO `shop_recharge_balance_access` VALUES ('106', 'ccvWPn', '13', '10', '100.00', '100.00', '1', '202002291558138740', null, '0', '1582963093', '1582963093', null);

-- ----------------------------
-- Table structure for `shop_score_banner`
-- ----------------------------
DROP TABLE IF EXISTS `shop_score_banner`;
CREATE TABLE `shop_score_banner` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(50) NOT NULL COMMENT '横幅名称',
  `pic_url` varchar(255) NOT NULL COMMENT '横幅图片',
  `jump_url` varchar(255) NOT NULL COMMENT '跳转链接',
  `type` tinyint(1) NOT NULL COMMENT '类型 1微信端 2小程序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='商户-积分商城横幅表';

-- ----------------------------
-- Records of shop_score_banner
-- ----------------------------
INSERT INTO `shop_score_banner` VALUES ('1', 'ccvWPn', '13', '11', 'http://tuan.weikejs.com/api/web/./uploads/merchant/shop/goods_picture/13/ccvWPn/15822165525e4eb56878bc5.png', '', '2', '1', '1582376410', '1582376410', null);

-- ----------------------------
-- Table structure for `shop_score_goods`
-- ----------------------------
DROP TABLE IF EXISTS `shop_score_goods`;
CREATE TABLE `shop_score_goods` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(128) NOT NULL COMMENT '积分商品名称',
  `code` varchar(52) DEFAULT NULL COMMENT '商品编码',
  `pic_urls` text NOT NULL COMMENT '商品图片',
  `score` int(10) NOT NULL COMMENT '所需积分',
  `stocks` int(10) NOT NULL COMMENT '总库存',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '类目id',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `type` tinyint(1) unsigned NOT NULL COMMENT '类型 1=实物 2=虚拟 3=服务',
  `detail_info` text COMMENT '详细说明(富文本)',
  `simple_info` varchar(256) DEFAULT NULL COMMENT '简单说明',
  `label` varchar(256) DEFAULT NULL COMMENT '标签',
  `short_name` varchar(256) DEFAULT NULL COMMENT '短标题',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐 1显示 0隐藏',
  `look` tinyint(10) NOT NULL DEFAULT '0' COMMENT '查看量',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效[下架]',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分商品表';

-- ----------------------------
-- Records of shop_score_goods
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_score_goods_category`
-- ----------------------------
DROP TABLE IF EXISTS `shop_score_goods_category`;
CREATE TABLE `shop_score_goods_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '类目名称',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `parent_id` int(11) DEFAULT '0' COMMENT '父类',
  `img_url` varchar(255) NOT NULL DEFAULT '' COMMENT '海报地址',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首页显示 1显示 0隐藏',
  `sort` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分商品类目表';

-- ----------------------------
-- Records of shop_score_goods_category
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_score_order`
-- ----------------------------
DROP TABLE IF EXISTS `shop_score_order`;
CREATE TABLE `shop_score_order` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `order_sn` varchar(32) NOT NULL DEFAULT '' COMMENT '订单编号',
  `name` varchar(256) NOT NULL COMMENT '商品名称',
  `pic_url` varchar(256) NOT NULL COMMENT '商品图片',
  `score_goods_id` int(10) NOT NULL COMMENT '商品id',
  `user_contact_id` int(10) NOT NULL COMMENT '用户信息id',
  `number` int(10) NOT NULL COMMENT '商品数量,目前只能1',
  `score` int(10) NOT NULL COMMENT '商品积分',
  `send_out_time` int(10) DEFAULT '0' COMMENT '发货时间',
  `express_id` int(10) DEFAULT NULL COMMENT '快递id',
  `express_number` varchar(52) DEFAULT NULL COMMENT '快递单号',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0=未发货 1=已发货',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `admin_remark` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分商品订单表';

-- ----------------------------
-- Records of shop_score_order
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_score_rule`
-- ----------------------------
DROP TABLE IF EXISTS `shop_score_rule`;
CREATE TABLE `shop_score_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `condition` char(100) NOT NULL COMMENT '触发条件',
  `score` tinyint(4) NOT NULL COMMENT '积分变动 正/负数',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商积分规则表';

-- ----------------------------
-- Records of shop_score_rule
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_shansong_order`
-- ----------------------------
DROP TABLE IF EXISTS `shop_shansong_order`;
CREATE TABLE `shop_shansong_order` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `key` varchar(8) NOT NULL COMMENT 'key',
  `merchant_id` int(8) NOT NULL COMMENT '商户id',
  `order_sn` varchar(18) NOT NULL COMMENT '平台订单号',
  `iss_order_sn` varchar(18) NOT NULL DEFAULT '' COMMENT '闪送订单号',
  `addition` varchar(10) CHARACTER SET utf8 DEFAULT NULL COMMENT '加价费（单位：分）',
  `weight` varchar(10) CHARACTER SET utf8 DEFAULT NULL COMMENT '重量 （单位：kg)',
  `appointTime` datetime DEFAULT NULL COMMENT '预约时间',
  `sender` text CHARACTER SET utf8 COMMENT '寄件人信息',
  `receiverList` text CHARACTER SET utf8 COMMENT '收件人信息',
  `status` int(8) NOT NULL DEFAULT '20' COMMENT '20-待抢单 30-已抢单（待取件）42-已就位（到达寄件人地址） 44-派送中 60-已完成 64-已取消',
  `create_time` varchar(18) DEFAULT NULL,
  `update_time` varchar(18) DEFAULT NULL,
  `deletet_time` varchar(18) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of shop_shansong_order
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_sign`
-- ----------------------------
DROP TABLE IF EXISTS `shop_sign`;
CREATE TABLE `shop_sign` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(6) NOT NULL,
  `merchant_id` int(4) DEFAULT NULL,
  `sign_id` int(4) DEFAULT NULL COMMENT '签到活动id',
  `user_id` int(4) DEFAULT NULL,
  `pic_url` varchar(256) DEFAULT NULL,
  `status` int(1) DEFAULT '1' COMMENT '1正常签到 2补签',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of shop_sign
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_sign_in`
-- ----------------------------
DROP TABLE IF EXISTS `shop_sign_in`;
CREATE TABLE `shop_sign_in` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(128) NOT NULL COMMENT '活动名称',
  `start_time` varchar(20) NOT NULL COMMENT '开始日期',
  `end_time` varchar(20) NOT NULL COMMENT '结束日期',
  `integral` int(2) NOT NULL COMMENT '每日签到积分',
  `pic_url_activity` varchar(256) NOT NULL COMMENT '活动背景',
  `pic_url_sign` varchar(256) NOT NULL COMMENT '签到默认背景',
  `continuous` int(11) NOT NULL COMMENT '连续签到是否开启',
  `continuous_arr` text NOT NULL COMMENT '连续签到未开始是空obj，连续签到开启包含内容 连续签到天数、获取类型、类型对应的值',
  `remark` varchar(256) NOT NULL COMMENT '签到说明',
  `quotations` varchar(256) NOT NULL COMMENT '打卡语录 字符串',
  `supplementary` int(1) NOT NULL DEFAULT '0' COMMENT '补签是否开启',
  `supplementary_price` float(11,2) NOT NULL COMMENT '单次补签费用',
  `supplementary_number` int(11) NOT NULL COMMENT '最多补签次数',
  `status` int(1) DEFAULT '1' COMMENT '1 : 0,//状态',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到活动';

-- ----------------------------
-- Records of shop_sign_in
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_sign_prize`
-- ----------------------------
DROP TABLE IF EXISTS `shop_sign_prize`;
CREATE TABLE `shop_sign_prize` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(6) NOT NULL,
  `merchant_id` int(4) DEFAULT NULL,
  `sign_id` int(4) DEFAULT NULL COMMENT '签到活动id',
  `user_id` int(4) DEFAULT NULL COMMENT '用户id',
  `days` varchar(256) DEFAULT NULL COMMENT '连续签到天数',
  `give_type` tinyint(1) DEFAULT NULL COMMENT '领取类型1积分2优惠券3实物商品',
  `give_value` varchar(255) DEFAULT NULL COMMENT '领取的奖励',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注，例如发放实物商品的订单号',
  `status` int(1) DEFAULT '1' COMMENT '是否已发放：1 发放 0 未发放',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到奖励表';

-- ----------------------------
-- Table structure for `shop_stock`
-- ----------------------------
DROP TABLE IF EXISTS `shop_stock`;
CREATE TABLE `shop_stock` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `property1_name` varchar(32) DEFAULT NULL COMMENT '第一个属性名称',
  `property2_name` varchar(32) DEFAULT NULL COMMENT '第二个属性名称',
  `name` varchar(256) NOT NULL COMMENT '商品名称',
  `code` varchar(52) DEFAULT NULL COMMENT '商品编码',
  `weight` double(5,2) NOT NULL DEFAULT '0.00' COMMENT '商品重量',
  `number` int(11) unsigned DEFAULT '0' COMMENT '库存数量',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `cost_price` double(10,2) NOT NULL COMMENT '成本价',
  `storehouse_number` int(11) NOT NULL DEFAULT '0' COMMENT '库存量出入库专用',
  `outbound_number` int(11) NOT NULL DEFAULT '0' COMMENT '出库数量',
  `incoming_number` int(11) NOT NULL DEFAULT '0' COMMENT '入库数量',
  `pic_url` varchar(128) NOT NULL DEFAULT '' COMMENT '图片',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=355 DEFAULT CHARSET=utf8 COMMENT='商品库存表';


-- ----------------------------
-- Table structure for `shop_store_payment`
-- ----------------------------
DROP TABLE IF EXISTS `shop_store_payment`;
CREATE TABLE `shop_store_payment` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `sid` int(11) NOT NULL,
  `store` varchar(50) NOT NULL,
  `order_sn` varchar(50) NOT NULL COMMENT '付款订单',
  `money` float(8,2) NOT NULL COMMENT '付款金额',
  `user_id` varchar(255) NOT NULL COMMENT '用户id',
  `nickname` varchar(255) NOT NULL COMMENT '用户昵称',
  `type` varchar(255) NOT NULL COMMENT '付款方式',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='商户-门店付款';



-- ----------------------------
-- Table structure for `shop_storehouse`
-- ----------------------------
DROP TABLE IF EXISTS `shop_storehouse`;
CREATE TABLE `shop_storehouse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用key',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '仓库名',
  `address` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '地址',
  `location` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '经纬度',
  `leader_ids` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '绑定的团长id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 无效 1 有效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='仓库表';



-- ----------------------------
-- Table structure for `shop_super_user`
-- ----------------------------
DROP TABLE IF EXISTS `shop_super_user`;
CREATE TABLE `shop_super_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `key` varchar(10) NOT NULL DEFAULT '' COMMENT '应用key',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '超级会员名称',
  `background_color` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '背景色',
  `icon` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '图标',
  `background_image` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '背景图',
  `condition` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '满足多少消费额',
  `cash_back` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '普通会员佣金（返现）',
  `self_buy` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '自购省钱',
  `recommend_back` decimal(5,2) NOT NULL COMMENT '直推平级分佣',
  `remark` text CHARACTER SET utf8 COMMENT '权益说明',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 关闭 1 开启',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='超级会员设置';



-- ----------------------------
-- Table structure for `shop_supplier`
-- ----------------------------
DROP TABLE IF EXISTS `shop_supplier`;
CREATE TABLE `shop_supplier` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 1=审核成功 0=审核中 2=审核失败',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商-供应商表';

-- ----------------------------
-- Records of shop_supplier
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_suppliers`
-- ----------------------------
DROP TABLE IF EXISTS `shop_suppliers`;
CREATE TABLE `shop_suppliers` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `brand` varchar(32) NOT NULL COMMENT '品牌',
  `mold` varchar(32) NOT NULL COMMENT '类型',
  `city` varchar(32) NOT NULL COMMENT '城市',
  `brand_type` tinyint(1) NOT NULL COMMENT '品牌来源 1=自有 2=供应商',
  `introduce` varchar(256) NOT NULL COMMENT '产品介绍',
  `pic_urls` text COMMENT '产品图片，逗号分割',
  `realname` varchar(12) NOT NULL COMMENT '姓名',
  `phone` char(11) NOT NULL COMMENT '手机',
  `position` varchar(32) NOT NULL COMMENT '职位',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 1=已处理 0=未处理',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商-供应商表';

-- ----------------------------
-- Records of shop_suppliers
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_suppliers_banner`
-- ----------------------------
DROP TABLE IF EXISTS `shop_suppliers_banner`;
CREATE TABLE `shop_suppliers_banner` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(50) NOT NULL COMMENT '横幅名称',
  `pic_url` varchar(255) NOT NULL COMMENT '横幅图片',
  `jump_url` varchar(255) NOT NULL COMMENT '跳转链接',
  `type` tinyint(1) NOT NULL COMMENT '类型 1微信端 2小程序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商户-供货商横幅表';

-- ----------------------------
-- Records of shop_suppliers_banner
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_tuan_config`
-- ----------------------------
DROP TABLE IF EXISTS `shop_tuan_config`;
CREATE TABLE `shop_tuan_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `is_open` tinyint(1) NOT NULL COMMENT '是否开启 1开启',
  `open_time` int(10) NOT NULL COMMENT '开市时间,当天开市的秒数',
  `close_time` int(10) NOT NULL COMMENT '休市时间，当天休市的秒数',
  `close_pic_url` varchar(128) NOT NULL DEFAULT '' COMMENT '休市图片',
  `banner_pic_url` varchar(128) NOT NULL DEFAULT '' COMMENT '轮播图',
  `pic_url` varchar(128) NOT NULL DEFAULT '' COMMENT '图片',
  `is_express` tinyint(1) NOT NULL COMMENT '是否开启快递',
  `is_site` tinyint(1) NOT NULL COMMENT '是否开启自提',
  `is_tuan_express` tinyint(1) NOT NULL COMMENT '是否开启团长送货 1=送货 0=不送货',
  `leader_name` varchar(16) NOT NULL DEFAULT '团长' COMMENT '自定义团长名称',
  `tuan_express_fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '团长送货费用',
  `min_withdraw_money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '最低提现金额',
  `withdraw_fee_ratio` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '提现手续费比例',
  `commission_leader_ratio` float(7,2) NOT NULL DEFAULT '0.00' COMMENT '团长佣金比例',
  `commission_selfleader_ratio` float(7,2) NOT NULL DEFAULT '0.00' COMMENT '自提点佣金比例',
  `commission_user_ratio` float(7,2) NOT NULL DEFAULT '0.00' COMMENT '推荐佣金比例',
  `content` varchar(255) NOT NULL,
  `leader_range` double(10,2) NOT NULL COMMENT '团长范围(千米)',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COMMENT='电商-团购插件-配置表';

INSERT INTO `shop_tuan_config` VALUES ('2', 'ccvWPn', '13', '0', '84480', '86341', 'https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650725075d491c7b828fb.png', 'http://ceshi.juanpao.cn/api/web/./uploads/merchant/shop/goods_picture/13/ccvWPn/15840080945e6a0b9e9d214.jpeg', 'https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15718143215daffbb1f1105.png', '1', '1', '1', '团长', '0.00', '1.00', '10.00', '50.00', '0.00', '5.00', '今日秒杀', '5000.00', '1', '1559387630', '1586609545', null);

-- ----------------------------
-- Table structure for `shop_tuan_leader`
-- ----------------------------
DROP TABLE IF EXISTS `shop_tuan_leader`;
CREATE TABLE `shop_tuan_leader` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `uid` int(10) NOT NULL DEFAULT '1' COMMENT '用户id id=1 用户表不可以有id =1的',
  `storehouse_id` int(11) NOT NULL DEFAULT '0' COMMENT '仓库id',
  `supplier_id` int(10) NOT NULL DEFAULT '0',
  `warehouse_id` int(10) NOT NULL DEFAULT '0' COMMENT '所属仓库id',
  `area_name` varchar(32) NOT NULL COMMENT '小区名称',
  `province_code` char(6) DEFAULT NULL COMMENT '省编码',
  `city_code` char(6) DEFAULT NULL COMMENT '市编码',
  `area_code` char(6) DEFAULT NULL COMMENT '区编码',
  `is_self` tinyint(1) DEFAULT '1' COMMENT '是否申请自提点 1=是 0=否',
  `addr` varchar(128) NOT NULL COMMENT '自提点地址',
  `longitude` double(10,6) NOT NULL COMMENT '精度',
  `latitude` double(10,6) NOT NULL COMMENT '纬度',
  `realname` varchar(16) NOT NULL COMMENT '姓名',
  `tuan_express_fee` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '团长送货费用',
  `is_tuan_express` tinyint(1) NOT NULL COMMENT '是否开启团长送货 1=送货 0=不送货',
  `goods_ids` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `recommend_uid` int(11) NOT NULL DEFAULT '0' COMMENT '推荐人uid',
  `remarks` varchar(512) NOT NULL DEFAULT '' COMMENT '备注',
  `admin_uid` int(11) NOT NULL DEFAULT '0' COMMENT '审核人uid',
  `admin_sub_uid` int(11) NOT NULL DEFAULT '0' COMMENT '审核 子账号uid',
  `partner_id` int(11) NOT NULL DEFAULT '0' COMMENT '合伙人id',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 1=审核成功 0=审核中 2=审核失败 ',
  `state` tinyint(1) DEFAULT '0' COMMENT '0正常 1冻结 2关闭',
  `check_time` int(11) DEFAULT NULL COMMENT '审核时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='电商-团购插件-团长表';



-- ----------------------------
-- Table structure for `shop_tuan_user`
-- ----------------------------
DROP TABLE IF EXISTS `shop_tuan_user`;
CREATE TABLE `shop_tuan_user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `uid` int(10) NOT NULL COMMENT '团员id',
  `is_verify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否核销员 1=是 0=否',
  `leader_uid` int(10) NOT NULL COMMENT '团长id',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=有效 0=无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COMMENT='电商-团购团员表';


-- ----------------------------
-- Table structure for `shop_tuan_warehouse`
-- ----------------------------
DROP TABLE IF EXISTS `shop_tuan_warehouse`;
CREATE TABLE `shop_tuan_warehouse` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `leader_uid` int(11) NOT NULL,
  `name` varchar(128) NOT NULL COMMENT '仓库名称',
  `realname` varchar(16) NOT NULL COMMENT '联系人姓名',
  `phone` char(11) NOT NULL COMMENT '联系电话',
  `addr` varchar(128) NOT NULL COMMENT '仓库地址',
  `longitude` double(10,6) NOT NULL COMMENT '精度',
  `latitude` double(10,6) NOT NULL COMMENT '纬度',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商-团购仓库表';

-- ----------------------------
-- Records of shop_tuan_warehouse
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_unpaid_vip`
-- ----------------------------
DROP TABLE IF EXISTS `shop_unpaid_vip`;
CREATE TABLE `shop_unpaid_vip` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(32) NOT NULL COMMENT '会员等级名称',
  `min_score` int(10) NOT NULL COMMENT '等级积分',
  `discount_ratio` double(5,2) NOT NULL COMMENT '优惠比例',
  `voucher_count` int(10) NOT NULL COMMENT '每月赠送优惠券数量',
  `voucher_type_id` int(10) NOT NULL DEFAULT '0' COMMENT '代金券类型的id',
  `score_times` double(5,2) NOT NULL COMMENT '会员获取积分的倍数 如1.5倍',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COMMENT='电商(非充值)会员配置表';

-- ----------------------------
-- Records of shop_unpaid_vip
-- ----------------------------
INSERT INTO `shop_unpaid_vip` VALUES ('1', 'ccvWPn', '13', '青铜会员', '50', '0.80', '2', '22', '1.20', '1', '1563439556', '1563440072', null);
INSERT INTO `shop_unpaid_vip` VALUES ('2', 'ccvWPn', '13', '白银会员', '500', '0.80', '2', '22', '1.20', '1', '1563439648', '1582782498', null);
INSERT INTO `shop_unpaid_vip` VALUES ('3', 'ccvWPn', '13', '黄金会员', '1000', '0.80', '2', '22', '1.20', '1', '1563439678', '1563439678', null);
INSERT INTO `shop_unpaid_vip` VALUES ('4', 'ccvWPn', '13', '黑铁会员', '10', '0.80', '2', '22', '1.20', '1', '1563439761', '1574675942', null);

-- ----------------------------
-- Table structure for `shop_user`
-- ----------------------------
DROP TABLE IF EXISTS `shop_user`;
CREATE TABLE `shop_user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `union_id` char(32) DEFAULT NULL COMMENT '微信标识符',
  `wx_open_id` char(32) DEFAULT NULL COMMENT '微信openid',
  `mini_open_id` char(32) DEFAULT NULL COMMENT '小程序openid',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` char(32) NOT NULL DEFAULT '' COMMENT '盐',
  `sex` tinyint(1) NOT NULL COMMENT '性别 1=男 2=女',
  `score` int(11) DEFAULT '0' COMMENT '积分',
  `balance` decimal(8,2) DEFAULT '0.00' COMMENT '用户余额(可提现)',
  `recharge_balance` decimal(8,2) DEFAULT '0.00' COMMENT '充值余额(不可提现 可购物)',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '市',
  `area` varchar(20) DEFAULT '' COMMENT '区',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '平台消费金额',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  `is_admin` tinyint(1) DEFAULT '0' COMMENT '是否管理员 1=是 0=否',
  `is_vip` tinyint(1) DEFAULT '0' COMMENT '是否会员 1=会员 0=非会员',
  `is_leader` tinyint(1) DEFAULT '0' COMMENT '是否团长  1=团长 0=非团长 2= 自提点',
  `leader_level` tinyint(2) DEFAULT '0' COMMENT '团长等级',
  `leader_exp` int(10) DEFAULT '0' COMMENT '团长经验值',
  `is_supplier` tinyint(1) DEFAULT '0' COMMENT '是否供应商  1=供应商 0=非供应商',
  `leader_uid` int(8) DEFAULT '0' COMMENT '团长id',
  `vip_validity_time` int(10) NOT NULL DEFAULT '0' COMMENT '会员有效期',
  `type` tinyint(1) NOT NULL COMMENT '来源类型  1=微信端 2=小程序',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=禁用(黑名单)',
  `last_login_time` int(11) NOT NULL COMMENT '最后登录时间',
  `level` int(1) NOT NULL DEFAULT '0' COMMENT '会员等级   0=普通会员  1=超级会员（才能推广） 2=代理商  3=运营商',
  `parent_id` int(11) DEFAULT NULL COMMENT '推荐父id',
  `parent_url` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '父节点路径化  顺序为   父  祖父  曾祖父  曾曾祖父',
  `fan_number` int(11) DEFAULT '0' COMMENT '直推粉丝数',
  `secondhand_fan_number` int(11) DEFAULT '0' COMMENT '非直推粉丝数',
  `commission` decimal(8,2) NOT NULL COMMENT '预估分销佣金',
  `withdrawable_commission` decimal(8,2) NOT NULL COMMENT '可提现分销佣金',
  `up_level` int(11) NOT NULL DEFAULT '0' COMMENT '手动审核的会员等级   0=普通会员  1=超级会员（才能推广） 2=代理商  3=运营商''',
  `is_check` int(8) NOT NULL DEFAULT '1',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='电商用户表';


-- ----------------------------
-- Table structure for `shop_user_balance`
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_balance`;
CREATE TABLE `shop_user_balance` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) NOT NULL COMMENT '用户id,0为系统',
  `supplier_id` int(10) NOT NULL,
  `balance_sn` varchar(18) DEFAULT '0' COMMENT '提现单号',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `order_sn` varchar(18) NOT NULL DEFAULT '' COMMENT '订单编号',
  `fee` decimal(8,2) NOT NULL COMMENT '手续费用',
  `money` decimal(8,2) NOT NULL COMMENT '金额 正数增加 负数消费',
  `remain_money` decimal(8,2) DEFAULT '0.00' COMMENT '到账金额(所得佣金)',
  `content` varchar(128) NOT NULL COMMENT '详细',
  `remarks` varchar(128) NOT NULL COMMENT '备注',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0=余额 1=微信 2=支付宝 3=银行卡',
  `is_recharge_balance` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否充值余额 0=否 1=是',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0=默认 1=团长佣金 2=推荐团长佣金 3=自提点佣金 4=推荐佣金 5=团长奖金 6=配送佣金 7=充值 8=余额下单',
  `realname` varchar(10) NOT NULL COMMENT '姓名（收款人姓名）',
  `pay_number` varchar(32) NOT NULL COMMENT '支付账号',
  `is_send` tinyint(1) DEFAULT '0' COMMENT '是否提现订单 1=是 0=否',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=已结算,0=结算中,2=已拒绝',
  `confirm_time` int(11) DEFAULT NULL COMMENT '确认时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='电商-金额表';

-- ----------------------------
-- Records of shop_user_balance
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_user_cart`
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_cart`;
CREATE TABLE `shop_user_cart` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `user_id` int(11) NOT NULL,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `stock_id` int(11) NOT NULL COMMENT '库存id',
  `supplier_id` int(1) NOT NULL,
  `price` decimal(10,2) NOT NULL COMMENT '商品单价',
  `property1_name` varchar(52) DEFAULT NULL COMMENT '第一个属性值',
  `property2_name` varchar(52) DEFAULT NULL COMMENT '第二个属性值',
  `pic_url` varchar(256) DEFAULT NULL COMMENT '图片地址',
  `number` int(11) NOT NULL COMMENT '数量(默认1)',
  `total_price` decimal(10,2) NOT NULL COMMENT '总价',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户购物车表';


-- ----------------------------
-- Table structure for `shop_user_comment`
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_comment`;
CREATE TABLE `shop_user_comment` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `order_id` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `content` varchar(256) NOT NULL DEFAULT '' COMMENT '评论内容',
  `pics_url` text NOT NULL COMMENT '评论图片地址',
  `describe_score` int(1) DEFAULT '0' COMMENT '描述评分',
  `express_score` int(1) DEFAULT '0' COMMENT '物流评分',
  `service_score` int(1) DEFAULT '0' COMMENT '服务评分',
  `type` tinyint(1) DEFAULT '1' COMMENT '评价 1=好评 2=中评 3=差评',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商-用户评价表';

-- ----------------------------
-- Records of shop_user_comment
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_user_contact`
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_contact`;
CREATE TABLE `shop_user_contact` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '市',
  `area` varchar(20) DEFAULT '' COMMENT '区',
  `street` varchar(50) DEFAULT '' COMMENT '街道',
  `postcode` varchar(10) DEFAULT '000000',
  `address` varchar(255) DEFAULT '' COMMENT '地址',
  `longitude` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `loction_name` varchar(255) NOT NULL COMMENT '定位名称',
  `loction_address` varchar(255) NOT NULL COMMENT '定位地址',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认 1=是 0=否',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='电商用户联系地址表';

-- ----------------------------
-- Table structure for `shop_user_score`
-- ----------------------------
DROP TABLE IF EXISTS `shop_user_score`;
CREATE TABLE `shop_user_score` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `score` int(11) NOT NULL COMMENT '积分',
  `content` varchar(32) NOT NULL COMMENT '详细',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=获取 0=消费',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=正常,0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='电商-用户积分表';



-- ----------------------------
-- Table structure for `shop_uu_account`
-- ----------------------------
DROP TABLE IF EXISTS `shop_uu_account`;
CREATE TABLE `shop_uu_account` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `appid` char(40) NOT NULL COMMENT 'UU账户appid',
  `appkey` char(40) NOT NULL COMMENT 'UU账户appkey',
  `openid` char(40) NOT NULL COMMENT 'UU账户openid',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='商户UU跑腿账号表';

-- ----------------------------
-- Table structure for `shop_uu_order`
-- ----------------------------
DROP TABLE IF EXISTS `shop_uu_order`;
CREATE TABLE `shop_uu_order` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `order_sn` varchar(24) NOT NULL DEFAULT '' COMMENT '订单编码',
  `ordercode` varchar(40) NOT NULL DEFAULT '' COMMENT 'UU跑腿订单号',
  `user_id` int(11) NOT NULL COMMENT '买家ID',
  `user_name` varchar(32) NOT NULL DEFAULT '' COMMENT '买家姓名',
  `user_phone` varchar(25) NOT NULL DEFAULT '' COMMENT '买家电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '买家地址',
  `driver_name` varchar(32) DEFAULT NULL COMMENT 'UU跑男姓名',
  `driver_jobnum` varchar(32) DEFAULT NULL COMMENT 'UU跑男工号',
  `driver_mobile` varchar(32) DEFAULT NULL COMMENT 'UU跑男电话',
  `driver_photo` varchar(255) DEFAULT NULL COMMENT 'UU跑男头像',
  `status` char(5) NOT NULL DEFAULT '' COMMENT 'UU跑腿订单状态 1=下单成功 3=跑男抢单 4=已到达 5=已取件 6=到达目的地 10=收件人已收货 -1=订单取消',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='UU跑腿订单表';

-- ----------------------------
-- Records of shop_uu_order
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_vip`
-- ----------------------------
DROP TABLE IF EXISTS `shop_vip`;
CREATE TABLE `shop_vip` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(32) NOT NULL COMMENT '会员卡名称，例：7天体验、月卡、季卡、年卡',
  `money` decimal(8,2) DEFAULT '0.00' COMMENT '会员卡金额',
  `validity_time` int(10) NOT NULL COMMENT '有效时间，秒数',
  `pay_count` tinyint(3) DEFAULT '0' COMMENT '购买次数 0=不限制',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商会员表';

-- ----------------------------
-- Records of shop_vip
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_vip_access`
-- ----------------------------
DROP TABLE IF EXISTS `shop_vip_access`;
CREATE TABLE `shop_vip_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(10) NOT NULL COMMENT '会员id',
  `vip_id` int(10) NOT NULL COMMENT '会员卡id',
  `money` decimal(8,2) NOT NULL COMMENT '会员卡金额',
  `validity_time` int(10) NOT NULL COMMENT '会员卡有效期',
  `pay_type` tinyint(1) NOT NULL COMMENT '购买类型  1=微信 2=支付宝 5=扫呗',
  `pay_sn` varchar(32) NOT NULL DEFAULT '' COMMENT '支付流水号',
  `transaction_id` varchar(52) DEFAULT NULL COMMENT '第三方支付流水号',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=已支付 0=未支付',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='电商会员购买记录表';

-- ----------------------------
-- Records of shop_vip_access
-- ----------------------------

-- ----------------------------
-- Table structure for `shop_vip_config`
-- ----------------------------
DROP TABLE IF EXISTS `shop_vip_config`;
CREATE TABLE `shop_vip_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `discount_ratio` double(5,2) NOT NULL COMMENT '优惠比例',
  `voucher_count` int(10) NOT NULL COMMENT '每月赠送优惠券数量',
  `voucher_type_id` int(10) NOT NULL DEFAULT '0' COMMENT '代金券类型的id',
  `score_times` double(5,2) NOT NULL COMMENT '会员获取积分的倍数 如1.5倍',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='电商会员配置表';



-- ----------------------------
-- Table structure for `shop_voucher`
-- ----------------------------
DROP TABLE IF EXISTS `shop_voucher`;
CREATE TABLE `shop_voucher` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `order_sn` varchar(32) DEFAULT NULL COMMENT '订单号',
  `cdkey` varchar(32) NOT NULL DEFAULT '' COMMENT '抵用券码',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户uid',
  `goods_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '面值',
  `full_price` int(6) NOT NULL DEFAULT '0' COMMENT '到达满减条件的金额，0为无要求',
  `is_exchange` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否兑换',
  `is_used` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否使用',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始生效时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '生效结束时间',
  `type_name` varchar(16) DEFAULT NULL COMMENT '代金券类型名称',
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT '代金券类型id',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态 0无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='抵用券表';


-- ----------------------------
-- Table structure for `shop_voucher_type`
-- ----------------------------
DROP TABLE IF EXISTS `shop_voucher_type`;
CREATE TABLE `shop_voucher_type` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(16) NOT NULL COMMENT '抵用券名称',
  `price` decimal(10,2) NOT NULL COMMENT '面值(运气红包小面额最大面值实例：2)',
  `min_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小面值(运气红包小面额最小面值实例：1)',
  `lucky_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运气红包面值（大面额最大红包值实例8）',
  `lucky_min_price` decimal(10,2) NOT NULL COMMENT '运气红包最小面值（大面额的最小红包值6）',
  `full_price` decimal(10,2) NOT NULL COMMENT '到达满减条件的金额，0为无要求',
  `receive_count` int(11) NOT NULL DEFAULT '0' COMMENT '领取次数 0为不限制 超过0为次数',
  `send_count` int(11) NOT NULL DEFAULT '0' COMMENT '已发放数量',
  `count` int(11) NOT NULL COMMENT '发放总量',
  `days` int(11) NOT NULL COMMENT '领取后有效天数',
  `from_date` int(11) NOT NULL COMMENT '开始时间',
  `to_date` int(11) NOT NULL COMMENT '结束时间',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=店铺红包 2=新人红包 3=拼手气红包 4类目红包 5商品红包  9签到红包 ',
  `goods_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `collection_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '领取类型 1.自主领取 2.系统发放',
  `set_online_time` int(11) NOT NULL DEFAULT '0' COMMENT '上一次上线时间',
  `status` tinyint(11) NOT NULL DEFAULT '0' COMMENT '状态 0无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='商户-抵用券类型';


-- ----------------------------
-- Table structure for `shop_ylyprint`
-- ----------------------------
DROP TABLE IF EXISTS `shop_ylyprint`;
CREATE TABLE `shop_ylyprint` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(255) NOT NULL COMMENT '打印机名称',
  `apikey` varchar(100) NOT NULL COMMENT 'API密钥',
  `machine_code` varchar(50) NOT NULL COMMENT '打印机终端号',
  `msign` varchar(50) NOT NULL COMMENT '打印机终端密钥',
  `partner` int(11) NOT NULL COMMENT '易联云用户id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1启用 0关闭',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='易联云打印机配置表';



-- ----------------------------
-- Table structure for `system_app`
-- ----------------------------
DROP TABLE IF EXISTS `system_app`;
CREATE TABLE `system_app` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '应用名称',
  `category_id` tinyint(4) NOT NULL COMMENT '类目id',
  `pic_url` varchar(255) NOT NULL COMMENT '应用图片',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `type` tinyint(1) NOT NULL COMMENT '类型 1微信 2小程序 3均有',
  `parent_id` tinyint(4) DEFAULT '0' COMMENT '工具类需绑定父类app_id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='应用表';

-- ----------------------------
-- Records of system_app
-- ----------------------------
INSERT INTO `system_app` VALUES ('1', '圈子', '1', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/app%2F2019%2F02%2F21%2F15507283305c6e3c8a672fe.png', '提供引流涨粉、激发互动等方案,深度挖掘粉丝价值', '3', '0', '0', '1531722160', '1556588673', '1581497286');
INSERT INTO `system_app` VALUES ('2', '社区团购微商城', '2', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/app%2F2019%2F02%2F21%2F15507282835c6e3c5b906a4.png', '微商城，社区团购，生鲜电商，DIY首页', '2', '0', '1', '1541052727', '1559543485', null);

-- ----------------------------
-- Table structure for `system_app_access`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_access`;
CREATE TABLE `system_app_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL DEFAULT '' COMMENT '专属标识符',
  `detail_info` varchar(256) DEFAULT NULL COMMENT '商户应用说明',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '商户应用名称',
  `phone` char(12) DEFAULT NULL COMMENT '客服电话',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '商户应用图片',
  `pic_url_login` varchar(255) DEFAULT '' COMMENT '登录背景图',
  `app_id` tinyint(4) NOT NULL COMMENT '应用id',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `combo_id` tinyint(4) DEFAULT NULL COMMENT '套餐id',
  `config` text NOT NULL COMMENT '配置',
  `expire_time` int(11) NOT NULL COMMENT '到期时间',
  `shop_category_id` int(10) DEFAULT '0' COMMENT '商品类目id',
  `copyright` int(1) NOT NULL DEFAULT '0' COMMENT '自定义版权状态',
  `supplier_phone` varchar(16) DEFAULT NULL COMMENT '供货商电话',
  `leader_phone` varchar(16) DEFAULT NULL COMMENT '团长电话',
  `leader_confirm` int(6) NOT NULL DEFAULT '0' COMMENT '团长自动确认收货天数  超过天数自动确认',
  `leader_send` int(6) NOT NULL DEFAULT '0' COMMENT '团长自动确认发货天数  超过天数自动确认',
  `user_confirm` int(6) NOT NULL DEFAULT '0' COMMENT '用户自动确认收货天数  超过天数自动确认',
  `user_vip` int(1) NOT NULL DEFAULT '0' COMMENT '会员卡  0为全部关闭  1为开启付费会员  2为开启积分会员',
  `pay_info` int(1) NOT NULL DEFAULT '0' COMMENT '购买信息 1为开启  0为关闭',
  `score_shop` int(1) NOT NULL DEFAULT '0' COMMENT '积分商城 1为开启  0为关闭',
  `leader_level` int(1) NOT NULL DEFAULT '0' COMMENT '团长等级 1为开启  0为关闭',
  `my_mini_info` int(1) NOT NULL DEFAULT '0' COMMENT '添加到我的小程序 1为开启  0为关闭',
  `good_phenosphere` int(1) NOT NULL DEFAULT '0' COMMENT '好物圈 1为开启  0为关闭',
  `balance_pay` int(1) NOT NULL DEFAULT '0' COMMENT '余额支付 1为开启  0为关闭',
  `shansong` int(1) NOT NULL DEFAULT '0' COMMENT '闪送  1为开启  0为关闭',
  `is_stock` int(1) NOT NULL DEFAULT '1' COMMENT '库存是否显示 1为是 0为否',
  `is_merchant_info` int(1) NOT NULL DEFAULT '1' COMMENT '是否显示商家信息',
  `is_info_header` int(1) NOT NULL DEFAULT '0' COMMENT '是否显示详情页的头部',
  `is_info_bottom` int(1) NOT NULL DEFAULT '0' COMMENT '是否显示详情页的底部',
  `is_info_header_bottom_goods` int(1) NOT NULL DEFAULT '1' COMMENT '是否显示详情页头部和底部的推荐商品',
  `group_buying` int(1) NOT NULL DEFAULT '1' COMMENT '团购  1为开启  0为关闭',
  `spike` int(1) NOT NULL DEFAULT '1' COMMENT '秒杀  1为开启  0为关闭',
  `sign_in` int(1) NOT NULL DEFAULT '1' COMMENT '签到 1为开启  0为关闭',
  `yly_print` int(1) NOT NULL DEFAULT '0' COMMENT '易联云自动推送  1为开启  0为关闭',
  `bargain_poster` varchar(255) NOT NULL DEFAULT '' COMMENT '砍价海报',
  `bargain_rotation` text NOT NULL COMMENT '砍价轮播图',
  `default_pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '转发默认图',
  `estimated_service_time_info` text COMMENT '预计送达时间',
  `reduction_info` text COMMENT '满减信息(is_estimated:0为关闭,1为开启；reduction_achieve:满多少；reduction_decrease:减多少)',
  `is_recruits` tinyint(1) NOT NULL DEFAULT '0' COMMENT '新人专享 1为开启  0为关闭',
  `is_recruits_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '新用户展示 1为开启  0为关闭',
  `partner_handling_fee` decimal(6,0) NOT NULL DEFAULT '0' COMMENT '合伙人提现手续费',
  `open_partner` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启合伙人设置 1开启 0关闭',
  `uu_is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'UU跑腿  1为开启  0为关闭',
  `dianwoda_is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '点我达  1为开启  0为关闭',
  `store_is_open` tinyint(1) DEFAULT '0' COMMENT '门店开关  1为开启  0为关闭',
  `partner_number` int(30) NOT NULL DEFAULT '10' COMMENT '可创建合伙人数量',
  `coordinate` char(50) DEFAULT '' COMMENT '经纬度',
  `starting_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '起订价',
  `distribution_is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '多级分销升级审核  0关闭 1开启',
  `store_payment` varchar(200) NOT NULL,
  `distribution` varchar(255) NOT NULL DEFAULT '0.00',
  `commissions_pool` decimal(8,2) NOT NULL COMMENT '分销未分配佣金池(每月结算股权后清零,无运营商则不动)',
  `commissions` decimal(8,2) NOT NULL COMMENT '平台分销佣金(每月结算股权之后佣金池剩余部分的累计)',
  `lodop` varchar(200) NOT NULL COMMENT 'lodop的授权码',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 1审核成功 0待审核 2审核失败',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=332 DEFAULT CHARSET=utf8 COMMENT='应用对应表';

-- ----------------------------
-- Records of system_app_access
-- ----------------------------
INSERT INTO `system_app_access` VALUES ('331', 'ccvWPn', '新鲜生活每一天！', '区小蜜', '15950765862', 'http://ceshi.juanpao.cn/api/web/./uploads/merchant/shop/goods_picture/13/ccvWPn/15837421385e65fcbae7660.png', 'http://ceshi.juanpao.cn/api/web/./uploads/merchant/shop/goods_picture/13/ccvWPn/15837421745e65fcde765c4.png', '2', '13', '2', '{\"is_large_scale\":\"1\",\"number\":\"100000\"}', '1590076800', '16', '1', '15950765862', '15950765862', '5', '3', '6', '0', '1', '0', '1', '1', '1', '1', '1', '1', '0', '0', '0', '1', '1', '1', '0', '1', 'https://api.juanpao.com/uploads/bargain_poster.jpg', 'https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650754955d49282751e04.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650805395d493bdb27965.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650035625d480f2a7c8c3.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15748386985dde21aa6d33d.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650754955d49282751e04.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650805395d493bdb27965.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650035625d480f2a7c8c3.jpeg,https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15748386985dde21aa6d33d.jpeg,', 'https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15758787415dee00559781d.png', '{\"is_estimated\":\"0\",\"estimated_type\":\"1\"}', '{\"is_reduction\":\"0\",\"reduction_achieve\":[\"1000\"],\"reduction_decrease\":[\"1\"],\"free_shipping\":[\"false\"]}', '1', '1', '1', '1', '1', '1', '1', '2', '119.22291,34.621236', '10.00', '1', '{\"isopen\":\"true\",\"qrcode\":\"true\"}', '', '0.00', '0.00', '', '0', '1', '1970', '1583839342', null);

-- ----------------------------
-- Table structure for `system_app_access_help`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_access_help`;
CREATE TABLE `system_app_access_help` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `category_id` mediumint(8) NOT NULL COMMENT '分组id',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容(富文本)',
  `page_view` int(8) NOT NULL DEFAULT '0' COMMENT '访问量',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序权重值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='系统-常见问题表';

-- ----------------------------
-- Records of system_app_access_help
-- ----------------------------
INSERT INTO `system_app_access_help` VALUES ('1', 'ccvWPn', '13', '5', '1', '<p><img src=\"https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15646456185d4298f2b7473.jpeg\" width=\"100%\"/></p>', '0', '1', '1', '1565079124', '1575358437', null);
INSERT INTO `system_app_access_help` VALUES ('2', 'ccvWPn', '13', '7', '11', '<p><img src=\"https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15650754955d49282751e04.jpeg\" width=\"100%\"/>123</p>', '0', '2', '0', '1565080304', '1575630978', null);
INSERT INTO `system_app_access_help` VALUES ('3', '000629', '321', '8', '关于货', '<p>撒大声地大师阿萨德阿萨德爱上爱上</p>', '0', '1', '1', '1567960784', '1567960784', null);
INSERT INTO `system_app_access_help` VALUES ('4', 'xxgNZf', '15', '10', '111', '<p>11111111111111111111</p>', '0', '1', '1', '1574406223', '1574406223', null);
INSERT INTO `system_app_access_help` VALUES ('5', 'ccvWPn', '13', '7', 'ww1', '<p>111</p>', '0', '1', '1', '1575358564', '1575358574', '1575358577');
INSERT INTO `system_app_access_help` VALUES ('6', 'ivResp', '15', '14', 'ee', '<p>rrr</p>', '0', '1', '0', '1576313788', '1576314032', '1576314034');
INSERT INTO `system_app_access_help` VALUES ('7', 'ivResp', '15', '16', 'ww', '<p>ee</p>', '0', '1', '1', '1576316346', '1576316848', null);
INSERT INTO `system_app_access_help` VALUES ('8', 'ivResp', '15', '16', 'ddd', '<p>fff<img class=\"wscnph\" src=\"https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F15%2FivResp%2F15763167095df4af253ff86.jpeg\" width=\"300\" /></p>', '0', '1', '1', '1576316729', '1576316846', null);

-- ----------------------------
-- Table structure for `system_app_access_help_category`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_access_help_category`;
CREATE TABLE `system_app_access_help_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(50) NOT NULL COMMENT '分组名称',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '分组排序权重值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='系统-常见问题分组表';



-- ----------------------------
-- Table structure for `system_app_access_version`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_access_version`;
CREATE TABLE `system_app_access_version` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL DEFAULT '' COMMENT '专属标识符',
  `app_id` tinyint(4) NOT NULL COMMENT '应用id',
  `app_access_id` mediumint(8) NOT NULL COMMENT '商户应用对应id',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `combo_id` tinyint(4) DEFAULT NULL COMMENT '套餐id',
  `number` varchar(52) NOT NULL COMMENT '版本号',
  `template_id` varchar(16) DEFAULT NULL COMMENT '模板id,小程序记录',
  `type` tinyint(1) NOT NULL COMMENT '类型 1微信 2小程序',
  `return_id` varchar(52) DEFAULT NULL COMMENT '小程序审核id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1提交成功 0升级中 2提交失败 3=审核中 4=审核发布失败 5审核成功  6发布成功 7发布失败',
  `remarks` varchar(256) DEFAULT NULL COMMENT '备注，例审核失败的原因',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=424 DEFAULT CHARSET=utf8 COMMENT='商户应用版本表';

-- ----------------------------
-- Records of system_app_access_version
-- ----------------------------

-- ----------------------------
-- Table structure for `system_app_category`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_category`;
CREATE TABLE `system_app_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '类目名称',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='应用类目表';

-- ----------------------------
-- Records of system_app_category
-- ----------------------------
INSERT INTO `system_app_category` VALUES ('1', '圈子', '圈子类应用', '1', '1527933831', '1541052647', null);
INSERT INTO `system_app_category` VALUES ('2', '商城', '商城类应用', '1', '1535763840', null, null);

-- ----------------------------
-- Table structure for `system_app_combo`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_combo`;
CREATE TABLE `system_app_combo` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '套餐名称',
  `app_id` tinyint(4) NOT NULL COMMENT '应用id',
  `pic_url` varchar(255) NOT NULL COMMENT '套餐图片',
  `level` tinyint(1) DEFAULT NULL COMMENT '套餐级别',
  `money` decimal(8,2) NOT NULL COMMENT '金额',
  `expired_days` int(10) NOT NULL DEFAULT '0' COMMENT '过期天数',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='应用套餐表';

-- ----------------------------
-- Records of system_app_combo
-- ----------------------------
INSERT INTO `system_app_combo` VALUES ('1', '标准版', '1', 'http://juanpao999-1255754174.cos.cn-south.myqcloud.com/combo%2F2019%2F02%2F21%2F15507275295c6e3969cf61b.png', '3', '0.00', '365', '标准版终身免费', '0', '1524980952', '1556588711', '1581505122');
INSERT INTO `system_app_combo` VALUES ('2', '全功能版', '2', 'https://imgs.juanpao.com/combo%2F2019%2F05%2F31%2F15592961435cf0f88f4e506.png', '2', '0.00', '765', '社区团购小程序', '1', '1541052843', '1559356912', null);


-- ----------------------------
-- Table structure for `system_app_version`
-- ----------------------------
DROP TABLE IF EXISTS `system_app_version`;
CREATE TABLE `system_app_version` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` mediumint(8) NOT NULL COMMENT '应用id 1圈子 2商城',
  `title` varchar(52) NOT NULL COMMENT '标题',
  `simple_info` text NOT NULL COMMENT '描述',
  `content` text NOT NULL COMMENT '内容(富文本)',
  `number` varchar(52) NOT NULL COMMENT '版本号',
  `template_id` varchar(16) DEFAULT NULL COMMENT '模板id,小程序记录',
  `type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '类型 1微信 2小程序 （默认小程序）',
  `ext_json` text COMMENT '小程序的ext_json',
  `show_status` tinyint(1) NOT NULL COMMENT '是否显示 1显示 0隐藏',
  `update_status` tinyint(1) NOT NULL COMMENT '建议更新 1建议 0不建议',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='应用版本表';

-- ----------------------------
-- Records of system_app_version
-- ----------------------------

-- ----------------------------
-- Table structure for `system_area`
-- ----------------------------
DROP TABLE IF EXISTS `system_area`;
CREATE TABLE `system_area` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `code` int(6) NOT NULL COMMENT '地区编码',
  `name` varchar(52) NOT NULL COMMENT '地区名称',
  `parent_id` int(6) NOT NULL DEFAULT '0' COMMENT '父类id',
  `level` tinyint(1) DEFAULT NULL COMMENT '级别 1=省 2=市 3=区',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1=正常 0=禁用',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3256 DEFAULT CHARSET=utf8mb4 COMMENT='系统-省市区表';

-- ----------------------------
-- Records of system_area
-- ----------------------------
INSERT INTO `system_area` VALUES ('1', '410000', '河南省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('2', '410900', '濮阳市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('3', '411100', '漯河市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('4', '411200', '三门峡市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('5', '410300', '洛阳市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('6', '411300', '南阳市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('7', '411000', '许昌市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('8', '411500', '信阳市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('9', '411700', '驻马店市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('10', '419001', '济源市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('11', '410500', '安阳市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('12', '410800', '焦作市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('13', '410600', '鹤壁市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('14', '410700', '新乡市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('15', '410200', '开封市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('16', '410400', '平顶山市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('17', '410100', '郑州市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('18', '411600', '周口市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('19', '411400', '商丘市', '410000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('20', '440000', '广东省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('21', '440500', '汕头市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('22', '440600', '佛山市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('23', '441200', '肇庆市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('24', '441300', '惠州市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('25', '440300', '深圳市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('26', '440800', '湛江市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('27', '440400', '珠海市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('28', '441700', '阳江市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('29', '440700', '江门市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('30', '442100', '东沙群岛', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('31', '440900', '茂名市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('32', '445100', '潮州市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('33', '441500', '汕尾市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('34', '445300', '云浮市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('35', '441600', '河源市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('36', '441400', '梅州市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('37', '440100', '广州市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('38', '440200', '韶关市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('39', '441900', '东莞市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('40', '445200', '揭阳市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('41', '441800', '清远市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('42', '442000', '中山市', '440000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('43', '150000', '内蒙古自治区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('44', '150300', '乌海市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('45', '150800', '巴彦淖尔市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('46', '150200', '包头市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('47', '150700', '呼伦贝尔市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('48', '150600', '鄂尔多斯市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('49', '152900', '阿拉善盟', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('50', '150400', '赤峰市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('51', '150500', '通辽市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('52', '152200', '兴安盟', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('53', '150900', '乌兰察布市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('54', '152500', '锡林郭勒盟', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('55', '150100', '呼和浩特市', '150000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('56', '230000', '黑龙江省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('57', '232700', '大兴安岭地区', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('58', '230900', '七台河市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('59', '230400', '鹤岗市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('60', '230700', '伊春市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('61', '231200', '绥化市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('62', '230100', '哈尔滨市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('63', '231100', '黑河市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('64', '230200', '齐齐哈尔市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('65', '231000', '牡丹江市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('66', '230300', '鸡西市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('67', '230600', '大庆市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('68', '230500', '双鸭山市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('69', '230800', '佳木斯市', '230000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('70', '650000', '新疆维吾尔自治区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('71', '659005', '北屯市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('72', '659006', '铁门关市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('73', '659007', '双河市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('74', '652700', '博尔塔拉蒙古自治州', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('75', '659008', '可克达拉市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('76', '654200', '塔城地区', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('77', '653200', '和田地区', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('78', '659009', '昆玉市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('79', '654300', '阿勒泰地区', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('80', '650200', '克拉玛依市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('81', '659001', '石河子市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('82', '652300', '昌吉回族自治州', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('83', '659004', '五家渠市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('84', '652800', '巴音郭楞蒙古自治州', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('85', '650100', '乌鲁木齐市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('86', '654000', '伊犁哈萨克自治州', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('87', '652900', '阿克苏地区', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('88', '659002', '阿拉尔市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('89', '653100', '喀什地区', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('90', '659003', '图木舒克市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('91', '653000', '克孜勒苏柯尔克孜自治州', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('92', '650500', '哈密市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('93', '650400', '吐鲁番市', '650000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('94', '420000', '湖北省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('95', '420300', '十堰市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('96', '420600', '襄阳市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('97', '420800', '荆门市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('98', '420500', '宜昌市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('99', '420100', '武汉市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('100', '421100', '黄冈市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('101', '429006', '天门市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('102', '420900', '孝感市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('103', '429005', '潜江市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('104', '422800', '恩施土家族苗族自治州', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('105', '429004', '仙桃市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('106', '421000', '荆州市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('107', '421200', '咸宁市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('108', '429021', '神农架林区', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('109', '421300', '随州市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('110', '420700', '鄂州市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('111', '420200', '黄石市', '420000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('112', '210000', '辽宁省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('113', '211400', '葫芦岛市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('114', '210200', '大连市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('115', '210600', '丹东市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('116', '210700', '锦州市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('117', '210400', '抚顺市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('118', '210100', '沈阳市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('119', '211200', '铁岭市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('120', '211300', '朝阳市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('121', '211000', '辽阳市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('122', '210300', '鞍山市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('123', '210800', '营口市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('124', '210900', '阜新市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('125', '211100', '盘锦市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('126', '210500', '本溪市', '210000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('127', '370000', '山东省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('128', '370600', '烟台市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('129', '371000', '威海市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('130', '370200', '青岛市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('131', '370300', '淄博市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('132', '371500', '聊城市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('133', '371300', '临沂市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('134', '370700', '潍坊市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('135', '370500', '东营市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('136', '371600', '滨州市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('137', '370400', '枣庄市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('138', '371100', '日照市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('139', '370800', '济宁市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('140', '370900', '泰安市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('141', '371400', '德州市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('142', '370100', '济南市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('143', '371700', '菏泽市', '370000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('144', '320000', '江苏省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('145', '320700', '连云港市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('146', '321300', '宿迁市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('147', '320100', '南京市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('148', '321100', '镇江市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('149', '320600', '南通市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('150', '320800', '淮安市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('151', '320300', '徐州市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('152', '320900', '盐城市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('153', '321200', '泰州市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('154', '321000', '扬州市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('155', '320200', '无锡市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('156', '320400', '常州市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('157', '320500', '苏州市', '320000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('158', '610000', '陕西省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('159', '611000', '商洛市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('160', '610100', '西安市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('161', '610700', '汉中市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('162', '610200', '铜川市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('163', '610900', '安康市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('164', '610800', '榆林市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('165', '610600', '延安市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('166', '610300', '宝鸡市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('167', '610400', '咸阳市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('168', '610500', '渭南市', '610000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('169', '310000', '上海市', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('170', '310100', '上海城区', '310000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('171', '520000', '贵州省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('172', '520300', '遵义市', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('173', '522600', '黔东南苗族侗族自治州', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('174', '520200', '六盘水市', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('175', '520600', '铜仁市', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('176', '522700', '黔南布依族苗族自治州', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('177', '522300', '黔西南布依族苗族自治州', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('178', '520400', '安顺市', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('179', '520500', '毕节市', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('180', '520100', '贵阳市', '520000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('181', '500000', '重庆市', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('182', '500200', '重庆郊县', '500000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('183', '500100', '重庆城区', '500000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('184', '540000', '西藏自治区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('185', '540300', '昌都市', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('186', '540600', '那曲市', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('187', '540100', '拉萨市', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('188', '540200', '日喀则市', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('189', '540500', '山南市', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('190', '540400', '林芝市', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('191', '542500', '阿里地区', '540000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('192', '340000', '安徽省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('193', '340600', '淮北市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('194', '341200', '阜阳市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('195', '340500', '马鞍山市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('196', '341700', '池州市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('197', '340700', '铜陵市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('198', '341600', '亳州市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('199', '340300', '蚌埠市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('200', '341100', '滁州市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('201', '340800', '安庆市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('202', '341800', '宣城市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('203', '341500', '六安市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('204', '341000', '黄山市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('205', '340400', '淮南市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('206', '340100', '合肥市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('207', '341300', '宿州市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('208', '340200', '芜湖市', '340000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('209', '350000', '福建省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('210', '350900', '宁德市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('211', '350100', '福州市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('212', '350800', '龙岩市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('213', '350300', '莆田市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('214', '350500', '泉州市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('215', '350200', '厦门市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('216', '350600', '漳州市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('217', '350700', '南平市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('218', '350400', '三明市', '350000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('219', '430000', '湖南省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('220', '430600', '岳阳市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('221', '430900', '益阳市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('222', '430400', '衡阳市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('223', '431300', '娄底市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('224', '430100', '长沙市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('225', '430800', '张家界市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('226', '431200', '怀化市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('227', '433100', '湘西土家族苗族自治州', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('228', '430700', '常德市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('229', '430200', '株洲市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('230', '430500', '邵阳市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('231', '430300', '湘潭市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('232', '431100', '永州市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('233', '431000', '郴州市', '430000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('234', '460000', '海南省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('235', '469024', '临高县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('236', '469021', '定安县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('237', '469025', '白沙黎族自治县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('238', '469026', '昌江黎族自治县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('239', '469006', '万宁市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('240', '469022', '屯昌县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('241', '469030', '琼中黎族苗族自治县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('242', '469002', '琼海市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('243', '469007', '东方市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('244', '469027', '乐东黎族自治县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('245', '469001', '五指山市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('246', '469029', '保亭黎族苗族自治县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('247', '460400', '儋州市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('248', '469005', '文昌市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('249', '469028', '陵水黎族自治县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('250', '460300', '三沙市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('251', '460200', '三亚市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('252', '469023', '澄迈县', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('253', '460100', '海口市', '460000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('254', '630000', '青海省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('255', '630200', '海东市', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('256', '632500', '海南藏族自治州', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('257', '632800', '海西蒙古族藏族自治州', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('258', '632700', '玉树藏族自治州', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('259', '632300', '黄南藏族自治州', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('260', '632600', '果洛藏族自治州', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('261', '630100', '西宁市', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('262', '632200', '海北藏族自治州', '630000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('263', '450000', '广西壮族自治区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('264', '451000', '百色市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('265', '450700', '钦州市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('266', '450500', '北海市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('267', '450300', '桂林市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('268', '451200', '河池市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('269', '450200', '柳州市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('270', '451300', '来宾市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('271', '450100', '南宁市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('272', '451400', '崇左市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('273', '450600', '防城港市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('274', '450400', '梧州市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('275', '451100', '贺州市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('276', '450900', '玉林市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('277', '450800', '贵港市', '450000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('278', '640000', '宁夏回族自治区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('279', '640400', '固原市', '640000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('280', '640100', '银川市', '640000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('281', '640500', '中卫市', '640000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('282', '640200', '石嘴山市', '640000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('283', '640300', '吴忠市', '640000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('284', '360000', '江西省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('285', '360400', '九江市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('286', '360500', '新余市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('287', '361000', '抚州市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('288', '360600', '鹰潭市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('289', '360700', '赣州市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('290', '360100', '南昌市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('291', '360900', '宜春市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('292', '360800', '吉安市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('293', '360300', '萍乡市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('294', '360200', '景德镇市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('295', '361100', '上饶市', '360000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('296', '330000', '浙江省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('297', '330900', '舟山市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('298', '330200', '宁波市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('299', '330400', '嘉兴市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('300', '331000', '台州市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('301', '330300', '温州市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('302', '331100', '丽水市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('303', '330100', '杭州市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('304', '330600', '绍兴市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('305', '330500', '湖州市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('306', '330800', '衢州市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('307', '330700', '金华市', '330000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('308', '130000', '河北省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('309', '130200', '唐山市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('310', '130800', '承德市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('311', '131000', '廊坊市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('312', '130300', '秦皇岛市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('313', '130600', '保定市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('314', '130100', '石家庄市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('315', '130400', '邯郸市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('316', '130500', '邢台市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('317', '130700', '张家口市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('318', '130900', '沧州市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('319', '131100', '衡水市', '130000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('320', '810000', '香港特别行政区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('321', '810013', '北区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('322', '810014', '大埔区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('323', '810015', '西贡区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('324', '810016', '沙田区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('325', '810011', '屯门区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('326', '810008', '黄大仙区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('327', '810007', '九龙城区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('328', '810006', '深水埗区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('329', '810009', '观塘区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('330', '810005', '油尖旺区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('331', '810018', '离岛区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('332', '810003', '东区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('333', '810001', '中西区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('334', '810002', '湾仔区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('335', '810004', '南区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('336', '810012', '元朗区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('337', '810010', '荃湾区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('338', '810017', '葵青区', '810000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('339', '710000', '台湾省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('340', '820000', '澳门特别行政区', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('341', '820004', '大堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('342', '820003', '望德堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('343', '820008', '圣方济各堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('344', '820006', '嘉模堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('345', '820005', '风顺堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('346', '820002', '花王堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('347', '820001', '花地玛堂区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('348', '820007', '路凼填海区', '820000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('349', '620000', '甘肃省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('350', '620200', '嘉峪关市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('351', '620900', '酒泉市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('352', '620300', '金昌市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('353', '620100', '兰州市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('354', '620800', '平凉市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('355', '620400', '白银市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('356', '620500', '天水市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('357', '620600', '武威市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('358', '621200', '陇南市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('359', '623000', '甘南藏族自治州', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('360', '622900', '临夏回族自治州', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('361', '620700', '张掖市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('362', '621000', '庆阳市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('363', '621100', '定西市', '620000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('364', '510000', '四川省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('365', '510800', '广元市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('366', '511300', '南充市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('367', '511900', '巴中市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('368', '510600', '德阳市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('369', '510700', '绵阳市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('370', '510100', '成都市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('371', '511600', '广安市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('372', '511700', '达州市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('373', '510900', '遂宁市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('374', '512000', '资阳市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('375', '511400', '眉山市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('376', '511000', '内江市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('377', '510300', '自贡市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('378', '511100', '乐山市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('379', '510500', '泸州市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('380', '513400', '凉山彝族自治州', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('381', '511500', '宜宾市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('382', '510400', '攀枝花市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('383', '513200', '阿坝藏族羌族自治州', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('384', '511800', '雅安市', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('385', '513300', '甘孜藏族自治州', '510000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('386', '220000', '吉林省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('387', '220200', '吉林市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('388', '220100', '长春市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('389', '220800', '白城市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('390', '220700', '松原市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('391', '220400', '辽源市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('392', '220300', '四平市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('393', '222400', '延边朝鲜族自治州', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('394', '220600', '白山市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('395', '220500', '通化市', '220000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('396', '120000', '天津市', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('397', '120100', '天津城区', '120000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('398', '530000', '云南省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('399', '530600', '昭通市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('400', '530300', '曲靖市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('401', '532500', '红河哈尼族彝族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('402', '533300', '怒江傈僳族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('403', '532800', '西双版纳傣族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('404', '530400', '玉溪市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('405', '532900', '大理白族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('406', '530700', '丽江市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('407', '533400', '迪庆藏族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('408', '532600', '文山壮族苗族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('409', '530500', '保山市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('410', '530800', '普洱市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('411', '530100', '昆明市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('412', '532300', '楚雄彝族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('413', '530900', '临沧市', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('414', '533100', '德宏傣族景颇族自治州', '530000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('415', '110000', '北京市', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('416', '110100', '北京城区', '110000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('417', '140000', '山西省', '100000', '1', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('418', '140300', '阳泉市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('419', '140400', '长治市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('420', '141000', '临汾市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('421', '140100', '太原市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('422', '140800', '运城市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('423', '140900', '忻州市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('424', '140600', '朔州市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('425', '140500', '晋城市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('426', '140700', '晋中市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('427', '141100', '吕梁市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('428', '140200', '大同市', '140000', '2', '1', '1554254202', '1554254202', null);
INSERT INTO `system_area` VALUES ('429', '410923', '南乐县', '410900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('430', '410927', '台前县', '410900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('431', '410922', '清丰县', '410900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('432', '410926', '范县', '410900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('433', '410902', '华龙区', '410900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('434', '410928', '濮阳县', '410900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('435', '411122', '临颍县', '411100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('436', '411102', '源汇区', '411100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('437', '411121', '舞阳县', '411100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('438', '411104', '召陵区', '411100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('439', '411103', '郾城区', '411100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('440', '411202', '湖滨区', '411200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('441', '411281', '义马市', '411200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('442', '411221', '渑池县', '411200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('443', '411224', '卢氏县', '411200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('444', '411282', '灵宝市', '411200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('445', '411203', '陕州区', '411200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('446', '410323', '新安县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('447', '410324', '栾川县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('448', '410326', '汝阳县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('449', '410305', '涧西区', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('450', '410329', '伊川县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('451', '410327', '宜阳县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('452', '410306', '吉利区', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('453', '410328', '洛宁县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('454', '410381', '偃师市', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('455', '410304', '瀍河回族区', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('456', '410311', '洛龙区', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('457', '410302', '老城区', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('458', '410303', '西工区', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('459', '410325', '嵩县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('460', '410322', '孟津县', '410300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('461', '411323', '西峡县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('462', '411303', '卧龙区', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('463', '411321', '南召县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('464', '411326', '淅川县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('465', '411327', '社旗县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('466', '411330', '桐柏县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('467', '411328', '唐河县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('468', '411325', '内乡县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('469', '411302', '宛城区', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('470', '411324', '镇平县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('471', '411329', '新野县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('472', '411322', '方城县', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('473', '411381', '邓州市', '411300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('474', '411003', '建安区', '411000', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('475', '411025', '襄城县', '411000', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('476', '411002', '魏都区', '411000', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('477', '411082', '长葛市', '411000', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('478', '411081', '禹州市', '411000', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('479', '411024', '鄢陵县', '411000', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('480', '411526', '潢川县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('481', '411527', '淮滨县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('482', '411521', '罗山县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('483', '411522', '光山县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('484', '411502', '浉河区', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('485', '411525', '固始县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('486', '411524', '商城县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('487', '411523', '新县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('488', '411528', '息县', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('489', '411503', '平桥区', '411500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('490', '411722', '上蔡县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('491', '411721', '西平县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('492', '411729', '新蔡县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('493', '411723', '平舆县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('494', '411727', '汝南县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('495', '411726', '泌阳县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('496', '411728', '遂平县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('497', '411702', '驿城区', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('498', '411725', '确山县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('499', '411724', '正阳县', '411700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('500', '410505', '殷都区', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('501', '410527', '内黄县', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('502', '410523', '汤阴县', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('503', '410526', '滑县', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('504', '410502', '文峰区', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('505', '410503', '北关区', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('506', '410581', '林州市', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('507', '410506', '龙安区', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('508', '410522', '安阳县', '410500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('509', '410804', '马村区', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('510', '410802', '解放区', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('511', '410882', '沁阳市', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('512', '410825', '温县', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('513', '410883', '孟州市', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('514', '410822', '博爱县', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('515', '410811', '山阳区', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('516', '410823', '武陟县', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('517', '410803', '中站区', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('518', '410821', '修武县', '410800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('519', '410602', '鹤山区', '410600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('520', '410622', '淇县', '410600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('521', '410603', '山城区', '410600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('522', '410621', '浚县', '410600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('523', '410611', '淇滨区', '410600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('524', '410704', '凤泉区', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('525', '410724', '获嘉县', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('526', '410782', '辉县市', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('527', '410727', '封丘县', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('528', '410726', '延津县', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('529', '410728', '长垣县', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('530', '410725', '原阳县', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('531', '410711', '牧野区', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('532', '410781', '卫辉市', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('533', '410721', '新乡县', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('534', '410702', '红旗区', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('535', '410703', '卫滨区', '410700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('536', '410225', '兰考县', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('537', '410212', '祥符区', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('538', '410203', '顺河回族区', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('539', '410205', '禹王台区', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('540', '410223', '尉氏县', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('541', '410222', '通许县', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('542', '410204', '鼓楼区', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('543', '410202', '龙亭区', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('544', '410221', '杞县', '410200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('545', '410425', '郏县', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('546', '410404', '石龙区', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('547', '410411', '湛河区', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('548', '410403', '卫东区', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('549', '410423', '鲁山县', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('550', '410422', '叶县', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('551', '410481', '舞钢市', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('552', '410402', '新华区', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('553', '410421', '宝丰县', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('554', '410482', '汝州市', '410400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('555', '410181', '巩义市', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('556', '410185', '登封市', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('557', '410105', '金水区', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('558', '410106', '上街区', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('559', '410182', '荥阳市', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('560', '410108', '惠济区', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('561', '410102', '中原区', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('562', '410103', '二七区', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('563', '410183', '新密市', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('564', '410122', '中牟县', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('565', '410104', '管城回族区', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('566', '410184', '新郑市', '410100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('567', '411628', '鹿邑县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('568', '411624', '沈丘县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('569', '411625', '郸城县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('570', '411621', '扶沟县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('571', '411626', '淮阳县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('572', '411681', '项城市', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('573', '411627', '太康县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('574', '411623', '商水县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('575', '411602', '川汇区', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('576', '411622', '西华县', '411600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('577', '411423', '宁陵县', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('578', '411424', '柘城县', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('579', '411402', '梁园区', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('580', '411403', '睢阳区', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('581', '411426', '夏邑县', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('582', '411481', '永城市', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('583', '411421', '民权县', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('584', '411422', '睢县', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('585', '411425', '虞城县', '411400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('586', '440513', '潮阳区', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('587', '440515', '澄海区', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('588', '440523', '南澳县', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('589', '440512', '濠江区', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('590', '440514', '潮南区', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('591', '440507', '龙湖区', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('592', '440511', '金平区', '440500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('593', '440607', '三水区', '440600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('594', '440608', '高明区', '440600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('595', '440606', '顺德区', '440600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('596', '440604', '禅城区', '440600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('597', '440605', '南海区', '440600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('598', '441224', '怀集县', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('599', '441284', '四会市', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('600', '441223', '广宁县', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('601', '441225', '封开县', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('602', '441226', '德庆县', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('603', '441203', '鼎湖区', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('604', '441204', '高要区', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('605', '441202', '端州区', '441200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('606', '441324', '龙门县', '441300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('607', '441322', '博罗县', '441300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('608', '441323', '惠东县', '441300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('609', '441303', '惠阳区', '441300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('610', '441302', '惠城区', '441300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('611', '440306', '宝安区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('612', '440305', '南山区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('613', '440304', '福田区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('614', '440308', '盐田区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('615', '440303', '罗湖区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('616', '440310', '坪山区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('617', '440309', '龙华区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('618', '440307', '龙岗区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('619', '440311', '光明区', '440300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('620', '440883', '吴川市', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('621', '440881', '廉江市', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('622', '440803', '霞山区', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('623', '440882', '雷州市', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('624', '440811', '麻章区', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('625', '440825', '徐闻县', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('626', '440804', '坡头区', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('627', '440802', '赤坎区', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('628', '440823', '遂溪县', '440800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('629', '440403', '斗门区', '440400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('630', '440402', '香洲区', '440400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('631', '440404', '金湾区', '440400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('632', '441781', '阳春市', '441700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('633', '441702', '江城区', '441700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('634', '441721', '阳西县', '441700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('635', '441704', '阳东区', '441700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('636', '440784', '鹤山市', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('637', '440704', '江海区', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('638', '440783', '开平市', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('639', '440781', '台山市', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('640', '440785', '恩平市', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('641', '440703', '蓬江区', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('642', '440705', '新会区', '440700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('643', '440983', '信宜市', '440900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('644', '440981', '高州市', '440900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('645', '440904', '电白区', '440900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('646', '440982', '化州市', '440900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('647', '440902', '茂南区', '440900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('648', '445122', '饶平县', '445100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('649', '445102', '湘桥区', '445100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('650', '445103', '潮安区', '445100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('651', '441523', '陆河县', '441500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('652', '441581', '陆丰市', '441500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('653', '441521', '海丰县', '441500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('654', '441502', '城区', '441500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('655', '445322', '郁南县', '445300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('656', '445381', '罗定市', '445300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('657', '445321', '新兴县', '445300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('658', '445303', '云安区', '445300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('659', '445302', '云城区', '445300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('660', '441622', '龙川县', '441600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('661', '441624', '和平县', '441600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('662', '441623', '连平县', '441600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('663', '441625', '东源县', '441600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('664', '441621', '紫金县', '441600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('665', '441602', '源城区', '441600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('666', '441426', '平远县', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('667', '441427', '蕉岭县', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('668', '441481', '兴宁市', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('669', '441424', '五华县', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('670', '441423', '丰顺县', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('671', '441403', '梅县区', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('672', '441422', '大埔县', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('673', '441402', '梅江区', '441400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('674', '440117', '从化区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('675', '440118', '增城区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('676', '440115', '南沙区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('677', '440114', '花都区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('678', '440112', '黄埔区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('679', '440106', '天河区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('680', '440111', '白云区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('681', '440104', '越秀区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('682', '440105', '海珠区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('683', '440103', '荔湾区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('684', '440113', '番禺区', '440100', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('685', '440224', '仁化县', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('686', '440282', '南雄市', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('687', '440232', '乳源瑶族自治县', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('688', '440222', '始兴县', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('689', '440204', '浈江区', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('690', '440203', '武江区', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('691', '440205', '曲江区', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('692', '440229', '翁源县', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('693', '440233', '新丰县', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('694', '440281', '乐昌市', '440200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('695', '445222', '揭西县', '445200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('696', '445281', '普宁市', '445200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('697', '445224', '惠来县', '445200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('698', '445202', '榕城区', '445200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('699', '445203', '揭东区', '445200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('700', '441882', '连州市', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('701', '441826', '连南瑶族自治县', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('702', '441825', '连山壮族瑶族自治县', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('703', '441881', '英德市', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('704', '441821', '佛冈县', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('705', '441823', '阳山县', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('706', '441803', '清新区', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('707', '441802', '清城区', '441800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('708', '150304', '乌达区', '150300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('709', '150303', '海南区', '150300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('710', '150302', '海勃湾区', '150300', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('711', '150824', '乌拉特中旗', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('712', '150821', '五原县', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('713', '150802', '临河区', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('714', '150822', '磴口县', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('715', '150826', '杭锦后旗', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('716', '150823', '乌拉特前旗', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('717', '150825', '乌拉特后旗', '150800', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('718', '150221', '土默特右旗', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('719', '150223', '达尔罕茂明安联合旗', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('720', '150206', '白云鄂博矿区', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('721', '150207', '九原区', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('722', '150203', '昆都仑区', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('723', '150222', '固阳县', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('724', '150205', '石拐区', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('725', '150202', '东河区', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('726', '150204', '青山区', '150200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('727', '150784', '额尔古纳市', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('728', '150782', '牙克石市', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('729', '150785', '根河市', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('730', '150725', '陈巴尔虎旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('731', '150722', '莫力达瓦达斡尔族自治旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('732', '150721', '阿荣旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('733', '150702', '海拉尔区', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('734', '150724', '鄂温克族自治旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('735', '150783', '扎兰屯市', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('736', '150781', '满洲里市', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('737', '150703', '扎赉诺尔区', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('738', '150727', '新巴尔虎右旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('739', '150726', '新巴尔虎左旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('740', '150723', '鄂伦春自治旗', '150700', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('741', '150625', '杭锦旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('742', '150621', '达拉特旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('743', '150622', '准格尔旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('744', '150624', '鄂托克旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('745', '150626', '乌审旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('746', '150623', '鄂托克前旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('747', '150627', '伊金霍洛旗', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('748', '150603', '康巴什区', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('749', '150602', '东胜区', '150600', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('750', '152923', '额济纳旗', '152900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('751', '152922', '阿拉善右旗', '152900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('752', '152921', '阿拉善左旗', '152900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('753', '150421', '阿鲁科尔沁旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('754', '150422', '巴林左旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('755', '150423', '巴林右旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('756', '150424', '林西县', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('757', '150425', '克什克腾旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('758', '150426', '翁牛特旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('759', '150404', '松山区', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('760', '150429', '宁城县', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('761', '150402', '红山区', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('762', '150428', '喀喇沁旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('763', '150403', '元宝山区', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('764', '150430', '敖汉旗', '150400', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('765', '150581', '霍林郭勒市', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('766', '150526', '扎鲁特旗', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('767', '150521', '科尔沁左翼中旗', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('768', '150523', '开鲁县', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('769', '150502', '科尔沁区', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('770', '150525', '奈曼旗', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('771', '150524', '库伦旗', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('772', '150522', '科尔沁左翼后旗', '150500', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('773', '152202', '阿尔山市', '152200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('774', '152223', '扎赉特旗', '152200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('775', '152222', '科尔沁右翼中旗', '152200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('776', '152224', '突泉县', '152200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('777', '152201', '乌兰浩特市', '152200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('778', '152221', '科尔沁右翼前旗', '152200', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('779', '150929', '四子王旗', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('780', '150923', '商都县', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('781', '150922', '化德县', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('782', '150921', '卓资县', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('783', '150927', '察哈尔右翼中旗', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('784', '150928', '察哈尔右翼后旗', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('785', '150924', '兴和县', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('786', '150981', '丰镇市', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('787', '150925', '凉城县', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('788', '150902', '集宁区', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('789', '150926', '察哈尔右翼前旗', '150900', '3', '1', '1554254246', '1554254246', null);
INSERT INTO `system_area` VALUES ('790', '152525', '东乌珠穆沁旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('791', '152522', '阿巴嘎旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('792', '152526', '西乌珠穆沁旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('793', '152523', '苏尼特左旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('794', '152502', '锡林浩特市', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('795', '152501', '二连浩特市', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('796', '152524', '苏尼特右旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('797', '152530', '正蓝旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('798', '152529', '正镶白旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('799', '152528', '镶黄旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('800', '152531', '多伦县', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('801', '152527', '太仆寺旗', '152500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('802', '150123', '和林格尔县', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('803', '150103', '回民区', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('804', '150104', '玉泉区', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('805', '150122', '托克托县', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('806', '150125', '武川县', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('807', '150121', '土默特左旗', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('808', '150102', '新城区', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('809', '150105', '赛罕区', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('810', '150124', '清水河县', '150100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('811', '232701', '漠河市', '232700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('812', '232722', '塔河县', '232700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('813', '232721', '呼玛县', '232700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('814', '232718', '加格达奇区', '232700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('815', '230903', '桃山区', '230900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('816', '230904', '茄子河区', '230900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('817', '230902', '新兴区', '230900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('818', '230921', '勃利县', '230900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('819', '230422', '绥滨县', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('820', '230403', '工农区', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('821', '230405', '兴安区', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('822', '230407', '兴山区', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('823', '230404', '南山区', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('824', '230402', '向阳区', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('825', '230421', '萝北县', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('826', '230406', '东山区', '230400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('827', '230722', '嘉荫县', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('828', '230714', '乌伊岭区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('829', '230712', '汤旺河区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('830', '230715', '红星区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('831', '230707', '新青区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('832', '230704', '友好区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('833', '230710', '五营区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('834', '230716', '上甘岭区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('835', '230708', '美溪区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('836', '230711', '乌马河区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('837', '230706', '翠峦区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('838', '230709', '金山屯区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('839', '230702', '伊春区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('840', '230705', '西林区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('841', '230781', '铁力市', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('842', '230713', '带岭区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('843', '230703', '南岔区', '230700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('844', '231226', '绥棱县', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('845', '231283', '海伦市', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('846', '231224', '庆安县', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('847', '231202', '北林区', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('848', '231221', '望奎县', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('849', '231223', '青冈县', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('850', '231225', '明水县', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('851', '231222', '兰西县', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('852', '231282', '肇东市', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('853', '231281', '安达市', '231200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('854', '230126', '巴彦县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('855', '230123', '依兰县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('856', '230128', '通河县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('857', '230127', '木兰县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('858', '230124', '方正县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('859', '230113', '双城区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('860', '230129', '延寿县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('861', '230108', '平房区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('862', '230125', '宾县', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('863', '230183', '尚志市', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('864', '230110', '香坊区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('865', '230102', '道里区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('866', '230184', '五常市', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('867', '230112', '阿城区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('868', '230103', '南岗区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('869', '230104', '道外区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('870', '230109', '松北区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('871', '230111', '呼兰区', '230100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('872', '231121', '嫩江县', '231100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('873', '231102', '爱辉区', '231100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('874', '231181', '北安市', '231100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('875', '231124', '孙吴县', '231100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('876', '231123', '逊克县', '231100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('877', '231182', '五大连池市', '231100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('878', '230281', '讷河市', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('879', '230229', '克山县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('880', '230225', '甘南县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('881', '230230', '克东县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('882', '230223', '依安县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('883', '230227', '富裕县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('884', '230208', '梅里斯达斡尔族区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('885', '230207', '碾子山区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('886', '230221', '龙江县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('887', '230203', '建华区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('888', '230204', '铁锋区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('889', '230206', '富拉尔基区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('890', '230202', '龙沙区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('891', '230205', '昂昂溪区', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('892', '230224', '泰来县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('893', '230231', '拜泉县', '230200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('894', '231004', '爱民区', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('895', '231002', '东安区', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('896', '231081', '绥芬河市', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('897', '231084', '宁安市', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('898', '231086', '东宁市', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('899', '231003', '阳明区', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('900', '231085', '穆棱市', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('901', '231025', '林口县', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('902', '231005', '西安区', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('903', '231083', '海林市', '231000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('904', '230302', '鸡冠区', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('905', '230306', '城子河区', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('906', '230304', '滴道区', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('907', '230303', '恒山区', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('908', '230307', '麻山区', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('909', '230305', '梨树区', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('910', '230382', '密山市', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('911', '230321', '鸡东县', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('912', '230381', '虎林市', '230300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('913', '230604', '让胡路区', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('914', '230605', '红岗区', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('915', '230622', '肇源县', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('916', '230606', '大同区', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('917', '230624', '杜尔伯特蒙古族自治县', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('918', '230623', '林甸县', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('919', '230603', '龙凤区', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('920', '230602', '萨尔图区', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('921', '230621', '肇州县', '230600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('922', '230502', '尖山区', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('923', '230522', '友谊县', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('924', '230505', '四方台区', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('925', '230503', '岭东区', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('926', '230506', '宝山区', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('927', '230523', '宝清县', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('928', '230524', '饶河县', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('929', '230521', '集贤县', '230500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('930', '230881', '同江市', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('931', '230826', '桦川县', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('932', '230828', '汤原县', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('933', '230803', '向阳区', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('934', '230804', '前进区', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('935', '230805', '东风区', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('936', '230811', '郊区', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('937', '230883', '抚远市', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('938', '230882', '富锦市', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('939', '230822', '桦南县', '230800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('940', '652723', '温泉县', '652700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('941', '652702', '阿拉山口市', '652700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('942', '652701', '博乐市', '652700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('943', '652722', '精河县', '652700', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('944', '654201', '塔城市', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('945', '654226', '和布克赛尔蒙古自治县', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('946', '654221', '额敏县', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('947', '654225', '裕民县', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('948', '654224', '托里县', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('949', '654223', '沙湾县', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('950', '654202', '乌苏市', '654200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('951', '653227', '民丰县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('952', '653226', '于田县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('953', '653201', '和田市', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('954', '653224', '洛浦县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('955', '653221', '和田县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('956', '653222', '墨玉县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('957', '653223', '皮山县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('958', '653225', '策勒县', '653200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('959', '654321', '布尔津县', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('960', '654324', '哈巴河县', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('961', '654322', '富蕴县', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('962', '654326', '吉木乃县', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('963', '654325', '青河县', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('964', '654301', '阿勒泰市', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('965', '654323', '福海县', '654300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('966', '650205', '乌尔禾区', '650200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('967', '650203', '克拉玛依区', '650200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('968', '650204', '白碱滩区', '650200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('969', '650202', '独山子区', '650200', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('970', '652325', '奇台县', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('971', '652324', '玛纳斯县', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('972', '652323', '呼图壁县', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('973', '652328', '木垒哈萨克自治县', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('974', '652302', '阜康市', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('975', '652301', '昌吉市', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('976', '652327', '吉木萨尔县', '652300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('977', '652827', '和静县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('978', '652828', '和硕县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('979', '652826', '焉耆回族自治县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('980', '652829', '博湖县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('981', '652824', '若羌县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('982', '652825', '且末县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('983', '652801', '库尔勒市', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('984', '652822', '轮台县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('985', '652823', '尉犁县', '652800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('986', '650107', '达坂城区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('987', '650102', '天山区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('988', '650103', '沙依巴克区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('989', '650105', '水磨沟区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('990', '650109', '米东区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('991', '650104', '新市区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('992', '650121', '乌鲁木齐县', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('993', '650106', '头屯河区', '650100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('994', '654003', '奎屯市', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('995', '654021', '伊宁县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('996', '654028', '尼勒克县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('997', '654024', '巩留县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('998', '654026', '昭苏县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('999', '654025', '新源县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1000', '654027', '特克斯县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1001', '654002', '伊宁市', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1002', '654023', '霍城县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1003', '654022', '察布查尔锡伯自治县', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1004', '654004', '霍尔果斯市', '654000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1005', '652923', '库车县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1006', '652926', '拜城县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1007', '652922', '温宿县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1008', '652925', '新和县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1009', '652901', '阿克苏市', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1010', '652924', '沙雅县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1011', '652929', '柯坪县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1012', '652928', '阿瓦提县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1013', '652927', '乌什县', '652900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1014', '653129', '伽师县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1015', '653122', '疏勒县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1016', '653128', '岳普湖县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1017', '653127', '麦盖提县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1018', '653125', '莎车县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1019', '653126', '叶城县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1020', '653131', '塔什库尔干塔吉克自治县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1021', '653124', '泽普县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1022', '653130', '巴楚县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1023', '653121', '疏附县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1024', '653123', '英吉沙县', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1025', '653101', '喀什市', '653100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1026', '653024', '乌恰县', '653000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1027', '653001', '阿图什市', '653000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1028', '653023', '阿合奇县', '653000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1029', '653022', '阿克陶县', '653000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1030', '650522', '伊吾县', '650500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1031', '650521', '巴里坤哈萨克自治县', '650500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1032', '650502', '伊州区', '650500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1033', '650402', '高昌区', '650400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1034', '650421', '鄯善县', '650400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1035', '650422', '托克逊县', '650400', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1036', '420302', '茅箭区', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1037', '420303', '张湾区', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1038', '420381', '丹江口市', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1039', '420304', '郧阳区', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1040', '420324', '竹溪县', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1041', '420323', '竹山县', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1042', '420325', '房县', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1043', '420322', '郧西县', '420300', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1044', '420682', '老河口市', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1045', '420683', '枣阳市', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1046', '420626', '保康县', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1047', '420625', '谷城县', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1048', '420607', '襄州区', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1049', '420624', '南漳县', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1050', '420684', '宜城市', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1051', '420606', '樊城区', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1052', '420602', '襄城区', '420600', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1053', '420881', '钟祥市', '420800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1054', '420802', '东宝区', '420800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1055', '420882', '京山市', '420800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1056', '420804', '掇刀区', '420800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1057', '420822', '沙洋县', '420800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1058', '420526', '兴山县', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1059', '420525', '远安县', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1060', '420582', '当阳市', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1061', '420527', '秭归县', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1062', '420504', '点军区', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1063', '420528', '长阳土家族自治县', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1064', '420503', '伍家岗区', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1065', '420583', '枝江市', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1066', '420505', '猇亭区', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1067', '420581', '宜都市', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1068', '420529', '五峰土家族自治县', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1069', '420502', '西陵区', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1070', '420506', '夷陵区', '420500', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1071', '420116', '黄陂区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1072', '420117', '新洲区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1073', '420102', '江岸区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1074', '420113', '汉南区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1075', '420114', '蔡甸区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1076', '420103', '江汉区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1077', '420107', '青山区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1078', '420115', '江夏区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1079', '420106', '武昌区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1080', '420105', '汉阳区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1081', '420111', '洪山区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1082', '420112', '东西湖区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1083', '420104', '硚口区', '420100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1084', '421122', '红安县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1085', '421123', '罗田县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1086', '421124', '英山县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1087', '421181', '麻城市', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1088', '421121', '团风县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1089', '421125', '浠水县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1090', '421126', '蕲春县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1091', '421102', '黄州区', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1092', '421127', '黄梅县', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1093', '421182', '武穴市', '421100', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1094', '420922', '大悟县', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1095', '420982', '安陆市', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1096', '420923', '云梦县', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1097', '420981', '应城市', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1098', '420902', '孝南区', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1099', '420984', '汉川市', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1100', '420921', '孝昌县', '420900', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1101', '422822', '建始县', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1102', '422801', '恩施市', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1103', '422802', '利川市', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1104', '422828', '鹤峰县', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1105', '422827', '来凤县', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1106', '422825', '宣恩县', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1107', '422823', '巴东县', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1108', '422826', '咸丰县', '422800', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1109', '421003', '荆州区', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1110', '421024', '江陵县', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1111', '421083', '洪湖市', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1112', '421023', '监利县', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1113', '421081', '石首市', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1114', '421087', '松滋市', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1115', '421002', '沙市区', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1116', '421022', '公安县', '421000', '3', '1', '1554254271', '1554254271', null);
INSERT INTO `system_area` VALUES ('1117', '421221', '嘉鱼县', '421200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1118', '421202', '咸安区', '421200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1119', '421224', '通山县', '421200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1120', '421223', '崇阳县', '421200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1121', '421281', '赤壁市', '421200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1122', '421222', '通城县', '421200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1123', '421381', '广水市', '421300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1124', '421303', '曾都区', '421300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1125', '421321', '随县', '421300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1126', '420703', '华容区', '420700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1127', '420702', '梁子湖区', '420700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1128', '420704', '鄂城区', '420700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1129', '420205', '铁山区', '420200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1130', '420203', '西塞山区', '420200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1131', '420204', '下陆区', '420200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1132', '420281', '大冶市', '420200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1133', '420222', '阳新县', '420200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1134', '420202', '黄石港区', '420200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1135', '211422', '建昌县', '211400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1136', '211403', '龙港区', '211400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1137', '211481', '兴城市', '211400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1138', '211421', '绥中县', '211400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1139', '211404', '南票区', '211400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1140', '211402', '连山区', '211400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1141', '210281', '瓦房店市', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1142', '210214', '普兰店区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1143', '210283', '庄河市', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1144', '210213', '金州区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1145', '210224', '长海县', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1146', '210211', '甘井子区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1147', '210212', '旅顺口区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1148', '210202', '中山区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1149', '210203', '西岗区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1150', '210204', '沙河口区', '210200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1151', '210682', '凤城市', '210600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1152', '210681', '东港市', '210600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1153', '210604', '振安区', '210600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1154', '210603', '振兴区', '210600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1155', '210602', '元宝区', '210600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1156', '210624', '宽甸满族自治县', '210600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1157', '210726', '黑山县', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1158', '210727', '义县', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1159', '210781', '凌海市', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1160', '210711', '太和区', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1161', '210782', '北镇市', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1162', '210703', '凌河区', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1163', '210702', '古塔区', '210700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1164', '210423', '清原满族自治县', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1165', '210422', '新宾满族自治县', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1166', '210411', '顺城区', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1167', '210404', '望花区', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1168', '210402', '新抚区', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1169', '210421', '抚顺县', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1170', '210403', '东洲区', '210400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1171', '210181', '新民市', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1172', '210113', '沈北新区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1173', '210112', '浑南区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1174', '210105', '皇姑区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1175', '210114', '于洪区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1176', '210102', '和平区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1177', '210111', '苏家屯区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1178', '210123', '康平县', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1179', '210124', '法库县', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1180', '210103', '沈河区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1181', '210104', '大东区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1182', '210115', '辽中区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1183', '210106', '铁西区', '210100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1184', '211281', '调兵山市', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1185', '211282', '开原市', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1186', '211204', '清河区', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1187', '211202', '银州区', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1188', '211221', '铁岭县', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1189', '211223', '西丰县', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1190', '211224', '昌图县', '211200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1191', '211322', '建平县', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1192', '211381', '北票市', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1193', '211321', '朝阳县', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1194', '211302', '双塔区', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1195', '211303', '龙城区', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1196', '211382', '凌源市', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1197', '211324', '喀喇沁左翼蒙古族自治县', '211300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1198', '211021', '辽阳县', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1199', '211011', '太子河区', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1200', '211005', '弓长岭区', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1201', '211003', '文圣区', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1202', '211081', '灯塔市', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1203', '211002', '白塔区', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1204', '211004', '宏伟区', '211000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1205', '210321', '台安县', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1206', '210323', '岫岩满族自治县', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1207', '210302', '铁东区', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1208', '210311', '千山区', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1209', '210303', '铁西区', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1210', '210304', '立山区', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1211', '210381', '海城市', '210300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1212', '210804', '鲅鱼圈区', '210800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1213', '210882', '大石桥市', '210800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1214', '210803', '西市区', '210800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1215', '210881', '盖州市', '210800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1216', '210811', '老边区', '210800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1217', '210802', '站前区', '210800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1218', '210911', '细河区', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1219', '210904', '太平区', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1220', '210902', '海州区', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1221', '210905', '清河门区', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1222', '210921', '阜新蒙古族自治县', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1223', '210903', '新邱区', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1224', '210922', '彰武县', '210900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1225', '211102', '双台子区', '211100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1226', '211104', '大洼区', '211100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1227', '211122', '盘山县', '211100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1228', '211103', '兴隆台区', '211100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1229', '210522', '桓仁满族自治县', '210500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1230', '210503', '溪湖区', '210500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1231', '210505', '南芬区', '210500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1232', '210502', '平山区', '210500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1233', '210504', '明山区', '210500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1234', '210521', '本溪满族自治县', '210500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1235', '370634', '长岛县', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1236', '370683', '莱州市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1237', '370602', '芝罘区', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1238', '370687', '海阳市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1239', '370612', '牟平区', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1240', '370681', '龙口市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1241', '370682', '莱阳市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1242', '370684', '蓬莱市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1243', '370685', '招远市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1244', '370611', '福山区', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1245', '370613', '莱山区', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1246', '370686', '栖霞市', '370600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1247', '371002', '环翠区', '371000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1248', '371082', '荣成市', '371000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1249', '371083', '乳山市', '371000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1250', '371003', '文登区', '371000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1251', '370285', '莱西市', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1252', '370283', '平度市', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1253', '370215', '即墨区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1254', '370212', '崂山区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1255', '370213', '李沧区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1256', '370214', '城阳区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1257', '370211', '黄岛区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1258', '370281', '胶州市', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1259', '370203', '市北区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1260', '370202', '市南区', '370200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1261', '370322', '高青县', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1262', '370321', '桓台县', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1263', '370305', '临淄区', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1264', '370303', '张店区', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1265', '370304', '博山区', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1266', '370323', '沂源县', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1267', '370302', '淄川区', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1268', '370306', '周村区', '370300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1269', '371525', '冠县', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1270', '371524', '东阿县', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1271', '371522', '莘县', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1272', '371526', '高唐县', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1273', '371521', '阳谷县', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1274', '371523', '茌平县', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1275', '371581', '临清市', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1276', '371502', '东昌府区', '371500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1277', '371323', '沂水县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1278', '371328', '蒙阴县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1279', '371326', '平邑县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1280', '371302', '兰山区', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1281', '371322', '郯城县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1282', '371321', '沂南县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1283', '371327', '莒南县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1284', '371311', '罗庄区', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1285', '371312', '河东区', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1286', '371329', '临沭县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1287', '371324', '兰陵县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1288', '371325', '费县', '371300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1289', '370702', '潍城区', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1290', '370705', '奎文区', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1291', '370704', '坊子区', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1292', '370785', '高密市', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1293', '370724', '临朐县', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1294', '370784', '安丘市', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1295', '370782', '诸城市', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1296', '370783', '寿光市', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1297', '370786', '昌邑市', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1298', '370703', '寒亭区', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1299', '370781', '青州市', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1300', '370725', '昌乐县', '370700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1301', '370522', '利津县', '370500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1302', '370523', '广饶县', '370500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1303', '370502', '东营区', '370500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1304', '370503', '河口区', '370500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1305', '370505', '垦利区', '370500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1306', '371603', '沾化区', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1307', '371602', '滨城区', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1308', '371625', '博兴县', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1309', '371681', '邹平市', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1310', '371623', '无棣县', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1311', '371621', '惠民县', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1312', '371622', '阳信县', '371600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1313', '370406', '山亭区', '370400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1314', '370402', '市中区', '370400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1315', '370403', '薛城区', '370400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1316', '370404', '峄城区', '370400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1317', '370405', '台儿庄区', '370400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1318', '370481', '滕州市', '370400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1319', '371121', '五莲县', '371100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1320', '371122', '莒县', '371100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1321', '371102', '东港区', '371100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1322', '371103', '岚山区', '371100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1323', '370881', '曲阜市', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1324', '370830', '汶上县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1325', '370831', '泗水县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1326', '370883', '邹城市', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1327', '370828', '金乡县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1328', '370827', '鱼台县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1329', '370811', '任城区', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1330', '370829', '嘉祥县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1331', '370832', '梁山县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1332', '370826', '微山县', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1333', '370812', '兖州区', '370800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1334', '370982', '新泰市', '370900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1335', '370983', '肥城市', '370900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1336', '370923', '东平县', '370900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1337', '370921', '宁阳县', '370900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1338', '370902', '泰山区', '370900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1339', '370911', '岱岳区', '370900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1340', '371422', '宁津县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1341', '371481', '乐陵市', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1342', '371403', '陵城区', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1343', '371402', '德城区', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1344', '371424', '临邑县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1345', '371428', '武城县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1346', '371426', '平原县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1347', '371482', '禹城市', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1348', '371427', '夏津县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1349', '371425', '齐河县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1350', '371423', '庆云县', '371400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1351', '370114', '章丘区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1352', '370113', '长清区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1353', '370116', '莱芜区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1354', '370117', '钢城区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1355', '370104', '槐荫区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1356', '370105', '天桥区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1357', '370103', '市中区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1358', '370124', '平阴县', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1359', '370112', '历城区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1360', '370102', '历下区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1361', '370126', '商河县', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1362', '370115', '济阳区', '370100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1363', '371726', '鄄城县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1364', '371702', '牡丹区', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1365', '371728', '东明县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1366', '371703', '定陶区', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1367', '371721', '曹县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1368', '371723', '成武县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1369', '371724', '巨野县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1370', '371725', '郓城县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1371', '371722', '单县', '371700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1372', '320707', '赣榆区', '320700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1373', '320722', '东海县', '320700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1374', '320703', '连云区', '320700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1375', '320723', '灌云县', '320700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1376', '320724', '灌南县', '320700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1377', '320706', '海州区', '320700', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1378', '321322', '沭阳县', '321300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1379', '321323', '泗阳县', '321300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1380', '321324', '泗洪县', '321300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1381', '321311', '宿豫区', '321300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1382', '321302', '宿城区', '321300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1383', '320111', '浦口区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1384', '320114', '雨花台区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1385', '320115', '江宁区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1386', '320117', '溧水区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1387', '320118', '高淳区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1388', '320116', '六合区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1389', '320105', '建邺区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1390', '320106', '鼓楼区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1391', '320104', '秦淮区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1392', '320113', '栖霞区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1393', '320102', '玄武区', '320100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1394', '321182', '扬中市', '321100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1395', '321111', '润州区', '321100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1396', '321181', '丹阳市', '321100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1397', '321183', '句容市', '321100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1398', '321112', '丹徒区', '321100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1399', '321102', '京口区', '321100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1400', '320682', '如皋市', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1401', '320684', '海门市', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1402', '320685', '海安市', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1403', '320681', '启东市', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1404', '320623', '如东县', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1405', '320612', '通州区', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1406', '320611', '港闸区', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1407', '320602', '崇川区', '320600', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1408', '320826', '涟水县', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1409', '320804', '淮阴区', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1410', '320803', '淮安区', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1411', '320813', '洪泽区', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1412', '320830', '盱眙县', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1413', '320831', '金湖县', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1414', '320812', '清江浦区', '320800', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1415', '320321', '丰县', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1416', '320382', '邳州市', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1417', '320381', '新沂市', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1418', '320311', '泉山区', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1419', '320324', '睢宁县', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1420', '320312', '铜山区', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1421', '320303', '云龙区', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1422', '320322', '沛县', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1423', '320305', '贾汪区', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1424', '320302', '鼓楼区', '320300', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1425', '320921', '响水县', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1426', '320922', '滨海县', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1427', '320924', '射阳县', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1428', '320923', '阜宁县', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1429', '320925', '建湖县', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1430', '320902', '亭湖区', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1431', '320903', '盐都区', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1432', '320981', '东台市', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1433', '320904', '大丰区', '320900', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1434', '321282', '靖江市', '321200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1435', '321202', '海陵区', '321200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1436', '321203', '高港区', '321200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1437', '321281', '兴化市', '321200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1438', '321283', '泰兴市', '321200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1439', '321204', '姜堰区', '321200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1440', '321012', '江都区', '321000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1441', '321003', '邗江区', '321000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1442', '321002', '广陵区', '321000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1443', '321081', '仪征市', '321000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1444', '321023', '宝应县', '321000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1445', '321084', '高邮市', '321000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1446', '320282', '宜兴市', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1447', '320213', '梁溪区', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1448', '320205', '锡山区', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1449', '320214', '新吴区', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1450', '320206', '惠山区', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1451', '320281', '江阴市', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1452', '320211', '滨湖区', '320200', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1453', '320413', '金坛区', '320400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1454', '320402', '天宁区', '320400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1455', '320404', '钟楼区', '320400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1456', '320481', '溧阳市', '320400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1457', '320411', '新北区', '320400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1458', '320412', '武进区', '320400', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1459', '320581', '常熟市', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1460', '320585', '太仓市', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1461', '320582', '张家港市', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1462', '320505', '虎丘区', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1463', '320508', '姑苏区', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1464', '320507', '相城区', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1465', '320509', '吴江区', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1466', '320506', '吴中区', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1467', '320583', '昆山市', '320500', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1468', '611021', '洛南县', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1469', '611002', '商州区', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1470', '611022', '丹凤县', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1471', '611026', '柞水县', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1472', '611023', '商南县', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1473', '611024', '山阳县', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1474', '611025', '镇安县', '611000', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1475', '610114', '阎良区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1476', '610115', '临潼区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1477', '610118', '鄠邑区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1478', '610117', '高陵区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1479', '610122', '蓝田县', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1480', '610116', '长安区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1481', '610112', '未央区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1482', '610104', '莲湖区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1483', '610124', '周至县', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1484', '610102', '新城区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1485', '610111', '灞桥区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1486', '610103', '碑林区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1487', '610113', '雁塔区', '610100', '3', '1', '1554254296', '1554254296', null);
INSERT INTO `system_area` VALUES ('1488', '610729', '留坝县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1489', '610723', '洋县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1490', '610730', '佛坪县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1491', '610726', '宁强县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1492', '610702', '汉台区', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1493', '610724', '西乡县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1494', '610727', '略阳县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1495', '610722', '城固县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1496', '610725', '勉县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1497', '610703', '南郑区', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1498', '610728', '镇巴县', '610700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1499', '610222', '宜君县', '610200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1500', '610203', '印台区', '610200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1501', '610204', '耀州区', '610200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1502', '610202', '王益区', '610200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1503', '610923', '宁陕县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1504', '610922', '石泉县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1505', '610902', '汉滨区', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1506', '610928', '旬阳县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1507', '610921', '汉阴县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1508', '610929', '白河县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1509', '610924', '紫阳县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1510', '610926', '平利县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1511', '610925', '岚皋县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1512', '610927', '镇坪县', '610900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1513', '610822', '府谷县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1514', '610802', '榆阳区', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1515', '610881', '神木市', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1516', '610824', '靖边县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1517', '610828', '佳县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1518', '610827', '米脂县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1519', '610803', '横山区', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1520', '610829', '吴堡县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1521', '610831', '子洲县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1522', '610826', '绥德县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1523', '610830', '清涧县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1524', '610825', '定边县', '610800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1525', '610623', '子长县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1526', '610626', '吴起县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1527', '610625', '志丹县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1528', '610603', '安塞区', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1529', '610622', '延川县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1530', '610627', '甘泉县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1531', '610630', '宜川县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1532', '610621', '延长县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1533', '610602', '宝塔区', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1534', '610628', '富县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1535', '610629', '洛川县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1536', '610632', '黄陵县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1537', '610631', '黄龙县', '610600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1538', '610327', '陇县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1539', '610329', '麟游县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1540', '610328', '千阳县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1541', '610303', '金台区', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1542', '610330', '凤县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1543', '610302', '渭滨区', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1544', '610323', '岐山县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1545', '610326', '眉县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1546', '610331', '太白县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1547', '610322', '凤翔县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1548', '610304', '陈仓区', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1549', '610324', '扶风县', '610300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1550', '610482', '彬州市', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1551', '610429', '旬邑县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1552', '610430', '淳化县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1553', '610424', '乾县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1554', '610426', '永寿县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1555', '610404', '渭城区', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1556', '610431', '武功县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1557', '610423', '泾阳县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1558', '610428', '长武县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1559', '610402', '秦都区', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1560', '610425', '礼泉县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1561', '610481', '兴平市', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1562', '610403', '杨陵区', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1563', '610422', '三原县', '610400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1564', '610581', '韩城市', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1565', '610527', '白水县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1566', '610523', '大荔县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1567', '610582', '华阴市', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1568', '610503', '华州区', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1569', '610522', '潼关县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1570', '610524', '合阳县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1571', '610525', '澄城县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1572', '610526', '蒲城县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1573', '610502', '临渭区', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1574', '610528', '富平县', '610500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1575', '310151', '崇明区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1576', '310120', '奉贤区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1577', '310116', '金山区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1578', '310115', '浦东新区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1579', '310113', '宝山区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1580', '310114', '嘉定区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1581', '310101', '黄浦区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1582', '310107', '普陀区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1583', '310110', '杨浦区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1584', '310117', '松江区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1585', '310105', '长宁区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1586', '310106', '静安区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1587', '310109', '虹口区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1588', '310112', '闵行区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1589', '310104', '徐汇区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1590', '310118', '青浦区', '310100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1591', '520322', '桐梓县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1592', '520323', '绥阳县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1593', '520328', '湄潭县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1594', '520329', '余庆县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1595', '520302', '红花岗区', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1596', '520303', '汇川区', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1597', '520327', '凤冈县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1598', '520326', '务川仡佬族苗族自治县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1599', '520382', '仁怀市', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1600', '520304', '播州区', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1601', '520381', '赤水市', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1602', '520330', '习水县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1603', '520324', '正安县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1604', '520325', '道真仡佬族苗族自治县', '520300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1605', '522624', '三穗县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1606', '522629', '剑河县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1607', '522634', '雷山县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1608', '522627', '天柱县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1609', '522631', '黎平县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1610', '522628', '锦屏县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1611', '522632', '榕江县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1612', '522633', '从江县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1613', '522623', '施秉县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1614', '522625', '镇远县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1615', '522601', '凯里市', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1616', '522635', '麻江县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1617', '522636', '丹寨县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1618', '522622', '黄平县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1619', '522630', '台江县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1620', '522626', '岑巩县', '522600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1621', '520201', '钟山区', '520200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1622', '520203', '六枝特区', '520200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1623', '520281', '盘州市', '520200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1624', '520221', '水城县', '520200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1625', '520625', '印江土家族苗族自治县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1626', '520623', '石阡县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1627', '520622', '玉屏侗族自治县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1628', '520602', '碧江区', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1629', '520624', '思南县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1630', '520627', '沿河土家族自治县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1631', '520626', '德江县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1632', '520621', '江口县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1633', '520603', '万山区', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1634', '520628', '松桃苗族自治县', '520600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1635', '522731', '惠水县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1636', '522701', '都匀市', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1637', '522727', '平塘县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1638', '522728', '罗甸县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1639', '522726', '独山县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1640', '522722', '荔波县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1641', '522729', '长顺县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1642', '522730', '龙里县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1643', '522702', '福泉市', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1644', '522723', '贵定县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1645', '522725', '瓮安县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1646', '522732', '三都水族自治县', '522700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1647', '522302', '兴仁市', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1648', '522328', '安龙县', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1649', '522301', '兴义市', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1650', '522327', '册亨县', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1651', '522326', '望谟县', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1652', '522323', '普安县', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1653', '522325', '贞丰县', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1654', '522324', '晴隆县', '522300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1655', '520402', '西秀区', '520400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1656', '520424', '关岭布依族苗族自治县', '520400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1657', '520423', '镇宁布依族苗族自治县', '520400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1658', '520425', '紫云苗族布依族自治县', '520400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1659', '520403', '平坝区', '520400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1660', '520422', '普定县', '520400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1661', '520502', '七星关区', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1662', '520521', '大方县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1663', '520522', '黔西县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1664', '520523', '金沙县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1665', '520525', '纳雍县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1666', '520524', '织金县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1667', '520527', '赫章县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1668', '520526', '威宁彝族回族苗族自治县', '520500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1669', '520121', '开阳县', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1670', '520113', '白云区', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1671', '520112', '乌当区', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1672', '520115', '观山湖区', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1673', '520122', '息烽县', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1674', '520123', '修文县', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1675', '520181', '清镇市', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1676', '520111', '花溪区', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1677', '520103', '云岩区', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1678', '520102', '南明区', '520100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1679', '500229', '城口县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1680', '500238', '巫溪县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1681', '500236', '奉节县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1682', '500230', '丰都县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1683', '500243', '彭水苗族土家族自治县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1684', '500241', '秀山土家族苗族自治县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1685', '500235', '云阳县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1686', '500237', '巫山县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1687', '500231', '垫江县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1688', '500242', '酉阳土家族苗族自治县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1689', '500240', '石柱土家族自治县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1690', '500233', '忠县', '500200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1691', '500117', '合川区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1692', '500152', '潼南区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1693', '500151', '铜梁区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1694', '500120', '璧山区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1695', '500115', '长寿区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1696', '500111', '大足区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1697', '500105', '江北区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1698', '500153', '荣昌区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1699', '500103', '渝中区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1700', '500118', '永川区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1701', '500156', '武隆区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1702', '500104', '大渡口区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1703', '500119', '南川区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1704', '500107', '九龙坡区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1705', '500101', '万州区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1706', '500102', '涪陵区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1707', '500112', '渝北区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1708', '500110', '綦江区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1709', '500154', '开州区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1710', '500155', '梁平区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1711', '500116', '江津区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1712', '500114', '黔江区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1713', '500113', '巴南区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1714', '500106', '沙坪坝区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1715', '500108', '南岸区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1716', '500109', '北碚区', '500100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1717', '540321', '江达县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1718', '540324', '丁青县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1719', '540323', '类乌齐县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1720', '540302', '卡若区', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1721', '540322', '贡觉县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1722', '540330', '边坝县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1723', '540329', '洛隆县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1724', '540325', '察雅县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1725', '540326', '八宿县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1726', '540327', '左贡县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1727', '540328', '芒康县', '540300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1728', '540624', '安多县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1729', '540623', '聂荣县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1730', '540628', '巴青县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1731', '540625', '申扎县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1732', '540627', '班戈县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1733', '540626', '索县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1734', '540622', '比如县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1735', '540602', '色尼区', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1736', '540621', '嘉黎县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1737', '540629', '尼玛县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1738', '540630', '双湖县', '540600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1739', '540122', '当雄县', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1740', '540121', '林周县', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1741', '540127', '墨竹工卡县', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1742', '540103', '堆龙德庆区', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1743', '540102', '城关区', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1744', '540123', '尼木县', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1745', '540104', '达孜区', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1746', '540124', '曲水县', '540100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1747', '540232', '仲巴县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1748', '540226', '昂仁县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1749', '540227', '谢通门县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1750', '540221', '南木林县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1751', '540236', '萨嘎县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1752', '540202', '桑珠孜区', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1753', '540225', '拉孜县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1754', '540229', '仁布县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1755', '540224', '萨迦县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1756', '540234', '吉隆县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1757', '540228', '白朗县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1758', '540222', '江孜县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1759', '540235', '聂拉木县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1760', '540223', '定日县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1761', '540230', '康马县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1762', '540237', '岗巴县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1763', '540231', '定结县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1764', '540233', '亚东县', '540200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1765', '540523', '桑日县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1766', '540528', '加查县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1767', '540502', '乃东区', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1768', '540521', '扎囊县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1769', '540522', '贡嘎县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1770', '540531', '浪卡子县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1771', '540525', '曲松县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1772', '540524', '琼结县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1773', '540526', '措美县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1774', '540529', '隆子县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1775', '540527', '洛扎县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1776', '540530', '错那县', '540500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1777', '540421', '工布江达县', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1778', '540424', '波密县', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1779', '540402', '巴宜区', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1780', '540423', '墨脱县', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1781', '540422', '米林县', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1782', '540426', '朗县', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1783', '540425', '察隅县', '540400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1784', '542526', '改则县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1785', '542522', '札达县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1786', '542527', '措勤县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1787', '542521', '普兰县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1788', '542524', '日土县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1789', '542523', '噶尔县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1790', '542525', '革吉县', '542500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1791', '340602', '杜集区', '340600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1792', '340621', '濉溪县', '340600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1793', '340603', '相山区', '340600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1794', '340604', '烈山区', '340600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1795', '341222', '太和县', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1796', '341204', '颍泉区', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1797', '341221', '临泉县', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1798', '341203', '颍东区', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1799', '341202', '颍州区', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1800', '341225', '阜南县', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1801', '341226', '颍上县', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1802', '341282', '界首市', '341200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1803', '340506', '博望区', '340500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1804', '340521', '当涂县', '340500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1805', '340504', '雨山区', '340500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1806', '340503', '花山区', '340500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1807', '340523', '和县', '340500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1808', '340522', '含山县', '340500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1809', '341723', '青阳县', '341700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1810', '341721', '东至县', '341700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1811', '341702', '贵池区', '341700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1812', '341722', '石台县', '341700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1813', '340711', '郊区', '340700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1814', '340705', '铜官区', '340700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1815', '340706', '义安区', '340700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1816', '340722', '枞阳县', '340700', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1817', '341621', '涡阳县', '341600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1818', '341602', '谯城区', '341600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1819', '341622', '蒙城县', '341600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1820', '341623', '利辛县', '341600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1821', '340323', '固镇县', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1822', '340311', '淮上区', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1823', '340322', '五河县', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1824', '340321', '怀远县', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1825', '340304', '禹会区', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1826', '340302', '龙子湖区', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1827', '340303', '蚌山区', '340300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1828', '341182', '明光市', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1829', '341181', '天长市', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1830', '341124', '全椒县', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1831', '341126', '凤阳县', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1832', '341122', '来安县', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1833', '341125', '定远县', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1834', '341103', '南谯区', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1835', '341102', '琅琊区', '341100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1836', '340828', '岳西县', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1837', '340824', '潜山市', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1838', '340811', '宜秀区', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1839', '340825', '太湖县', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1840', '340802', '迎江区', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1841', '340881', '桐城市', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1842', '340827', '望江县', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1843', '340826', '宿松县', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1844', '340822', '怀宁县', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1845', '340803', '大观区', '340800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1846', '341822', '广德县', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1847', '341823', '泾县', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1848', '341825', '旌德县', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1849', '341824', '绩溪县', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1850', '341802', '宣州区', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1851', '341881', '宁国市', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1852', '341821', '郎溪县', '341800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1853', '341525', '霍山县', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1854', '341503', '裕安区', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1855', '341524', '金寨县', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1856', '341502', '金安区', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1857', '341523', '舒城县', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1858', '341522', '霍邱县', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1859', '341504', '叶集区', '341500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1860', '341003', '黄山区', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1861', '341023', '黟县', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1862', '341024', '祁门县', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1863', '341021', '歙县', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1864', '341002', '屯溪区', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1865', '341004', '徽州区', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1866', '341022', '休宁县', '341000', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1867', '340421', '凤台县', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1868', '340406', '潘集区', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1869', '340405', '八公山区', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1870', '340402', '大通区', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1871', '340422', '寿县', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1872', '340403', '田家庵区', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1873', '340404', '谢家集区', '340400', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1874', '340103', '庐阳区', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1875', '340122', '肥东县', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1876', '340124', '庐江县', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1877', '340121', '长丰县', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1878', '340102', '瑶海区', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1879', '340111', '包河区', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1880', '340181', '巢湖市', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1881', '340123', '肥西县', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1882', '340104', '蜀山区', '340100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1883', '341321', '砀山县', '341300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1884', '341323', '灵璧县', '341300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1885', '341302', '埇桥区', '341300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1886', '341324', '泗县', '341300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1887', '341322', '萧县', '341300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1888', '340222', '繁昌县', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1889', '340203', '弋江区', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1890', '340202', '镜湖区', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1891', '340225', '无为县', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1892', '340223', '南陵县', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1893', '340208', '三山区', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1894', '340207', '鸠江区', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1895', '340221', '芜湖县', '340200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1896', '350924', '寿宁县', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1897', '350981', '福安市', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1898', '350925', '周宁县', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1899', '350923', '屏南县', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1900', '350926', '柘荣县', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1901', '350982', '福鼎市', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1902', '350922', '古田县', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1903', '350902', '蕉城区', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1904', '350921', '霞浦县', '350900', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1905', '350123', '罗源县', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1906', '350121', '闽侯县', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1907', '350124', '闽清县', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1908', '350122', '连江县', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1909', '350104', '仓山区', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1910', '350112', '长乐区', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1911', '350125', '永泰县', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1912', '350181', '福清市', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1913', '350128', '平潭县', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1914', '350102', '鼓楼区', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1915', '350103', '台江区', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1916', '350105', '马尾区', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1917', '350111', '晋安区', '350100', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1918', '350821', '长汀县', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1919', '350825', '连城县', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1920', '350881', '漳平市', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1921', '350823', '上杭县', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1922', '350802', '新罗区', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1923', '350824', '武平县', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1924', '350803', '永定区', '350800', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1925', '350322', '仙游县', '350300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1926', '350302', '城厢区', '350300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1927', '350304', '荔城区', '350300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1928', '350305', '秀屿区', '350300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1929', '350303', '涵江区', '350300', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1930', '350526', '德化县', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1931', '350525', '永春县', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1932', '350524', '安溪县', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1933', '350583', '南安市', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1934', '350504', '洛江区', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1935', '350521', '惠安县', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1936', '350505', '泉港区', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1937', '350503', '丰泽区', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1938', '350581', '石狮市', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1939', '350582', '晋江市', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1940', '350527', '金门县', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1941', '350502', '鲤城区', '350500', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1942', '350212', '同安区', '350200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1943', '350211', '集美区', '350200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1944', '350205', '海沧区', '350200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1945', '350203', '思明区', '350200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1946', '350206', '湖里区', '350200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1947', '350213', '翔安区', '350200', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1948', '350629', '华安县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1949', '350627', '南靖县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1950', '350625', '长泰县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1951', '350624', '诏安县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1952', '350681', '龙海市', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1953', '350623', '漳浦县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1954', '350626', '东山县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1955', '350602', '芗城区', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1956', '350603', '龙文区', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1957', '350622', '云霄县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1958', '350628', '平和县', '350600', '3', '1', '1554254320', '1554254320', null);
INSERT INTO `system_area` VALUES ('1959', '350722', '浦城县', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1960', '350782', '武夷山市', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1961', '350723', '光泽县', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1962', '350724', '松溪县', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1963', '350703', '建阳区', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1964', '350781', '邵武市', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1965', '350725', '政和县', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1966', '350783', '建瓯市', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1967', '350721', '顺昌县', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1968', '350702', '延平区', '350700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1969', '350429', '泰宁县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1970', '350424', '宁化县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1971', '350423', '清流县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1972', '350402', '梅列区', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1973', '350403', '三元区', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1974', '350481', '永安市', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1975', '350425', '大田县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1976', '350427', '沙县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1977', '350426', '尤溪县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1978', '350430', '建宁县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1979', '350421', '明溪县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1980', '350428', '将乐县', '350400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1981', '430623', '华容县', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1982', '430611', '君山区', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1983', '430603', '云溪区', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1984', '430602', '岳阳楼区', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1985', '430681', '汨罗市', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1986', '430624', '湘阴县', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1987', '430682', '临湘市', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1988', '430621', '岳阳县', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1989', '430626', '平江县', '430600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1990', '430921', '南县', '430900', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1991', '430981', '沅江市', '430900', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1992', '430902', '资阳区', '430900', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1993', '430922', '桃江县', '430900', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1994', '430903', '赫山区', '430900', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1995', '430923', '安化县', '430900', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1996', '430423', '衡山县', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1997', '430424', '衡东县', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1998', '430412', '南岳区', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('1999', '430421', '衡阳县', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2000', '430426', '祁东县', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2001', '430407', '石鼓区', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2002', '430422', '衡南县', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2003', '430406', '雁峰区', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2004', '430405', '珠晖区', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2005', '430481', '耒阳市', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2006', '430408', '蒸湘区', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2007', '430482', '常宁市', '430400', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2008', '431381', '冷水江市', '431300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2009', '431322', '新化县', '431300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2010', '431382', '涟源市', '431300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2011', '431321', '双峰县', '431300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2012', '431302', '娄星区', '431300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2013', '430105', '开福区', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2014', '430104', '岳麓区', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2015', '430181', '浏阳市', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2016', '430103', '天心区', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2017', '430111', '雨花区', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2018', '430102', '芙蓉区', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2019', '430121', '长沙县', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2020', '430182', '宁乡市', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2021', '430112', '望城区', '430100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2022', '430821', '慈利县', '430800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2023', '430811', '武陵源区', '430800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2024', '430822', '桑植县', '430800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2025', '430802', '永定区', '430800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2026', '431228', '芷江侗族自治县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2027', '431281', '洪江市', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2028', '431230', '通道侗族自治县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2029', '431222', '沅陵县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2030', '431223', '辰溪县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2031', '431224', '溆浦县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2032', '431225', '会同县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2033', '431229', '靖州苗族侗族自治县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2034', '431226', '麻阳苗族自治县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2035', '431227', '新晃侗族自治县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2036', '431221', '中方县', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2037', '431202', '鹤城区', '431200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2038', '433127', '永顺县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2039', '433125', '保靖县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2040', '433124', '花垣县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2041', '433101', '吉首市', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2042', '433126', '古丈县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2043', '433122', '泸溪县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2044', '433130', '龙山县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2045', '433123', '凤凰县', '433100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2046', '430723', '澧县', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2047', '430726', '石门县', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2048', '430724', '临澧县', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2049', '430781', '津市市', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2050', '430703', '鼎城区', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2051', '430722', '汉寿县', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2052', '430702', '武陵区', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2053', '430725', '桃源县', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2054', '430721', '安乡县', '430700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2055', '430202', '荷塘区', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2056', '430223', '攸县', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2057', '430224', '茶陵县', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2058', '430225', '炎陵县', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2059', '430211', '天元区', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2060', '430203', '芦淞区', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2061', '430212', '渌口区', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2062', '430204', '石峰区', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2063', '430281', '醴陵市', '430200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2064', '430524', '隆回县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2065', '430511', '北塔区', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2066', '430502', '双清区', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2067', '430523', '邵阳县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2068', '430503', '大祥区', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2069', '430527', '绥宁县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2070', '430528', '新宁县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2071', '430529', '城步苗族自治县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2072', '430581', '武冈市', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2073', '430525', '洞口县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2074', '430521', '邵东县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2075', '430522', '新邵县', '430500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2076', '430304', '岳塘区', '430300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2077', '430321', '湘潭县', '430300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2078', '430302', '雨湖区', '430300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2079', '430382', '韶山市', '430300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2080', '430381', '湘乡市', '430300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2081', '431122', '东安县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2082', '431121', '祁阳县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2083', '431103', '冷水滩区', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2084', '431102', '零陵区', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2085', '431128', '新田县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2086', '431126', '宁远县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2087', '431124', '道县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2088', '431125', '江永县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2089', '431129', '江华瑶族自治县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2090', '431123', '双牌县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2091', '431127', '蓝山县', '431100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2092', '431028', '安仁县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2093', '431081', '资兴市', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2094', '431023', '永兴县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2095', '431026', '汝城县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2096', '431027', '桂东县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2097', '431003', '苏仙区', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2098', '431002', '北湖区', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2099', '431025', '临武县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2100', '431021', '桂阳县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2101', '431022', '宜章县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2102', '431024', '嘉禾县', '431000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2103', '460323', '中沙群岛的岛礁及其海域', '460300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2104', '460321', '西沙群岛', '460300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2105', '460322', '南沙群岛', '460300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2106', '460205', '崖州区', '460200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2107', '460204', '天涯区', '460200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2108', '460203', '吉阳区', '460200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2109', '460202', '海棠区', '460200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2110', '460108', '美兰区', '460100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2111', '460106', '龙华区', '460100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2112', '460107', '琼山区', '460100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2113', '460105', '秀英区', '460100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2114', '630222', '民和回族土族自治县', '630200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2115', '630202', '乐都区', '630200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2116', '630224', '化隆回族自治县', '630200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2117', '630225', '循化撒拉族自治县', '630200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2118', '630203', '平安区', '630200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2119', '630223', '互助土族自治县', '630200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2120', '632521', '共和县', '632500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2121', '632523', '贵德县', '632500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2122', '632525', '贵南县', '632500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2123', '632522', '同德县', '632500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2124', '632524', '兴海县', '632500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2125', '632823', '天峻县', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2126', '632802', '德令哈市', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2127', '632801', '格尔木市', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2128', '632822', '都兰县', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2129', '632821', '乌兰县', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2130', '632825', '海西蒙古族藏族自治州直辖', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2131', '632803', '茫崖市', '632800', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2132', '632724', '治多县', '632700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2133', '632726', '曲麻莱县', '632700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2134', '632723', '称多县', '632700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2135', '632722', '杂多县', '632700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2136', '632701', '玉树市', '632700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2137', '632725', '囊谦县', '632700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2138', '632322', '尖扎县', '632300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2139', '632321', '同仁县', '632300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2140', '632323', '泽库县', '632300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2141', '632324', '河南蒙古族自治县', '632300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2142', '632621', '玛沁县', '632600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2143', '632623', '甘德县', '632600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2144', '632625', '久治县', '632600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2145', '632624', '达日县', '632600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2146', '632622', '班玛县', '632600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2147', '632626', '玛多县', '632600', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2148', '630123', '湟源县', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2149', '630121', '大通回族土族自治县', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2150', '630102', '城东区', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2151', '630122', '湟中县', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2152', '630103', '城中区', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2153', '630104', '城西区', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2154', '630105', '城北区', '630100', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2155', '632223', '海晏县', '632200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2156', '632221', '门源回族自治县', '632200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2157', '632224', '刚察县', '632200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2158', '632222', '祁连县', '632200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2159', '451028', '乐业县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2160', '451031', '隆林各族自治县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2161', '451030', '西林县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2162', '451027', '凌云县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2163', '451002', '右江区', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2164', '451021', '田阳县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2165', '451022', '田东县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2166', '451023', '平果县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2167', '451024', '德保县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2168', '451081', '靖西市', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2169', '451026', '那坡县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2170', '451029', '田林县', '451000', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2171', '450721', '灵山县', '450700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2172', '450702', '钦南区', '450700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2173', '450722', '浦北县', '450700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2174', '450703', '钦北区', '450700', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2175', '450521', '合浦县', '450500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2176', '450502', '海城区', '450500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2177', '450503', '银海区', '450500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2178', '450512', '铁山港区', '450500', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2179', '450325', '兴安县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2180', '450329', '资源县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2181', '450312', '临桂区', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2182', '450311', '雁山区', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2183', '450332', '恭城瑶族自治县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2184', '450321', '阳朔县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2185', '450326', '永福县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2186', '450305', '七星区', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2187', '450330', '平乐县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2188', '450381', '荔浦市', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2189', '450328', '龙胜各族自治县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2190', '450302', '秀峰区', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2191', '450304', '象山区', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2192', '450324', '全州县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2193', '450327', '灌阳县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2194', '450303', '叠彩区', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2195', '450323', '灵川县', '450300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2196', '451221', '南丹县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2197', '451226', '环江毛南族自治县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2198', '451222', '天峨县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2199', '451225', '罗城仫佬族自治县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2200', '451202', '金城江区', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2201', '451203', '宜州区', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2202', '451223', '凤山县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2203', '451228', '都安瑶族自治县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2204', '451227', '巴马瑶族自治县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2205', '451229', '大化瑶族自治县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2206', '451224', '东兰县', '451200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2207', '450226', '三江侗族自治县', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2208', '450225', '融水苗族自治县', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2209', '450224', '融安县', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2210', '450223', '鹿寨县', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2211', '450202', '城中区', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2212', '450205', '柳北区', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2213', '450222', '柳城县', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2214', '450203', '鱼峰区', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2215', '450206', '柳江区', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2216', '450204', '柳南区', '450200', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2217', '451324', '金秀瑶族自治县', '451300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2218', '451321', '忻城县', '451300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2219', '451322', '象州县', '451300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2220', '451302', '兴宾区', '451300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2221', '451381', '合山市', '451300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2222', '451323', '武宣县', '451300', '3', '1', '1554254348', '1554254348', null);
INSERT INTO `system_area` VALUES ('2223', '450124', '马山县', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2224', '450125', '上林县', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2225', '450110', '武鸣区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2226', '450126', '宾阳县', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2227', '450127', '横县', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2228', '450105', '江南区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2229', '450109', '邕宁区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2230', '450108', '良庆区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2231', '450107', '西乡塘区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2232', '450102', '兴宁区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2233', '450103', '青秀区', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2234', '450123', '隆安县', '450100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2235', '451425', '天等县', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2236', '451424', '大新县', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2237', '451421', '扶绥县', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2238', '451402', '江州区', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2239', '451423', '龙州县', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2240', '451422', '宁明县', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2241', '451481', '凭祥市', '451400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2242', '450621', '上思县', '450600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2243', '450603', '防城区', '450600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2244', '450602', '港口区', '450600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2245', '450681', '东兴市', '450600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2246', '450423', '蒙山县', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2247', '450422', '藤县', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2248', '450405', '长洲区', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2249', '450406', '龙圩区', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2250', '450481', '岑溪市', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2251', '450403', '万秀区', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2252', '450421', '苍梧县', '450400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2253', '451123', '富川瑶族自治县', '451100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2254', '451121', '昭平县', '451100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2255', '451102', '八步区', '451100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2256', '451122', '钟山县', '451100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2257', '451103', '平桂区', '451100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2258', '450903', '福绵区', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2259', '450924', '兴业县', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2260', '450902', '玉州区', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2261', '450922', '陆川县', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2262', '450923', '博白县', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2263', '450981', '北流市', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2264', '450921', '容县', '450900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2265', '450802', '港北区', '450800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2266', '450804', '覃塘区', '450800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2267', '450803', '港南区', '450800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2268', '450881', '桂平市', '450800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2269', '450821', '平南县', '450800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2270', '640423', '隆德县', '640400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2271', '640425', '彭阳县', '640400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2272', '640424', '泾源县', '640400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2273', '640422', '西吉县', '640400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2274', '640402', '原州区', '640400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2275', '640104', '兴庆区', '640100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2276', '640106', '金凤区', '640100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2277', '640122', '贺兰县', '640100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2278', '640121', '永宁县', '640100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2279', '640105', '西夏区', '640100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2280', '640181', '灵武市', '640100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2281', '640522', '海原县', '640500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2282', '640521', '中宁县', '640500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2283', '640502', '沙坡头区', '640500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2284', '640205', '惠农区', '640200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2285', '640202', '大武口区', '640200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2286', '640221', '平罗县', '640200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2287', '640381', '青铜峡市', '640300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2288', '640302', '利通区', '640300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2289', '640303', '红寺堡区', '640300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2290', '640323', '盐池县', '640300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2291', '640324', '同心县', '640300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2292', '360428', '都昌县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2293', '360430', '彭泽县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2294', '360404', '柴桑区', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2295', '360481', '瑞昌市', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2296', '360429', '湖口县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2297', '360423', '武宁县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2298', '360424', '修水县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2299', '360402', '濂溪区', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2300', '360483', '庐山市', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2301', '360426', '德安县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2302', '360403', '浔阳区', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2303', '360425', '永修县', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2304', '360482', '共青城市', '360400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2305', '360521', '分宜县', '360500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2306', '360502', '渝水区', '360500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2307', '361003', '东乡区', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2308', '361002', '临川区', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2309', '361027', '金溪县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2310', '361028', '资溪县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2311', '361021', '南城县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2312', '361026', '宜黄县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2313', '361024', '崇仁县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2314', '361025', '乐安县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2315', '361022', '黎川县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2316', '361030', '广昌县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2317', '361023', '南丰县', '361000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2318', '360681', '贵溪市', '360600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2319', '360603', '余江区', '360600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2320', '360602', '月湖区', '360600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2321', '360730', '宁都县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2322', '360731', '于都县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2323', '360781', '瑞金市', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2324', '360735', '石城县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2325', '360732', '兴国县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2326', '360724', '上犹县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2327', '360733', '会昌县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2328', '360726', '安远县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2329', '360723', '大余县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2330', '360725', '崇义县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2331', '360729', '全南县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2332', '360722', '信丰县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2333', '360734', '寻乌县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2334', '360727', '龙南县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2335', '360704', '赣县区', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2336', '360728', '定南县', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2337', '360703', '南康区', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2338', '360702', '章贡区', '360700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2339', '360123', '安义县', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2340', '360124', '进贤县', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2341', '360104', '青云谱区', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2342', '360121', '南昌县', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2343', '360112', '新建区', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2344', '360105', '湾里区', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2345', '360103', '西湖区', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2346', '360102', '东湖区', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2347', '360111', '青山湖区', '360100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2348', '360925', '靖安县', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2349', '360921', '奉新县', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2350', '360924', '宜丰县', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2351', '360983', '高安市', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2352', '360923', '上高县', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2353', '360981', '丰城市', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2354', '360926', '铜鼓县', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2355', '360922', '万载县', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2356', '360902', '袁州区', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2357', '360982', '樟树市', '360900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2358', '360823', '峡江县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2359', '360830', '永新县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2360', '360802', '吉州区', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2361', '360822', '吉水县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2362', '360803', '青原区', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2363', '360821', '吉安县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2364', '360825', '永丰县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2365', '360828', '万安县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2366', '360881', '井冈山市', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2367', '360827', '遂川县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2368', '360826', '泰和县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2369', '360824', '新干县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2370', '360829', '安福县', '360800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2371', '360313', '湘东区', '360300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2372', '360302', '安源区', '360300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2373', '360322', '上栗县', '360300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2374', '360321', '莲花县', '360300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2375', '360323', '芦溪县', '360300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2376', '360202', '昌江区', '360200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2377', '360222', '浮梁县', '360200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2378', '360203', '珠山区', '360200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2379', '360281', '乐平市', '360200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2380', '361130', '婺源县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2381', '361181', '德兴市', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2382', '361127', '余干县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2383', '361121', '上饶县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2384', '361126', '弋阳县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2385', '361125', '横峰县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2386', '361103', '广丰区', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2387', '361102', '信州区', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2388', '361124', '铅山县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2389', '361123', '玉山县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2390', '361128', '鄱阳县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2391', '361129', '万年县', '361100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2392', '330922', '嵊泗县', '330900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2393', '330903', '普陀区', '330900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2394', '330902', '定海区', '330900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2395', '330921', '岱山县', '330900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2396', '330206', '北仑区', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2397', '330225', '象山县', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2398', '330205', '江北区', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2399', '330211', '镇海区', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2400', '330226', '宁海县', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2401', '330213', '奉化区', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2402', '330281', '余姚市', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2403', '330203', '海曙区', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2404', '330212', '鄞州区', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2405', '330282', '慈溪市', '330200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2406', '330482', '平湖市', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2407', '330402', '南湖区', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2408', '330481', '海宁市', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2409', '330411', '秀洲区', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2410', '330421', '嘉善县', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2411', '330424', '海盐县', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2412', '330483', '桐乡市', '330400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2413', '331082', '临海市', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2414', '331002', '椒江区', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2415', '331004', '路桥区', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2416', '331083', '玉环市', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2417', '331081', '温岭市', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2418', '331022', '三门县', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2419', '331023', '天台县', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2420', '331003', '黄岩区', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2421', '331024', '仙居县', '331000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2422', '330324', '永嘉县', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2423', '330382', '乐清市', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2424', '330326', '平阳县', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2425', '330305', '洞头区', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2426', '330381', '瑞安市', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2427', '330329', '泰顺县', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2428', '330327', '苍南县', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2429', '330328', '文成县', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2430', '330303', '龙湾区', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2431', '330302', '鹿城区', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2432', '330304', '瓯海区', '330300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2433', '331102', '莲都区', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2434', '331124', '松阳县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2435', '331181', '龙泉市', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2436', '331125', '云和县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2437', '331123', '遂昌县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2438', '331127', '景宁畲族自治县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2439', '331121', '青田县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2440', '331122', '缙云县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2441', '331126', '庆元县', '331100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2442', '330106', '西湖区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2443', '330102', '上城区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2444', '330111', '富阳区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2445', '330122', '桐庐县', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2446', '330182', '建德市', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2447', '330127', '淳安县', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2448', '330109', '萧山区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2449', '330108', '滨江区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2450', '330103', '下城区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2451', '330104', '江干区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2452', '330112', '临安区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2453', '330110', '余杭区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2454', '330105', '拱墅区', '330100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2455', '330683', '嵊州市', '330600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2456', '330604', '上虞区', '330600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2457', '330602', '越城区', '330600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2458', '330603', '柯桥区', '330600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2459', '330681', '诸暨市', '330600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2460', '330624', '新昌县', '330600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2461', '330521', '德清县', '330500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2462', '330523', '安吉县', '330500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2463', '330522', '长兴县', '330500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2464', '330502', '吴兴区', '330500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2465', '330503', '南浔区', '330500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2466', '330824', '开化县', '330800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2467', '330803', '衢江区', '330800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2468', '330822', '常山县', '330800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2469', '330802', '柯城区', '330800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2470', '330881', '江山市', '330800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2471', '330825', '龙游县', '330800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2472', '330726', '浦江县', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2473', '330784', '永康市', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2474', '330781', '兰溪市', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2475', '330783', '东阳市', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2476', '330727', '磐安县', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2477', '330723', '武义县', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2478', '330782', '义乌市', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2479', '330703', '金东区', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2480', '330702', '婺城区', '330700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2481', '130283', '迁安市', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2482', '130227', '迁西县', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2483', '130281', '遵化市', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2484', '130284', '滦州市', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2485', '130224', '滦南县', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2486', '130209', '曹妃甸区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2487', '130225', '乐亭县', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2488', '130229', '玉田县', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2489', '130202', '路南区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2490', '130207', '丰南区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2491', '130203', '路北区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2492', '130208', '丰润区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2493', '130204', '古冶区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2494', '130205', '开平区', '130200', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2495', '130804', '鹰手营子矿区', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2496', '130828', '围场满族蒙古族自治县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2497', '130825', '隆化县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2498', '130826', '丰宁满族自治县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2499', '130827', '宽城满族自治县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2500', '130822', '兴隆县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2501', '130881', '平泉市', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2502', '130824', '滦平县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2503', '130803', '双滦区', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2504', '130802', '双桥区', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2505', '130821', '承德县', '130800', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2506', '131028', '大厂回族自治县', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2507', '131081', '霸州市', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2508', '131025', '大城县', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2509', '131024', '香河县', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2510', '131022', '固安县', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2511', '131023', '永清县', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2512', '131026', '文安县', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2513', '131003', '广阳区', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2514', '131082', '三河市', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2515', '131002', '安次区', '131000', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2516', '130321', '青龙满族自治县', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2517', '130303', '山海关区', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2518', '130324', '卢龙县', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2519', '130302', '海港区', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2520', '130306', '抚宁区', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2521', '130304', '北戴河区', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2522', '130322', '昌黎县', '130300', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2523', '130630', '涞源县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2524', '130633', '易县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2525', '130626', '定兴县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2526', '130609', '徐水区', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2527', '130638', '雄县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2528', '130624', '阜平县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2529', '130629', '容城县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2530', '130636', '顺平县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2531', '130632', '安新县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2532', '130631', '望都县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2533', '130628', '高阳县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2534', '130602', '竞秀区', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2535', '130634', '曲阳县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2536', '130627', '唐县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2537', '130607', '满城区', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2538', '130683', '安国市', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2539', '130682', '定州市', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2540', '130606', '莲池区', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2541', '130608', '清苑区', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2542', '130623', '涞水县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2543', '130637', '博野县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2544', '130635', '蠡县', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2545', '130681', '涿州市', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2546', '130684', '高碑店市', '130600', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2547', '130131', '平山县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2548', '130126', '灵寿县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2549', '130125', '行唐县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2550', '130123', '正定县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2551', '130109', '藁城区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2552', '130130', '无极县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2553', '130110', '鹿泉区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2554', '130183', '晋州市', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2555', '130108', '裕华区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2556', '130111', '栾城区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2557', '130132', '元氏县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2558', '130133', '赵县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2559', '130129', '赞皇县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2560', '130127', '高邑县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2561', '130184', '新乐市', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2562', '130105', '新华区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2563', '130181', '辛集市', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2564', '130104', '桥西区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2565', '130102', '长安区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2566', '130121', '井陉县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2567', '130107', '井陉矿区', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2568', '130128', '深泽县', '130100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2569', '130426', '涉县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2570', '130481', '武安市', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2571', '130425', '大名县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2572', '130406', '峰峰矿区', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2573', '130423', '临漳县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2574', '130432', '广平县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2575', '130434', '魏县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2576', '130407', '肥乡区', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2577', '130435', '曲周县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2578', '130431', '鸡泽县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2579', '130404', '复兴区', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2580', '130403', '丛台区', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2581', '130408', '永年区', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2582', '130427', '磁县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2583', '130424', '成安县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2584', '130402', '邯山区', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2585', '130433', '馆陶县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2586', '130430', '邱县', '130400', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2587', '130524', '柏乡县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2588', '130522', '临城县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2589', '130534', '清河县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2590', '130528', '宁晋县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2591', '130530', '新河县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2592', '130581', '南宫市', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2593', '130532', '平乡县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2594', '130531', '广宗县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2595', '130523', '内丘县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2596', '130527', '南和县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2597', '130525', '隆尧县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2598', '130529', '巨鹿县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2599', '130582', '沙河市', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2600', '130503', '桥西区', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2601', '130526', '任县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2602', '130521', '邢台县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2603', '130502', '桥东区', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2604', '130533', '威县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2605', '130535', '临西县', '130500', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2606', '130731', '涿鹿县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2607', '130725', '尚义县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2608', '130730', '怀来县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2609', '130723', '康保县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2610', '130709', '崇礼区', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2611', '130727', '阳原县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2612', '130722', '张北县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2613', '130708', '万全区', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2614', '130724', '沽源县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2615', '130732', '赤城县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2616', '130706', '下花园区', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2617', '130726', '蔚县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2618', '130705', '宣化区', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2619', '130703', '桥西区', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2620', '130702', '桥东区', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2621', '130728', '怀安县', '130700', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2622', '130982', '任丘市', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2623', '130984', '河间市', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2624', '130929', '献县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2625', '130902', '新华区', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2626', '130903', '运河区', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2627', '130926', '肃宁县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2628', '130927', '南皮县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2629', '130930', '孟村回族自治县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2630', '130924', '海兴县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2631', '130925', '盐山县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2632', '130923', '东光县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2633', '130928', '吴桥县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2634', '130983', '黄骅市', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2635', '130922', '青县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2636', '130921', '沧县', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2637', '130981', '泊头市', '130900', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2638', '131123', '武强县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2639', '131102', '桃城区', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2640', '131122', '武邑县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2641', '131103', '冀州区', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2642', '131121', '枣强县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2643', '131126', '故城县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2644', '131127', '景县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2645', '131182', '深州市', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2646', '131128', '阜城县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2647', '131125', '安平县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2648', '131124', '饶阳县', '131100', '3', '1', '1554254371', '1554254371', null);
INSERT INTO `system_area` VALUES ('2649', '620982', '敦煌市', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2650', '620921', '金塔县', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2651', '620902', '肃州区', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2652', '620981', '玉门市', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2653', '620923', '肃北蒙古族自治县', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2654', '620922', '瓜州县', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2655', '620924', '阿克塞哈萨克族自治县', '620900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2656', '620321', '永昌县', '620300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2657', '620302', '金川区', '620300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2658', '620121', '永登县', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2659', '620122', '皋兰县', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2660', '620111', '红古区', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2661', '620104', '西固区', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2662', '620103', '七里河区', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2663', '620105', '安宁区', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2664', '620102', '城关区', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2665', '620123', '榆中县', '620100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2666', '620825', '庄浪县', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2667', '620823', '崇信县', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2668', '620802', '崆峒区', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2669', '620822', '灵台县', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2670', '620826', '静宁县', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2671', '620881', '华亭市', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2672', '620821', '泾川县', '620800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2673', '620421', '靖远县', '620400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2674', '620402', '白银区', '620400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2675', '620403', '平川区', '620400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2676', '620422', '会宁县', '620400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2677', '620423', '景泰县', '620400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2678', '620522', '秦安县', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2679', '620525', '张家川回族自治县', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2680', '620523', '甘谷县', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2681', '620521', '清水县', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2682', '620502', '秦州区', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2683', '620524', '武山县', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2684', '620503', '麦积区', '620500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2685', '620602', '凉州区', '620600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2686', '620622', '古浪县', '620600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2687', '620623', '天祝藏族自治县', '620600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2688', '620621', '民勤县', '620600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2689', '621226', '礼县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2690', '621227', '徽县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2691', '621228', '两当县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2692', '621221', '成县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2693', '621202', '武都区', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2694', '621223', '宕昌县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2695', '621224', '康县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2696', '621222', '文县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2697', '621225', '西和县', '621200', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2698', '623027', '夏河县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2699', '623021', '临潭县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2700', '623025', '玛曲县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2701', '623023', '舟曲县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2702', '623024', '迭部县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2703', '623022', '卓尼县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2704', '623001', '合作市', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2705', '623026', '碌曲县', '623000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2706', '622923', '永靖县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2707', '622926', '东乡族自治县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2708', '622927', '积石山保安族东乡族撒拉族自治县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2709', '622921', '临夏县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2710', '622901', '临夏市', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2711', '622925', '和政县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2712', '622924', '广河县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2713', '622922', '康乐县', '622900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2714', '620724', '高台县', '620700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2715', '620721', '肃南裕固族自治县', '620700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2716', '620722', '民乐县', '620700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2717', '620725', '山丹县', '620700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2718', '620702', '甘州区', '620700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2719', '620723', '临泽县', '620700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2720', '621024', '合水县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2721', '621025', '正宁县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2722', '621021', '庆城县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2723', '621002', '西峰区', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2724', '621027', '镇原县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2725', '621023', '华池县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2726', '621022', '环县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2727', '621026', '宁县', '621000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2728', '621126', '岷县', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2729', '621122', '陇西县', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2730', '621123', '渭源县', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2731', '621124', '临洮县', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2732', '621125', '漳县', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2733', '621121', '通渭县', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2734', '621102', '安定区', '621100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2735', '510811', '昭化区', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2736', '510823', '剑阁县', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2737', '510812', '朝天区', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2738', '510824', '苍溪县', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2739', '510821', '旺苍县', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2740', '510822', '青川县', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2741', '510802', '利州区', '510800', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2742', '511321', '南部县', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2743', '511304', '嘉陵区', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2744', '511325', '西充县', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2745', '511322', '营山县', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2746', '511323', '蓬安县', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2747', '511302', '顺庆区', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2748', '511381', '阆中市', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2749', '511324', '仪陇县', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2750', '511303', '高坪区', '511300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2751', '511921', '通江县', '511900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2752', '511902', '巴州区', '511900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2753', '511923', '平昌县', '511900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2754', '511922', '南江县', '511900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2755', '511903', '恩阳区', '511900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2756', '510603', '旌阳区', '510600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2757', '510681', '广汉市', '510600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2758', '510623', '中江县', '510600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2759', '510682', '什邡市', '510600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2760', '510604', '罗江区', '510600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2761', '510683', '绵竹市', '510600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2762', '510781', '江油市', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2763', '510725', '梓潼县', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2764', '510704', '游仙区', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2765', '510722', '三台县', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2766', '510705', '安州区', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2767', '510723', '盐亭县', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2768', '510726', '北川羌族自治县', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2769', '510703', '涪城区', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2770', '510727', '平武县', '510700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2771', '510182', '彭州市', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2772', '510181', '都江堰市', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2773', '510113', '青白江区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2774', '510184', '崇州市', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2775', '510129', '大邑县', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2776', '510131', '蒲江县', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2777', '510185', '简阳市', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2778', '510121', '金堂县', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2779', '510132', '新津县', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2780', '510183', '邛崃市', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2781', '510105', '青羊区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2782', '510115', '温江区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2783', '510106', '金牛区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2784', '510116', '双流区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2785', '510107', '武侯区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2786', '510117', '郫都区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2787', '510114', '新都区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2788', '510112', '龙泉驿区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2789', '510108', '成华区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2790', '510104', '锦江区', '510100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2791', '511622', '武胜县', '511600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2792', '511623', '邻水县', '511600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2793', '511602', '广安区', '511600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2794', '511621', '岳池县', '511600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2795', '511603', '前锋区', '511600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2796', '511681', '华蓥市', '511600', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2797', '511724', '大竹县', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2798', '511722', '宣汉县', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2799', '511703', '达川区', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2800', '511781', '万源市', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2801', '511725', '渠县', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2802', '511723', '开江县', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2803', '511702', '通川区', '511700', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2804', '510923', '大英县', '510900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2805', '510922', '射洪县', '510900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2806', '510921', '蓬溪县', '510900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2807', '510904', '安居区', '510900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2808', '510903', '船山区', '510900', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2809', '512002', '雁江区', '512000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2810', '512021', '安岳县', '512000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2811', '512022', '乐至县', '512000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2812', '511421', '仁寿县', '511400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2813', '511424', '丹棱县', '511400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2814', '511423', '洪雅县', '511400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2815', '511425', '青神县', '511400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2816', '511403', '彭山区', '511400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2817', '511402', '东坡区', '511400', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2818', '511025', '资中县', '511000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2819', '511011', '东兴区', '511000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2820', '511024', '威远县', '511000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2821', '511083', '隆昌市', '511000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2822', '511002', '市中区', '511000', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2823', '510321', '荣县', '510300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2824', '510304', '大安区', '510300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2825', '510322', '富顺县', '510300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2826', '510303', '贡井区', '510300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2827', '510311', '沿滩区', '510300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2828', '510302', '自流井区', '510300', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2829', '511126', '夹江县', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2830', '511124', '井研县', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2831', '511112', '五通桥区', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2832', '511111', '沙湾区', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2833', '511113', '金口河区', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2834', '511132', '峨边彝族自治县', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2835', '511123', '犍为县', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2836', '511129', '沐川县', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2837', '511102', '市中区', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2838', '511133', '马边彝族自治县', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2839', '511181', '峨眉山市', '511100', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2840', '510521', '泸县', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2841', '510504', '龙马潭区', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2842', '510524', '叙永县', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2843', '510503', '纳溪区', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2844', '510525', '古蔺县', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2845', '510502', '江阳区', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2846', '510522', '合江县', '510500', '3', '1', '1554254411', '1554254411', null);
INSERT INTO `system_area` VALUES ('2847', '513435', '甘洛县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2848', '513431', '昭觉县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2849', '513423', '盐源县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2850', '513434', '越西县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2851', '513436', '美姑县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2852', '513422', '木里藏族自治县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2853', '513433', '冕宁县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2854', '513401', '西昌市', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2855', '513437', '雷波县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2856', '513428', '普格县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2857', '513432', '喜德县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2858', '513429', '布拖县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2859', '513430', '金阳县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2860', '513426', '会东县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2861', '513425', '会理县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2862', '513424', '德昌县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2863', '513427', '宁南县', '513400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2864', '511525', '高县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2865', '511524', '长宁县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2866', '511503', '南溪区', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2867', '511526', '珙县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2868', '511528', '兴文县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2869', '511523', '江安县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2870', '511527', '筠连县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2871', '511502', '翠屏区', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2872', '511529', '屏山县', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2873', '511504', '叙州区', '511500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2874', '510421', '米易县', '510400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2875', '510403', '西区', '510400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2876', '510402', '东区', '510400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2877', '510411', '仁和区', '510400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2878', '510422', '盐边县', '510400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2879', '513232', '若尔盖县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2880', '513225', '九寨沟县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2881', '513231', '阿坝县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2882', '513233', '红原县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2883', '513228', '黑水县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2884', '513201', '马尔康市', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2885', '513226', '金川县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2886', '513222', '理县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2887', '513221', '汶川县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2888', '513224', '松潘县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2889', '513223', '茂县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2890', '513227', '小金县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2891', '513230', '壤塘县', '513200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2892', '511827', '宝兴县', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2893', '511826', '芦山县', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2894', '511825', '天全县', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2895', '511802', '雨城区', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2896', '511822', '荥经县', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2897', '511823', '汉源县', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2898', '511824', '石棉县', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2899', '511803', '名山区', '511800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2900', '513332', '石渠县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2901', '513328', '甘孜县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2902', '513330', '德格县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2903', '513327', '炉霍县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2904', '513329', '新龙县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2905', '513331', '白玉县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2906', '513326', '道孚县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2907', '513301', '康定市', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2908', '513334', '理塘县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2909', '513335', '巴塘县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2910', '513325', '雅江县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2911', '513337', '稻城县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2912', '513336', '乡城县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2913', '513324', '九龙县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2914', '513338', '得荣县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2915', '513323', '丹巴县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2916', '513333', '色达县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2917', '513322', '泸定县', '513300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2918', '220283', '舒兰市', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2919', '220204', '船营区', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2920', '220211', '丰满区', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2921', '220282', '桦甸市', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2922', '220203', '龙潭区', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2923', '220281', '蛟河市', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2924', '220202', '昌邑区', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2925', '220221', '永吉县', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2926', '220284', '磐石市', '220200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2927', '220183', '德惠市', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2928', '220182', '榆树市', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2929', '220112', '双阳区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2930', '220122', '农安县', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2931', '220104', '朝阳区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2932', '220106', '绿园区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2933', '220103', '宽城区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2934', '220105', '二道区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2935', '220102', '南关区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2936', '220113', '九台区', '220100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2937', '220881', '洮南市', '220800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2938', '220882', '大安市', '220800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2939', '220822', '通榆县', '220800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2940', '220802', '洮北区', '220800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2941', '220821', '镇赉县', '220800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2942', '220781', '扶余市', '220700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2943', '220702', '宁江区', '220700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2944', '220721', '前郭尔罗斯蒙古族自治县', '220700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2945', '220723', '乾安县', '220700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2946', '220722', '长岭县', '220700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2947', '220402', '龙山区', '220400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2948', '220403', '西安区', '220400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2949', '220422', '东辽县', '220400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2950', '220421', '东丰县', '220400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2951', '220303', '铁东区', '220300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2952', '220382', '双辽市', '220300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2953', '220381', '公主岭市', '220300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2954', '220323', '伊通满族自治县', '220300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2955', '220322', '梨树县', '220300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2956', '220302', '铁西区', '220300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2957', '222403', '敦化市', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2958', '222404', '珲春市', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2959', '222402', '图们市', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2960', '222406', '和龙市', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2961', '222424', '汪清县', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2962', '222401', '延吉市', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2963', '222426', '安图县', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2964', '222405', '龙井市', '222400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2965', '220622', '靖宇县', '220600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2966', '220605', '江源区', '220600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2967', '220623', '长白朝鲜族自治县', '220600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2968', '220621', '抚松县', '220600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2969', '220602', '浑江区', '220600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2970', '220681', '临江市', '220600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2971', '220503', '二道江区', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2972', '220502', '东昌区', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2973', '220521', '通化县', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2974', '220524', '柳河县', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2975', '220581', '梅河口市', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2976', '220523', '辉南县', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2977', '220582', '集安市', '220500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2978', '120101', '和平区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2979', '120115', '宝坻区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2980', '120105', '河北区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2981', '120103', '河西区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2982', '120110', '东丽区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2983', '120112', '津南区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2984', '120102', '河东区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2985', '120118', '静海区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2986', '120116', '滨海新区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2987', '120119', '蓟州区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2988', '120114', '武清区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2989', '120111', '西青区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2990', '120104', '南开区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2991', '120117', '宁河区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2992', '120106', '红桥区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2993', '120113', '北辰区', '120100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2994', '530626', '绥江县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2995', '530625', '永善县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2996', '530681', '水富市', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2997', '530624', '大关县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2998', '530629', '威信县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('2999', '530622', '巧家县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3000', '530627', '镇雄县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3001', '530628', '彝良县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3002', '530602', '昭阳区', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3003', '530621', '鲁甸县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3004', '530623', '盐津县', '530600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3005', '530326', '会泽县', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3006', '530303', '沾益区', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3007', '530304', '马龙区', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3008', '530302', '麒麟区', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3009', '530324', '罗平县', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3010', '530322', '陆良县', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3011', '530323', '师宗县', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3012', '530381', '宣威市', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3013', '530325', '富源县', '530300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3014', '532527', '泸西县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3015', '532504', '弥勒市', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3016', '532524', '建水县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3017', '532525', '石屏县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3018', '532502', '开远市', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3019', '532503', '蒙自市', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3020', '532501', '个旧市', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3021', '532529', '红河县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3022', '532523', '屏边苗族自治县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3023', '532528', '元阳县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3024', '532531', '绿春县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3025', '532530', '金平苗族瑶族傣族自治县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3026', '532532', '河口瑶族自治县', '532500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3027', '533324', '贡山独龙族怒族自治县', '533300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3028', '533323', '福贡县', '533300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3029', '533325', '兰坪白族普米族自治县', '533300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3030', '533301', '泸水市', '533300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3031', '532801', '景洪市', '532800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3032', '532822', '勐海县', '532800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3033', '532823', '勐腊县', '532800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3034', '530425', '易门县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3035', '530422', '澄江县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3036', '530424', '华宁县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3037', '530402', '红塔区', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3038', '530426', '峨山彝族自治县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3039', '530403', '江川区', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3040', '530427', '新平彝族傣族自治县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3041', '530423', '通海县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3042', '530428', '元江哈尼族彝族傣族自治县', '530400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3043', '532931', '剑川县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3044', '532932', '鹤庆县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3045', '532930', '洱源县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3046', '532929', '云龙县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3047', '532924', '宾川县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3048', '532901', '大理市', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3049', '532922', '漾濞彝族自治县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3050', '532923', '祥云县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3051', '532928', '永平县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3052', '532927', '巍山彝族回族自治县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3053', '532925', '弥渡县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3054', '532926', '南涧彝族自治县', '532900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3055', '530724', '宁蒗彝族自治县', '530700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3056', '530721', '玉龙纳西族自治县', '530700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3057', '530702', '古城区', '530700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3058', '530722', '永胜县', '530700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3059', '530723', '华坪县', '530700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3060', '533422', '德钦县', '533400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3061', '533401', '香格里拉市', '533400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3062', '533423', '维西傈僳族自治县', '533400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3063', '532627', '广南县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3064', '532626', '丘北县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3065', '532622', '砚山县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3066', '532623', '西畴县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3067', '532601', '文山市', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3068', '532624', '麻栗坡县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3069', '532625', '马关县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3070', '532628', '富宁县', '532600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3071', '530581', '腾冲市', '530500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3072', '530502', '隆阳区', '530500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3073', '530524', '昌宁县', '530500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3074', '530521', '施甸县', '530500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3075', '530523', '龙陵县', '530500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3076', '530823', '景东彝族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3077', '530825', '镇沅彝族哈尼族拉祜族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3078', '530822', '墨江哈尼族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3079', '530824', '景谷傣族彝族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3080', '530821', '宁洱哈尼族彝族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3081', '530828', '澜沧拉祜族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3082', '530802', '思茅区', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3083', '530829', '西盟佤族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3084', '530827', '孟连傣族拉祜族佤族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3085', '530826', '江城哈尼族彝族自治县', '530800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3086', '530113', '东川区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3087', '530129', '寻甸回族彝族自治县', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3088', '530102', '五华区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3089', '530114', '呈贡区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3090', '530112', '西山区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3091', '530125', '宜良县', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3092', '530126', '石林彝族自治县', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3093', '530115', '晋宁区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3094', '530181', '安宁市', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3095', '530124', '富民县', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3096', '530128', '禄劝彝族苗族自治县', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3097', '530111', '官渡区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3098', '530127', '嵩明县', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3099', '530103', '盘龙区', '530100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3100', '532326', '大姚县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3101', '532328', '元谋县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3102', '532323', '牟定县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3103', '532325', '姚安县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3104', '532331', '禄丰县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3105', '532324', '南华县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3106', '532301', '楚雄市', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3107', '532322', '双柏县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3108', '532329', '武定县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3109', '532327', '永仁县', '532300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3110', '530921', '凤庆县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3111', '530922', '云县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3112', '530923', '永德县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3113', '530902', '临翔区', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3114', '530926', '耿马傣族佤族自治县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3115', '530924', '镇康县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3116', '530925', '双江拉祜族佤族布朗族傣族自治县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3117', '530927', '沧源佤族自治县', '530900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3118', '533123', '盈江县', '533100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3119', '533122', '梁河县', '533100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3120', '533124', '陇川县', '533100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3121', '533103', '芒市', '533100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3122', '533102', '瑞丽市', '533100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3123', '110116', '怀柔区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3124', '110118', '密云区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3125', '110119', '延庆区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3126', '110106', '丰台区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3127', '110109', '门头沟区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3128', '110113', '顺义区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3129', '110105', '朝阳区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3130', '110107', '石景山区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3131', '110117', '平谷区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3132', '110112', '通州区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3133', '110115', '大兴区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3134', '110114', '昌平区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3135', '110108', '海淀区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3136', '110102', '西城区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3137', '110101', '东城区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3138', '110111', '房山区', '110100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3139', '140321', '平定县', '140300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3140', '140303', '矿区', '140300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3141', '140311', '郊区', '140300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3142', '140302', '城区', '140300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3143', '140322', '盂县', '140300', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3144', '140426', '黎城县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3145', '140405', '屯留区', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3146', '140406', '潞城区', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3147', '140428', '长子县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3148', '140427', '壶关县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3149', '140404', '上党区', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3150', '140431', '沁源县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3151', '140425', '平顺县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3152', '140423', '襄垣县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3153', '140403', '潞州区', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3154', '140430', '沁县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3155', '140429', '武乡县', '140400', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3156', '141034', '汾西县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3157', '141026', '安泽县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3158', '141031', '隰县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3159', '141025', '古县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3160', '141030', '大宁县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3161', '141024', '洪洞县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3162', '141002', '尧都区', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3163', '141027', '浮山县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3164', '141028', '吉县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3165', '141021', '曲沃县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3166', '141023', '襄汾县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3167', '141082', '霍州市', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3168', '141029', '乡宁县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3169', '141081', '侯马市', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3170', '141022', '翼城县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3171', '141032', '永和县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3172', '141033', '蒲县', '141000', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3173', '140123', '娄烦县', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3174', '140108', '尖草坪区', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3175', '140109', '万柏林区', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3176', '140110', '晋源区', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3177', '140107', '杏花岭区', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3178', '140105', '小店区', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3179', '140106', '迎泽区', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3180', '140122', '阳曲县', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3181', '140121', '清徐县', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3182', '140181', '古交市', '140100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3183', '140823', '闻喜县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3184', '140802', '盐湖区', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3185', '140822', '万荣县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3186', '140828', '夏县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3187', '140821', '临猗县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3188', '140881', '永济市', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3189', '140829', '平陆县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3190', '140830', '芮城县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3191', '140827', '垣曲县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3192', '140826', '绛县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3193', '140825', '新绛县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3194', '140824', '稷山县', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3195', '140882', '河津市', '140800', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3196', '140928', '五寨县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3197', '140923', '代县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3198', '140932', '偏关县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3199', '140930', '河曲县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3200', '140981', '原平市', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3201', '140925', '宁武县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3202', '140922', '五台县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3203', '140921', '定襄县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3204', '140902', '忻府区', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3205', '140929', '岢岚县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3206', '140926', '静乐县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3207', '140927', '神池县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3208', '140924', '繁峙县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3209', '140931', '保德县', '140900', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3210', '140623', '右玉县', '140600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3211', '140603', '平鲁区', '140600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3212', '140622', '应县', '140600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3213', '140621', '山阴县', '140600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3214', '140602', '朔城区', '140600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3215', '140681', '怀仁市', '140600', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3216', '140581', '高平市', '140500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3217', '140502', '城区', '140500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3218', '140525', '泽州县', '140500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3219', '140524', '陵川县', '140500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3220', '140521', '沁水县', '140500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3221', '140522', '阳城县', '140500', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3222', '140724', '昔阳县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3223', '140723', '和顺县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3224', '140728', '平遥县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3225', '140726', '太谷县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3226', '140702', '榆次区', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3227', '140725', '寿阳县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3228', '140781', '介休市', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3229', '140721', '榆社县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3230', '140722', '左权县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3231', '140727', '祁县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3232', '140729', '灵石县', '140700', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3233', '141127', '岚县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3234', '141124', '临县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3235', '141128', '方山县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3236', '141102', '离石区', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3237', '141125', '柳林县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3238', '141130', '交口县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3239', '141129', '中阳县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3240', '141123', '兴县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3241', '141126', '石楼县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3242', '141122', '交城县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3243', '141181', '孝义市', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3244', '141182', '汾阳市', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3245', '141121', '文水县', '141100', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3246', '140223', '广灵县', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3247', '140225', '浑源县', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3248', '140224', '灵丘县', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3249', '140226', '左云县', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3250', '140221', '阳高县', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3251', '140212', '新荣区', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3252', '140214', '云冈区', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3253', '140215', '云州区', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3254', '140213', '平城区', '140200', '3', '1', '1554254434', '1554254434', null);
INSERT INTO `system_area` VALUES ('3255', '140222', '天镇县', '140200', '3', '1', '1554254434', '1554254434', null);

-- ----------------------------
-- Table structure for `system_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_group`;
CREATE TABLE `system_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `title` char(100) NOT NULL DEFAULT '' COMMENT '权限组名称',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=管理 2=商户',
  `rules` text COMMENT '权限规则',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1可用0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='权限组表';

-- ----------------------------
-- Records of system_auth_group
-- ----------------------------
INSERT INTO `system_auth_group` VALUES ('1', '1', '0', '1', '1', '1', null, '1525481721');
INSERT INTO `system_auth_group` VALUES ('2', '123', '0', '2,', '1', '1524982377', null, '1524983481');
INSERT INTO `system_auth_group` VALUES ('3', '123', '0', '2,', '1', '1524982388', null, '1524983484');
INSERT INTO `system_auth_group` VALUES ('4', '123', '0', '2,', '1', '1524982448', null, '1524983486');
INSERT INTO `system_auth_group` VALUES ('5', '短信管理员', '1', '36,37,38,39,97,41,42,43,44,98,109,', '1', '1524982458', '1559182248', null);
INSERT INTO `system_auth_group` VALUES ('6', '123', '0', '2,3,,', '1', '1524982541', null, '1524983489');
INSERT INTO `system_auth_group` VALUES ('7', '群主', '1', '0,', '1', '1524983510', '1539592815', '1550907257');
INSERT INTO `system_auth_group` VALUES ('8', '社区', '0', '0,', '1', '1524983517', '1532671082', '1550907255');
INSERT INTO `system_auth_group` VALUES ('9', '臧诗岩', '2', '', '1', '1532671117', '1532671136', '1550907251');
INSERT INTO `system_auth_group` VALUES ('10', '商户管理', '2', '', '1', '1542071757', null, '1550907205');
INSERT INTO `system_auth_group` VALUES ('11', '1', '1', '', '1', '1547883695', null, '1550907165');
INSERT INTO `system_auth_group` VALUES ('12', '超级管理员', '1', '263,264,265,266,251,252,253,254,255,33,46,47,48,49,99,51,52,53,54,100,15,16,17,18,91,116,111,112,113,114,115,352,353,354,355,356,317,347,348,350,10,11,12,13,85,86,87,88,89,90,31,30,282,283,284,285,286,288,249,268,269,270,271,272,273,257,258,259,260,261,61,62,63,64,102,66,67,68,69,103,319,320,321,322,323,56,57,58,59,101,275,276,277,278,279,76,77,78,79,105,81,82,83,84,106,71,72,73,74,104,300,301,302,303,304,312,313,314,315,316,306,307,308,309,310,36,37,38,39,97,41,42,43,44,98,109,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,342,343,344,345,346,25,26,27,28,95,96,20,21,22,23,92,93,94,', '1', '1552089900', '1576739122', null);

-- ----------------------------
-- Table structure for `system_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_group_access`;
CREATE TABLE `system_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=管理 2=商户',
  `group_ids` varchar(52) NOT NULL DEFAULT '' COMMENT '权限组ids',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  UNIQUE KEY `uid_group_id` (`uid`,`group_ids`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_ids`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限对应表';

-- ----------------------------
-- Records of system_auth_group_access
-- ----------------------------
INSERT INTO `system_auth_group_access` VALUES ('1', '1', '12', '1554977140', '1571964511', null);
INSERT INTO `system_auth_group_access` VALUES ('3', '2', '10', '1542071782', null, null);
INSERT INTO `system_auth_group_access` VALUES ('4', '2', '10', '1544514322', null, null);
INSERT INTO `system_auth_group_access` VALUES ('7', '2', '10', '1548752770', null, null);
INSERT INTO `system_auth_group_access` VALUES ('34', '0', '8', '1525428440', null, null);
INSERT INTO `system_auth_group_access` VALUES ('35', '1', '7', '1539930565', null, '1551830390');
INSERT INTO `system_auth_group_access` VALUES ('36', '1', '12', '1554977140', '1559037617', '1559715343');
INSERT INTO `system_auth_group_access` VALUES ('37', '1', '12', '1558942209', null, '1558945128');
INSERT INTO `system_auth_group_access` VALUES ('38', '1', '5', '1568081706', null, '1571727243');

-- ----------------------------
-- Table structure for `system_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_rule`;
CREATE TABLE `system_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父id',
  `name` char(80) NOT NULL COMMENT '权限名称',
  `title` char(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '权限标题',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型 1=管理 2=商城',
  `rule_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0操作 1页面 2模块',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1可用0禁用',
  `condition` char(100) NOT NULL COMMENT '触发条件',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `menu_url` varchar(50) DEFAULT NULL COMMENT '菜单路由地址',
  `menu_name` varchar(50) DEFAULT NULL COMMENT '菜单名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of system_auth_rule
-- ----------------------------
INSERT INTO `system_auth_rule` VALUES ('1', '0', '用户', '用户', '1', '1', '1', '', '', 'users', '用户', '0', '1529542410', null, null);
INSERT INTO `system_auth_rule` VALUES ('2', '1', '商户管理-商户列表', '商户列表', '1', '1', '1', '', '', 'users/merchant', '商户管理', '0', '1529542444', '1555134537', null);
INSERT INTO `system_auth_rule` VALUES ('3', '1', '管理员管理', '管理员管理', '1', '1', '1', '', '', 'users/staff', '管理员管理', '0', '1529542465', null, null);
INSERT INTO `system_auth_rule` VALUES ('4', '0', '系统', '系统', '1', '1', '1', '', '', 'set', '系统', '0', '1529542497', null, null);
INSERT INTO `system_auth_rule` VALUES ('5', '4', '基础', '基础', '1', '1', '1', '', '', 'set/system', '基础', '0', '1529542549', null, null);
INSERT INTO `system_auth_rule` VALUES ('6', '4', '我的设置', '我的设置', '1', '1', '1', '', '', 'user', '我的设置', '0', '1529542565', null, null);
INSERT INTO `system_auth_rule` VALUES ('7', '0', '应用管理', '应用管理', '1', '1', '1', '', '', 'application', '应用管理', '0', '1529542580', null, null);
INSERT INTO `system_auth_rule` VALUES ('8', '0', '活动', '活动', '1', '1', '1', '', '', 'voucher', '活动', '0', '1529542602', null, null);
INSERT INTO `system_auth_rule` VALUES ('9', '2', '商户列表', '商户列表', '1', '1', '1', '', '', 'users/merchant/list', '商户列表', '0', '1529542885', '1529543039', null);
INSERT INTO `system_auth_rule` VALUES ('10', '9', '商户列表', '新增', '1', '0', '1', 'admin_user_merchant_add_post', '', '', '', '1', '1529543078', '1529543452', null);
INSERT INTO `system_auth_rule` VALUES ('11', '9', '商户列表', '删除', '1', '0', '1', 'admin_user_merchant_delete_delete', '', '', '', '2', '1529543276', null, null);
INSERT INTO `system_auth_rule` VALUES ('12', '9', '商户列表', '编辑', '1', '0', '1', 'admin_user_merchant_update_put', '', '', '', '3', '1529543365', null, null);
INSERT INTO `system_auth_rule` VALUES ('13', '9', '商户列表', '查询', '1', '0', '1', 'admin_user_merchant_list_get', '', '', '', '4', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('14', '3', '员工管理', '员工管理', '1', '1', '1', '', '', 'users/staff/list', '员工管理', '0', '1529543994', '1540274202', null);
INSERT INTO `system_auth_rule` VALUES ('15', '14', '员工管理', '新增', '1', '0', '1', 'admin_user_user_add_post', '', '', '', '1', '1529544056', null, null);
INSERT INTO `system_auth_rule` VALUES ('16', '14', '员工管理', '删除', '1', '0', '1', 'admin_user_user_delete_delete', '', '', '', '2', '1529544082', null, null);
INSERT INTO `system_auth_rule` VALUES ('17', '14', '员工管理', '编辑', '1', '0', '1', 'admin_user_user_update_put', '', '', '', '3', '1529544122', null, null);
INSERT INTO `system_auth_rule` VALUES ('18', '14', '员工管理', '查询', '1', '0', '1', 'admin_user_user_finds_get', '', '', '', '4', '1529544163', null, null);
INSERT INTO `system_auth_rule` VALUES ('19', '5', '角色管理', '角色管理', '1', '1', '1', '', '', 'set/system/group', '角色管理', '0', '1529544299', null, null);
INSERT INTO `system_auth_rule` VALUES ('20', '19', '角色管理', '新增', '1', '0', '1', 'admin_user_group_add_post', '', '', '', '1', '1529544458', null, null);
INSERT INTO `system_auth_rule` VALUES ('21', '19', '角色管理', '删除', '1', '0', '1', 'admin_user_group_delete_delete', '', '', '', '2', '1529544504', null, null);
INSERT INTO `system_auth_rule` VALUES ('22', '19', '角色管理', '编辑', '1', '0', '1', 'admin_user_group_update_put', '', '', '', '3', '1529544549', null, null);
INSERT INTO `system_auth_rule` VALUES ('23', '19', '角色管理', '查询', '1', '0', '1', 'admin_user_group_list_get', '', '', '', '4', '1529544582', null, null);
INSERT INTO `system_auth_rule` VALUES ('24', '5', '菜单权限管理', '菜单权限管理', '1', '1', '1', '', '', 'set/system/jurisdiction', '菜单权限管理', '0', '1529544620', null, null);
INSERT INTO `system_auth_rule` VALUES ('25', '24', '菜单权限管理', '新增', '1', '0', '1', 'admin_user_rule_add_post', '', '', '', '1', '1529544766', null, null);
INSERT INTO `system_auth_rule` VALUES ('26', '24', '菜单权限管理', '删除', '1', '0', '1', 'admin_user_rule_delete_delete', '', '', '', '2', '1529544821', null, null);
INSERT INTO `system_auth_rule` VALUES ('27', '24', '菜单权限管理', '编辑', '1', '0', '1', 'admin_user_rule_update_put', '', '', '', '3', '1529544886', null, null);
INSERT INTO `system_auth_rule` VALUES ('28', '24', '菜单权限管理', '查询', '1', '0', '1', 'admin_user_rule_list_get', '', '', '', '4', '1529544915', null, null);
INSERT INTO `system_auth_rule` VALUES ('29', '6', '基本资料', '基本资料', '1', '1', '1', '', '', 'user/info', '基本资料', '0', '1529545102', null, null);
INSERT INTO `system_auth_rule` VALUES ('30', '29', '基本资料', '编辑', '1', '0', '1', 'admin_user_user_updateinfo_put', '', '', '', '2', '1529545250', null, null);
INSERT INTO `system_auth_rule` VALUES ('31', '29', '基本资料', '查询', '1', '0', '1', 'admin_user_user_info_get', '', '', '', '1', '1529545332', null, null);
INSERT INTO `system_auth_rule` VALUES ('32', '6', '修改密码', '修改密码', '1', '1', '1', '', '', 'user/password', '修改密码', '0', '1529546672', '1540274196', null);
INSERT INTO `system_auth_rule` VALUES ('33', '32', '修改密码', '编辑', '1', '0', '1', 'admin_user_user_updatepassword_put', '', '', '', '1', '1529546725', null, null);
INSERT INTO `system_auth_rule` VALUES ('34', '4', '短信', '短信', '1', '1', '1', '', '', 'sms', '短信', '0', '1529546846', null, null);
INSERT INTO `system_auth_rule` VALUES ('35', '34', '短信模板', '短信模板', '1', '1', '1', '', '', 'sms/tempList', '短信模板', '0', '1529546901', null, null);
INSERT INTO `system_auth_rule` VALUES ('36', '35', '短信模板', '新增', '1', '0', '1', 'admin_message_template_add_post', '', '', '', '1', '1529546957', null, null);
INSERT INTO `system_auth_rule` VALUES ('37', '35', '短信模板', '删除', '1', '0', '1', 'admin_message_template_delete_delete', '', '', '', '2', '1529546992', null, null);
INSERT INTO `system_auth_rule` VALUES ('38', '35', '短信模板', '编辑', '1', '0', '1', 'admin_message_template_update_put', '', '', '', '3', '1529547037', null, null);
INSERT INTO `system_auth_rule` VALUES ('39', '35', '短信模板', '查询', '1', '0', '1', 'admin_message_template_finds_get', '', '', '', '4', '1529547602', null, null);
INSERT INTO `system_auth_rule` VALUES ('40', '0', '短信签名', '短信签名', '1', '1', '1', '', '', 'sms/signList', '短信签名', '0', '1529547689', null, null);
INSERT INTO `system_auth_rule` VALUES ('41', '40', '短信签名', '新增', '1', '0', '1', 'admin_message_signature_add_post', '', '', '', '1', '1529548011', null, null);
INSERT INTO `system_auth_rule` VALUES ('42', '40', '短信签名', '删除', '1', '0', '1', 'admin_message_signature_delete_delete', '', '', '', '2', '1529548118', null, null);
INSERT INTO `system_auth_rule` VALUES ('43', '40', '短信签名', '编辑', '1', '0', '1', 'admin_message_signature_update_put', '', '', '', '3', '1529548139', null, null);
INSERT INTO `system_auth_rule` VALUES ('44', '40', '短信签名', '查询', '1', '0', '1', 'admin_message_signature_finds_get', '', '', '', '4', '1529548409', null, null);
INSERT INTO `system_auth_rule` VALUES ('45', '4', '全局配置', '全局配置', '1', '1', '1', '', '', 'set/config', '全局配置', '0', '1529548579', null, null);
INSERT INTO `system_auth_rule` VALUES ('46', '45', '全局配置', '新增', '1', '0', '1', 'admin_config_config_add_post', '', '', '', '1', '1529548874', null, null);
INSERT INTO `system_auth_rule` VALUES ('47', '45', '全局配置', '删除', '1', '0', '1', 'admin_config_config_delete_delete', '', '', '', '2', '1529548919', '1529637550', null);
INSERT INTO `system_auth_rule` VALUES ('48', '45', '全局配置', '编辑', '1', '0', '1', 'admin_config_config_update_put', '', '', '', '3', '1529548946', null, null);
INSERT INTO `system_auth_rule` VALUES ('49', '45', '全局配置', '查询', '1', '0', '1', 'admin_config_config_list_get', '', '', '', '4', '1529548996', null, null);
INSERT INTO `system_auth_rule` VALUES ('50', '4', '全局配置类目', '全局配置类目', '1', '1', '1', '', '', 'set/configCategory', '全局配置类目', '0', '1529549883', null, null);
INSERT INTO `system_auth_rule` VALUES ('51', '50', '全局配置类目', '新增', '1', '0', '1', 'admin_config_category_add_post', '', '', '', '1', '1529549935', null, null);
INSERT INTO `system_auth_rule` VALUES ('52', '50', '全局配置类目', '删除', '1', '0', '1', 'admin_config_category_delete_delete', '', '', '', '2', '1529549976', null, null);
INSERT INTO `system_auth_rule` VALUES ('53', '50', '全局配置类目', '编辑', '1', '0', '1', 'admin_config_category_update_put', '', '', '', '3', '1529550051', null, null);
INSERT INTO `system_auth_rule` VALUES ('54', '50', '全局配置类目', '查询', '1', '0', '1', 'admin_config_category_list_get', '', '', '', '4', '1529550090', null, null);
INSERT INTO `system_auth_rule` VALUES ('55', '7', '应用类目', '应用类目', '1', '1', '1', '', '', 'application/category', '应用类目', '0', '1529550437', null, null);
INSERT INTO `system_auth_rule` VALUES ('56', '55', '应用类目', '新增', '1', '0', '1', 'admin_app_category_add_post', '', '', '', '1', '1529551885', null, null);
INSERT INTO `system_auth_rule` VALUES ('57', '55', '应用类目', '删除', '1', '0', '1', 'admin_app_category_delete_delete', '', '', '', '2', '1529551973', null, null);
INSERT INTO `system_auth_rule` VALUES ('58', '55', '应用类目', '编辑', '1', '0', '1', 'admin_app_category_update_put', '', '', '', '3', '1529551997', null, null);
INSERT INTO `system_auth_rule` VALUES ('59', '55', '应用类目', '查询', '1', '0', '1', 'admin_app_category_list_get', '', '', '', '4', '1529552018', null, null);
INSERT INTO `system_auth_rule` VALUES ('60', '7', '应用列表', '应用列表', '1', '1', '1', '', '', 'application/list', '应用列表', '0', '1529552049', null, null);
INSERT INTO `system_auth_rule` VALUES ('61', '60', '应用列表', '新增', '1', '0', '1', 'admin_app_app_add_post', '', '', '', '1', '1529560828', null, null);
INSERT INTO `system_auth_rule` VALUES ('62', '60', '应用列表', '删除', '1', '0', '1', 'admin_app_app_delete_delete', '', '', '', '2', '1529560925', null, null);
INSERT INTO `system_auth_rule` VALUES ('63', '60', '应用列表', '编辑', '1', '0', '1', 'admin_app_app_update_put', '', '', '', '3', '1529562653', null, null);
INSERT INTO `system_auth_rule` VALUES ('64', '60', '应用列表', '查询', '1', '0', '1', 'admin_app_app_list_get', '', '', '', '4', '1529562706', null, null);
INSERT INTO `system_auth_rule` VALUES ('65', '7', '应用管理-套餐管理', '应用管理-套餐管理', '1', '1', '1', '', '', 'application/combo', '应用管理-套餐管理', '0', '1529562774', '1559701872', null);
INSERT INTO `system_auth_rule` VALUES ('66', '65', '应用管理-套餐管理', '新增', '1', '0', '1', 'admin_app_combo_add_post', '', '', '', '1', '1529562923', '1557364822', null);
INSERT INTO `system_auth_rule` VALUES ('67', '65', '应用管理-套餐管理', '删除', '1', '0', '1', 'admin_app_combo_delete_delete', '', '', '', '2', '1529562948', '1557364828', null);
INSERT INTO `system_auth_rule` VALUES ('68', '65', '应用管理-套餐管理', '编辑', '1', '0', '1', 'admin_app_combo_update_put', '', '', '', '3', '1529562968', '1557364833', null);
INSERT INTO `system_auth_rule` VALUES ('69', '65', '应用管理-套餐管理', '查询', '1', '0', '1', 'admin_app_combo_list_get', '', '', '', '4', '1529562990', '1557364845', null);
INSERT INTO `system_auth_rule` VALUES ('70', '8', '抵用券类型', '抵用券类型', '1', '1', '1', '', '', 'voucher/type', '抵用券类型', '0', '1529563702', null, null);
INSERT INTO `system_auth_rule` VALUES ('71', '70', '抵用券类型', '新增', '1', '0', '1', 'admin_voucher_type_add_post', '', '', '', '1', '1529563760', null, null);
INSERT INTO `system_auth_rule` VALUES ('72', '70', '抵用券类型', '删除', '1', '0', '1', 'admin_voucher_type_delete_delete', '', '', '', '2', '1529563760', null, null);
INSERT INTO `system_auth_rule` VALUES ('73', '70', '抵用券类型', '编辑', '1', '0', '1', 'admin_voucher_type_update_put', '', '', '', '3', '1529563760', null, null);
INSERT INTO `system_auth_rule` VALUES ('74', '70', '抵用券类型', '查询', '1', '0', '1', 'admin_voucher_type_list_get', '', '', '', '4', '1529563760', null, null);
INSERT INTO `system_auth_rule` VALUES ('75', '8', '抵用券', '抵用券', '1', '1', '1', '', '', 'voucher/list', '抵用券', '0', '1529563942', null, null);
INSERT INTO `system_auth_rule` VALUES ('76', '75', '抵用券', '新增', '1', '0', '1', 'admin_voucher_voucher_add_post', '', '', '', '1', '1529564182', null, null);
INSERT INTO `system_auth_rule` VALUES ('77', '75', '抵用券', '删除', '1', '0', '1', 'admin_voucher_voucher_delete_delete', '', '', '', '2', '1529564182', null, null);
INSERT INTO `system_auth_rule` VALUES ('78', '75', '抵用券', '编辑', '1', '0', '1', 'admin_voucher_voucher_update_put', '', '', '', '3', '1529564182', null, null);
INSERT INTO `system_auth_rule` VALUES ('79', '75', '抵用券', '查询', '1', '0', '1', 'admin_voucher_voucher_list_get', '', '', '', '4', '1529564182', null, null);
INSERT INTO `system_auth_rule` VALUES ('80', '8', '抵用券活动', '抵用券活动', '1', '1', '1', '', '', 'voucher/activity', '抵用券活动', '0', '1529564428', null, null);
INSERT INTO `system_auth_rule` VALUES ('81', '80', '抵用券活动', '新增', '1', '0', '1', 'admin_voucher_channel_add_post', '', '', '', '1', '1529564482', null, null);
INSERT INTO `system_auth_rule` VALUES ('82', '80', '抵用券活动', '删除', '1', '0', '1', 'admin_voucher_channel_delete_delete', '', '', '', '2', '1529564482', null, null);
INSERT INTO `system_auth_rule` VALUES ('83', '80', '抵用券活动', '编辑', '1', '0', '1', 'admin_voucher_channel_update_put', '', '', '', '3', '1529564482', null, null);
INSERT INTO `system_auth_rule` VALUES ('84', '80', '抵用券活动', '查询', '1', '0', '1', 'admin_voucher_channel_list_get', '', '', '', '4', '1529564482', null, null);
INSERT INTO `system_auth_rule` VALUES ('85', '9', '商户列表', '查询单条', '1', '0', '1', 'admin_user_merchant_single_get', '', '', '', '5', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('86', '9', '商户列表', '获取商户微信配置', '1', '0', '1', 'admin_user_merchant_wx_get', '', '', '', '6', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('87', '9', '商户列表', '获取商户支付宝配置', '1', '0', '1', 'admin_user_merchant_ali_get', '', '', '', '7', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('88', '9', '商户列表', '商户微信更新', '1', '0', '1', 'admin_user_merchant_updatewx_put', '', '', '', '8', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('89', '9', '商户列表', '商户支付宝更新', '1', '0', '1', 'admin_user_merchant_updateali_put', '', '', '', '9', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('90', '9', '商户列表', '商户密匙更新', '1', '0', '1', 'admin_user_merchant_secret_put', '', '', '', '10', '1529543430', null, null);
INSERT INTO `system_auth_rule` VALUES ('91', '14', '员工管理', '查询单条', '1', '0', '1', 'admin_user_user_find_get', '', '', '', '5', '1529544163', null, null);
INSERT INTO `system_auth_rule` VALUES ('92', '19', '角色管理', '查询单条', '1', '0', '1', 'admin_user_group_single_get', '', '', '', '5', '1529544582', null, null);
INSERT INTO `system_auth_rule` VALUES ('93', '19', '角色管理', '获取角色权限', '1', '0', '1', 'admin_user_group_rule_get', '', '', '', '6', '1529544582', null, null);
INSERT INTO `system_auth_rule` VALUES ('94', '19', '角色管理', '获取角色用户信息', '1', '0', '1', 'admin_user_group_users_get', '', '', '', '7', '1529544582', null, null);
INSERT INTO `system_auth_rule` VALUES ('95', '24', '菜单权限管理', '查询单条', '1', '0', '1', 'admin_user_rule_single_get', '', '', '', '5', '1529544915', null, null);
INSERT INTO `system_auth_rule` VALUES ('96', '24', '菜单权限管理', '权限菜单', '1', '0', '1', 'admin_user_rule_menu_get', '', '', '', '6', '1529544915', null, null);
INSERT INTO `system_auth_rule` VALUES ('97', '35', '短信模板', '查询单条', '1', '0', '1', 'admin_message_template_find_get', '', '', '', '5', '1529547602', null, null);
INSERT INTO `system_auth_rule` VALUES ('98', '40', '短信签名', '查询单条', '1', '0', '1', 'admin_message_signature_find_get', '', '', '', '5', '1529548409', null, null);
INSERT INTO `system_auth_rule` VALUES ('99', '45', '全局配置', '查询单条', '1', '0', '1', 'admin_config_config_single_get', '', '', '', '5', '1529548996', null, null);
INSERT INTO `system_auth_rule` VALUES ('100', '50', '全局配置类目', '查询单条', '1', '0', '1', 'admin_config_category_single_get', '', '', '', '5', '1529550090', null, null);
INSERT INTO `system_auth_rule` VALUES ('101', '55', '应用类目', '查询单条', '1', '0', '1', 'admin_app_category_single_get', '', '', '', '5', '1529552018', null, null);
INSERT INTO `system_auth_rule` VALUES ('102', '60', '应用列表', '查询单条', '1', '0', '1', 'admin_app_app_single_get', '', '', '', '5', '1529562706', null, null);
INSERT INTO `system_auth_rule` VALUES ('103', '65', '应用管理-套餐管理', '查询单条', '1', '0', '1', 'admin_app_combo_single_get', '', '', '', '5', '1529562990', '1557364850', null);
INSERT INTO `system_auth_rule` VALUES ('104', '70', '抵用券类型', '查询单条', '1', '0', '1', 'admin_voucher_type_single_get', '', '', '', '5', '1529563760', null, null);
INSERT INTO `system_auth_rule` VALUES ('105', '75', '抵用券', '查询单条', '1', '0', '1', 'admin_voucher_voucher_single_get', '', '', '', '5', '1529564182', null, null);
INSERT INTO `system_auth_rule` VALUES ('106', '80', '抵用券活动', '查询单条', '1', '0', '1', 'admin_voucher_channel_single_get', '', '', '', '5', '1529564482', null, null);
INSERT INTO `system_auth_rule` VALUES ('109', '40', '短信签名', '列表状态编辑', '1', '0', '1', 'admin_message_signature_status_put', '', '', '', '6', '1529548139', null, null);
INSERT INTO `system_auth_rule` VALUES ('110', '4', '商品类目列表', '商品类目列表', '1', '1', '1', '', '', 'set/shopCategory', '商品类目列表', '0', '1536549174', '1536549852', null);
INSERT INTO `system_auth_rule` VALUES ('111', '110', '商品类目列表', '查询', '1', '0', '1', 'admin_shop_category_list_get', '', '', '', '0', '1536549837', '1536550023', null);
INSERT INTO `system_auth_rule` VALUES ('112', '110', '商品类目列表', '查询单条', '1', '0', '1', 'admin_shop_category_single_get', '', '', '', '0', '1536550009', null, null);
INSERT INTO `system_auth_rule` VALUES ('113', '110', '商品类目列表', '新增', '1', '0', '1', 'admin_shop_category_add_post', '', '', '', '0', '1536550085', null, null);
INSERT INTO `system_auth_rule` VALUES ('114', '110', '商品类目列表', '删除', '1', '0', '1', 'admin_shop_category_delete_delete', '', '', '', '0', '1536550129', null, null);
INSERT INTO `system_auth_rule` VALUES ('115', '110', '商品类目列表', '编辑', '1', '0', '1', 'admin_shop_category_update_put', '', '', '', '0', '1536550167', null, null);
INSERT INTO `system_auth_rule` VALUES ('116', '0', '商品分类', '查询一级分类', '1', '0', '1', 'admin_shop_category_parent_get', '', '', '', '0', '1540024492', null, null);
INSERT INTO `system_auth_rule` VALUES ('117', '0', '商品', '商品', '2', '1', '1', '', '', 'goods', '商品', '0', '1548141206', null, null);
INSERT INTO `system_auth_rule` VALUES ('118', '117', '商品-商品分组', '商品分组', '2', '1', '1', '', '', 'goods/group', '商品分组', '0', '1548141297', '1548397535', null);
INSERT INTO `system_auth_rule` VALUES ('119', '118', '商品分组', '新增', '2', '0', '1', 'merchant_shop_category_add_post', '', '', '', '0', '1548141499', null, null);
INSERT INTO `system_auth_rule` VALUES ('120', '118', '商品分组', '删除', '2', '0', '1', 'merchant_shop_category_delete_delete', '', '', '', '0', '1548141545', null, null);
INSERT INTO `system_auth_rule` VALUES ('121', '118', '商品分组', '编辑', '2', '0', '1', 'merchant_shop_category_update_put', '', '', '', '0', '1548141600', null, null);
INSERT INTO `system_auth_rule` VALUES ('122', '118', '商品分组', '查询', '2', '0', '1', 'merchant_shop_category_list_get', '', '', '', '0', '1548141662', null, null);
INSERT INTO `system_auth_rule` VALUES ('123', '118', '商品分组', '查询单条', '2', '0', '1', 'merchant_shop_category_single_get', '', '', '', '0', '1548141708', null, null);
INSERT INTO `system_auth_rule` VALUES ('124', '118', '商品分组', '查询父类', '2', '0', '1', 'merchant_shop_category_parent_get', '', '', '', '0', '1548141796', null, null);
INSERT INTO `system_auth_rule` VALUES ('125', '117', '商品-商品列表', '商品列表', '2', '1', '1', '', '', 'goods/list', '商品列表', '0', '1548145597', '1548397557', null);
INSERT INTO `system_auth_rule` VALUES ('126', '125', '商品列表', '新增', '2', '0', '1', 'merchant_shop_goods_add_post', '', '', '', '0', '1548145736', null, null);
INSERT INTO `system_auth_rule` VALUES ('127', '125', '商品列表', '删除', '2', '0', '1', 'merchant_shop_goods_delete_delete', '', '', '', '0', '1548145817', null, null);
INSERT INTO `system_auth_rule` VALUES ('128', '125', '商品列表', '编辑', '2', '0', '1', 'merchant_shop_goods_update_put', '', '', '', '0', '1548145886', null, null);
INSERT INTO `system_auth_rule` VALUES ('129', '125', '商品列表', '查询', '2', '0', '1', 'merchant_shop_goods_list_get', '', '', '', '0', '1548145977', null, null);
INSERT INTO `system_auth_rule` VALUES ('130', '125', '商品列表', '查询单条', '2', '0', '1', 'merchant_shop_goods_single_get', '', '', '', '0', '1548146068', null, null);
INSERT INTO `system_auth_rule` VALUES ('131', '125', '商品列表', '获取商品分组', '2', '0', '1', 'merchant_shop_category_merchanttype_get', '', '', '', '0', '1548146426', null, null);
INSERT INTO `system_auth_rule` VALUES ('132', '125', '商品列表', '上传商品图', '2', '0', '1', 'merchant_shop_goods_uploads_post', '', '', '', '0', '1548146574', null, null);
INSERT INTO `system_auth_rule` VALUES ('133', '0', '订单', '订单', '2', '1', '1', '', '', 'order', '订单', '0', '1548146725', null, null);
INSERT INTO `system_auth_rule` VALUES ('134', '133', '订单', '订单概述', '2', '0', '1', 'merchant_shop_order_summary_get', '', '', '', '0', '1548146811', null, null);
INSERT INTO `system_auth_rule` VALUES ('135', '133', '订单管理', '订单管理', '2', '1', '1', '', '', 'order/list', '订单管理', '0', '1548146944', '1548147545', null);
INSERT INTO `system_auth_rule` VALUES ('136', '135', '订单管理', '订单列表', '2', '0', '1', 'merchant_shop_order_list_get', '', '', '', '0', '1548147153', '1548147713', null);
INSERT INTO `system_auth_rule` VALUES ('137', '135', '订单管理', '取消订单', '2', '0', '1', 'merchant_shop_order_cancel_put', '', '', '', '0', '1548147704', null, null);
INSERT INTO `system_auth_rule` VALUES ('138', '135', '订单管理', '查询单条', '2', '0', '1', 'merchant_shop_order_suborder_get', '', '', '', '0', '1548147916', null, null);
INSERT INTO `system_auth_rule` VALUES ('139', '135', '订单管理', '发货', '2', '0', '1', 'merchant_shop_order_send_put', '', '', '', '0', '1548147961', null, null);
INSERT INTO `system_auth_rule` VALUES ('140', '135', '订单管理', '图片上传', '2', '0', '1', 'merchant_shop_order_uploads_post', '', '', '', '0', '1548148046', null, null);
INSERT INTO `system_auth_rule` VALUES ('141', '133', '维权订单', '维权订单', '2', '1', '1', '', '', 'order/safeguardingRights', '维权订单', '0', '1548148222', null, null);
INSERT INTO `system_auth_rule` VALUES ('142', '141', '维权订单', '查询', '2', '0', '1', 'merchant_shop_order_all_get', '', '', '', '0', '1548148293', null, null);
INSERT INTO `system_auth_rule` VALUES ('143', '135', '订单管理', '退款操作', '2', '0', '1', 'merchant_shop_order_refund_put', '', '', '', '0', '1548148434', null, null);
INSERT INTO `system_auth_rule` VALUES ('144', '135', '订单管理', '一键退款', '2', '0', '1', 'merchant_shop_order_refunds_put', '', '', '', '0', '1548148478', null, null);
INSERT INTO `system_auth_rule` VALUES ('145', '133', '评价管理', '评价管理', '2', '1', '1', '', '', 'order/evaluate', '评价管理', '0', '1548148596', '1548148619', null);
INSERT INTO `system_auth_rule` VALUES ('146', '145', '评价管理', '删除', '2', '0', '1', 'merchant_shop_comment_delete_delete', '', '', '', '0', '1548148676', null, null);
INSERT INTO `system_auth_rule` VALUES ('147', '145', '评价管理', '查询', '2', '0', '1', 'merchant_shop_comment_list_get', '', '', '', '0', '1548148706', null, null);
INSERT INTO `system_auth_rule` VALUES ('148', '0', '会员', '会员', '2', '1', '1', '', '', 'user', '会员', '0', '1548148758', null, null);
INSERT INTO `system_auth_rule` VALUES ('149', '148', '会员列表', '会员列表', '2', '1', '1', '', '', 'user/list', '会员列表', '0', '1548148846', null, null);
INSERT INTO `system_auth_rule` VALUES ('150', '149', '会员列表', '查询', '2', '0', '1', 'merchant_shop_user_list_get', '', '', '', '0', '1548148898', null, null);
INSERT INTO `system_auth_rule` VALUES ('151', '0', '轮播图', '轮播图', '2', '1', '1', '', '', 'banner', '轮播图', '0', '1548148959', null, null);
INSERT INTO `system_auth_rule` VALUES ('152', '151', '轮播图列表', '轮播图列表', '2', '1', '1', '', '', 'banner/list', '轮播图列表', '0', '1548148998', '1559098768', null);
INSERT INTO `system_auth_rule` VALUES ('153', '152', '轮播图列表', '新增', '2', '0', '1', 'merchant_shop_banner_add_post', '', '', '', '0', '1548149066', null, null);
INSERT INTO `system_auth_rule` VALUES ('154', '152', '轮播图列表', '删除', '2', '0', '1', 'merchant_shop_banner_delete_delete', '', '', '', '0', '1548149108', null, null);
INSERT INTO `system_auth_rule` VALUES ('155', '152', '轮播图列表', '编辑', '2', '0', '1', 'merchant_shop_banner_update_put', '', '', '', '0', '1548149153', null, null);
INSERT INTO `system_auth_rule` VALUES ('156', '152', '轮播图列表', '查询', '2', '0', '1', 'merchant_shop_banner_list_get', '', '', '', '0', '1548149195', null, null);
INSERT INTO `system_auth_rule` VALUES ('157', '152', '轮播图列表', '查询单条', '2', '0', '1', 'merchant_shop_banner_single_get', '', '', '', '0', '1548149227', null, null);
INSERT INTO `system_auth_rule` VALUES ('158', '0', '营销', '营销', '2', '1', '1', '', '', 'voucher', '营销', '0', '1548205096', null, null);
INSERT INTO `system_auth_rule` VALUES ('159', '158', '优惠券类型', '优惠券类型', '2', '1', '1', '', '', 'voucher/type', '优惠券类型', '0', '1548205152', null, null);
INSERT INTO `system_auth_rule` VALUES ('160', '159', '优惠券类型', '新增', '2', '0', '1', 'merchant_shop_vouchertype_add_post', '', '', '', '0', '1548205219', null, null);
INSERT INTO `system_auth_rule` VALUES ('161', '159', '优惠券类型', '删除', '2', '0', '1', 'merchant_shop_vouchertype_delete_delete', '', '', '', '0', '1548205252', null, null);
INSERT INTO `system_auth_rule` VALUES ('162', '159', '优惠券类型', '编辑', '2', '0', '1', 'merchant_shop_vouchertype_update_put', '', '', '', '0', '1548205294', null, null);
INSERT INTO `system_auth_rule` VALUES ('163', '159', '优惠券类型', '查询', '2', '0', '1', 'merchant_shop_vouchertype_list_get', '', '', '', '0', '1548205329', null, null);
INSERT INTO `system_auth_rule` VALUES ('164', '159', '优惠券类型', '查询单条', '2', '0', '1', 'merchant_shop_vouchertype_single_get', '', '', '', '0', '1548205371', null, null);
INSERT INTO `system_auth_rule` VALUES ('165', '0', '公众号', '公众号', '2', '1', '1', '', '', 'wechat', '公众号', '0', '1548205697', null, null);
INSERT INTO `system_auth_rule` VALUES ('166', '165', '公众号-基本配置', '基本配置', '2', '1', '1', '', '', 'wechat/base', '基本配置', '0', '1548205835', '1548206869', null);
INSERT INTO `system_auth_rule` VALUES ('167', '166', '基本配置', '查询单条', '2', '0', '1', 'merchant_config_config_single_get', '', '', '', '0', '1548205928', null, null);
INSERT INTO `system_auth_rule` VALUES ('168', '166', '基本配置', '获取授权', '2', '0', '1', 'wechat_officialAccount_openplat_get', '', '', '', '0', '1548206084', '1548206878', null);
INSERT INTO `system_auth_rule` VALUES ('169', '166', '基本配置', '手动编辑', '2', '0', '1', 'merchant_config_config_update_put', '', '', '', '0', '1548206205', null, null);
INSERT INTO `system_auth_rule` VALUES ('170', '165', '公众号-支付管理', '支付管理', '2', '1', '1', '', '', 'wechat/pay', '支付管理', '0', '1548206265', null, null);
INSERT INTO `system_auth_rule` VALUES ('171', '170', '支付管理', '查询单条', '2', '0', '1', 'merchant_config_config_single_get', '', '', '', '0', '1548206349', null, null);
INSERT INTO `system_auth_rule` VALUES ('172', '170', '支付管理', '编辑', '2', '0', '1', 'merchant_config_config_update_put', '', '', '', '0', '1548206459', null, null);
INSERT INTO `system_auth_rule` VALUES ('173', '165', '公众号-菜单管理', '菜单管理', '2', '1', '1', '', '', 'wechat/menu', '菜单管理', '0', '1548206540', '1548206560', null);
INSERT INTO `system_auth_rule` VALUES ('174', '173', '菜单管理', '查询', '2', '0', '1', 'wechat_officialAccount_menu_get', '', '', '', '0', '1548206630', '1548206800', null);
INSERT INTO `system_auth_rule` VALUES ('175', '173', '菜单管理', '保存', '2', '0', '1', 'wechat_officialAccount_menu_create_post', '', '', '', '0', '1548206776', null, null);
INSERT INTO `system_auth_rule` VALUES ('176', '173', '菜单管理', '获取关键字', '2', '0', '1', 'wechat_officialAccount_words_list_get', '', '', '', '0', '1548207005', null, null);
INSERT INTO `system_auth_rule` VALUES ('177', '165', '公众号-回复管理', '回复管理', '2', '1', '1', '', '', 'wechat/reply', '回复管理', '0', '1548207099', null, null);
INSERT INTO `system_auth_rule` VALUES ('178', '177', '回复管理', '关键词新增', '2', '0', '1', 'wechat_officialAccount_words_add_post', '', '', '', '0', '1548207595', null, null);
INSERT INTO `system_auth_rule` VALUES ('179', '177', '回复管理', '关键词删除', '2', '0', '1', 'wechat_officialAccount_words_delete_delete', '', '', '', '0', '1548207652', null, null);
INSERT INTO `system_auth_rule` VALUES ('180', '177', '回复管理', '关键词编辑', '2', '0', '1', 'wechat_officialAccount_words_update_put', '', '', '', '0', '1548207714', null, null);
INSERT INTO `system_auth_rule` VALUES ('181', '177', '回复管理', '关键词查询', '2', '0', '1', 'wechat_officialAccount_words_list_get', '', '', '', '0', '1548207758', null, null);
INSERT INTO `system_auth_rule` VALUES ('182', '177', '回复管理', '关键词单条查询', '2', '0', '1', 'wechat_officialAccount_words_single_get', '', '', '', '0', '1548207830', null, null);
INSERT INTO `system_auth_rule` VALUES ('183', '177', '回复管理', '获取图片素材', '2', '0', '1', 'wechat_officialAccount_media_get', '', '', '', '0', '1548207938', null, null);
INSERT INTO `system_auth_rule` VALUES ('184', '177', '回复管理', '非关键词与关注新增', '2', '0', '1', 'wechat_officialAccount_words_adds_post', '', '', '', '0', '1548208060', null, null);
INSERT INTO `system_auth_rule` VALUES ('185', '177', '回复管理', '非关键词与关注编辑', '2', '0', '1', 'wechat_officialAccount_words_updates_put', '', '', '', '0', '1548208100', null, null);
INSERT INTO `system_auth_rule` VALUES ('186', '177', '回复管理', '非关键词与关注查询单条', '2', '0', '1', 'wechat_officialAccount_words_one_get', '', '', '', '0', '1548208143', null, null);
INSERT INTO `system_auth_rule` VALUES ('187', '165', '公众号-素材管理', '素材管理', '2', '1', '1', '', '', 'wechat/media', '素材管理', '0', '1548208231', null, null);
INSERT INTO `system_auth_rule` VALUES ('188', '187', '素材管理', '新增', '2', '0', '1', 'wechat_officialAccount_media_uploads_post', '', '', '', '0', '1548208374', null, null);
INSERT INTO `system_auth_rule` VALUES ('189', '187', '素材管理', '删除', '2', '0', '1', 'wechat_officialAccount_media_delete_delete', '', '', '', '0', '1548208407', null, null);
INSERT INTO `system_auth_rule` VALUES ('190', '187', '素材管理', '查询', '2', '0', '1', 'wechat_officialAccount_media_get', '', '', '', '0', '1548208851', null, null);
INSERT INTO `system_auth_rule` VALUES ('191', '0', '小程序', '小程序', '2', '1', '1', '', '', 'miniProgram', '小程序', '0', '1548209360', null, null);
INSERT INTO `system_auth_rule` VALUES ('192', '191', '小程序-基本配置', '基本配置', '2', '1', '1', '', '', 'miniProgram/base', '基本配置', '0', '1548209427', null, null);
INSERT INTO `system_auth_rule` VALUES ('193', '192', '基本配置', '查询单条', '2', '0', '1', 'merchant_config_config_single_get', '', '', '', '0', '1548209517', null, null);
INSERT INTO `system_auth_rule` VALUES ('194', '192', '基本配置', '获取授权', '2', '0', '1', 'wechat_officialAccount_openplat_get', '', '', '', '0', '1548209593', null, null);
INSERT INTO `system_auth_rule` VALUES ('195', '191', '小程序-支付管理', '支付管理', '2', '1', '1', '', '', 'miniProgram/pay', '支付管理', '0', '1548209624', '1548209640', null);
INSERT INTO `system_auth_rule` VALUES ('196', '195', '支付管理', '编辑', '2', '0', '1', 'merchant_config_config_update_put', '', '', '', '0', '1548209775', null, null);
INSERT INTO `system_auth_rule` VALUES ('197', '195', '支付管理', '查询单条', '2', '0', '1', 'merchant_config_config_single_get', '', '', '', '0', '1548209811', null, null);
INSERT INTO `system_auth_rule` VALUES ('198', '191', '小程序-上传发布', '上传发布', '2', '1', '1', '', '', 'miniProgram/formal', '上传发布', '0', '1548211428', null, null);
INSERT INTO `system_auth_rule` VALUES ('199', '198', '上传发布', '查询单条', '2', '0', '1', 'wechat_officialAccount_openplat_miniprogram_get', '', '', '', '0', '1548211496', null, null);
INSERT INTO `system_auth_rule` VALUES ('200', '198', '上传发布', '上传', '2', '0', '1', 'wechat_officialAccount_openplat_commit_post', '', '', '', '0', '1548211556', null, null);
INSERT INTO `system_auth_rule` VALUES ('201', '198', '上传发布', '审核', '2', '0', '1', 'wechat_officialAccount_openplat_audit_post', '', '', '', '0', '1548211636', null, null);
INSERT INTO `system_auth_rule` VALUES ('202', '198', '上传发布', '发布', '2', '0', '1', 'wechat_officialAccount_openplat_release_post', '', '', '', '0', '1548211716', null, null);
INSERT INTO `system_auth_rule` VALUES ('203', '198', '上传发布', '获取临时二维码', '2', '0', '1', 'wechat_officialAccount_openplat_qrcode_get', '', '', '', '0', '1548211805', null, null);
INSERT INTO `system_auth_rule` VALUES ('204', '0', '设置', '设置', '2', '1', '1', '', '', 'appSet', '设置', '0', '1548212195', null, null);
INSERT INTO `system_auth_rule` VALUES ('205', '204', '设置-运费模板', '运费模板', '2', '1', '1', '', '', 'logistics/express', '运费模板', '0', '1548212240', null, null);
INSERT INTO `system_auth_rule` VALUES ('206', '205', '运费模板', '省级查询', '2', '0', '1', 'shop_user_address_get', '', '', '', '0', '1548212361', null, null);
INSERT INTO `system_auth_rule` VALUES ('207', '205', '运费模板', '新增', '2', '0', '1', 'merchant_shop_template_add_post', '', '', '', '0', '1548212435', null, null);
INSERT INTO `system_auth_rule` VALUES ('208', '205', '运费模板', '编辑', '2', '0', '1', 'merchant_shop_template_update_put', '', '', '', '0', '1548212495', null, null);
INSERT INTO `system_auth_rule` VALUES ('209', '205', '运费模板', '删除', '2', '0', '1', 'merchant_shop_template_delete_delete', '', '', '', '0', '1548212914', null, null);
INSERT INTO `system_auth_rule` VALUES ('210', '205', '运费模板', '查询', '2', '0', '1', 'merchant_shop_template_list_get', '', '', '', '0', '1548212969', null, null);
INSERT INTO `system_auth_rule` VALUES ('211', '205', '运费模板', '查询单条', '2', '0', '1', 'merchant_shop_template_single_get', '', '', '', '0', '1548213004', null, null);
INSERT INTO `system_auth_rule` VALUES ('212', '204', '设置-物流列表', '物流列表', '2', '1', '1', '', '', 'logistics/list', '物流列表', '0', '1548213115', null, null);
INSERT INTO `system_auth_rule` VALUES ('213', '212', '物流列表', '物流总列表查询', '2', '0', '1', 'merchant_shop_express_all_get', '', '', '', '0', '1548213335', null, null);
INSERT INTO `system_auth_rule` VALUES ('214', '212', '物流列表', '新增', '2', '0', '1', 'merchant_shop_express_add_post', '', '', '', '0', '1548213465', null, null);
INSERT INTO `system_auth_rule` VALUES ('215', '212', '物流列表', '编辑', '2', '0', '1', 'merchant_shop_express_update_put', '', '', '', '0', '1548213636', null, null);
INSERT INTO `system_auth_rule` VALUES ('216', '212', '物流列表', '删除', '2', '0', '1', 'merchant_shop_express_delete_delete', '', '', '', '0', '1548213675', null, null);
INSERT INTO `system_auth_rule` VALUES ('217', '212', '物流列表', '查询', '2', '0', '1', 'merchant_shop_express_list_get', '', '', '', '0', '1548213753', null, null);
INSERT INTO `system_auth_rule` VALUES ('218', '204', '设置-收货信息', '收货信息', '2', '1', '1', '', '', 'info/list', '收货信息', '0', '1548213803', null, null);
INSERT INTO `system_auth_rule` VALUES ('219', '218', '收货信息', '新增', '2', '0', '1', 'merchant_shop_afterinfo_add_post', '', '', '', '0', '1548214111', null, null);
INSERT INTO `system_auth_rule` VALUES ('220', '218', '收货信息', '编辑', '2', '0', '1', 'merchant_shop_afterinfo_update_put', '', '', '', '0', '1548214159', null, null);
INSERT INTO `system_auth_rule` VALUES ('221', '218', '收货信息', '查询', '2', '0', '1', 'merchant_shop_afterinfo_list_get', '', '', '', '0', '1548214200', null, null);
INSERT INTO `system_auth_rule` VALUES ('222', '204', '设置-基础设置', '基础设置', '2', '1', '1', '', '', 'appSet/info', '基础设置', '0', '1548214317', null, null);
INSERT INTO `system_auth_rule` VALUES ('223', '222', '基础设置', '编辑', '2', '0', '1', 'merchant_app_access_update_put', '', '', '', '0', '1548214419', null, null);
INSERT INTO `system_auth_rule` VALUES ('224', '222', '基础设置', '查询单条', '2', '0', '1', 'merchant_app_access_single_get', '', '', '', '0', '1548214467', null, null);
INSERT INTO `system_auth_rule` VALUES ('225', '222', '基础设置', '商城分类查询', '2', '0', '1', 'merchant_shop_category_category_get', '', '', '', '0', '1548214532', null, null);
INSERT INTO `system_auth_rule` VALUES ('226', '204', '设置-商城设置', '商城设置', '2', '1', '1', '', '', 'shopConfig', '商城设置', '0', '1548214573', null, null);
INSERT INTO `system_auth_rule` VALUES ('227', '226', '商城设置', '编辑', '2', '0', '1', 'merchant_config_config_configup_put', '', '', '', '0', '1548222338', null, null);
INSERT INTO `system_auth_rule` VALUES ('228', '226', '商城设置', '查询单条', '2', '0', '1', 'merchant_config_config_config_get', '', '', '', '0', '1548222401', null, null);
INSERT INTO `system_auth_rule` VALUES ('229', '0', '员工管理', '员工管理', '2', '1', '1', '', '', 'staff', '员工管理', '0', '1548222685', null, null);
INSERT INTO `system_auth_rule` VALUES ('230', '229', '员工管理-员工管理', '员工管理', '2', '1', '1', '', '', 'staff/list', '员工管理', '0', '1548222777', null, null);
INSERT INTO `system_auth_rule` VALUES ('231', '230', '员工管理', '权限组查询', '2', '0', '1', 'merchant_system_group_list_get', '', '', '', '0', '1548224211', null, null);
INSERT INTO `system_auth_rule` VALUES ('232', '230', '员工管理', '新增', '2', '0', '1', 'merchant_system_user_add_post', '', '', '', '0', '1548224271', null, null);
INSERT INTO `system_auth_rule` VALUES ('233', '230', '员工管理', '编辑', '2', '0', '1', 'merchant_system_user_update_put', '', '', '', '0', '1548224301', null, null);
INSERT INTO `system_auth_rule` VALUES ('234', '230', '员工管理', '删除', '2', '0', '1', 'merchant_system_user_delete_delete', '', '', '', '0', '1548224336', null, null);
INSERT INTO `system_auth_rule` VALUES ('235', '230', '员工管理', '查询', '2', '0', '1', 'merchant_system_user_list_get', '', '', '', '0', '1548224403', null, null);
INSERT INTO `system_auth_rule` VALUES ('236', '230', '员工管理', '查询单条', '2', '0', '1', 'merchant_system_user_single_get', '', '', '', '0', '1548224437', null, null);
INSERT INTO `system_auth_rule` VALUES ('237', '229', '员工管理-权限管理', '权限管理', '2', '1', '1', '', '', 'staff/group', '权限管理', '0', '1548224676', null, null);
INSERT INTO `system_auth_rule` VALUES ('238', '237', '权限管理', '新增', '2', '0', '1', 'merchant_system_group_add_post', '', '', '', '0', '1548224740', null, null);
INSERT INTO `system_auth_rule` VALUES ('239', '237', '权限管理', '删除', '2', '0', '1', 'merchant_system_group_delete_delete', '', '', '', '0', '1548224800', null, null);
INSERT INTO `system_auth_rule` VALUES ('240', '237', '权限管理', '编辑', '2', '0', '1', 'merchant_system_group_update_put', '', '', '', '0', '1548224831', null, null);
INSERT INTO `system_auth_rule` VALUES ('241', '237', '权限管理', '查询', '2', '0', '1', 'merchant_system_group_list_get', '', '', '', '0', '1548224867', null, null);
INSERT INTO `system_auth_rule` VALUES ('242', '237', '权限管理', '查询单条', '2', '0', '1', 'merchant_system_group_single_get', '', '', '', '0', '1548224990', null, null);
INSERT INTO `system_auth_rule` VALUES ('243', '237', '权限管理', '权限查询', '2', '0', '1', 'merchant_system_group_all_get', '', '', '', '0', '1548225081', null, null);
INSERT INTO `system_auth_rule` VALUES ('244', '117', '商品-回收站', '回收站', '2', '1', '1', '', '', 'goods/recycleBin', '回收站', '0', '1548397481', null, null);
INSERT INTO `system_auth_rule` VALUES ('245', '244', '回收站', '恢复', '2', '0', '1', 'merchant_shop_goods_reduction_put', '', '', '', '0', '1548397689', null, null);
INSERT INTO `system_auth_rule` VALUES ('246', '244', '回收站', '查询', '2', '0', '1', 'merchant_shop_goods_recycle_get', '', '', '', '0', '1548397784', '1548397897', null);
INSERT INTO `system_auth_rule` VALUES ('247', '205', '运费模板', '启用模板', '2', '0', '1', 'merchant_shop_template_updates_put', '', '', '', '0', '1548407230', null, null);
INSERT INTO `system_auth_rule` VALUES ('248', '229', '员工管理-客服管理', '客服管理', '1', '1', '1', '', '', 'staff/customerService', '客服管理', '0', '1549002811', null, null);
INSERT INTO `system_auth_rule` VALUES ('249', '248', '客服管理', '查询', '1', '0', '1', 'merchant_system_user_kefu_get', '', '', '', '0', '1549002956', null, null);
INSERT INTO `system_auth_rule` VALUES ('250', '4', '代理设置', '代理设置', '1', '1', '1', '', '', 'set/proxy', '代理设置', '0', '1554974653', null, null);
INSERT INTO `system_auth_rule` VALUES ('251', '250', '代理设置', '新增', '1', '0', '1', 'admin_system_vip_add_post', '', '', '', '0', '1554974782', '1555032781', null);
INSERT INTO `system_auth_rule` VALUES ('252', '250', '代理设置', '删除', '1', '0', '1', 'admin_system_vip_delete_delete', '', '', '', '0', '1554974827', '1555032787', null);
INSERT INTO `system_auth_rule` VALUES ('253', '250', '代理设置', '编辑', '1', '0', '1', 'admin_system_vip_update_put', '', '', '', '0', '1554974880', '1555032792', null);
INSERT INTO `system_auth_rule` VALUES ('254', '250', '代理设置', '查询', '1', '0', '1', 'admin_system_vip_list_get', '', '', '', '0', '1554974924', null, null);
INSERT INTO `system_auth_rule` VALUES ('255', '250', '代理设置', '查询单条', '1', '0', '1', 'admin_system_vip_single_get', '', '', '', '0', '1554974958', '1555032806', null);
INSERT INTO `system_auth_rule` VALUES ('256', '4', '小程序版本控制', '小程序版本控制', '1', '1', '1', '', '', 'set/version', '小程序版本控制', '0', '1555030374', null, null);
INSERT INTO `system_auth_rule` VALUES ('257', '256', '小程序版本控制', '新增', '1', '0', '1', 'admin_system_version_add_post', '', '', '', '0', '1555030425', null, null);
INSERT INTO `system_auth_rule` VALUES ('258', '256', '小程序版本控制', '删除', '1', '0', '1', 'admin_system_version_delete_delete', '', '', '', '0', '1555030473', null, null);
INSERT INTO `system_auth_rule` VALUES ('259', '256', '小程序版本控制', '编辑', '1', '0', '1', 'admin_system_version_update_put', '', '', '', '0', '1555030509', null, null);
INSERT INTO `system_auth_rule` VALUES ('260', '256', '小程序版本控制', '查询', '1', '0', '1', 'admin_system_version_list_get', '', '', '', '0', '1555030567', null, null);
INSERT INTO `system_auth_rule` VALUES ('261', '256', '小程序版本控制', '查询单条', '1', '0', '1', 'admin_system_version_single_get', '', '', '', '0', '1555030596', null, null);
INSERT INTO `system_auth_rule` VALUES ('262', '2', '商户管理-代理管理', '代理管理', '1', '1', '1', '', '', 'users/proxy', '代理管理', '0', '1555134406', '1555134545', null);
INSERT INTO `system_auth_rule` VALUES ('263', '262', '代理管理', '删除', '1', '0', '1', 'admin_system_user_delete_delete', '', '', '', '0', '1555134585', null, null);
INSERT INTO `system_auth_rule` VALUES ('264', '262', '代理管理', '编辑', '1', '0', '1', 'admin_system_user_update_put', '', '', '', '0', '1555134657', null, null);
INSERT INTO `system_auth_rule` VALUES ('265', '262', '代理管理', '查询', '1', '0', '1', 'admin_system_user_list_get', '', '', '', '0', '1555134694', null, null);
INSERT INTO `system_auth_rule` VALUES ('266', '262', '代理管理', '查询单条', '1', '0', '1', 'admin_system_user_single_get', '', '', '', '0', '1555134734', null, null);
INSERT INTO `system_auth_rule` VALUES ('267', '4', '小程序消息模板', '小程序消息模板', '1', '1', '1', '', '', 'set/messageTemplate', '小程序消息模板', '0', '1555490329', null, null);
INSERT INTO `system_auth_rule` VALUES ('268', '267', '小程序消息模板', '新增', '1', '0', '1', 'admin_system_template_add_post', '', '', '', '0', '1555490431', null, null);
INSERT INTO `system_auth_rule` VALUES ('269', '267', '小程序消息模板', '删除', '1', '0', '1', 'admin_system_template_delete_delete', '', '', '', '0', '1555490467', null, null);
INSERT INTO `system_auth_rule` VALUES ('270', '267', '小程序消息模板', '编辑', '1', '0', '1', 'admin_system_template_update_put', '', '', '', '0', '1555490518', null, null);
INSERT INTO `system_auth_rule` VALUES ('271', '267', '小程序消息模板', '查询', '1', '0', '1', 'admin_system_template_list_get', '', '', '', '0', '1555490549', null, null);
INSERT INTO `system_auth_rule` VALUES ('272', '267', '小程序消息模板', '查询单条', '1', '0', '1', 'admin_system_template_single_get', '', '', '', '0', '1555490584', null, null);
INSERT INTO `system_auth_rule` VALUES ('273', '267', '小程序消息模板', '获取模板库某个模板标题下关键词库', '1', '0', '1', 'admin_system_template_temp_post', '', '', '', '0', '1555635648', null, null);
INSERT INTO `system_auth_rule` VALUES ('274', '4', '店铺装修模板', '店铺装修模板', '1', '1', '1', '', '', 'set/decoration/list', '店铺装修模板', '0', '1555985329', null, null);
INSERT INTO `system_auth_rule` VALUES ('275', '274', '店铺装修模板', '新增', '1', '0', '1', 'admin_system_decoration_add_post', '', '', '', '0', '1555985407', null, null);
INSERT INTO `system_auth_rule` VALUES ('276', '274', '店铺装修模板', '删除', '1', '0', '1', 'admin_system_decoration_delete_delete', '', '', '', '0', '1555985462', null, null);
INSERT INTO `system_auth_rule` VALUES ('277', '274', '店铺装修模板', '编辑', '1', '0', '1', 'admin_system_decoration_update_put', '', '', '', '0', '1555985497', null, null);
INSERT INTO `system_auth_rule` VALUES ('278', '274', '店铺装修模板', '查询', '1', '0', '1', 'admin_system_decoration_list_get', '', '', '', '0', '1555985539', null, null);
INSERT INTO `system_auth_rule` VALUES ('279', '274', '店铺装修模板', '查询单条', '1', '0', '1', 'admin_system_decoration_single_get', '', '', '', '0', '1555985567', null, null);
INSERT INTO `system_auth_rule` VALUES ('280', '0', '套餐', '套餐', '1', '1', '1', '', '', 'setMeal', '套餐', '0', '1557364656', null, null);
INSERT INTO `system_auth_rule` VALUES ('281', '280', '套餐-套餐管理', '套餐-套餐管理', '1', '1', '1', '', '', 'setMeal/list', '套餐-套餐管理', '0', '1557365020', '1557365835', null);
INSERT INTO `system_auth_rule` VALUES ('282', '281', '套餐-套餐管理', '新增', '1', '0', '1', 'admin_system_combo_add_post', '', '', '', '0', '1557365917', null, null);
INSERT INTO `system_auth_rule` VALUES ('283', '281', '套餐-套餐管理', '删除', '1', '0', '1', 'admin_system_combo_delete_delete', '', '', '', '0', '1557365972', null, null);
INSERT INTO `system_auth_rule` VALUES ('284', '281', '套餐-套餐管理', '编辑', '1', '0', '1', 'admin_system_combo_update_put', '', '', '', '0', '1557366010', null, null);
INSERT INTO `system_auth_rule` VALUES ('285', '281', '套餐-套餐管理', '查询', '1', '0', '1', 'admin_system_combo_list_get', '', '', '', '0', '1557366052', null, null);
INSERT INTO `system_auth_rule` VALUES ('286', '281', '套餐-套餐管理', '查询单条', '1', '0', '1', 'admin_system_combo_single_get', '', '', '', '0', '1557366082', null, null);
INSERT INTO `system_auth_rule` VALUES ('287', '280', '套餐-套餐购买记录', '套餐-套餐购买记录', '1', '1', '1', '', '', 'setMeal/record', '套餐-套餐购买记录', '0', '1557391233', null, null);
INSERT INTO `system_auth_rule` VALUES ('288', '287', '套餐-套餐购买记录', '查询', '1', '0', '1', 'admin_system_combo_all_get', '', '', '', '0', '1557391321', null, null);
INSERT INTO `system_auth_rule` VALUES ('289', '0', '供货商', '供货商', '2', '1', '1', '', '', 'supplier', '供货商', '0', '1557714819', null, null);
INSERT INTO `system_auth_rule` VALUES ('290', '289', '供货商-商品', '供货商-商品', '2', '1', '1', '', '', 'supplier/goods', '供货商-商品', '0', '1557714958', null, null);
INSERT INTO `system_auth_rule` VALUES ('291', '290', '供货商-商品', '新增', '2', '0', '1', 'merchant_tuan_goods_add_post', '', '', '', '0', '1557715026', null, null);
INSERT INTO `system_auth_rule` VALUES ('292', '290', '供货商-商品', '删除', '2', '0', '1', 'merchant_tuan_goods_delete_delete', '', '', '', '0', '1557715075', '1557717643', null);
INSERT INTO `system_auth_rule` VALUES ('293', '290', '供货商-商品', '编辑', '2', '0', '1', 'merchant_tuan_goods_update_put', '', '', '', '0', '1557715249', null, null);
INSERT INTO `system_auth_rule` VALUES ('294', '290', '供货商-商品', '查询', '2', '0', '1', 'merchant_tuan_goods_list_get', '', '', '', '0', '1557715666', '1557718030', null);
INSERT INTO `system_auth_rule` VALUES ('295', '290', '供货商-商品', '查询单条', '2', '0', '1', 'merchant_tuan_goods_single_get', '', '', '', '0', '1557717613', '1557717648', null);
INSERT INTO `system_auth_rule` VALUES ('296', '290', '供货商-商品', '主图上传', '2', '0', '1', 'merchant_tuan_goods_uploads_post', '', '', '', '0', '1557717734', null, null);
INSERT INTO `system_auth_rule` VALUES ('297', '290', '供货商-商品', '查询城市列表', '2', '0', '1', 'merchant_tuan_city_list_get', '', '', '', '0', '1557718146', null, null);
INSERT INTO `system_auth_rule` VALUES ('298', '0', '文章', '文章', '1', '1', '1', '', '', 'news', '文章', '0', '1557812910', null, null);
INSERT INTO `system_auth_rule` VALUES ('299', '298', '文章-产品动态', '文章-产品动态', '1', '1', '1', '', '', 'news/productDynamics', '文章-产品动态', '0', '1557813025', null, null);
INSERT INTO `system_auth_rule` VALUES ('300', '299', '文章-产品动态', '新增', '1', '0', '1', 'admin_system_news_add_post', '', '', '', '0', '1557813075', null, null);
INSERT INTO `system_auth_rule` VALUES ('301', '299', '文章-产品动态', '删除', '1', '0', '1', 'admin_system_news_delete_delete', '', '', '', '0', '1557813135', null, null);
INSERT INTO `system_auth_rule` VALUES ('302', '299', '文章-产品动态', '编辑', '1', '0', '1', 'admin_system_news_update_put', '', '', '', '0', '1557813417', null, null);
INSERT INTO `system_auth_rule` VALUES ('303', '299', '文章-产品动态', '查询', '1', '0', '1', 'admin_system_news_list_get', '', '', '', '0', '1557813463', null, null);
INSERT INTO `system_auth_rule` VALUES ('304', '299', '文章-产品动态', '查询单条', '1', '0', '1', 'admin_system_news_single_get', '', '', '', '0', '1557813491', null, null);
INSERT INTO `system_auth_rule` VALUES ('305', '298', '文章-帮助中心类型', '文章-帮助中心类型', '1', '1', '1', '', '', 'news/helpCategory', '文章-帮助中心类型', '0', '1557813544', null, null);
INSERT INTO `system_auth_rule` VALUES ('306', '305', '文章-帮助中心类型', '新增', '1', '0', '1', 'admin_system_helps_add_post', '', '', '', '0', '1557813604', null, null);
INSERT INTO `system_auth_rule` VALUES ('307', '305', '文章-帮助中心类型', '删除', '1', '0', '1', 'admin_system_helps_delete_delete', '', '', '', '0', '1557813639', null, null);
INSERT INTO `system_auth_rule` VALUES ('308', '305', '文章-帮助中心类型', '编辑', '1', '0', '1', 'admin_system_helps_update_put', '', '', '', '0', '1557813666', null, null);
INSERT INTO `system_auth_rule` VALUES ('309', '305', '文章-帮助中心类型', '查询', '1', '0', '1', 'admin_system_helps_list_get', '', '', '', '0', '1557813709', null, null);
INSERT INTO `system_auth_rule` VALUES ('310', '305', '文章-帮助中心类型', '查询单条', '1', '0', '1', 'admin_system_helps_single_get', '', '', '', '0', '1557813744', null, null);
INSERT INTO `system_auth_rule` VALUES ('311', '298', '文章-帮助中心', '文章-帮助中心', '1', '1', '1', '', '', 'news/help', '文章-帮助中心', '0', '1557813782', null, null);
INSERT INTO `system_auth_rule` VALUES ('312', '311', '文章-帮助中心', '新增', '1', '0', '1', 'admin_system_help_add_post', '', '', '', '0', '1557813856', null, null);
INSERT INTO `system_auth_rule` VALUES ('313', '311', '文章-帮助中心', '删除', '1', '0', '1', 'admin_system_help_delete_delete', '', '', '', '0', '1557813891', null, null);
INSERT INTO `system_auth_rule` VALUES ('314', '311', '文章-帮助中心', '编辑', '1', '0', '1', 'admin_system_help_update_put', '', '', '', '0', '1557813918', null, null);
INSERT INTO `system_auth_rule` VALUES ('315', '311', '文章-帮助中心', '查询', '1', '0', '1', 'admin_system_help_list_get', '', '', '', '0', '1557813952', null, null);
INSERT INTO `system_auth_rule` VALUES ('316', '311', '文章-帮助中心', '查询单条', '1', '0', '1', 'admin_system_help_single_get', '', '', '', '0', '1557813977', null, null);
INSERT INTO `system_auth_rule` VALUES ('317', '9', '商户列表', '短信、订单充值', '1', '0', '1', 'admin_system_combo_insert_post', '', '', '', '0', '1559117339', null, null);
INSERT INTO `system_auth_rule` VALUES ('318', '7', '应用管理-插件管理', '应用管理-插件管理', '1', '1', '1', '', '', 'application/plugin', '应用管理-插件管理', '0', '1559701930', null, null);
INSERT INTO `system_auth_rule` VALUES ('319', '65', '应用管理-插件管理', '新增', '1', '0', '1', 'admin_system_plugin_add_post', '', '', '', '0', '1559702014', null, null);
INSERT INTO `system_auth_rule` VALUES ('320', '318', '应用管理-插件管理', '编辑', '1', '0', '1', 'admin_system_plugin_update_put', '', '', '', '0', '1559702050', null, null);
INSERT INTO `system_auth_rule` VALUES ('321', '318', '应用管理-插件管理', '删除', '1', '0', '1', 'admin_system_plugin_delete_delete', '', '', '', '0', '1559702081', null, null);
INSERT INTO `system_auth_rule` VALUES ('322', '318', '应用管理-插件管理', '查询', '1', '0', '1', 'admin_system_plugin_list_get', '', '', '', '0', '1559702160', null, null);
INSERT INTO `system_auth_rule` VALUES ('323', '318', '应用管理-插件管理', '查询单条', '1', '0', '1', 'admin_system_plugin_one_get', '', '', '', '0', '1559702190', null, null);
INSERT INTO `system_auth_rule` VALUES ('324', '4', '系统-打印设置', '系统-打印设置', '1', '1', '1', '', '', 'set/printing', '系统-打印设置', '0', '1560152492', null, null);
INSERT INTO `system_auth_rule` VALUES ('325', '324', '系统-打印设置', '分组新增', '1', '0', '1', 'admin_system_printing_add_post', '', '', '', '0', '1560152532', '1560156778', null);
INSERT INTO `system_auth_rule` VALUES ('326', '324', '系统-打印设置', '分组删除', '1', '0', '1', 'admin_system_printing_delete_delete', '', '', '', '0', '1560152600', '1560156785', null);
INSERT INTO `system_auth_rule` VALUES ('327', '324', '系统-打印设置', '分组编辑', '1', '0', '1', 'admin_system_printing_update_put', '', '', '', '0', '1560152644', '1560156791', null);
INSERT INTO `system_auth_rule` VALUES ('328', '324', '系统-打印设置', '分组查询', '1', '0', '1', 'admin_system_printing_list_get', '', '', '', '0', '1560152681', '1560156798', null);
INSERT INTO `system_auth_rule` VALUES ('329', '324', '系统-打印设置', '分组查询单条', '1', '0', '1', 'admin_system_printing_one_get', '', '', '', '0', '1560152709', '1560156804', null);
INSERT INTO `system_auth_rule` VALUES ('330', '324', '系统-打印设置', '分组字段新增', '1', '0', '1', 'admin_system_printing_keyadd_post', '', '', '', '0', '1560156903', null, null);
INSERT INTO `system_auth_rule` VALUES ('331', '324', '系统-打印设置', '分组字段删除', '1', '0', '1', 'admin_system_printing_keydelete_delete', '', '', '', '0', '1560156994', null, null);
INSERT INTO `system_auth_rule` VALUES ('332', '324', '系统-打印设置', '分组字段编辑', '1', '0', '1', 'admin_system_printing_keyupdate_put', '', '', '', '0', '1560157057', null, null);
INSERT INTO `system_auth_rule` VALUES ('333', '324', '系统-打印设置', '分组字段查询', '1', '0', '1', 'admin_system_printing_keylist_get', '', '', '', '0', '1560157126', null, null);
INSERT INTO `system_auth_rule` VALUES ('334', '324', '系统-打印设置', '分组字段单条', '1', '0', '1', 'admin_system_printing_keyone_get', '', '', '', '0', '1560157210', null, null);
INSERT INTO `system_auth_rule` VALUES ('335', '324', '系统-打印设置', '模板新增', '1', '0', '1', 'admin_system_printing_tempadd_post', '', '', '', '0', '1560246384', null, null);
INSERT INTO `system_auth_rule` VALUES ('336', '324', '系统-打印设置', '模板更新', '1', '0', '1', 'admin_system_printing_tempupdate_put', '', '', '', '0', '1560246458', null, null);
INSERT INTO `system_auth_rule` VALUES ('337', '324', '系统-打印设置', '模板删除', '1', '0', '1', 'admin_system_printing_tempdelete_delete', '', '', '', '0', '1560246513', null, null);
INSERT INTO `system_auth_rule` VALUES ('338', '0', '系统-打印设置', '模板查询', '1', '0', '1', 'admin_system_printing_templist_get', '', '', '', '0', '1560246558', null, null);
INSERT INTO `system_auth_rule` VALUES ('339', '0', '系统-打印设置', '模板单条', '1', '0', '1', 'admin_system_printing_tempone_get', '', '', '', '0', '1560246604', null, null);
INSERT INTO `system_auth_rule` VALUES ('340', '324', '系统-打印设置', '查询分组及字段', '1', '0', '1', 'admin_system_printing_pulldownlist_get', '', '', '', '0', '1560325111', null, null);
INSERT INTO `system_auth_rule` VALUES ('341', '4', '系统-系统配置', '系统配置', '1', '1', '1', '', '', 'set/sysConfig', '系统配置', '0', '1561774322', '1561774353', null);
INSERT INTO `system_auth_rule` VALUES ('342', '341', '系统-系统配置', '新增', '1', '0', '1', 'admin_system_diy_add_post', '', '', '', '0', '1561774451', null, null);
INSERT INTO `system_auth_rule` VALUES ('343', '341', '系统-系统配置', '删除', '1', '0', '1', 'admin_system_diy_delete_delete', '', '', '', '0', '1561774487', null, null);
INSERT INTO `system_auth_rule` VALUES ('344', '341', '系统-系统配置', '编辑', '1', '0', '1', 'admin_system_diy_update_put', '', '', '', '0', '1561774521', null, null);
INSERT INTO `system_auth_rule` VALUES ('345', '341', '系统-系统配置', '查询', '1', '0', '1', 'admin_system_diy_list_get', '', '', '', '0', '1561774555', null, null);
INSERT INTO `system_auth_rule` VALUES ('346', '341', '系统-系统配置', '查询单条', '1', '0', '1', 'admin_system_diy_single_get', '', '', '', '0', '1561774585', null, null);
INSERT INTO `system_auth_rule` VALUES ('347', '9', '商户列表', '套餐购买记录', '1', '0', '1', 'admin_system_combo_alls_get', '', '', '', '0', '1562134035', null, null);
INSERT INTO `system_auth_rule` VALUES ('348', '9', '商户列表', '自定义版权修改状态', '1', '0', '1', 'admin_system_version_upd_put', '', '', '', '0', '1564384629', '1564384739', null);
INSERT INTO `system_auth_rule` VALUES ('349', '0', '首页应用', '首页应用列表', '2', '0', '1', 'merchant_app_app_list_get', '', '', '', '0', '1568112231', null, '111');
INSERT INTO `system_auth_rule` VALUES ('350', '2', '商户列表', '购买记录', '1', '0', '1', 'admin_user_merchant_buy-t-c-instance_get', '', '', '', '0', '1568790794', null, null);
INSERT INTO `system_auth_rule` VALUES ('351', '5', '商城应用权限管理', '商城应用权限管理', '1', '1', '1', '', '', 'set/system/appJurisdiction', '商城应用权限管理', '0', '1576738829', null, null);
INSERT INTO `system_auth_rule` VALUES ('352', '351', '商城应用权限管理', '新增', '1', '0', '1', 'admin_system_menu_add_post', '', '', '', '0', '1576738876', null, null);
INSERT INTO `system_auth_rule` VALUES ('353', '351', '商城应用权限管理', '删除', '1', '0', '1', 'admin_system_menu_delete_delete', '', '', '', '0', '1576738956', null, null);
INSERT INTO `system_auth_rule` VALUES ('354', '351', '商城应用权限管理', '编辑', '1', '0', '1', 'admin_system_menu_update_put', '', '', '', '0', '1576738997', null, null);
INSERT INTO `system_auth_rule` VALUES ('355', '351', '商城应用权限管理', '查询', '1', '0', '1', 'admin_system_menu_list_get', '', '', '', '0', '1576739039', null, null);
INSERT INTO `system_auth_rule` VALUES ('356', '351', '商城应用权限管理', '查询单条', '1', '0', '1', 'admin_system_menu_single_get', '', '', '', '0', '1576739068', null, null);

-- ----------------------------
-- Table structure for `system_authorizer_wechat`
-- ----------------------------
DROP TABLE IF EXISTS `system_authorizer_wechat`;
CREATE TABLE `system_authorizer_wechat` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `open_appid` varchar(32) NOT NULL COMMENT '公众平台绑定id',
  `authorizer_appid` varchar(32) NOT NULL COMMENT '授权的appid',
  `nick_name` varchar(128) NOT NULL DEFAULT '' COMMENT '公众号名称',
  `head_img` varchar(255) DEFAULT '' COMMENT '公众号头像',
  `func_info` text COMMENT '权限 json格式',
  `service_type` tinyint(1) NOT NULL COMMENT '授权方公众号类型，0=订阅号 1=由历史老帐号升级后的订阅号 2=服务号',
  `verify_type` tinyint(1) NOT NULL COMMENT '授权方认证类型，-1代表未认证，0代表微信认证，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证',
  `wechat_id` varchar(50) NOT NULL COMMENT '授权方公众号的原始ID',
  `principal_name` varchar(200) NOT NULL COMMENT '公众号的主体名称',
  `qrcode_url` varchar(255) NOT NULL COMMENT '二维码图片的URL',
  `access_token_time` varchar(32) NOT NULL COMMENT 'token生成时间',
  `access_token` varchar(255) NOT NULL,
  `refresh_token` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '授权类型 2=微信 1=小程序',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=有效 0=无效',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='授权公众号表';



-- ----------------------------
-- Table structure for `system_auto_words`
-- ----------------------------
DROP TABLE IF EXISTS `system_auto_words`;
CREATE TABLE `system_auto_words` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=全匹配 2=半匹配',
  `reply_type` tinyint(1) NOT NULL COMMENT '回复类型 1=文字 2=图片 3=图文',
  `auto_type` tinyint(1) NOT NULL COMMENT '类型 1=关注后回复 2=收到消息回复 3=关键词回复',
  `words` varchar(50) DEFAULT '' COMMENT '关键词',
  `content` text COMMENT '回复内容(json)',
  `media_id` varchar(100) DEFAULT NULL COMMENT '素材 id',
  `meida_url` varchar(255) DEFAULT NULL COMMENT '素材图片地址',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COMMENT='系统-自动回复';



-- ----------------------------
-- Table structure for `system_banner`
-- ----------------------------
DROP TABLE IF EXISTS `system_banner`;
CREATE TABLE `system_banner` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL COMMENT '应用id',
  `name` varchar(50) NOT NULL COMMENT '横幅名称',
  `purpose` varchar(50) NOT NULL COMMENT '用途',
  `pic_url` varchar(255) NOT NULL COMMENT '横幅图片',
  `jump_url` varchar(255) NOT NULL COMMENT '跳转链接',
  `type` tinyint(1) NOT NULL COMMENT '类型 默认0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='系统-横幅表';



-- ----------------------------
-- Table structure for `system_config`
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL COMMENT '类目',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `key` varchar(50) DEFAULT NULL COMMENT '键',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=数值 2=字符串 3=数组',
  `status` tinyint(1) NOT NULL COMMENT '状态 1可用 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- ----------------------------
-- Table structure for `system_config_category`
-- ----------------------------
DROP TABLE IF EXISTS `system_config_category`;
CREATE TABLE `system_config_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '名称',
  `prefix` varchar(50) DEFAULT '' COMMENT '前缀',
  `status` tinyint(1) NOT NULL COMMENT '状态 1可用 2禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='系统配置类目表';

-- ----------------------------
-- Records of system_config_category
-- ----------------------------
INSERT INTO `system_config_category` VALUES ('1', '社交', '', '1', '1528444951', null, null);
INSERT INTO `system_config_category` VALUES ('2', '商家', '', '0', '1528445516', '1528447305', null);
INSERT INTO `system_config_category` VALUES ('3', '管理后台', '', '1', '1529897368', null, null);

-- ----------------------------
-- Table structure for `system_cos`
-- ----------------------------
DROP TABLE IF EXISTS `system_cos`;
CREATE TABLE `system_cos` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `appId` varchar(50) NOT NULL,
  `secretId` varchar(50) NOT NULL,
  `secretKey` varchar(50) NOT NULL,
  `token` varchar(50) NOT NULL,
  `Bucket` varchar(50) DEFAULT NULL COMMENT '桶名称',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of system_cos
-- ----------------------------

-- ----------------------------
-- Table structure for `system_design`
-- ----------------------------
DROP TABLE IF EXISTS `system_design`;
CREATE TABLE `system_design` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(32) NOT NULL COMMENT '模板名称',
  `pic_url` varchar(256) NOT NULL DEFAULT '' COMMENT '模板图片',
  `appid` int(8) NOT NULL COMMENT '应用id，system_app表里的',
  `info` longtext NOT NULL COMMENT '设计信息(json格式)',
  `tags` varchar(256) NOT NULL COMMENT '标签，逗号分隔(茶叶,简约)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `is_edit` tinyint(1) DEFAULT '0' COMMENT '是否编辑 0=未编辑 1=正在编辑',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `admin_remark` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='系统模板表';

-- ----------------------------
-- Records of system_design
-- ----------------------------
INSERT INTO `system_design` VALUES ('39', '默认模版1', 'https://imgs.juanpao.com/admin%2Fvip%2F15677579725d721694c359c.png', '2', '\"[{\\\"type\\\":1,\\\"edit\\\":false,\\\"details\\\":{\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F15%2F15605806585d049232c8054.jpeg\\\",\\\"link\\\":\\\"link1\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617862305d16f7767bb03.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617862525d16f78cb2a13.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"}],\\\"dotShow\\\":true,\\\"color1\\\":\\\"#ff0000\\\",\\\"color2\\\":\\\"#fff\\\",\\\"boxHeight\\\":102},\\\"id\\\":0},{\\\"type\\\":22,\\\"edit\\\":false,\\\"details\\\":{\\\"text\\\":\\\"\\u8bf7\\u8f93\\u5165\\\",\\\"color1\\\":\\\"#fff\\\",\\\"color2\\\":\\\"#fff\\\",\\\"color3\\\":\\\"#333\\\"},\\\"id\\\":1},{\\\"type\\\":3,\\\"edit\\\":false,\\\"details\\\":{\\\"col\\\":\\\"25%\\\",\\\"fontSize\\\":\\\"12px\\\",\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859565d16f664b6beb.jpeg\\\",\\\"text\\\":\\\"\\u7504\\u9009\\u9c9c\\u679c\\\",\\\"link\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859605d16f668b69c8.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u8425\\u517b\\u852c\\u83dc\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859645d16f66c348d7.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u79bd\\u86cb\\u8089\\u7c7b\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859675d16f66f74520.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u6c34\\u4ea7\\u6d77\\u9c9c\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859735d16f67518520.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u7cae\\u6cb9\\u8c03\\u5473\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859805d16f67c8544e.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u5bb6\\u5ead\\u526f\\u98df\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859835d16f67fdafac.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u4f11\\u95f2\\u96f6\\u98df\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F06%2F29%2F15617859865d16f682df584.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u9152\\u996e\\u51b2\\u8c03\\\",\\\"title\\\":\\\"\\\"}],\\\"color1\\\":\\\"#333\\\",\\\"color2\\\":\\\"#fff\\\",\\\"radius\\\":100},\\\"id\\\":2},{\\\"type\\\":27,\\\"edit\\\":false,\\\"details\\\":{\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F05%2F31%2F15592809955cf0bd6302e17.png\\\",\\\"link\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F05%2F31%2F15592809965cf0bd64ca835.png\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F05%2F31%2F15592809995cf0bd67303a4.png\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"}]},\\\"id\\\":3},{\\\"type\\\":17,\\\"edit\\\":false,\\\"details\\\":{\\\"positionRight\\\":6,\\\"positionBottom\\\":16,\\\"opacity\\\":0.9,\\\"imgs\\\":[{\\\"src\\\":\\\".\\/decoration\\/images\\/service.png\\\",\\\"link\\\":\\\"link1\\\"}]},\\\"id\\\":4},{\\\"type\\\":28,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"1\\\"},\\\"id\\\":5}]\"', '', null, '1', '1', null, '1559280965', '1567757972', null);
INSERT INTO `system_design` VALUES ('40', '系统模版', 'https://imgs.juanpao.com/admin%2Fvip%2F15677596485d721d2033899.png', '2', '\"[{\\\"type\\\":13,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"1\\\",\\\"color1\\\":\\\"#f5f5f5\\\",\\\"color2\\\":\\\"#fff\\\",\\\"boxHeight\\\":5,\\\"paddingTopBottom\\\":0},\\\"id\\\":0},{\\\"type\\\":1,\\\"edit\\\":false,\\\"details\\\":{\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677557975d720e15ef894.png\\\",\\\"link\\\":\\\"link1\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677579405d72167472d01.png\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"}],\\\"dotShow\\\":true,\\\"color1\\\":\\\"#ff0000\\\",\\\"color2\\\":\\\"#fff\\\",\\\"boxHeight\\\":145},\\\"id\\\":1},{\\\"type\\\":3,\\\"edit\\\":false,\\\"details\\\":{\\\"col\\\":\\\"25%\\\",\\\"fontSize\\\":\\\"12px\\\",\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677558415d720e41b2cf5.jpeg\\\",\\\"text\\\":\\\"\\u7504\\u9009\\u9c9c\\u679c\\\",\\\"link\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677558395d720e3fc9e31.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u65b0\\u9c9c\\u852c\\u83dc\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677558575d720e51c1f4b.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u575a\\u679c\\u96f6\\u98df\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677558695d720e5d42ad6.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\u5bb6\\u5c45\\u65e5\\u7528\\\",\\\"title\\\":\\\"\\\"}],\\\"color1\\\":\\\"#787878\\\",\\\"color2\\\":\\\"#fff\\\",\\\"radius\\\":20},\\\"id\\\":2},{\\\"type\\\":13,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"1\\\",\\\"color1\\\":\\\"#f5f5f5\\\",\\\"color2\\\":\\\"#fff\\\",\\\"boxHeight\\\":5,\\\"paddingTopBottom\\\":0},\\\"id\\\":3},{\\\"type\\\":27,\\\"edit\\\":false,\\\"details\\\":{\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677565835d721127a3e20.png\\\",\\\"link\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677565865d72112a5b2bb.png\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677565885d72112c6beda.png\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"}]},\\\"id\\\":4},{\\\"type\\\":13,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"1\\\",\\\"color1\\\":\\\"#f5f5f5\\\",\\\"color2\\\":\\\"#fff\\\",\\\"boxHeight\\\":5,\\\"paddingTopBottom\\\":0},\\\"id\\\":5},{\\\"type\\\":6,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"2\\\",\\\"color2\\\":\\\"#fff\\\",\\\"radius\\\":10,\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677577655d7215c51bfb2.jpeg\\\",\\\"text\\\":\\\"\\\",\\\"link\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677577715d7215cb516b3.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677577885d7215dc7c8ae.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"},{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677577905d7215de4cad7.jpeg\\\",\\\"link\\\":\\\"\\\",\\\"text\\\":\\\"\\\",\\\"title\\\":\\\"\\\"}]},\\\"id\\\":6},{\\\"type\\\":13,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"1\\\",\\\"color1\\\":\\\"#f5f5f5\\\",\\\"color2\\\":\\\"#fff\\\",\\\"boxHeight\\\":5,\\\"paddingTopBottom\\\":0},\\\"id\\\":7},{\\\"type\\\":28,\\\"edit\\\":false,\\\"details\\\":{\\\"style\\\":\\\"1\\\"},\\\"id\\\":8},{\\\"type\\\":14,\\\"edit\\\":false,\\\"details\\\":{\\\"positionRight\\\":5,\\\"positionBottom\\\":23,\\\"opacity\\\":0.84,\\\"goTop\\\":true,\\\"shire\\\":true,\\\"imgs\\\":[{\\\"src\\\":\\\"https:\\/\\/imgs.juanpao.com\\/2019%2F09%2F06%2F15677595955d721ceb03326.png\\\",\\\"link\\\":\\\"link1\\\"}]},\\\"id\\\":9},{\\\"type\\\":17,\\\"edit\\\":false,\\\"details\\\":{\\\"positionRight\\\":5,\\\"positionBottom\\\":16,\\\"opacity\\\":0.9,\\\"imgs\\\":[{\\\"src\\\":\\\".\\/decoration\\/images\\/service.png\\\",\\\"link\\\":\\\"link1\\\"}]},\\\"id\\\":10}]\"', '', null, '0', '1', null, '1567757949', '1567759648', null);

-- ----------------------------
-- Table structure for `system_diy_config`
-- ----------------------------
DROP TABLE IF EXISTS `system_diy_config`;
CREATE TABLE `system_diy_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL COMMENT '应用id',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `key` varchar(50) DEFAULT NULL COMMENT '键',
  `value` text NOT NULL COMMENT '值 html格式',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=数值 2=字符串 3=数组，目前只有2',
  `status` tinyint(1) NOT NULL COMMENT '状态 1可用 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COMMENT='系统自定义配置表';

-- ----------------------------
-- Records of system_diy_config
-- ----------------------------
INSERT INTO `system_diy_config` VALUES ('1', '2', '供货商宣传单页', '供货商宣传单页', 'supplierbrochure', '<p><img src=\"https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15647267025d43d5ae83fc5.png\" width=\"100%\" alt=\"供应商入驻详情.png\"/></p>', '2', '1', '1561775891', '1562665732', null);
INSERT INTO `system_diy_config` VALUES ('2', '2', '团长宣传单页', '团长宣传单页', 'groupbrochure', '<p><img src=\"https://imgs.juanpao.com/merchant%2Fshop%2Fgoods_picture%2F13%2FccvWPn%2F15647266575d43d5813c269.png\" width=\"100%\" alt=\"团长招募详情.png\"/></p>', '2', '1', '1561778473', '1562665720', null);
INSERT INTO `system_diy_config` VALUES ('3', '2', '团长联系手机号', '团长联系手机号', 'groupphone', '13999999999', '2', '1', '1562055531', '1562056394', '1564538589');
INSERT INTO `system_diy_config` VALUES ('4', '2', '供货商联系电话', '供货商联系电话', 'supplierphone', '13999999999', '2', '1', '1562056332', '1562056401', '1564538586');
INSERT INTO `system_diy_config` VALUES ('5', '2', '资质规格', '用户协议', 'user_protocol', '<p>用户协议</p>', '2', '1', '1562837497', '1562837497', null);
INSERT INTO `system_diy_config` VALUES ('6', '2', '资质规格', '隐私政策', 'privacy_policy', '<p>隐私政策</p>', '2', '1', '1562837553', '1562837553', null);
INSERT INTO `system_diy_config` VALUES ('7', '2', '资质规格', '资质', 'qualifications', '<p><span style=\"color: rgb(102, 102, 102); font-family: 思源字体, sans-serif; font-size: 14px; background-color: rgb(255, 255, 255);\">资质</span></p>', '2', '1', '1562837590', '1562837590', null);
INSERT INTO `system_diy_config` VALUES ('8', '2', '常见问题', '什么时候发货', 'when_to_ship', '<p>什么时候发货</p>', '2', '1', '1562837651', '1562837651', '1564456710');
INSERT INTO `system_diy_config` VALUES ('9', '2', '客服电话', '客服电话', 'wolive_phone', '<p>1</p>', '2', '1', '1564456397', '1564456397', '1564456429');
INSERT INTO `system_diy_config` VALUES ('10', '2', '详情顶部', '', 'info_header', '', '2', '1', '1564456397', '1564456397', null);
INSERT INTO `system_diy_config` VALUES ('11', '2', '详情底部', '', 'info_bottom', '', '2', '1', '1564456397', '1564456397', null);

-- ----------------------------
-- Table structure for `system_diy_express_template`
-- ----------------------------
DROP TABLE IF EXISTS `system_diy_express_template`;
CREATE TABLE `system_diy_express_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(32) NOT NULL COMMENT '模板名称',
  `english_name` varchar(32) NOT NULL COMMENT '英文名称',
  `appid` int(8) NOT NULL COMMENT '应用id，system_app表里的',
  `keywords_ids` text NOT NULL COMMENT '参数ids',
  `keywrod_info` text COMMENT '选中的信息',
  `info` longtext NOT NULL COMMENT '设计信息(html)',
  `width` float(5,2) NOT NULL COMMENT '宽度',
  `height` float(5,2) NOT NULL COMMENT '高度',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `admin_remark` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='系统快递模板表';

-- ----------------------------
-- Records of system_diy_express_template
-- ----------------------------
INSERT INTO `system_diy_express_template` VALUES (1, '团长单', 'leader_order', 2, '[{\"name\":\"商家信息\",\"id\":\"2\",\"type\":\"0\",\"child\":[{\"name\":\"商家姓名\",\"id\":\"70\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_name\"},{\"name\":\"商家地址\",\"id\":\"21\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_addr\"},{\"name\":\"商家电话\",\"id\":\"20\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_phone\"},{\"name\":\"商家名称\",\"id\":\"19\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"widget_name\"}]},{\"name\":\"表格信息\",\"id\":\"3\",\"type\":\"1\",\"child\":[{\"name\":\"货号\",\"id\":\"68\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goods_code\"},{\"name\":\"商品ID\",\"id\":\"67\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goods_id\"},{\"name\":\"规格\",\"id\":\"66\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"property\"},{\"name\":\"单价\",\"id\":\"7\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"price\"},{\"name\":\"数量\",\"id\":\"4\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"number\"},{\"name\":\"商品名称\",\"id\":\"2\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goodsname\"},{\"name\":\"标签\",\"id\":\"16\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"label\"},{\"name\":\"短标题\",\"id\":\"17\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"short_name\"}]},{\"name\":\"团长信息\",\"id\":\"5\",\"type\":\"0\",\"child\":[{\"name\":\"路线\",\"id\":\"41\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"route\"},{\"name\":\"配送方式\",\"id\":\"40\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"express_type\"},{\"name\":\"团长城市\",\"id\":\"39\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_city\"},{\"name\":\"团长小区\",\"id\":\"38\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_area_name\"},{\"name\":\"取货点\",\"id\":\"69\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_addr\"},{\"name\":\"团长ID\",\"id\":\"37\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_uid\"},{\"name\":\"团长电话\",\"id\":\"36\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_phone\"},{\"name\":\"团长姓名\",\"id\":\"35\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_name\"},{\"name\":\"团长昵称\",\"id\":\"34\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_nickname\"}]},{\"name\":\"图片信息\",\"id\":\"6\",\"type\":\"2\",\"child\":[{\"name\":\"小程序码\",\"id\":\"24\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281435d1db5af6d82a.jpeg\",\"english_name\":\"widget_code\"},{\"name\":\"LOGO\",\"id\":\"23\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281705d1db5ca42e1b.jpeg\",\"english_name\":\"logo\"}]}]', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"19\",\"name\":\"商家名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"37\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_uid\"},{\"id\":\"35\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_name\"},{\"id\":\"34\",\"name\":\"团长昵称\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_nickname\"},{\"id\":\"36\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_phone\"},{\"id\":\"69\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_addr\"},{\"id\":\"38\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_area_name\"},{\"id\":\"40\",\"name\":\"配送方式\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"express_type\"},{\"id\":\"39\",\"name\":\"团长城市\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"leader_city\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"16\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"label\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 982px;\">\n					<div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; height: 26px; z-index: 0; left: 0px; top: 21px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 43px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 37\" id=\"item-active\" data-name=\"37\" data-englishname=\"leader_uid\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 0px;\"><span>团长ID:$leader_uid</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 35\" id=\"item-active\" data-name=\"35\" data-englishname=\"leader_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 22px;\"><span>团长姓名:$leader_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 34\" id=\"item-active\" data-name=\"34\" data-englishname=\"leader_nickname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 42px;\"><span>团长昵称:$leader_nickname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 36\" id=\"item-active\" data-name=\"36\" data-englishname=\"leader_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 163px; top: 65px;\"><span>团长电话:$leader_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 69\" id=\"item-active\" data-name=\"69\" data-englishname=\"leader_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 86px;\"><span>取货点:$leader_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 38\" id=\"item-active\" data-name=\"38\" data-englishname=\"leader_area_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 91px;\"><span>团长小区:$leader_area_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 40\" id=\"item-active\" data-name=\"40\" data-englishname=\"express_type\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 115px;\"><span>配送方式:$express_type</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 39\" id=\"item-active\" data-name=\"39\" data-englishname=\"leader_city\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 162px; top: 114px;\"><span>团长城市:$leader_city</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 0px; top: 143px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"label\" style=\"border:1px solid black;padding:10px;\">标签</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19 item-active\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 63px;\"><span>商家名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', 378.00, 980.00, NULL, 1, NULL, 1562233324, 1588062284, NULL);
INSERT INTO `system_diy_express_template` VALUES (2, '发货单', 'Invoice', 2, '[{\"name\":\"商家信息\",\"id\":\"2\",\"type\":\"0\",\"child\":[{\"name\":\"商家姓名\",\"id\":\"70\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_name\"},{\"name\":\"商家地址\",\"id\":\"21\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_addr\"},{\"name\":\"商家电话\",\"id\":\"20\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_phone\"},{\"name\":\"商家名称\",\"id\":\"19\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"widget_name\"}]},{\"name\":\"表格信息\",\"id\":\"3\",\"type\":\"1\",\"child\":[{\"name\":\"商品ID\",\"id\":\"67\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goods_id\"},{\"name\":\"规格\",\"id\":\"66\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"property\"},{\"name\":\"单价\",\"id\":\"7\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"price\"},{\"name\":\"数量\",\"id\":\"4\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"number\"},{\"name\":\"商品名称\",\"id\":\"2\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goodsname\"}]},{\"name\":\"买家信息\",\"id\":\"4\",\"type\":\"0\",\"child\":[{\"name\":\"买家电话\",\"id\":\"9\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"phone\"},{\"name\":\"买家姓名\",\"id\":\"6\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"name\"},{\"name\":\"买家区域\",\"id\":\"33\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"buyer_area\"},{\"name\":\"买家城市\",\"id\":\"32\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"buyer_city\"},{\"name\":\"买家留言\",\"id\":\"31\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"remark\"},{\"name\":\"实付金额\",\"id\":\"29\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"payment_money\"},{\"name\":\"买家地址\",\"id\":\"26\",\"parentId\":\"4\",\"pic_url\":\"\",\"english_name\":\"address\"}]},{\"name\":\"团长信息\",\"id\":\"5\",\"type\":\"0\",\"child\":[{\"name\":\"路线\",\"id\":\"41\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"route\"},{\"name\":\"配送方式\",\"id\":\"40\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"express_type\"},{\"name\":\"团长城市\",\"id\":\"39\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_city\"},{\"name\":\"团长小区\",\"id\":\"38\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_area_name\"},{\"name\":\"取货点\",\"id\":\"69\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_addr\"},{\"name\":\"团长ID\",\"id\":\"37\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_uid\"},{\"name\":\"团长电话\",\"id\":\"36\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_phone\"},{\"name\":\"团长姓名\",\"id\":\"35\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_name\"},{\"name\":\"团长昵称\",\"id\":\"34\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"leader_nickname\"}]},{\"name\":\"图片信息\",\"id\":\"6\",\"type\":\"2\",\"child\":[{\"name\":\"小程序码\",\"id\":\"24\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281435d1db5af6d82a.jpeg\",\"english_name\":\"widget_code\"},{\"name\":\"LOGO\",\"id\":\"23\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281705d1db5ca42e1b.jpeg\",\"english_name\":\"logo\"}]}]', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"19\",\"name\":\"商家名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":4,\"name\":\"买家信息\",\"type\":0,\"child\":[{\"id\":\"9\",\"name\":\"买家电话\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"phone\"},{\"id\":\"6\",\"name\":\"买家姓名\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"name\"},{\"id\":\"33\",\"name\":\"买家区域\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"buyer_area\"},{\"id\":\"32\",\"name\":\"买家城市\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"buyer_city\"},{\"id\":\"26\",\"name\":\"买家地址\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"address\"},{\"id\":\"31\",\"name\":\"买家留言\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"remark\"},{\"id\":\"29\",\"name\":\"实付金额\",\"pic_url\":\"\",\"parentId\":\"4\",\"english_name\":\"payment_money\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"40\",\"name\":\"配送方式\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"express_type\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"2\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goodsname\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 24px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 25px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 47px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 9\" id=\"item-active\" data-name=\"9\" data-englishname=\"phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 69px;\"><span>买家电话:$phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 6\" id=\"item-active\" data-name=\"6\" data-englishname=\"name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 68px;\"><span>买家姓名:$name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 33\" id=\"item-active\" data-name=\"33\" data-englishname=\"buyer_area\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 92px;\"><span>买家区域:$buyer_area</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 32\" id=\"item-active\" data-name=\"32\" data-englishname=\"buyer_city\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 171px; top: 91px;\"><span>买家城市:$buyer_city</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 26\" id=\"item-active\" data-name=\"26\" data-englishname=\"address\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 116px;\"><span>买家地址:$address</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 40\" id=\"item-active\" data-name=\"40\" data-englishname=\"express_type\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 171px; top: 117px;\"><span>配送方式:$express_type</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 31\" id=\"item-active\" data-name=\"31\" data-englishname=\"remark\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 137px;\"><span>买家留言:$remark</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 29\" id=\"item-active\" data-name=\"29\" data-englishname=\"payment_money\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 169px; top: 136px;\"><span>实付金额:$payment_money</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 4px; top: 171px; position: absolute; cursor: move; z-index: 0; width: 369px;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goodsname\" style=\"border:1px solid black;padding:10px;\">商品名称</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19 item-active\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 92px; top: 3px;\"><span>商家名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', 378.00, 680.00, NULL, 1, NULL, 1562233713, 1588062376, NULL);
INSERT INTO `system_diy_express_template` VALUES (3, '采购单', 'purchasing_order', 2, '[{\"name\":\"商家信息\",\"id\":\"2\",\"type\":\"0\",\"child\":[{\"name\":\"商家姓名\",\"id\":\"70\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_name\"},{\"name\":\"商家地址\",\"id\":\"21\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_addr\"},{\"name\":\"商家电话\",\"id\":\"20\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_phone\"},{\"name\":\"商家名称\",\"id\":\"19\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"widget_name\"}]},{\"name\":\"表格信息\",\"id\":\"3\",\"type\":\"1\",\"child\":[{\"name\":\"货号\",\"id\":\"68\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goods_code\"},{\"name\":\"商品ID\",\"id\":\"67\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goods_id\"},{\"name\":\"规格\",\"id\":\"66\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"property\"},{\"name\":\"单价\",\"id\":\"7\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"price\"},{\"name\":\"数量\",\"id\":\"4\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"number\"},{\"name\":\"商品名称\",\"id\":\"2\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"goodsname\"},{\"name\":\"标签\",\"id\":\"16\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"label\"},{\"name\":\"短标题\",\"id\":\"17\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"short_name\"}]},{\"name\":\"图片信息\",\"id\":\"6\",\"type\":\"2\",\"child\":[{\"name\":\"小程序码\",\"id\":\"24\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281435d1db5af6d82a.jpeg\",\"english_name\":\"widget_code\"},{\"name\":\"LOGO\",\"id\":\"23\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281705d1db5ca42e1b.jpeg\",\"english_name\":\"logo\"}]}]', '[{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"67\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_id\"},{\"id\":\"68\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"goods_code\"},{\"id\":\"17\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"short_name\"},{\"id\":\"16\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"label\"},{\"id\":\"66\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"property\"},{\"id\":\"4\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"number\"},{\"id\":\"7\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"price\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"19\",\"name\":\"商家名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 19\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 98px; top: 15px;\"><span>商家名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 48px; position: absolute; cursor: move;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"goods_id\" style=\"border:1px solid black;padding:10px;\">商品ID</th><th data-englishname=\"goods_code\" style=\"border:1px solid black;padding:10px;\">货号</th><th data-englishname=\"short_name\" style=\"border:1px solid black;padding:10px;\">短标题</th><th data-englishname=\"label\" style=\"border:1px solid black;padding:10px;\">标签</th><th data-englishname=\"property\" style=\"border:1px solid black;padding:10px;\">规格</th><th data-englishname=\"number\" style=\"border:1px solid black;padding:10px;\">数量</th><th data-englishname=\"price\" style=\"border:1px solid black;padding:10px;\">单价</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', 378.00, 680.00, NULL, 1, NULL, 1562233991, 1591262596, NULL);
INSERT INTO `system_diy_express_template` VALUES (4, '配货单', 'distribution_bill', 2, '[{\"name\":\"商品信息\",\"id\":\"1\",\"type\":\"0\",\"child\":[{\"name\":\"货号\",\"id\":\"46\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"goods_code\"},{\"name\":\"商品ID\",\"id\":\"45\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"goods_id\"},{\"name\":\"规格\",\"id\":\"44\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"property\"},{\"name\":\"短标题\",\"id\":\"43\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"short_name\"},{\"name\":\"标签\",\"id\":\"42\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"label\"},{\"name\":\"单价\",\"id\":\"8\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"price\"},{\"name\":\"数量\",\"id\":\"3\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"number\"},{\"name\":\"商品名称\",\"id\":\"1\",\"parentId\":\"1\",\"pic_url\":\"\",\"english_name\":\"goodsname\"}]},{\"name\":\"商家信息\",\"id\":\"2\",\"type\":\"0\",\"child\":[{\"name\":\"商家姓名\",\"id\":\"70\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_name\"},{\"name\":\"商家地址\",\"id\":\"21\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_addr\"},{\"name\":\"商家电话\",\"id\":\"20\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_phone\"},{\"name\":\"商家名称\",\"id\":\"19\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"widget_name\"}]},{\"name\":\"表格信息\",\"id\":\"3\",\"type\":\"1\",\"child\":[{\"name\":\"取货点\",\"id\":\"71\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_addr\"},{\"name\":\"路线\",\"id\":\"65\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"route\"},{\"name\":\"配送方式\",\"id\":\"64\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"express_type\"},{\"name\":\"团长城市\",\"id\":\"63\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_city\"},{\"name\":\"团长小区\",\"id\":\"62\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_area_name\"},{\"name\":\"团长ID\",\"id\":\"61\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_uid\"},{\"name\":\"团长电话\",\"id\":\"60\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_phone\"},{\"name\":\"团长昵称\",\"id\":\"59\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_nickname\"},{\"name\":\"数量\",\"id\":\"4\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"number\"},{\"name\":\"团长姓名\",\"id\":\"18\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_name\"}]}]', '[{\"id\":1,\"name\":\"商品信息\",\"type\":0,\"child\":[{\"id\":\"45\",\"name\":\"商品ID\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_id\"},{\"id\":\"46\",\"name\":\"货号\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goods_code\"},{\"id\":\"1\",\"name\":\"商品名称\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"goodsname\"},{\"id\":\"43\",\"name\":\"短标题\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"short_name\"},{\"id\":\"42\",\"name\":\"标签\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"label\"},{\"id\":\"44\",\"name\":\"规格\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"property\"},{\"id\":\"3\",\"name\":\"数量\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"number\"},{\"id\":\"8\",\"name\":\"单价\",\"pic_url\":\"\",\"parentId\":\"1\",\"english_name\":\"price\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"65\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"route\"},{\"id\":\"61\",\"name\":\"团长ID\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_uid\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"},{\"id\":\"18\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_name\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 45\" id=\"item-active\" data-name=\"45\" data-englishname=\"goods_id\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 0px;\"><span>商品ID:$goods_id</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 46\" id=\"item-active\" data-name=\"46\" data-englishname=\"goods_code\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 52px;\"><span>货号:$goods_code</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 1\" id=\"item-active\" data-name=\"1\" data-englishname=\"goodsname\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 175px; top: 0px;\"><span>商品名称:$goodsname</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 43\" id=\"item-active\" data-name=\"43\" data-englishname=\"short_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 177px; top: 26px;\"><span>短标题:$short_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 42\" id=\"item-active\" data-name=\"42\" data-englishname=\"label\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 27px;\"><span>标签:$label</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 44\" id=\"item-active\" data-name=\"44\" data-englishname=\"property\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 176px; top: 54px;\"><span>规格:$property</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 3\" id=\"item-active\" data-name=\"3\" data-englishname=\"number\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 0px; top: 80px;\"><span>数量:$number</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 8\" id=\"item-active\" data-name=\"8\" data-englishname=\"price\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 173px; top: 80px;\"><span>单价:$price</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table item-active\" data-name=\"name-table\" style=\"left: 0px; top: 108px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"route\" style=\"border:1px solid black;padding:10px;\">路线</th><th data-englishname=\"leader_uid\" style=\"border:1px solid black;padding:10px;\">团长ID</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th><th data-englishname=\"leader_name\" style=\"border:1px solid black;padding:10px;\">团长姓名</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', 378.00, 680.00, NULL, 1, NULL, 1562234447, 1588062480, NULL);
INSERT INTO `system_diy_express_template` VALUES (5, '团长路线单', 'leader_route', 2, '[{\"name\":\"商家信息\",\"id\":\"2\",\"type\":\"0\",\"child\":[{\"name\":\"商家姓名\",\"id\":\"70\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_name\"},{\"name\":\"商家地址\",\"id\":\"21\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_addr\"},{\"name\":\"商家电话\",\"id\":\"20\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_phone\"},{\"name\":\"商家名称\",\"id\":\"19\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"widget_name\"}]},{\"name\":\"表格信息\",\"id\":\"3\",\"type\":\"1\",\"child\":[{\"name\":\"取货点\",\"id\":\"71\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_addr\"},{\"name\":\"团长小区\",\"id\":\"62\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_area_name\"},{\"name\":\"团长电话\",\"id\":\"60\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_phone\"},{\"name\":\"团长姓名\",\"id\":\"18\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"leader_name\"}]},{\"name\":\"团长信息\",\"id\":\"5\",\"type\":\"0\",\"child\":[{\"name\":\"路线\",\"id\":\"41\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"route\"}]},{\"name\":\"图片信息\",\"id\":\"6\",\"type\":\"2\",\"child\":[{\"name\":\"小程序码\",\"id\":\"24\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281435d1db5af6d82a.jpeg\",\"english_name\":\"widget_code\"},{\"name\":\"LOGO\",\"id\":\"23\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281705d1db5ca42e1b.jpeg\",\"english_name\":\"logo\"}]}]', '[{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"19\",\"name\":\"商家名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"18\",\"name\":\"团长姓名\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_name\"},{\"id\":\"60\",\"name\":\"团长电话\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_phone\"},{\"id\":\"62\",\"name\":\"团长小区\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_area_name\"},{\"id\":\"71\",\"name\":\"取货点\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"leader_addr\"}]},{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"41\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"route\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 8px; top: 36px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 184px; top: 40px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 6px; top: 62px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 41\" id=\"item-active\" data-name=\"41\" data-englishname=\"route\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 133px; top: 9px;\"><span>路线:$route</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 4px; top: 92px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"leader_name\" style=\"border:1px solid black;padding:10px;\">团长姓名</th><th data-englishname=\"leader_phone\" style=\"border:1px solid black;padding:10px;\">团长电话</th><th data-englishname=\"leader_area_name\" style=\"border:1px solid black;padding:10px;\">团长小区</th><th data-englishname=\"leader_addr\" style=\"border:1px solid black;padding:10px;\">取货点</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19 item-active\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 181px; top: 63px;\"><span>商家名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', 378.00, 680.00, NULL, 1, NULL, 1562234501, 1588062521, NULL);
INSERT INTO `system_diy_express_template` VALUES (7, '仓库路线单', 'warehouse_route', 2, '[{\"name\":\"商家信息\",\"id\":\"2\",\"type\":\"0\",\"child\":[{\"name\":\"商家姓名\",\"id\":\"70\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_name\"},{\"name\":\"商家地址\",\"id\":\"21\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_addr\"},{\"name\":\"商家电话\",\"id\":\"20\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"merchant_phone\"},{\"name\":\"商家名称\",\"id\":\"19\",\"parentId\":\"2\",\"pic_url\":\"\",\"english_name\":\"widget_name\"}]},{\"name\":\"表格信息\",\"id\":\"3\",\"type\":\"1\",\"child\":[{\"name\":\"仓库地址\",\"id\":\"73\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"warehouse_addr\"},{\"name\":\"仓库名称\",\"id\":\"72\",\"parentId\":\"3\",\"pic_url\":\"\",\"english_name\":\"warehouse_name\"}]},{\"name\":\"团长信息\",\"id\":\"5\",\"type\":\"0\",\"child\":[{\"name\":\"路线\",\"id\":\"41\",\"parentId\":\"5\",\"pic_url\":\"\",\"english_name\":\"route\"}]},{\"name\":\"图片信息\",\"id\":\"6\",\"type\":\"2\",\"child\":[{\"name\":\"小程序码\",\"id\":\"24\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281435d1db5af6d82a.jpeg\",\"english_name\":\"widget_code\"},{\"name\":\"LOGO\",\"id\":\"23\",\"parentId\":\"6\",\"pic_url\":\"https://imgs.juanpao.com/admin%2Fprint%2F15622281705d1db5ca42e1b.jpeg\",\"english_name\":\"logo\"}]}]', '[{\"id\":5,\"name\":\"团长信息\",\"type\":0,\"child\":[{\"id\":\"41\",\"name\":\"路线\",\"pic_url\":\"\",\"parentId\":\"5\",\"english_name\":\"route\"}]},{\"id\":2,\"name\":\"商家信息\",\"type\":0,\"child\":[{\"id\":\"70\",\"name\":\"商家姓名\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_name\"},{\"id\":\"21\",\"name\":\"商家地址\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_addr\"},{\"id\":\"20\",\"name\":\"商家电话\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"merchant_phone\"},{\"id\":\"19\",\"name\":\"商家名称\",\"pic_url\":\"\",\"parentId\":\"2\",\"english_name\":\"widget_name\"}]},{\"id\":3,\"name\":\"表格信息\",\"type\":1,\"child\":[{\"id\":\"72\",\"name\":\"仓库名称\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"warehouse_name\"},{\"id\":\"73\",\"name\":\"仓库地址\",\"pic_url\":\"\",\"parentId\":\"3\",\"english_name\":\"warehouse_addr\"}]}]', '<div class=\"edit-box\" style=\"width: 380px; height: 682px;\">\n					<div class=\"item 41\" id=\"item-active\" data-name=\"41\" data-englishname=\"route\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 141px; top: 12px;\"><span>路线:$route</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 70\" id=\"item-active\" data-name=\"70\" data-englishname=\"merchant_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 7px; top: 38px;\"><span>商家姓名:$merchant_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 21\" id=\"item-active\" data-name=\"21\" data-englishname=\"merchant_addr\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 182px; top: 37px;\"><span>商家地址:$merchant_addr</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 20\" id=\"item-active\" data-name=\"20\" data-englishname=\"merchant_phone\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 6px; top: 58px;\"><span>商家电话:$merchant_phone</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item item-table\" data-name=\"name-table\" style=\"left: 8px; top: 89px; position: absolute; cursor: move; z-index: 0;\" l_zoom_mode=\"auto\"><table style=\"white-space:normal;border:1px solid black;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr><th data-englishname=\"warehouse_name\" style=\"border:1px solid black;padding:10px;\">仓库名称</th><th data-englishname=\"warehouse_addr\" style=\"border:1px solid black;padding:10px;\">仓库地址</th></tr></tbody></table><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div><div class=\"item 19 item-active\" id=\"item-active\" data-name=\"19\" data-englishname=\"widget_name\" l_zoom_mode=\"auto\" style=\"position: absolute; cursor: move; z-index: 0; left: 181px; top: 60px;\"><span>商家名称:$widget_name</span><div class=\"zoom_right\"></div><div class=\"zoom_rb\"></div><div class=\"zoom_bottom\"></div></div></div>', 378.00, 680.00, NULL, 1, NULL, 1587368291, 1588062544, NULL);


-- ----------------------------
-- Table structure for `system_express`
-- ----------------------------
DROP TABLE IF EXISTS `system_express`;
CREATE TABLE `system_express` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '物流名称',
  `simple_name` varchar(100) NOT NULL DEFAULT '' COMMENT '缩写名称',
  `phone` varchar(255) NOT NULL DEFAULT '' COMMENT '联系电话',
  `img_url` varchar(512) NOT NULL DEFAULT '' COMMENT '图片地址',
  `url` varchar(512) NOT NULL DEFAULT '' COMMENT '官网地址',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `is_ok` int(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=421 DEFAULT CHARSET=utf8 COMMENT='系统-快递表';

-- ----------------------------
-- Records of system_express
-- ----------------------------
INSERT INTO `system_express` VALUES ('1', '顺丰速运', 'SF', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('2', '百世快递', 'HTKY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('3', '中通快递', 'ZTO', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('4', '申通快递', 'STO', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('5', '圆通速递', 'YTO', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('6', '韵达速递', 'YD', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('7', '邮政快递包裹', 'YZPY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('8', 'EMS', 'EMS', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('9', '天天快递', 'HHTT', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('10', '京东快递', 'JD', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('11', '优速快递', 'UC', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('12', '德邦快递', 'DBL', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('13', '宅急送', 'ZJS', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('14', 'TNT快递', 'TNT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('15', 'UPS', 'UPS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('16', 'DHL', 'DHL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('17', 'FEDEX联邦(国内件）', 'FEDEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('18', 'FEDEX联邦(国际件）', 'FEDEX_GJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('19', '安捷快递', 'AJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('20', '阿里跨境电商物流', 'ALKJWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('21', '安迅物流', 'AX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('22', '安邮美国', 'AYUS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('23', '亚马逊物流', 'AMAZON', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('24', '澳门邮政', 'AOMENYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('25', '安能物流', 'ANE', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('26', '澳多多', 'ADD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('27', '澳邮专线', 'AYCA', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('28', '安鲜达', 'AXD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('29', '安能快运', 'ANEKY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('30', '八达通  ', 'BDT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('31', '百腾物流', 'BETWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('32', '北极星快运', 'BJXKY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('33', '奔腾物流', 'BNTWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('34', '百福东方', 'BFDF', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('35', '贝海国际 ', 'BHGJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('36', '八方安运', 'BFAY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('37', '百世快运', 'BTWL', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('38', '春风物流', 'CFWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('39', '诚通物流', 'CHTWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('40', '传喜物流', 'CXHY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('41', '程光   ', 'CG', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('42', '城市100', 'CITY100', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('43', '城际快递', 'CJKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('44', 'CNPEX中邮快递', 'CNPEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('45', 'COE东方快递', 'COE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('46', '长沙创一', 'CSCY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('47', '成都善途速运', 'CDSTKY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('48', '联合运通', 'CTG', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('49', '疯狂快递', 'CRAZY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('50', 'CBO钏博物流', 'CBO', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('51', '承诺达', 'CND', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('52', 'D速物流', 'DSWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('53', '到了港', 'DLG ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('54', '大田物流', 'DTWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('55', '东骏快捷物流', 'DJKJWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('56', '德坤', 'DEKUN', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('57', '德邦快运', 'DBLKY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('58', '大马鹿', 'DML', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('59', 'E特快', 'ETK', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('60', 'EWE', 'EWE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('61', '快服务', 'KFW', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('62', '飞康达', 'FKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('63', '富腾达  ', 'FTD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('64', '凡宇货的', 'FYKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('65', '速派快递', 'FASTGO', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('66', '丰通快运', 'FT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('67', '冠达   ', 'GD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('68', '国通快递', 'GTO', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('69', '广东邮政', 'GDEMS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('70', '共速达', 'GSD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('71', '广通       ', 'GTONG', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('72', '迦递快递', 'GAI', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('73', '港快速递', 'GKSD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('74', '高铁速递', 'GTSD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('75', '汇丰物流', 'HFWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('76', '黑狗冷链', 'HGLL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('77', '恒路物流', 'HLWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('78', '天地华宇', 'HOAU', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('79', '鸿桥供应链', 'HOTSCM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('80', '海派通物流公司', 'HPTEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('81', '华强物流', 'hq568', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('82', '环球速运  ', 'HQSY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('83', '华夏龙物流', 'HXLWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('84', '豪翔物流 ', 'HXWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('85', '合肥汇文', 'HFHW', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('86', '辉隆物流', 'HLONGWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('87', '华企快递', 'HQKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('88', '韩润物流', 'HRWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('89', '青岛恒通快递', 'HTKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('90', '货运皇物流', 'HYH', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('91', '好来运快递', 'HYLSD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('92', '皇家物流', 'HJWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('93', '捷安达  ', 'JAD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('94', '京广速递', 'JGSD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('95', '九曳供应链', 'JIUYE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('96', '急先达', 'JXD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('97', '晋越快递', 'JYKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('98', '加运美', 'JYM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('99', '景光物流', 'JGWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('100', '佳怡物流', 'JYWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('101', '京东快运', 'JDKY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('102', '佳吉快运', 'CNEX', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('103', '跨越速运', 'KYSY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('104', '跨越物流', 'KYWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('105', '快速递物流', 'KSDWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('106', '快8速运', 'KBSY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('107', '龙邦快递', 'LB', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('108', '立即送', 'LJSKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('109', '联昊通速递', 'LHT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('110', '民邦快递', 'MB', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('111', '民航快递', 'MHKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('112', '美快    ', 'MK', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('113', '门对门快递', 'MDM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('114', '迈隆递运', 'MRDY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('115', '明亮物流', 'MLWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('116', '南方', 'NF', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('117', '能达速递', 'NEDA', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('118', '平安达腾飞快递', 'PADTF', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('119', '泛捷快递', 'PANEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('120', '品骏快递', 'PJ', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('121', 'PCA Express', 'PCA', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('122', '全晨快递', 'QCKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('123', '全日通快递', 'QRT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('124', '快客快递', 'QUICK', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('125', '全信通', 'QXT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('126', '荣庆物流', 'RQ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('127', '七曜中邮', 'QYZY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('128', '如风达', 'RFD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('129', '日日顺物流', 'RRS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('130', '瑞丰速递', 'RFEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('131', '赛澳递', 'SAD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('132', '苏宁物流', 'SNWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('133', '圣安物流', 'SAWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('134', '晟邦物流', 'SBWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('135', '上大物流', 'SDWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('136', '盛丰物流', 'SFWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('137', '速通物流', 'ST', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('138', '速腾快递', 'STWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('139', '速必达物流', 'SUBIDA', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('140', '速递e站', 'SDEZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('141', '速呈宅配', 'SCZPDS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('142', '速尔快递', 'SURE', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('143', '闪送', 'SS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('144', '盛通快递', 'STKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('145', '台湾邮政', 'TAIWANYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('146', '唐山申通', 'TSSTO', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('147', '特急送', 'TJS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('148', '通用物流', 'TYWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('149', '腾林物流', 'TLWL', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('150', '全一快递', 'UAPEX', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('151', '优联吉运', 'ULUCKEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('152', 'UEQ Express', 'UEQ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('153', '万家康  ', 'WJK', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('154', '万家物流', 'WJWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('155', '武汉同舟行', 'WHTZX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('156', '维普恩', 'WPE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('157', '万象物流', 'WXWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('158', '微特派', 'WTP', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('159', '温通物流', 'WTWL', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('160', '迅驰物流  ', 'XCWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('161', '信丰物流', 'XFEX', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('162', '希优特', 'XYT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('163', '新杰物流', 'XJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('164', '源安达快递', 'YADEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('165', '远成物流', 'YCWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('166', '远成快运', 'YCSY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('167', '义达国际物流', 'YDH', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('168', '易达通  ', 'YDT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('169', '原飞航物流', 'YFHEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('170', '亚风快递', 'YFSD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('171', '运通快递', 'YTKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('172', '亿翔快递', 'YXKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('173', '运东西网', 'YUNDX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('174', '壹米滴答', 'YMDD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('175', '邮政国内标快', 'YZBK', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('176', '一站通速运', 'YZTSY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('177', '驭丰速运', 'YFSUYUN', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('178', '余氏东风', 'YSDF', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('179', '耀飞快递', 'YF', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('180', '韵达快运', 'YDKY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('181', '云路', 'YL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('182', '增益快递', 'ZENY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('183', '汇强快递', 'ZHQKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('184', '众通快递', 'ZTE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('185', '中铁快运', 'ZTKY', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('186', '中铁物流', 'ZTWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('187', '郑州速捷', 'SJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('188', '中通快运', 'ZTOKY', '', '', '', '', '1', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('189', '中邮快递', 'ZYKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('190', '中粮我买网', 'WM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('191', '芝麻开门', 'ZMKM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('192', '中骅物流', 'ZHWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('193', '', '', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('194', 'AAE全球专递', 'AAE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('195', 'ACS雅仕快递', 'ACS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('196', 'ADP Express Tracking', 'ADP', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('197', '安圭拉邮政', 'ANGUILAYOU', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('198', 'APAC', 'APAC', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('199', 'Aramex', 'ARAMEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('200', '奥地利邮政', 'AT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('201', 'Australia Post Tracking', 'AUSTRALIA', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('202', '比利时邮政', 'BEL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('203', 'BHT快递', 'BHT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('204', '秘鲁邮政', 'BILUYOUZHE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('205', '巴西邮政', 'BR', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('206', '不丹邮政', 'BUDANYOUZH', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('207', 'CDEK', 'CDEK', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('208', '加拿大邮政', 'CA', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('209', '递必易国际物流', 'DBYWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('210', '大道物流', 'DDWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('211', '德国云快递', 'DGYKD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('212', '到乐国际', 'DLGJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('213', 'DHL德国', 'DHL_DE', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('214', 'DHL(英文版)', 'DHL_EN', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('215', 'DHL全球', 'DHL_GLB', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('216', 'DHL Global Mail', 'DHLGM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('217', '丹麦邮政', 'DK', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('218', 'DPD', 'DPD', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('219', 'DPEX', 'DPEX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('220', '递四方速递', 'D4PX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('221', 'EMS国际', 'EMSGJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('222', '易客满', 'EKM', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('223', 'EPS (联众国际快运)', 'EPS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('224', 'EShipper', 'ESHIPPER', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('225', '丰程物流', 'FCWL', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('226', '法翔速运', 'FX', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('227', 'FQ', 'FQ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('228', '芬兰邮政', 'FLYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('229', '方舟国际速递', 'FZGJ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('230', '国际e邮宝', 'GJEYB', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('231', '国际邮政包裹', 'GJYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('232', 'GE2D', 'GE2D', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('233', '冠泰', 'GT', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('234', 'GLS', 'GLS', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('235', '欧洲专线(邮政)', 'IOZYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('236', '澳大利亚邮政', 'IADLYYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('237', '阿尔巴尼亚邮政', 'IAEBNYYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('238', '阿尔及利亚邮政', 'IAEJLYYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('239', '阿富汗邮政', 'IAFHYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('240', '安哥拉邮政', 'IAGLYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('241', '埃及邮政', 'IAJYZ', '', '', '', '', '0', '1', '1540524600', null, null);
INSERT INTO `system_express` VALUES ('242', '阿鲁巴邮政', 'IALBYZ', '', '', '', '', '0', '1', '1540524600', null, null);

-- ----------------------------
-- Table structure for `system_express_keyword`
-- ----------------------------
DROP TABLE IF EXISTS `system_express_keyword`;
CREATE TABLE `system_express_keyword` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `category_id` tinyint(3) NOT NULL COMMENT '分组id',
  `english_name` varchar(32) DEFAULT NULL COMMENT '类目英文名',
  `name` varchar(32) NOT NULL COMMENT '类目名称',
  `pic_url` varchar(128) NOT NULL DEFAULT '' COMMENT '二维码图片',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COMMENT='快递模板分组字段表';

-- ----------------------------
-- Records of system_express_keyword
-- ----------------------------
INSERT INTO `system_express_keyword` VALUES ('1', '1', 'goodsname', '商品名称', '', '0', '1', '1561016090', '1561016090', null);
INSERT INTO `system_express_keyword` VALUES ('2', '3', 'goodsname', '商品名称', '', '0', '1', '1561016119', '1561016119', null);
INSERT INTO `system_express_keyword` VALUES ('3', '1', 'number', '数量', '', '0', '1', '1561016333', '1561016333', null);
INSERT INTO `system_express_keyword` VALUES ('4', '3', 'number', '数量', '', '0', '1', '1561016348', '1561016348', null);
INSERT INTO `system_express_keyword` VALUES ('5', '3', 'name', '买家姓名', '', '0', '1', '1561016468', '1561016508', null);
INSERT INTO `system_express_keyword` VALUES ('6', '4', 'name', '买家姓名', '', '0', '1', '1561016496', '1561016496', null);
INSERT INTO `system_express_keyword` VALUES ('7', '3', 'price', '单价', '', '0', '1', '1561016613', '1561016613', null);
INSERT INTO `system_express_keyword` VALUES ('8', '1', 'price', '单价', '', '0', '1', '1561016627', '1561016627', null);
INSERT INTO `system_express_keyword` VALUES ('9', '4', 'phone', '买家电话', '', '0', '1', '1561016882', '1561016882', null);
INSERT INTO `system_express_keyword` VALUES ('10', '3', 'address', '买家地址', '', '0', '1', '1561964152', '1561969797', null);
INSERT INTO `system_express_keyword` VALUES ('11', '3', 'order_sn', '订单号', '', '0', '1', '1561964189', '1561964189', null);
INSERT INTO `system_express_keyword` VALUES ('12', '3', 'phone', '买家电话', '', '0', '1', '1561964364', '1561964364', null);
INSERT INTO `system_express_keyword` VALUES ('13', '3', 'stock_number', '库存', '', '0', '1', '1561964622', '1561964622', null);
INSERT INTO `system_express_keyword` VALUES ('14', '3', 'merchant_name', '商家姓名', '', '0', '1', '1561964677', '1561964677', null);
INSERT INTO `system_express_keyword` VALUES ('15', '3', 'merchant_phone', '商家电话', '', '0', '1', '1561964693', '1561964693', null);
INSERT INTO `system_express_keyword` VALUES ('16', '3', 'label', '标签', '', '0', '1', '1561964832', '1561972847', null);
INSERT INTO `system_express_keyword` VALUES ('17', '3', 'short_name', '短标题', '', '0', '1', '1561964852', '1561964852', null);
INSERT INTO `system_express_keyword` VALUES ('18', '3', 'leader_name', '团长姓名', '', '0', '1', '1561965170', '1561972948', null);
INSERT INTO `system_express_keyword` VALUES ('19', '2', 'widget_name', '小程序名称', '', '0', '1', '1561968993', '1561968993', null);
INSERT INTO `system_express_keyword` VALUES ('20', '2', 'merchant_phone', '商家电话', '', '0', '1', '1561969080', '1561969080', null);
INSERT INTO `system_express_keyword` VALUES ('21', '2', 'merchant_addr', '商家地址', '', '0', '1', '1561969312', '1561969312', null);
INSERT INTO `system_express_keyword` VALUES ('22', '2', 'sender', '发件人', '', '0', '1', '1561969371', '1561969371', '1561969371');
INSERT INTO `system_express_keyword` VALUES ('23', '6', 'logo', 'LOGO', 'https://imgs.juanpao.com/admin%2Fprint%2F15622281705d1db5ca42e1b.jpeg', '0', '1', '1561969416', '1562228170', null);
INSERT INTO `system_express_keyword` VALUES ('24', '6', 'widget_code', '小程序码', 'https://imgs.juanpao.com/admin%2Fprint%2F15622281435d1db5af6d82a.jpeg', '0', '1', '1561969542', '1562228143', null);
INSERT INTO `system_express_keyword` VALUES ('25', '2', 'merchant_remark', '商家备注', '', '0', '1', '1561969589', '1561969589', '1561969371');
INSERT INTO `system_express_keyword` VALUES ('26', '4', 'address', '买家地址', '', '0', '1', '1561969812', '1561969812', null);
INSERT INTO `system_express_keyword` VALUES ('27', '4', 'buyer_nickname', '买家昵称', '', '0', '1', '1561970169', '1561970169', null);
INSERT INTO `system_express_keyword` VALUES ('28', '4', 'order_sn', '订单号', '', '0', '1', '1561970233', '1561970258', null);
INSERT INTO `system_express_keyword` VALUES ('29', '4', 'payment_money', '实付金额', '', '0', '1', '1561970345', '1561970345', null);
INSERT INTO `system_express_keyword` VALUES ('30', '4', 'pay_time', '付款时间', '', '0', '1', '1561970644', '1561970644', null);
INSERT INTO `system_express_keyword` VALUES ('31', '4', 'remark', '买家留言', '', '0', '1', '1561970746', '1561970780', null);
INSERT INTO `system_express_keyword` VALUES ('32', '4', 'buyer_city', '买家城市', '', '0', '1', '1561970906', '1561970906', null);
INSERT INTO `system_express_keyword` VALUES ('33', '4', 'buyer_area', '买家区域', '', '0', '1', '1561970954', '1561970954', null);
INSERT INTO `system_express_keyword` VALUES ('34', '5', 'leader_nickname', '团长昵称', '', '0', '1', '1561971109', '1561971109', null);
INSERT INTO `system_express_keyword` VALUES ('35', '5', 'leader_name', '团长姓名', '', '0', '1', '1561971149', '1561971149', null);
INSERT INTO `system_express_keyword` VALUES ('36', '5', 'leader_phone', '团长电话', '', '0', '1', '1561971206', '1561971206', null);
INSERT INTO `system_express_keyword` VALUES ('37', '5', 'leader_uid', '团长ID', '', '0', '1', '1561971300', '1561971300', null);
INSERT INTO `system_express_keyword` VALUES ('38', '5', 'leader_area_name', '团长小区', '', '0', '1', '1561971384', '1561971384', null);
INSERT INTO `system_express_keyword` VALUES ('39', '5', 'leader_city', '团长城市', '', '0', '1', '1561971542', '1561971542', null);
INSERT INTO `system_express_keyword` VALUES ('40', '5', 'express_type', '配送方式', '', '0', '1', '1561971643', '1561973531', null);
INSERT INTO `system_express_keyword` VALUES ('41', '5', 'route', '路线', '', '0', '1', '1561971683', '1561971683', null);
INSERT INTO `system_express_keyword` VALUES ('42', '1', 'label', '标签', '', '0', '1', '1561972044', '1561972044', null);
INSERT INTO `system_express_keyword` VALUES ('43', '1', 'short_name', '短标题', '', '0', '1', '1561972069', '1561972069', null);
INSERT INTO `system_express_keyword` VALUES ('44', '1', 'property', '规格', '', '0', '1', '1561972169', '1561972169', null);
INSERT INTO `system_express_keyword` VALUES ('45', '1', 'goods_id', '商品ID', '', '0', '1', '1561972220', '1561972220', null);
INSERT INTO `system_express_keyword` VALUES ('46', '1', 'goods_code', '货号', '', '0', '1', '1561972338', '1561972338', null);
INSERT INTO `system_express_keyword` VALUES ('47', '3', 'widget_name', '小程序名称', '', '0', '1', '1561973048', '1561973048', null);
INSERT INTO `system_express_keyword` VALUES ('48', '3', 'merchant_addr', '商家地址', '', '0', '1', '1561973085', '1561973085', null);
INSERT INTO `system_express_keyword` VALUES ('49', '3', '发件人', 'sender', '', '0', '1', '1561973100', '1561973100', '1561969371');
INSERT INTO `system_express_keyword` VALUES ('50', '3', 'logo', 'LOGO', '', '0', '1', '1561973121', '1561973121', '1562228155');
INSERT INTO `system_express_keyword` VALUES ('51', '3', 'widget_code', '小程序码', '', '0', '1', '1561973139', '1561973139', '1562228119');
INSERT INTO `system_express_keyword` VALUES ('52', '3', 'merchant_remark', '商家备注', '', '0', '1', '1561973152', '1561973152', '1561969371');
INSERT INTO `system_express_keyword` VALUES ('53', '3', 'buyer_nickname', '买家昵称', '', '0', '1', '1561973164', '1561973164', null);
INSERT INTO `system_express_keyword` VALUES ('54', '3', 'payment_money', '实付金额', '', '0', '1', '1561973176', '1561973176', null);
INSERT INTO `system_express_keyword` VALUES ('55', '3', 'pay_time', '付款时间', '', '0', '1', '1561973189', '1561973189', null);
INSERT INTO `system_express_keyword` VALUES ('56', '3', 'remark', '买家留言', '', '0', '1', '1561973202', '1561973202', null);
INSERT INTO `system_express_keyword` VALUES ('57', '3', 'buyer_city', '买家城市', '', '0', '1', '1561973231', '1561973231', null);
INSERT INTO `system_express_keyword` VALUES ('58', '3', 'buyer_area', '买家区域', '', '0', '1', '1561973315', '1561973315', null);
INSERT INTO `system_express_keyword` VALUES ('59', '3', 'leader_nickname', '团长昵称', '', '0', '1', '1561973333', '1561973333', null);
INSERT INTO `system_express_keyword` VALUES ('60', '3', 'leader_phone', '团长电话', '', '0', '1', '1561973347', '1561973347', null);
INSERT INTO `system_express_keyword` VALUES ('61', '3', 'leader_uid', '团长ID', '', '0', '1', '1561973357', '1561973357', null);
INSERT INTO `system_express_keyword` VALUES ('62', '3', 'leader_area_name', '团长小区', '', '0', '1', '1561973381', '1561973381', null);
INSERT INTO `system_express_keyword` VALUES ('63', '3', 'leader_city', '团长城市', '', '0', '1', '1561973395', '1561973395', null);
INSERT INTO `system_express_keyword` VALUES ('64', '3', 'express_type', '配送方式', '', '0', '1', '1561973424', '1561973424', null);
INSERT INTO `system_express_keyword` VALUES ('65', '3', 'route', '路线', '', '0', '1', '1561973444', '1561973444', null);
INSERT INTO `system_express_keyword` VALUES ('66', '3', 'property', '规格', '', '0', '1', '1561973462', '1561973462', null);
INSERT INTO `system_express_keyword` VALUES ('67', '3', 'goods_id', '商品ID', '', '0', '1', '1561973475', '1561973475', null);
INSERT INTO `system_express_keyword` VALUES ('68', '3', 'goods_code', '货号', '', '0', '1', '1561973486', '1561973486', null);
INSERT INTO `system_express_keyword` VALUES ('69', '5', 'leader_addr', '取货点', '', '0', '1', '1562057673', '1562057673', null);
INSERT INTO `system_express_keyword` VALUES ('70', '2', 'merchant_name', '商家姓名', '', '0', '1', '1562058122', '1562058122', null);
INSERT INTO `system_express_keyword` VALUES ('71', '3', 'leader_addr', '取货点', '', '0', '1', '1562234255', '1562234255', null);

-- ----------------------------
-- Table structure for `system_express_keyword_category`
-- ----------------------------
DROP TABLE IF EXISTS `system_express_keyword_category`;
CREATE TABLE `system_express_keyword_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(32) NOT NULL COMMENT '类目名称',
  `english_name` varchar(32) NOT NULL DEFAULT '' COMMENT '英文名字',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=普通字段 1=表格 2=图片',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1=启用 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='快递模板分组表';

-- ----------------------------
-- Records of system_express_keyword_category
-- ----------------------------
INSERT INTO `system_express_keyword_category` VALUES ('1', '商品信息', 'commodity_info', '0', '0', '1', '1561015903', '1561968892', null);
INSERT INTO `system_express_keyword_category` VALUES ('2', '商家信息', 'business_info', '0', '0', '1', '1561015966', '1561968887', null);
INSERT INTO `system_express_keyword_category` VALUES ('3', '表格信息', 'table_info', '1', '0', '1', '1561016004', '1561016004', null);
INSERT INTO `system_express_keyword_category` VALUES ('4', '买家信息', 'buyer_info', '0', '0', '1', '1561016443', '1561969672', null);
INSERT INTO `system_express_keyword_category` VALUES ('5', '团长信息', 'leader_info', '0', '0', '1', '1561971043', '1561971043', null);
INSERT INTO `system_express_keyword_category` VALUES ('6', '图片信息', 'pic_info', '2', '0', '1', '1562228070', '1562228070', null);

-- ----------------------------
-- Table structure for `system_help`
-- ----------------------------
DROP TABLE IF EXISTS `system_help`;
CREATE TABLE `system_help` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT '应用id',
  `category_id` mediumint(8) NOT NULL COMMENT '分组id',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容(富文本)',
  `page_view` int(8) NOT NULL DEFAULT '0' COMMENT '访问量',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序权重值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='系统-帮助表';

-- ----------------------------
-- Records of system_help
-- ----------------------------
INSERT INTO `system_help` VALUES ('1', '0', '3', '注册认证小程序', '<p><span style=\"box-sizing: border-box; margin: 0px; padding: 0px; color: rgb(0, 0, 0); font-family: 微软雅黑; font-size: 10pt;\">公众号和小程序注册网址：&nbsp;<a class=\"imui-msg-link unknown-link J_link_6269472716007132205_bf950a2b85e1764de2187fc549275fd0\" href=\"https://mp.weixin.qq.com/\" target=\"_blank\" style=\"box-sizing: border-box; margin: 0px; padding: 0px; background: 0px 0px transparent; color: rgb(48, 137, 220); text-decoration-line: none; transition: all 0.3s ease 0s;\"><span class=\"link-sf\" style=\"box-sizing: border-box; margin: 0px 2px 0px 0px; padding: 0px; -webkit-font-smoothing: antialiased; -webkit-text-stroke-width: 0.1px; font-family: IMUIIcon !important;\"></span>https://mp.weixin.qq.com/</a></span></p><p>一般建议先注册服务号，再使用服务号快速生成小程序，可以省300元钱</p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><span style=\"box-sizing: border-box; margin: 0px; padding: 0px; font-family: &quot;microsoft yahei&quot;; font-size: 12px; background-color: rgb(251, 251, 251);\">&nbsp;1，打开网址，立即注册</span></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/common/ueditor/admin/image/20190601/1559370326735615.jpg\" title=\"1559370326735615.jpg\" alt=\"1.jpg\" width=\"590\" height=\"270\"/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">2，选择注册的类型</p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/common/ueditor/admin/image/20190601/1559370333916916.png\" title=\"1559370333916916.png\" alt=\"2.png\" width=\"595\" height=\"407\"/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">3，设置登陆邮箱和密码</p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/common/ueditor/admin/image/20190601/1559370344290158.png\" title=\"1559370344290158.png\" alt=\"3.png\" width=\"589\" height=\"467\"/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">4，选择地区，登记信息等<br/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/common/ueditor/admin/image/20190601/1559370388125742.png\" title=\"1559370388125742.png\" alt=\"4.png\" width=\"595\" height=\"349\"/><img src=\"/public/common/ueditor/admin/image/20190601/1559370394955846.png\" title=\"1559370394955846.png\" alt=\"5.png\" width=\"589\" height=\"348\"/><img src=\"/public/common/ueditor/admin/image/20190601/1559370399991619.png\" title=\"1559370399991619.png\" alt=\"6.png\" width=\"592\" height=\"394\"/><img src=\"/public/common/ueditor/admin/image/20190601/1559370405761328.png\" title=\"1559370405761328.png\" alt=\"7.png\" width=\"592\" height=\"556\"/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">5，注册好后，公众号或者小程序都需要认证（腾讯会收取300元的认证费）</p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/common/ueditor/admin/image/20190601/1559370438302791.png\" title=\"1559370438302791.png\" alt=\"8.png\" width=\"585\" height=\"478\"/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">6，需要下载打印公函，盖章上传</p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\"><img src=\"/public/common/ueditor/admin/image/20190601/1559370448672445.png\" title=\"1559370448672445.png\" alt=\"9.png\" width=\"592\" height=\"164\"/></p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">按照要求上传公函，营业执照等信息，最后付款300元，需要发票可以填写收货地址</p><p style=\"box-sizing: border-box; margin-top: 0px; margin-bottom: 0px; padding: 10px 0px 3px; color: rgb(51, 51, 51); font-family: 微软雅黑; white-space: normal; background-color: rgb(255, 255, 255);\">正常情况下第二天认证人员就会联系你确认信息，期间会向你的公司帐户打入几分钱，你需要告诉他，就完成认证了</p><p><br/></p>', '0', '0', '1', '1551076079', '1566542667', null);
INSERT INTO `system_help` VALUES ('2', '0', '2', '测试1', '<p><img src=\"/ueditor/admin/image/20190225/1551083341.png\" title=\"1551083341.png\" alt=\"审核.png\"/></p>', '0', '2', '1', '1551076258', '1551083344', '1551404809');
INSERT INTO `system_help` VALUES ('3', '0', '1', '测试2', '<p><img src=\"http://api.map.baidu.com/staticimage?center=119.223436,34.61995&zoom=17&width=530&height=340&markers=119.223436,34.61995\" width=\"530\" height=\"340\"/></p>', '0', '3', '1', '1551076327', '1551085395', '1551404806');
INSERT INTO `system_help` VALUES ('4', '0', '3', '123', '<p><img src=\"/ueditor/admin/image/20190225/1551084840.png\" title=\"1551084840.png\" alt=\"文具.png\"/></p>', '0', '4', '1', '1551082633', '1551084842', '1551085345');
INSERT INTO `system_help` VALUES ('5', '0', '2', '帐号管理', '<p><a href=\"http://shipin.xiguaje.com/86a75734d3be45b385afb53e6eb0d9b4/80639059cdd74a16a049a6f2000e7974-3f3e633d6e479eb7b437b24b910a1f15-fd.mp4\" target=\"_blank\">http://shipin.xiguaje.com/86a75734d3be45b385afb53e6eb0d9b4/80639059cdd74a16a049a6f2000e7974-3f3e633d6e479eb7b437b24b910a1f15-fd.mp4</a></p><p><img src=\"http://img.baidu.com/hi/jx2/j_0001.gif\"/>哈哈哈哈哈哈</p><p><img src=\"/public/common/ueditor/admin/image/20190522/1558506992271077.png\" title=\"1558506992271077.png\" alt=\"水果.png\"/></p><p><img src=\"/public/common/ueditor/admin/image/20190522/1558507035124449.png\" title=\"1558507035124449.png\" alt=\"上传.png\"/></p>', '0', '3', '1', '1553331635', '1558507036', '1558603688');
INSERT INTO `system_help` VALUES ('6', '0', '3', '5.添加服务器域名', '<p><span style=\"font-size: 24px;\">登陆小程序公众号平台，网址：<a href=\"https://mp.weixin.qq.com/\">https://mp.weixin.qq.com</a></span><br/></p><p>左侧下面的：开发--开发设置--服务器域名</p><p><span style=\"color: rgb(255, 0, 0);\">修改</span></p><p>把：api.juanpao.com 和 imgs.juanpao.com</p><p>都添加进去，每条都加</p><p><img src=\"/public/common/ueditor/admin/image/20190514/1557814648938305.png\" title=\"1557814648938305.png\" alt=\"添加服务器域名.png\"/><img src=\"/public/common/ueditor/admin/image/20190514/1557814652510490.png\" title=\"1557814652510490.png\" alt=\"添加服务器域名2.png\"/><img src=\"/public/common/ueditor/admin/image/20190514/1557814654785717.png\" title=\"1557814654785717.png\" alt=\"添加服务器域名3.png\"/></p>', '0', '5', '1', '1557815066', '1566542988', null);
INSERT INTO `system_help` VALUES ('7', '0', '4', '添加商品分类', '<p>&nbsp; &nbsp;商品分类必须要有二级分类，二级分类的海报图不显示在前端，只有一级分类的海报是显示在前端分类页的<br/></p><p>商品-商品分类-新增</p><p>如果不选择上级分类，添加的就是一级分类</p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560931548564465.png\" title=\"1560931548564465.png\" alt=\"1.png\"/></p>', '0', '1', '1', '1560931605', '1560932522', null);
INSERT INTO `system_help` VALUES ('8', '0', '1', '添加区域分组', '<p>商品--区域分组--新增</p><p>区域分组：当商品选择了区域分组，那这个商品只能在这个地区显示</p><p>如需设置分地区销售，要先添加区域分组，再编辑商品，选择区域分组</p><p><br/></p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560931753151220.png\" title=\"1560931753151220.png\" alt=\"2.png\"/></p>', '0', '2', '1', '1560931829', null, null);
INSERT INTO `system_help` VALUES ('9', '0', '4', '添加商品', '<p>商品--商品列表-新增</p><p>按照页面要求填写标题，上传主图，编辑详情</p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560931998527809.png\" title=\"1560931998527809.png\" alt=\"3.png\"/></p>', '0', '3', '1', '1560932001', '1560932550', null);
INSERT INTO `system_help` VALUES ('10', '0', '4', '添加商品规格', '<p>添加商品规格，先填写名称，如：颜色，值：红色</p><p>点击添加，下面就显示了红色，点击红色，再点击生成，再设置价格和库存</p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560932155723441.png\" title=\"1560932155723441.png\" alt=\"4.png\"/></p>', '0', '4', '1', '1560932312', '1560932560', null);
INSERT INTO `system_help` VALUES ('11', '0', '3', '1.小程序授权', '<p>小程序--基本配置--点击授权</p><p>然后管理员扫码确认授权</p><p><br/></p><p>微信支付的商户号和密钥，需要手工填写，</p><p>如果不会可以联系我们客服人员协助你哦！</p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560933985578322.png\" title=\"1560933985578322.png\" alt=\"5.png\"/><img src=\"/public/common/ueditor/admin/image/20190619/1560934441421138.png\" title=\"1560934441421138.png\" alt=\"6.png\"/></p><p><br/></p>', '0', '1', '1', '1560934145', '1566542689', null);
INSERT INTO `system_help` VALUES ('12', '0', '3', '6.小程序上传，审核，发布', '<p style=\"white-space: normal;\">小程序--<span style=\"background-color: rgb(255, 255, 255); color: rgb(255, 0, 0);\">上传发布</span></p><p style=\"white-space: normal;\">先点击上传，填写版本说明，上传后，点击底部：点击获取，生成二维码，管理员扫码体验</p><p style=\"white-space: normal;\">体验无误，点击：提交审核，期间等待微信审核人员审核结果，一般1天左右，通过后会收到微信系统信息，然后再次登陆我们的系统后台点击发布，就可以看到小程序线上正式版本了</p><p style=\"white-space: normal;\"><br/></p><p style=\"white-space: normal;\"><strong><span style=\"color: rgb(255, 0, 0);\">注：首次提交审核，务必上传1-2个商品，不能是测试商品，不然无法通过审核</span></strong></p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560934859696397.png\" title=\"1560934859696397.png\" alt=\"7.png\"/></p>', '0', '6', '1', '1560934862', '1566543101', null);
INSERT INTO `system_help` VALUES ('13', '0', '3', '底部菜单和主题颜色', '<p>小程序--主题配色</p><p>应用创建好会有默认菜单，</p><p>进入主题配色，顶部文字颜色一般是白色</p><p>选择喜欢的颜色，如我们系统里没有的，你也可以提交喜欢的色号，我们添加进去</p><p>设置底部菜单名称，图标，文字颜色，链接位置，保存<br/></p><p><img src=\"/public/common/ueditor/admin/image/20190619/1560935143124995.png\" title=\"1560935143124995.png\" alt=\"8.png\"/></p>', '0', '7', '1', '1560935146', '1560935178', '1566542892');
INSERT INTO `system_help` VALUES ('14', '0', '1', '系统功能表', '<p><img src=\"/public/common/ueditor/admin/image/20191219/1576752179243879.jpg\" title=\"1576752179243879.jpg\" alt=\"price3.jpg\"/></p>', '0', '1', '1', '1563582046', '1576752181', null);
INSERT INTO `system_help` VALUES ('15', '0', '1', '佣金设置', '<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">佣金设置规则<br/></span></p><p style=\"text-align: center;\"><span style=\"font-family: 黑体, SimHei;\"><strong><span style=\"font-size: 24px; color: rgb(255, 0, 0);\">商品总佣金<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong>=团长佣金<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong>+自提点佣金<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong></span></strong></span></p><p><strong><span style=\"font-size: 24px; color: rgb(255, 0, 0);\"><strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">团长：负责推广，团长和会员是永久绑定关系，推广后会员只要购物，团长都享有佣金。</strong></span></strong></p><p><strong><span style=\"font-size: 24px; color: rgb(255, 0, 0);\"><strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">自提点：处理货物的地点，用户去这个自提点，他就有自提点佣金。</strong></span></strong></p><p><strong><span style=\"font-size: 24px; color: rgb(255, 0, 0);\"><br/></span></strong></p><p><strong><span style=\"font-size: 24px; color: rgb(255, 0, 0);\">比如准备设置商品的佣金或者全店佣金是10<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong>，</span></strong></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong>那就是：10%=<span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\">团长佣金5<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong>+自提点佣金5<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong></span></strong></span></strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\">具体多少要看对团长的重视度是多高</span></strong></span></strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\">我们的建议是：<span style=\"color: rgb(12, 12, 12); font-size: 24px; font-family: 黑体, SimHei;\">自提点7<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong>，团长3<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong></span></span></strong></span></strong></span></span></p><p style=\"text-align: left;\"><span style=\"color:#0c0c0c;font-family:黑体, SimHei\"><span style=\"font-size: 24px;\"><strong>如：商品价格100元，自提点得到7元，团长得到3元</strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\"><span style=\"color: rgb(12, 12, 12); font-size: 24px; font-family: 黑体, SimHei;\"><br/></span></span></strong></span></strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\">如果是采用老模式：每个小区一个团长一个自提点</span></strong></span></strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\">建议自提点9<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong>，团长1<strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">%</strong></span></strong></span></strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\"><strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\"><br/></strong></span></strong></span></strong></span></span></p><p><span style=\"color:#ff0000\"><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong><span style=\"font-size: 24px; color: rgb(12, 12, 12);\"><strong style=\"text-align: center; white-space: normal;\"><span style=\"font-size: 24px;\"><strong style=\"color: rgb(12, 12, 12); font-size: 24px; white-space: normal;\">如团长或者自提点不做了，后台可以【解绑会员】</strong></span></strong></span></strong></span></span></p>', '0', '1', '1', '1565246470', '1566866053', null);
INSERT INTO `system_help` VALUES ('16', '0', '1', '素材下载', '<p>整理的部分素材，可以下载使用</p><p>链接：<a href=\"https://pan.baidu.com/s/12eSow99hTGbpi8ibizxTnA\" target=\"_self\">https://pan.baidu.com/s/12eSow99hTGbpi8ibizxTnA</a>&nbsp;</p><p>提取码：sg4a&nbsp;</p><p><br/></p>', '0', '10', '1', '1565325845', '1565325882', null);
INSERT INTO `system_help` VALUES ('17', '0', '3', '2.装修首页', '<p>设置--店铺装修，选择系统模版，选用</p><p>我的模版-启用，编辑，保存</p>', '0', '2', '1', '1566542443', '1566542703', null);
INSERT INTO `system_help` VALUES ('18', '0', '3', '3.设置主题配色', '<p>小程序-主题配色</p><p>首次进入，务必刷新一下，以获取系统默认配置，然后再编辑</p>', '0', '4', '1', '1566542621', '1566542709', null);
INSERT INTO `system_help` VALUES ('19', '0', '1', '小程序页面链接', '<p>pages/index/index/index</p><p>首页</p><p><br/></p><p>pages/goodsItem/goodsItem/goodsItem?id=1</p><p>商品详情（数字改为商品ID编号）</p><p><br/></p><p>pages/classification/classification/classification</p><p>商品分类</p><p><br/></p><p>pages/home/my/my</p><p>个人中心</p><p><br/></p><p>pages/home/address/address</p><p>收货地址</p><p><br/></p><p>pages/shopCart/shopCart/shopCart</p><p>购物车</p><p><br/></p><p>pages/goodsClassify/goodsClassify/goodsClassify?id=1&amp;name=分类名</p><p>商品分类（数字改为商品ID编号，分类名称改为具体名称）</p><p><br/></p><p>pages/order/order/order</p><p>订单列表</p><p><br/></p><p>pages/home/coupons/coupons/coupons</p><p>我的优惠券</p><p><br/></p>', '0', '333', '1', '1572592423', null, null);

-- ----------------------------
-- Table structure for `system_help_category`
-- ----------------------------
DROP TABLE IF EXISTS `system_help_category`;
CREATE TABLE `system_help_category` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT '应用id',
  `name` varchar(50) NOT NULL COMMENT '分组名称',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '分组排序权重值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='系统-帮助分组表';

-- ----------------------------
-- Records of system_help_category
-- ----------------------------
INSERT INTO `system_help_category` VALUES ('1', '0', '基础教程', '1', '1', '1551059681', '1551404800', null);
INSERT INTO `system_help_category` VALUES ('2', '0', '账号管理', '2', '0', '1551059727', '1560933855', '1560933874');
INSERT INTO `system_help_category` VALUES ('3', '0', '小程序首次上线', '0', '1', '1551059750', '1566542143', null);
INSERT INTO `system_help_category` VALUES ('4', '0', '商品管理', '4', '1', '1551059792', null, null);

-- ----------------------------
-- Table structure for `system_log`
-- ----------------------------
DROP TABLE IF EXISTS `system_log`;
CREATE TABLE `system_log` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `merchant_id` int(11) NOT NULL COMMENT '商户id，0为系统',
  `sub_id` int(11) DEFAULT '0' COMMENT '子账号id，0为非子账号',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符，system为系统',
  `user_id` int(11) NOT NULL COMMENT '用户id，0为访客',
  `condition` char(100) NOT NULL COMMENT '触发条件',
  `title` varchar(32) DEFAULT NULL COMMENT '标题',
  `remarks` varchar(256) DEFAULT NULL COMMENT '备注',
  `ip` char(32) NOT NULL COMMENT '访问者ip',
  `user_agent` varchar(256) NOT NULL DEFAULT '' COMMENT '浏览器标识',
  `request` text NOT NULL COMMENT '请求信息',
  `response` text COMMENT '响应信息',
  `record_id` int(10) NOT NULL COMMENT '访问id，例:order_id,pay_id,good_id',
  `type` tinyint(1) NOT NULL COMMENT '类型 1前端 2=后台',
  `status` tinyint(1) NOT NULL COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统日志表';

-- ----------------------------
-- Records of system_log
-- ----------------------------

-- ----------------------------
-- Table structure for `system_menu`
-- ----------------------------
DROP TABLE IF EXISTS `system_menu`;
CREATE TABLE `system_menu` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(6) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `url` varchar(256) DEFAULT NULL COMMENT '菜单链接',
  `pid` int(32) NOT NULL DEFAULT '0' COMMENT '父菜单',
  `sort` varchar(20) NOT NULL COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COMMENT='菜单表';

-- ----------------------------
-- Records of system_menu
-- ----------------------------
INSERT INTO `system_menu` VALUES ('1', '总览', '', '0', '1000', '1', '1576739245', '1576742527', null);
INSERT INTO `system_menu` VALUES ('2', '商品', '', '0', '1100', '1', '1576740464', '1576742543', null);
INSERT INTO `system_menu` VALUES ('3', '订单', '', '0', '1200', '1', '1576740690', '1576742551', null);
INSERT INTO `system_menu` VALUES ('4', '会员', '', '0', '1300', '1', '1576740703', '1576742559', null);
INSERT INTO `system_menu` VALUES ('5', '团长', '', '0', '1400', '1', '1576740712', '1576742568', null);
INSERT INTO `system_menu` VALUES ('6', '营销', '', '0', '1500', '1', '1576740724', '1576742577', null);
INSERT INTO `system_menu` VALUES ('7', '小程序', '', '0', '1600', '1', '1576740736', '1576742586', null);
INSERT INTO `system_menu` VALUES ('8', '设置', '', '0', '1700', '1', '1576740745', '1576742606', null);
INSERT INTO `system_menu` VALUES ('9', '员工管理', '', '0', '1800', '1', '1576740760', '1576742694', null);
INSERT INTO `system_menu` VALUES ('10', '财务', '', '0', '1900', '1', '1576740770', '1576742704', null);
INSERT INTO `system_menu` VALUES ('11', '积分商城', '', '0', '2000', '1', '1576740785', '1576742720', null);
INSERT INTO `system_menu` VALUES ('12', '门店', '', '0', '2100', '1', '1576740820', '1576742734', null);
INSERT INTO `system_menu` VALUES ('13', '常见问题', '', '0', '2200', '1', '1576740833', '1576742741', null);
INSERT INTO `system_menu` VALUES ('14', '合伙人', '', '0', '2300', '1', '1576740838', '1576742750', null);
INSERT INTO `system_menu` VALUES ('15', '操作记录', '', '0', '2400', '1', '1576740902', '1576742867', null);
INSERT INTO `system_menu` VALUES ('16', '数据总览', 'overview/index', '1', '1001', '1', '1576741580', '1576742886', null);
INSERT INTO `system_menu` VALUES ('17', '升级日志', 'overview/log', '1', '1002', '1', '1576741903', '1576742894', null);
INSERT INTO `system_menu` VALUES ('18', '商品列表', 'goods/list', '2', '1101', '1', '1576742024', '1576742907', null);
INSERT INTO `system_menu` VALUES ('19', '商品分组', 'goods/group', '2', '1102', '1', '1576742052', '1576742914', null);
INSERT INTO `system_menu` VALUES ('20', '区域分组', 'goods/areaGroup', '2', '1103', '1', '1576742069', '1576742921', null);
INSERT INTO `system_menu` VALUES ('21', '回收站', 'goods/recycleBin', '2', '1104', '1', '1576742180', '1576742929', null);
INSERT INTO `system_menu` VALUES ('22', '订单管理', 'order/list', '3', '1201', '1', '1576742479', '1576742937', null);
INSERT INTO `system_menu` VALUES ('23', '订单概述', 'order/summary', '3', '1202', '1', '1576743314', '1576743314', null);
INSERT INTO `system_menu` VALUES ('24', '评价管理', 'order/evaluate', '3', '1203', '1', '1576745025', '1576745025', null);
INSERT INTO `system_menu` VALUES ('25', '订单配送', 'go_to_print', '3', '1204', '1', '1576745580', '1576745580', null);
INSERT INTO `system_menu` VALUES ('26', '会员列表', 'user/list', '4', '1301', '1', '1576745918', '1576745918', null);
INSERT INTO `system_menu` VALUES ('27', '会员卡（付费', 'voucher/vips', '4', '1302', '1', '1576745963', '1576745963', null);
INSERT INTO `system_menu` VALUES ('28', '会员卡（积分', 'voucher/vipsUnpaid', '4', '1303', '1', '1576745992', '1576745992', null);
INSERT INTO `system_menu` VALUES ('29', '团长', 'customers/selfRaisingPoint', '5', '1401', '1', '1576746168', '1576746168', null);
INSERT INTO `system_menu` VALUES ('30', '团长审核', 'customers/groupExamineList', '5', '1402', '1', '1576746191', '1576746191', null);
INSERT INTO `system_menu` VALUES ('31', '团长等级', 'customers/level', '5', '1403', '1', '1576746223', '1576746223', null);
INSERT INTO `system_menu` VALUES ('32', '推客', 'customers/groupList', '5', '1404', '1', '1576746244', '1576746244', null);
INSERT INTO `system_menu` VALUES ('33', '推客审核', 'customers/pusherExamineList', '5', '1405', '1', '1576746514', '1576746514', null);
INSERT INTO `system_menu` VALUES ('34', '推客等级', 'customers/pusherLevel', '5', '1406', '1', '1576746539', '1576746539', null);
INSERT INTO `system_menu` VALUES ('35', '路线', 'customers/warehouse', '5', '1407', '1', '1576746560', '1576746560', null);
INSERT INTO `system_menu` VALUES ('36', '优惠券', 'voucher/type', '6', '1501', '1', '1576746675', '1576746675', null);
INSERT INTO `system_menu` VALUES ('37', '秒杀', 'voucher/flash', '6', '1502', '1', '1576746718', '1576746718', null);
INSERT INTO `system_menu` VALUES ('38', '签到', 'voucher/signIn', '6', '1503', '1', '1576747517', '1576747517', null);
INSERT INTO `system_menu` VALUES ('39', '模板信息', 'voucher/miniTemplate', '6', '1504', '1', '1576747554', '1576747554', null);
INSERT INTO `system_menu` VALUES ('40', '拼团', 'voucher/assemble', '6', '1505', '1', '1576747577', '1576747577', null);
INSERT INTO `system_menu` VALUES ('41', '充值', 'voucher/pay', '6', '1506', '1', '1576747597', '1576747597', null);
INSERT INTO `system_menu` VALUES ('42', '砍价', 'voucher/bargain', '6', '1507', '1', '1576747619', '1576747619', null);
INSERT INTO `system_menu` VALUES ('43', '满减', 'voucher/reduction', '6', '1508', '1', '1576747634', '1576747634', null);
INSERT INTO `system_menu` VALUES ('44', '新人专享', 'voucher/recruits', '6', '1509', '1', '1576747668', '1576747668', null);
INSERT INTO `system_menu` VALUES ('45', '自定义版权', 'voucher/copyright', '6', '1510', '1', '1576747689', '1576747689', null);
INSERT INTO `system_menu` VALUES ('46', '基本配置', 'miniProgram/base', '7', '1601', '1', '1576747721', '1576747721', null);
INSERT INTO `system_menu` VALUES ('47', '上传发布', 'miniProgram/formal', '7', '1602', '1', '1576747748', '1576747748', null);
INSERT INTO `system_menu` VALUES ('48', '主题配色', 'miniProgram/theme', '7', '1603', '1', '1576747766', '1576747766', null);
INSERT INTO `system_menu` VALUES ('49', '运费模板', 'logistics/express', '8', '1701', '1', '1576747851', '1576747851', null);
INSERT INTO `system_menu` VALUES ('50', '收货信息', 'info/list', '8', '1702', '1', '1576747871', '1576747871', null);
INSERT INTO `system_menu` VALUES ('51', '基本设置', 'appSet/info', '8', '1703', '1', '1576747888', '1576747888', null);
INSERT INTO `system_menu` VALUES ('52', '团购配置', 'customers/groupConfig', '8', '1704', '1', '1576747916', '1576747916', null);
INSERT INTO `system_menu` VALUES ('53', '电子面单', 'electronics', '8', '1705', '1', '1576747938', '1576747938', null);
INSERT INTO `system_menu` VALUES ('54', '店铺装修', 'decoration', '8', '1706', '1', '1576747963', '1576747963', null);
INSERT INTO `system_menu` VALUES ('55', '页面配置', 'sysConfig', '8', '1707', '1', '1576748032', '1576748032', null);
INSERT INTO `system_menu` VALUES ('56', '闪送', 'appSet/shansong', '8', '1708', '1', '1576748056', '1576748056', null);
INSERT INTO `system_menu` VALUES ('57', 'UU跑腿', 'appSet/uu', '8', '1709', '1', '1576748076', '1576748076', null);
INSERT INTO `system_menu` VALUES ('58', '易联云', 'appSet/yly', '8', '1710', '1', '1576748096', '1576748096', null);
INSERT INTO `system_menu` VALUES ('59', '分享海报', 'appSet/posters', '8', '1711', '1', '1576748121', '1576751441', null);
INSERT INTO `system_menu` VALUES ('60', '员工管理', 'staff/list', '9', '1801', '1', '1576748152', '1576748152', null);
INSERT INTO `system_menu` VALUES ('61', '角色管理', 'staff/group', '9', '1802', '1', '1576748176', '1576748176', null);
INSERT INTO `system_menu` VALUES ('62', '客服管理', 'staff/customerService', '9', '1803', '1', '1576748197', '1576748197', null);
INSERT INTO `system_menu` VALUES ('63', '佣金流水', 'staff/customerService', '10', '1901', '1', '1576748223', '1576748223', null);
INSERT INTO `system_menu` VALUES ('64', '佣金提现申请', 'finance/cashWithdrawal', '10', '1902', '1', '1576748244', '1576748244', null);
INSERT INTO `system_menu` VALUES ('65', '积分商品分组', 'score/group', '11', '2001', '1', '1576748386', '1576748386', null);
INSERT INTO `system_menu` VALUES ('66', '积分商品列表', 'score/list', '11', '2002', '1', '1576748410', '1576748410', null);
INSERT INTO `system_menu` VALUES ('67', '积分订单', 'score/order', '11', '2003', '1', '1576748435', '1576748435', null);
INSERT INTO `system_menu` VALUES ('68', '轮播图', 'score/banner', '11', '2004', '1', '1576750341', '1576750341', null);
INSERT INTO `system_menu` VALUES ('69', '申请列表', 'supplier/apply', '12', '2101', '1', '1576750371', '1576750371', null);
INSERT INTO `system_menu` VALUES ('70', '门店', 'supplier/list', '12', '2102', '1', '1576750400', '1576750400', null);
INSERT INTO `system_menu` VALUES ('71', '商品', 'supplier/goods', '12', '2103', '1', '1576750418', '1576750418', null);
INSERT INTO `system_menu` VALUES ('72', '订单', 'supplier/order', '12', '2104', '1', '1576750439', '1576750439', null);
INSERT INTO `system_menu` VALUES ('73', '提现', 'supplier/withdrawal', '12', '2105', '1', '1576750500', '1576750500', null);
INSERT INTO `system_menu` VALUES ('74', '常见问题分组', 'help/group', '13', '2201', '1', '1576750550', '1576750550', null);
INSERT INTO `system_menu` VALUES ('75', '常见问题列表', 'help/list', '13', '2202', '1', '1576750574', '1576750574', null);
INSERT INTO `system_menu` VALUES ('76', '设置', 'partner/set', '14', '2301', '1', '1576750602', '1576750602', null);
INSERT INTO `system_menu` VALUES ('77', '合伙人列表', 'partner/list', '14', '2302', '1', '1576750618', '1576750618', null);
INSERT INTO `system_menu` VALUES ('78', '提现列表', 'partner/withdrawal', '14', '2303', '1', '1576750645', '1576750645', null);
INSERT INTO `system_menu` VALUES ('79', '操作记录', 'operationRecord/list', '15', '2401', '1', '1576750675', '1576750675', null);

-- ----------------------------
-- Table structure for `system_merchant_combo`
-- ----------------------------
DROP TABLE IF EXISTS `system_merchant_combo`;
CREATE TABLE `system_merchant_combo` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '套餐名称',
  `pic_url` varchar(255) NOT NULL COMMENT '套餐图片',
  `sms_number` int(11) NOT NULL DEFAULT '0' COMMENT '短信数量',
  `order_number` int(11) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `money` decimal(8,2) NOT NULL COMMENT '套餐金额',
  `validity_time` int(11) NOT NULL COMMENT '套餐有效期',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `type` tinyint(1) NOT NULL COMMENT '1=短信 2=订单 5=组合  9 平台赠送',
  `number` tinyint(3) NOT NULL DEFAULT '0' COMMENT '可购买次数 0=不限制',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='商户套餐表';

-- ----------------------------
-- Records of system_merchant_combo
-- ----------------------------
INSERT INTO `system_merchant_combo` VALUES ('1', '100元1000条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15573668195cd38823c7b39.png', '1000', '0', '100.00', '0', '1000', '1', '0', '1', '1557366819', '1557368685', '1558226227');
INSERT INTO `system_merchant_combo` VALUES ('2', '1000元10000条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15573673185cd38a16d0a3e.png', '10000', '0', '1000.00', '0', '10000', '1', '0', '1', '1557367318', '1557368487', '1558226230');
INSERT INTO `system_merchant_combo` VALUES ('3', '9', 'https://imgs.juanpao.com/admin%2Fvip%2F15584307745ce3c43662878.jpeg', '200', '1', '9.00', '12', '200', '1', '100', '1', '1557367536', '1559120438', '1559726068');
INSERT INTO `system_merchant_combo` VALUES ('4', '10元100条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15573676835cd38b83e8d16.png', '100', '0', '10.00', '0', '100', '1', '0', '1', '1557367683', '1557368589', '1558226352');
INSERT INTO `system_merchant_combo` VALUES ('5', '100元1000条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15573678875cd38c4f6f08f.png', '1000', '0', '100.00', '0', '1000', '1', '0', '1', '1557367887', '1557368601', '1558226349');
INSERT INTO `system_merchant_combo` VALUES ('6', '100元', 'https://imgs.juanpao.com/admin%2Fvip%2F15584285205ce3bb6828a1c.jpeg', '1', '1000', '100.00', '12', '1000', '2', '100', '1', '1557368026', '1559716400', '1559724858');
INSERT INTO `system_merchant_combo` VALUES ('7', '10元100条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15573682975cd38de9c8aa0.png', '100', '0', '10.00', '0', '100', '2', '0', '0', '1557368297', '1557368297', '1557368625');
INSERT INTO `system_merchant_combo` VALUES ('8', '10元100条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15573683265cd38e06a7bb6.png', '100', '0', '10.00', '0', '100', '2', '0', '1', '1557368326', '1557368326', '1557368355');
INSERT INTO `system_merchant_combo` VALUES ('9', '体验套餐', 'https://imgs.juanpao.com/admin%2Fvip%2F15584169955ce38e637f3d9.png', '1', '0', '0.01', '0', '1000', '2', '127', '1', '1557371745', '1558416995', '1558428475');
INSERT INTO `system_merchant_combo` VALUES ('10', '组合套餐1', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15578156215cda6145b107c.png', '1000', '1000', '150.00', '12', '150', '5', '0', '0', '1557815621', '1559715553', '1559716313');
INSERT INTO `system_merchant_combo` VALUES ('11', '体验套餐', 'https://imgs.juanpao.com/admin%2Fvip%2F15584284675ce3bb339a397.jpeg', '0', '100', '0.01', '12', '100', '2', '100', '1', '1558417051', '1559120411', '1559716278');
INSERT INTO `system_merchant_combo` VALUES ('12', '500元', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15584286055ce3bbbd9fbf3.jpeg', '0', '8000', '500.00', '24', '8000', '2', '100', '1', '1558428605', '1559716541', '1559724860');
INSERT INTO `system_merchant_combo` VALUES ('13', '平台赠送', '', '0', '0', '0.00', '12', '平台赠送', '9', '0', '0', '1559117373', '1559117373', null);
INSERT INTO `system_merchant_combo` VALUES ('14', '测试', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591826395cef3d2fbc5f9.png', '0', '1000', '50.00', '12', '1', '2', '0', '1', '1559182639', '1559182639', '1559183883');
INSERT INTO `system_merchant_combo` VALUES ('15', '测试1', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591826915cef3d63e1897.png', '100', '500', '30.00', '12', '1', '1', '0', '1', '1559182691', '1559183351', '1559183895');
INSERT INTO `system_merchant_combo` VALUES ('16', '测试2', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591835025cef408e5b666.png', '1000', '0', '50.00', '12', '1', '1', '0', '1', '1559183502', '1559183502', '1559183892');
INSERT INTO `system_merchant_combo` VALUES ('17', '测试3', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591838515cef41eba4bfe.png', '0', '1000', '50.00', '12', '1', '2', '0', '1', '1559183851', '1559183851', '1559183889');
INSERT INTO `system_merchant_combo` VALUES ('18', '测试4', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591838765cef4204edb2f.png', '1000', '1000', '100.00', '12', '2', '5', '0', '1', '1559183876', '1559183876', '1559183887');
INSERT INTO `system_merchant_combo` VALUES ('19', '测试1', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591840025cef4282e6c91.png', '1000', '0', '50.00', '12', '1', '1', '0', '1', '1559184002', '1559184002', '1559184308');
INSERT INTO `system_merchant_combo` VALUES ('20', '测试2', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591840215cef429555b30.png', '0', '1000', '50.00', '12', '1', '2', '0', '1', '1559184021', '1559184021', '1559184309');
INSERT INTO `system_merchant_combo` VALUES ('21', '测试3', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15591840415cef42a96aa2f.png', '1000', '1000', '80.00', '12', '1', '5', '0', '1', '1559184041', '1559184041', '1559184311');
INSERT INTO `system_merchant_combo` VALUES ('22', '1000', 'https://imgs.juanpao.com/admin%2Fvip%2F15597238195cf77f2b27dfe.png', '0', '20000', '1000.00', '24', '1000', '2', '0', '1', '1559723299', '1559723819', '1559724867');
INSERT INTO `system_merchant_combo` VALUES ('23', '私有化部署', 'https://imgs.juanpao.com/admin%2Fvip%2F15717375015daecf9d0bbcf.png', '0', '100', '6980.00', '12', '私有化部署', '2', '0', '1', '1559725061', '1571737500', null);
INSERT INTO `system_merchant_combo` VALUES ('24', '企业进阶版', 'https://imgs.juanpao.com/admin%2Fvip%2F15651627045d4a7cd05f4f4.png', '0', '10000000', '4980.00', '12', '企业不限版', '2', '0', '0', '1559725112', '1571727417', null);
INSERT INTO `system_merchant_combo` VALUES ('25', '企业版', 'https://imgs.juanpao.com/admin%2Fvip%2F15717275075daea893556be.png', '0', '50000000', '4980.00', '12', '企业版', '2', '0', '1', '1559725147', '1571727528', null);
INSERT INTO `system_merchant_combo` VALUES ('26', '全民创业版', 'https://imgs.juanpao.com/admin%2Fvip%2F15611620135d0d711db1eb6.png', '0', '10000', '498.00', '12', '全民创业版', '2', '0', '0', '1559725167', '1563514150', null);
INSERT INTO `system_merchant_combo` VALUES ('27', '短信12000条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15597260905cf7880ad287a.png', '12000', '0', '500.00', '24', '500', '1', '0', '0', '1559726090', '1565780509', null);
INSERT INTO `system_merchant_combo` VALUES ('28', '短信2000条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15597261425cf7883e66271.png', '2000', '0', '90.00', '24', '90', '1', '0', '0', '1559726142', '1565780508', null);
INSERT INTO `system_merchant_combo` VALUES ('29', '短信200条', 'https://imgs.juanpao.com/admin%2Fdecoration%2F15597261855cf788693217d.png', '200', '0', '9.00', '24', '9', '1', '0', '0', '1559726185', '1565780507', null);
INSERT INTO `system_merchant_combo` VALUES ('30', '年费版', 'https://imgs.juanpao.com/admin%2Fvip%2F15605616385d0447e61aa0c.png', '0', '10000000', '6800.00', '12', '年费版', '2', '0', '0', '1560561319', '1560817619', null);
INSERT INTO `system_merchant_combo` VALUES ('31', '首次购买赠送', 'https://imgs.juanpao.com/admin%2Fvip%2F15584284675ce3bb339a397.jpeg', '0', '100', '0.00', '12', '一年内100条', '9', '0', '1', '1560561319', '1560817619', null);

-- ----------------------------
-- Table structure for `system_merchant_combo_access`
-- ----------------------------
DROP TABLE IF EXISTS `system_merchant_combo_access`;
CREATE TABLE `system_merchant_combo_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `order_sn` varchar(24) DEFAULT '0' COMMENT '订单编码 可为0',
  `combo_id` tinyint(3) DEFAULT '0' COMMENT '套餐id 可为0',
  `uid` int(10) NOT NULL COMMENT '用户id',
  `order_number` int(10) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `order_remain_number` int(10) NOT NULL DEFAULT '0' COMMENT '订单剩余数量',
  `sms_number` int(10) NOT NULL DEFAULT '0' COMMENT '短信数量',
  `sms_remain_number` int(10) NOT NULL DEFAULT '0' COMMENT '短信剩余数量',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=短信  2=订单 5=组合',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注 例：创建订单使用',
  `validity_time` int(11) DEFAULT '0' COMMENT '有效期 当前时间戳+12个月',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1=成功 0=已创建 2=失败',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=651 DEFAULT CHARSET=utf8 COMMENT='商户套餐使用记录';

INSERT INTO `system_merchant_combo_access` VALUES ('179', 'ccvWPn', '13', 'combo_201907021451318482', '27', '0', '0', '0', '12000', '12000', '1', null, '1625155200', '1', '1562050290', '1562050311', null);
INSERT INTO `system_merchant_combo_access` VALUES ('188', 'ccvWPn', '13', 'combo_201907121849175145', '13', '0', '20', '0', '0', '0', '9', null, '1594483200', '0', '1562928557', '1563863810', null);
INSERT INTO `system_merchant_combo_access` VALUES ('192', 'ccvWPn', '13', 'combo_201907241322383107', '13', '0', '10', '0', '0', '0', '9', null, '1595520000', '0', '1563945758', '1563947910', null);
INSERT INTO `system_merchant_combo_access` VALUES ('210', 'ccvWPn', '13', 'combo_201907291419317121', '24', '0', '10000000', '10000000', '0', '0', '2', null, '12', '0', '1564381171', '1564381171', null);
INSERT INTO `system_merchant_combo_access` VALUES ('259', 'ccvWPn', '13', 'combo_201908051441547018', '31', '0', '100', '57', '0', '0', '9', null, '1596556800', '0', '1564987314', '1565161533', null);
INSERT INTO `system_merchant_combo_access` VALUES ('263', 'ccvWPn', '13', 'combo_201908071528299855', '31', '0', '5', '4', '0', '0', '9', null, '1596729600', '0', '1565162909', '1565162911', null);
INSERT INTO `system_merchant_combo_access` VALUES ('264', 'ccvWPn', '13', 'combo_201908071528527783', '31', '0', '5', '1', '0', '0', '9', null, '1596729600', '0', '1565162932', '1565165314', null);
INSERT INTO `system_merchant_combo_access` VALUES ('272', 'ccvWPn', '13', 'combo_201908081935486647', '25', '0', '50000', '49807', '0', '0', '2', '平台添加', '1596816000', '1', '1565264147', '1569200646', null);
INSERT INTO `system_merchant_combo_access` VALUES ('629', 'ccvWPn', '13', 'combo_201912031919394616', '9', '0', '0', '0', '0', '0', '2', '平台添加', '1577721600', '1', '1575371979', '1575371979', null);
INSERT INTO `system_merchant_combo_access` VALUES ('630', 'ccvWPn', '13', 'combo_201912031922506212', '9', '0', '0', '0', '0', '0', '2', '平台添加', '1577721600', '1', '1575372170', '1575372170', null);
INSERT INTO `system_merchant_combo_access` VALUES ('631', 'ccvWPn', '13', 'combo_201912031927465747', '25', '0', '50000000', '50000000', '0', '0', '2', '平台添加', '1577721600', '1', '1575372466', '1575372466', null);
INSERT INTO `system_merchant_combo_access` VALUES ('636', 'ccvWPn', '13', 'combo_201912041021516771', '25', '0', '50000000', '50000000', '0', '0', '2', '平台添加', '1577635200', '1', '1575426111', '1575426111', null);
INSERT INTO `system_merchant_combo_access` VALUES ('638', 'ccvWPn', '13', 'combo_201912041130346287', '25', '0', '50000000', '49999996', '0', '0', '2', '平台添加', '1577721600', '1', '1575430234', '1576808242', null);

-- ----------------------------
-- Table structure for `system_merchant_design`
-- ----------------------------
DROP TABLE IF EXISTS `system_merchant_design`;
CREATE TABLE `system_merchant_design` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `template_id` int(8) NOT NULL DEFAULT '0' COMMENT '模板id，可自设置时为0',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(32) NOT NULL COMMENT '自定义名称',
  `pic_url` varchar(256) NOT NULL DEFAULT '' COMMENT '模板图片',
  `info` longtext NOT NULL COMMENT '设计信息(json格式)',
  `is_wx_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否微信默认模板，0否 1是',
  `is_mini_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否小程序默认模板，0否 1是',
  `is_edit` tinyint(1) DEFAULT '0' COMMENT '是否编辑 0未编辑 1正在编辑',
  `is_enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用 0=未启用 1=已启用',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=启用 0=禁用',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `admin_remark` varchar(255) DEFAULT NULL COMMENT '管理员备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8 COMMENT='商户模板表';

-- ----------------------------
-- Records of system_merchant_design
-- ----------------------------
INSERT INTO `system_merchant_design` VALUES ('21', 'ccvWPn', '39', '13', '男装专卖', 'http://tuan.weikejs.com/api/web/./uploads/shop/banner/2020/02/21/15822197925e4ec210801f5.png', '[{\"type\":1,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F05%2F31%2F15592810425cf0bd92ab6eb.jpeg\",\"link\":\"link1\"}],\"dotShow\":true,\"color1\":\"#ff0000\",\"color2\":\"#fff\",\"boxHeight\":180},\"id\":0},{\"type\":22,\"edit\":false,\"details\":{\"text\":\"请输入\",\"color1\":\"#fff\",\"color2\":\"#fff\",\"color3\":\"#333\"},\"id\":1},{\"type\":3,\"edit\":false,\"details\":{\"col\":\"25%\",\"fontSize\":\"12px\",\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596399725cf637a483b17.png\",\"text\":\"母婴\",\"link\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596399835cf637af54a96.png\",\"link\":\"\",\"text\":\"家居\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596400035cf637c37cc58.png\",\"link\":\"\",\"text\":\"我的\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596399965cf637bc62549.png\",\"link\":\"\",\"text\":\"收藏\",\"title\":\"\"}],\"color1\":\"#333\",\"color2\":\"#fff\",\"radius\":100},\"id\":2},{\"type\":27,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F05%2F31%2F15592809955cf0bd6302e17.png\",\"link\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F05%2F31%2F15592809965cf0bd64ca835.png\",\"link\":\"\",\"text\":\"\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F05%2F31%2F15592809995cf0bd67303a4.png\",\"link\":\"\",\"text\":\"\",\"title\":\"\"}]},\"id\":3},{\"type\":7,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596411595cf63c4790f01.png\",\"link\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596411625cf63c4abd362.png\",\"link\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F04%2F15596411665cf63c4e5c600.jpeg\",\"link\":\"\"}]},\"id\":4},{\"type\":28,\"edit\":false,\"details\":{\"style\":\"1\"},\"id\":5}]', '0', '1', '1', '0', '1', null, null, '1559614408', '1582219792', null);
INSERT INTO `system_merchant_design` VALUES ('143', 'ccvWPn', '40', '13', '系统模版', 'http://tuan.weikejs.com/api/web/./uploads/shop/banner/2020/02/21/15822523845e4f4160417f7.png', '[{\"type\":13,\"edit\":false,\"details\":{\"style\":\"1\",\"color1\":\"#f5f5f5\",\"color2\":\"#fff\",\"boxHeight\":5,\"paddingTopBottom\":0},\"id\":0},{\"type\":1,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677557975d720e15ef894.png\",\"link\":\"/pages/group/groupbrochure/groupbrochure\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677579405d72167472d01.png\",\"link\":\"/pages/supplier/supplierbrochure/supplierbrochure\",\"text\":\"\",\"title\":\"\"}],\"dotShow\":true,\"color1\":\"#ff0000\",\"color2\":\"#fff\",\"boxHeight\":145,\"style\":\"1\"},\"id\":1},{\"type\":3,\"edit\":false,\"details\":{\"col\":\"25%\",\"fontSize\":\"12px\",\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677558415d720e41b2cf5.jpeg\",\"text\":\"甄选鲜果\",\"link\":\"/pages/goodsClassify/goodsClassify/goodsClassify?id=145&name=新鲜蔬菜\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677558395d720e3fc9e31.jpeg\",\"link\":\"/pages/goodsClassify/goodsClassify/goodsClassify?id=145&name=新鲜蔬菜\",\"text\":\"新鲜蔬菜\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677558575d720e51c1f4b.jpeg\",\"link\":\"/pages/goodsClassify/goodsClassify/goodsClassify?id=145&name=新鲜蔬菜\",\"text\":\"坚果零食\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677558695d720e5d42ad6.jpeg\",\"link\":\"/pages/goodsClassify/goodsClassify/goodsClassify?id=145&name=新鲜蔬菜\",\"text\":\"家居日用\",\"title\":\"\"}],\"color1\":\"#787878\",\"color2\":\"#fff\",\"radius\":20,\"style\":\"3\"},\"id\":2},{\"type\":13,\"edit\":false,\"details\":{\"style\":\"1\",\"color1\":\"#f5f5f5\",\"color2\":\"#fff\",\"boxHeight\":5,\"paddingTopBottom\":0},\"id\":3},{\"type\":27,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677565835d721127a3e20.png\",\"link\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677565865d72112a5b2bb.png\",\"link\":\"\",\"text\":\"\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677565885d72112c6beda.png\",\"link\":\"\",\"text\":\"\",\"title\":\"\"}]},\"id\":4},{\"type\":13,\"edit\":false,\"details\":{\"style\":\"1\",\"color1\":\"#f5f5f5\",\"color2\":\"#fff\",\"boxHeight\":5,\"paddingTopBottom\":0},\"id\":5},{\"type\":6,\"edit\":false,\"details\":{\"style\":\"3\",\"color2\":\"#fff\",\"radius\":10,\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677577655d7215c51bfb2.jpeg\",\"text\":\"\",\"link\":\"/pages/goodsClassify/goodsClassify/goodsClassify?id=145&name=新鲜蔬菜\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677577715d7215cb516b3.jpeg\",\"link\":\"/pages/seckill/seckill/seckill\",\"text\":\"\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677577885d7215dc7c8ae.jpeg\",\"link\":\"/bargaining/pages/bargaining/Index/Index\",\"text\":\"\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677577905d7215de4cad7.jpeg\",\"link\":\"/pages/clockIn/clockIn/clockIn\",\"text\":\"\",\"title\":\"\"}]},\"id\":6},{\"type\":28,\"edit\":true,\"details\":{\"style\":\"1\",\"show\":false},\"id\":7},{\"type\":14,\"edit\":false,\"details\":{\"positionRight\":5,\"positionBottom\":29,\"opacity\":0.9,\"goTop\":false,\"shire\":true,\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677597255d721d6da8ee1.png\",\"link\":\"link1\"}]},\"id\":8},{\"type\":17,\"edit\":false,\"details\":{\"positionRight\":5,\"positionBottom\":20,\"opacity\":0.9,\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F06%2F15%2F15605811505d04941ee6a43.png\",\"link\":\"link1\"}]},\"id\":9},{\"type\":1,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F12%2F15682505515d799ab7272b3.jpeg\",\"link\":\"link1\"}],\"dotShow\":true,\"color1\":\"#ff0000\",\"color2\":\"#fff\",\"boxHeight\":180},\"id\":10}]', '0', '1', '1', '0', '1', null, null, '1567758309', '1582252384', null);
INSERT INTO `system_merchant_design` VALUES ('226', 'ccvWPn', '40', '13', '系统模版', 'http://ceshi.juanpao.cn/api/web/./uploads/shop/banner/2020/03/10/15838392665e677822f3ed0.png', '[{\"type\":1,\"edit\":false,\"details\":{\"imgs\":[{\"link\":\"/pages/classification/classification/classification\",\"src\":\"http://tuan.weikejs.com/api/web/./uploads/2020/03/09/15837607365e6645604e1df.jpeg\"}],\"dotShow\":true,\"color1\":\"#ff0000\",\"color2\":\"#fff\",\"boxHeight\":145},\"id\":0},{\"type\":13,\"edit\":false,\"details\":{\"style\":\"1\",\"color1\":\"#f5f5f5\",\"color2\":\"#fff\",\"boxHeight\":5,\"paddingTopBottom\":0},\"id\":1},{\"type\":27,\"edit\":false,\"details\":{\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677565835d721127a3e20.png\",\"link\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677565865d72112a5b2bb.png\",\"link\":\"\",\"text\":\"\",\"title\":\"\"},{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677565885d72112c6beda.png\",\"link\":\"\",\"text\":\"\",\"title\":\"\"}]},\"id\":2},{\"type\":13,\"edit\":false,\"details\":{\"style\":\"1\",\"color1\":\"#f5f5f5\",\"color2\":\"#fff\",\"boxHeight\":5,\"paddingTopBottom\":0},\"id\":3},{\"type\":13,\"edit\":false,\"details\":{\"style\":\"1\",\"color1\":\"#f5f5f5\",\"color2\":\"#fff\",\"boxHeight\":5,\"paddingTopBottom\":0},\"id\":4},{\"type\":28,\"edit\":false,\"details\":{\"style\":\"1\",\"show\":false},\"id\":5},{\"type\":14,\"edit\":true,\"details\":{\"positionRight\":5,\"positionBottom\":23,\"opacity\":0.84,\"goTop\":true,\"shire\":true,\"imgs\":[{\"src\":\"https://imgs.juanpao.com/2019%2F09%2F06%2F15677595955d721ceb03326.png\",\"link\":\"link1\"}]},\"id\":6},{\"type\":17,\"edit\":false,\"details\":{\"positionRight\":5,\"positionBottom\":16,\"opacity\":0.9,\"imgs\":[{\"src\":\"./decoration/images/service.png\",\"link\":\"link1\"}]},\"id\":7}]', '0', '1', '1', '1', '1', null, null, '1575339268', '1583839267', null);

-- ----------------------------
-- Table structure for `system_merchant_mini_template`
-- ----------------------------
DROP TABLE IF EXISTS `system_merchant_mini_template`;
CREATE TABLE `system_merchant_mini_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '小程序模板名称',
  `system_mini_template_id` mediumint(8) NOT NULL COMMENT '第三方平台模板id',
  `content` text NOT NULL COMMENT '模板内容',
  `template_id` varchar(60) NOT NULL DEFAULT '' COMMENT '小程序模板id，用户下发消息',
  `template_purpose` varchar(32) NOT NULL COMMENT '小程序模板用途,例:pay_order',
  `scope` int(1) NOT NULL DEFAULT '0' COMMENT '1=全部用户  0 = 默认',
  `scope_type` int(1) NOT NULL DEFAULT '0' COMMENT '1=即使推送',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=583 DEFAULT CHARSET=utf8mb4 COMMENT='系统-商户小程序模板表';

-- ----------------------------
-- Table structure for `system_mini_formid`
-- ----------------------------
DROP TABLE IF EXISTS `system_mini_formid`;
CREATE TABLE `system_mini_formid` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `mini_open_id` char(32) NOT NULL DEFAULT '' COMMENT '小程序openid',
  `formid` varchar(52) NOT NULL COMMENT '发送码id',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=禁用 2=已使用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COMMENT='系统-小程序发送码表';


-- ----------------------------
-- Table structure for `system_mini_template`
-- ----------------------------
DROP TABLE IF EXISTS `system_mini_template`;
CREATE TABLE `system_mini_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '模板名称 例:发货消息',
  `purpose` varchar(32) NOT NULL DEFAULT '' COMMENT '用途, 同步 order 消息 message',
  `app_id` tinyint(3) NOT NULL COMMENT '对应的appid 应用id',
  `keyword_list_id` varchar(10) NOT NULL COMMENT '模板库id',
  `keyword_list_name` varchar(52) NOT NULL DEFAULT '' COMMENT '模板库名称',
  `keyword_list` text NOT NULL COMMENT '模板关键词库(json) keyword_id、name、example，其中example设置为参数名',
  `keyword_id_list` varchar(512) NOT NULL DEFAULT '' COMMENT 'keywordids ，例如 1，2，3',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=正常 0=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COMMENT='系统-小程序模板表';

-- ----------------------------
-- Table structure for `system_mini_template_access`
-- ----------------------------
DROP TABLE IF EXISTS `system_mini_template_access`;
CREATE TABLE `system_mini_template_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `mini_open_id` varchar(52) NOT NULL DEFAULT '' COMMENT '用户id',
  `template_id` varchar(100) NOT NULL DEFAULT '' COMMENT '小程序模板id，用户下发消息',
  `number` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送次数 大于5次不操作',
  `template_params` text NOT NULL COMMENT '参数信息',
  `template_purpose` varchar(32) NOT NULL COMMENT '小程序模板用途,例:pay_order',
  `page` varchar(255) DEFAULT NULL COMMENT '跳转页',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=推送成功 -1=未推送  0=推送中 2=推送失败',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COMMENT='系统-商户小程序模板记录表';

-- ----------------------------
-- Table structure for `system_mini_template_access_group`
-- ----------------------------
DROP TABLE IF EXISTS `system_mini_template_access_group`;
CREATE TABLE `system_mini_template_access_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `template_id` varchar(100) NOT NULL DEFAULT '' COMMENT '小程序模板id，用户下发消息',
  `template_params` text NOT NULL COMMENT '参数信息',
  `template_purpose` varchar(32) NOT NULL COMMENT '小程序模板用途,例:pay_order',
  `page` varchar(255) DEFAULT NULL COMMENT '跳转页',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态 1=推送成功 -1=未推送  0=推送中 2=推送失败',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='系统-商户小程序模板记录表';

-- ----------------------------
-- Table structure for `system_news`
-- ----------------------------
DROP TABLE IF EXISTS `system_news`;
CREATE TABLE `system_news` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` mediumint(8) NOT NULL DEFAULT '0' COMMENT '应用id',
  `type` mediumint(8) NOT NULL DEFAULT '1' COMMENT '类型 1产品动态',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容(富文本)',
  `page_view` int(8) NOT NULL DEFAULT '0' COMMENT '访问量',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序权重值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='系统-新闻表';

-- ----------------------------
-- Records of system_news
-- ----------------------------
INSERT INTO `system_news` VALUES ('5', '0', '1', '动态1', '<p><br/></p><p>此套装为西服+西裤两件套哦，如果需要衬衫或者马甲的亲需要再拍一个衬衫或者马甲链接哦。此款面料是毛料的，面料含毛量在95%以上，属于高端的西装面料。</p><p>&nbsp; &nbsp; 我们门店地址：<span style=\"text-decoration:underline;\">上海市黄浦区陆家浜路428号中福面料商城1楼A138号</span>。需要来门店定制的亲需要提前电话预约下哦（电话：18014828823袁），我们有师傅专门给您量体的哈，如果不方便来门店的亲们，可以选择网上下单的方式定制。支持来图定制。我们的售后服务都是有保障的哦，三个月内有任何尺寸问题我们免费调整的哈。</p><p><img src=\"https://img.alicdn.com/imgextra/i3/681204617/TB2Vk04kYtlpuFjSspfXXXLUpXa_!!681204617.jpg\" alt=\"\" width=\"563\" height=\"677\"/><img src=\"https://img.alicdn.com/imgextra/i2/681204617/TB22D0Eco3iyKJjSspnXXXbIVXa_!!681204617.jpg\" alt=\"\" width=\"545\" height=\"419\"/></p><p>&nbsp;</p><p>&nbsp;<strong><span style=\"text-decoration:underline;\">定制的款式参考，都是经典的西服款式。<img src=\"https://img.alicdn.com/imgextra/i4/681204617/TB2hbvysFXXXXaCXXXXXXXXXXXX_!!681204617.jpg\" width=\"559\" height=\"628\"/><br/></span></strong><img src=\"https://img.alicdn.com/imgextra/i1/681204617/TB2DYYzsFXXXXaeXXXXXXXXXXXX_!!681204617.jpg\" width=\"567\" height=\"539\"/><img src=\"https://img.alicdn.com/imgextra/i4/681204617/TB21suAsFXXXXcSXpXXXXXXXXXX_!!681204617.jpg\" width=\"508\" height=\"415\"/><img src=\"https://img.alicdn.com/imgextra/i1/681204617/TB2cErbsFXXXXc5XXXXXXXXXXXX_!!681204617.jpg\" width=\"558\" height=\"403\"/><img src=\"https://img.alicdn.com/imgextra/i1/681204617/TB2twTvsFXXXXasXXXXXXXXXXXX_!!681204617.jpg\" width=\"588\" height=\"671\"/><img src=\"https://img.alicdn.com/imgextra/i1/681204617/TB2XZfusFXXXXaRXXXXXXXXXXXX_!!681204617.jpg\" width=\"582\" height=\"496\"/></p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;<strong><span style=\"text-decoration:underline;\">定制的面料手机实拍照片</span></strong></p><p><br/></p><p><br/></p><p><br/></p><p><br/></p><p><br/></p><p><br/></p><p><br/></p><p><br/></p><p>&nbsp;</p><p><br/></p><p><br/></p><p><br/></p><p><br/></p><p><br/></p>', '0', '1', '1', '1551173680', '1551234288', '1551404268');
INSERT INTO `system_news` VALUES ('6', '0', '1', '动态3', '<p><img src=\"/ueditor/admin/image/20190226/1551173718.png\" title=\"1551173718.png\" alt=\"水果.png\"/>这是动态3，有图片的</p>', '0', '3', '1', '1551173721', '1551232996', '1551404269');
INSERT INTO `system_news` VALUES ('7', '0', '1', '动态2', '<p><img src=\"http://img.baidu.com/hi/jx2/j_0007.gif\"/>这是动态2，有图片的</p>', '0', '2', '1', '1551173738', '1551233002', '1551404271');
INSERT INTO `system_news` VALUES ('8', '0', '1', '社交电商火了，2019新年如何布局属于自己的社交电商营销系统平台？', '<p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\">随着移动互联网的普及和手机流量的增加，用户花在手机上的时间越来越多。通过自媒体上分享资源的学习，通过视频平台消磨时间，通过电商平台的购物，都让人觉得网络的神奇。社交电商在这一年火的一塌糊涂，个人或企业都在纷纷的布局，希望在接下来的社交电商市场中占一片地方，分一杯羹。为什么社交电商就火了呢？社交电商有什么的魅力？</p><p></p><p class=\"pgc-img-caption\" style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px;\">社交电商火了，2019新年如何布局？</p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\"><strong style=\"word-wrap: break-word; margin: 0px; padding: 0px;\">社交电商具有导购员的作用</strong>，可以帮助消费者解决怎么买，为什么买的问题，因为社交电商是以人为中心，在营销推广之初就是从人群定位到解决痛点需求开始的，所以定位了解很重要；同时它与生俱来的社交化的元素，可以让用户之间基于社交网络并借助平台相关工具进行互动分享，产品的质量可以保证加上经营者的个人ip做背书，轻松实现产品传播、流量变现。</p><p></p><p class=\"pgc-img-caption\" style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px;\">社交裂变</p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\">分享产品带来的效果、变化及评价，是用户对产品的认可。用户的社交传播心理：“我喜欢这个产品，我要分享给身边的好友。”社交电商营销的核心是如何让自己的商品获得在社交关系链里的快速传播与裂变。在运营过程中，社交电商的确具备了传统电商不可比拟的优势——那就是<strong style=\"word-wrap: break-word; margin: 0px; padding: 0px;\">裂变传播</strong>，可以将用户的社交流量转换成你的平台流量甚至是你产品的销量。社交电商火了，2019新年如何布局属于自己的社交电商营销系统平台？</p><p></p><p class=\"pgc-img-caption\" style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px;\">裂变传播</p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\">现在的产品何其多，企业想要快速把产品销售出去，想要快速的资金回笼。用社交电商的营销方式比传统电商的销售模式速度更快。传播产品品牌的同时获取传播裂变的种子用户，为之后的产品升级再销售打下了基础。所以，为什么社交电商火了？它是未来营销的趋势，品牌曝光的机会。</p><p></p><p class=\"pgc-img-caption\" style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px;\"><br/></p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\"><strong style=\"word-wrap: break-word; margin: 0px; padding: 0px;\">企业如何建立自己的社交电商营销系统？如何把留存用户变为种子用户？</strong></p><ul style=\"word-wrap: break-word; margin: 1em 2em; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\" class=\" list-paddingleft-2\"><li><p>自建社交电商平台，打通维新生态系统。通过SEO和SEM获取从搜索引擎来的用户、通过自媒体平台软文营销来的用户、通过问答平台解决问题来的用户、通过视频平台分享来的用户都导入到自建电商社交平台的流量池中。</p></li><li><p>打造平台的内容质量，让用户消除疑虑放心购。当用户在平台遇到和自己有相同需求的用户时，从心里深处会有一种归属感。</p></li><li><p>购买产品的售后质量。在平台可以解决痛点需求的疑惑以及预防的措施，而且可以和有相同需求的朋友共同进步，社交电商营销的社群部分就建设完成了。</p></li><li><p>留存用户通过一段时间的沉淀，购买产品后的服务加上社群的氛围，那么，留存用户过渡到裂变传播的用户只是时间上的问题了。</p></li></ul><p></p><p class=\"pgc-img-caption\" style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px;\"><br/></p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\">通过对用户的引流，留存，变现，裂变的营销，慢慢积累下来的用户就是自己的种子用户（私域流量）。随着用户引流的成本在增加，用户种子用户去传播裂变带来的用户就可以大大的降低引流的成本。</p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\">为什么维护一个老客户的成本要比开发一个新客户的成本低得多，就是因为，跟一个陌生人建立连接，并进而产生信任是一个非常困难的事情。要快速建立信任，有一个付出的心态是非常重要的，没有人愿意跟一味索取型的人打交道。</p><p style=\"word-wrap: break-word; margin-top: 0px; margin-bottom: 12px; padding: 0px; border: 0px; color: rgb(102, 102, 102); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; white-space: normal; background-color: rgb(255, 255, 255);\">社交电商火了，2019新年如何布局属于自己的社交电商营销系统平台？得流量者，赚钱so easy。流量，是一切生意是根源。</p><p><br/></p>', '0', '4', '1', '1551231149', '1551404361', null);
INSERT INTO `system_news` VALUES ('9', '0', '1', '抢占200+社区、月销破百万的群团购生意经，我们帮你拿到了！ ...', '<p><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">经熟人推荐、用户直接在小程序内下单，第二天由商家将货品送至小区据点，再由团长分发给用户——这便是一个典型的群团购场景。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">其中“泽惠果蔬”和“小区严选”先后借助有赞做起了群团购生意：“小区严选” 1毛钱买黄岩蜜橘活动，第一天就带来10000+访客；而“泽惠果蔬”也将过去积累的200位团长聚集起来，目前月销售额稳定在150万左右。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">今天我们将剖析这两位典型商家，看不过瘾还可以点击【阅读原文】报名直播培训，资深运营同学解答你的经营疑惑！</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">卖货不用自己来</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团长是群团购运营的“灵魂”，要让团购活动发挥最大价值，商家需要在</span><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">招募、激励、日常管理</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">三个方面多下功夫。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">招募团长的3种模式：</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">线下地推：刚刚接触社区团购的商家，在前期缺乏社区资源的情况下，可以到各个小区实地招募团长。现场向团长展示店铺产品以及团购玩法，用礼品、奖励吸引小区住户报名，发展他们成为种子用户。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">泽惠果蔬至今依然会通过定期地推的方式招募团长，目前已有200多位团长，分布在新疆伊犁、石河子、乌鲁木齐等各大城市。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">线上招募：有一定粉丝基础的商家，也可以直接通过线上渠道招募团长，以“社区合伙人”、“代言人”的名义引起用户关注，设置团长佣金鼓励报名。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团长转介绍：招募到首批社区团长后，商家可以用“邀请奖励”来激励团长转介绍，以此获取更多精准、优质的社区KOL资源。小区严选就是由最早的6名种子用户，裂变到如今250多位活跃团长，覆盖山东滨州、淄博、东营等多个地区。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">筛选团长的维度：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">群团购的主要销售场景是由各快递代收点、社区便利店、物业、业主等发起的社区微信群，每个群都相当于一个社区店。商家在招募时，可以优先选择以下几类人群：</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">1、小区业主/小区工作人员，比如年轻宝妈群体或者便利店工作人员，他们有闲暇并且乐于服务邻里，能够快速建群；</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">2、在小区有一定的影响力、有用户基础的社区KOL，已有或已加入50、100人的小区住户群聊；</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">3、有自提点优先，比如车库、便利店。部分涉及冷链配送的产品，商家也可以为团长配备保鲜柜，小区严选就推出了保鲜柜租赁服务，团长缴纳押金即可领取。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团长激励体系：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">情感、金钱、荣誉刺激三管齐下，才是一套合格的激励体系，团长可以通过微信群统一维护，商家需要做到以下几点：</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">1、向新进团长介绍社区团购模式、利益点；</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">2、提供推广素材、制定推广节奏，方便团长传播；</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">小区严选会在每天订单量比较多的早上8点、中午12点、晚上8点集中推活动，而14-15点是下单低谷，通常这个时候可以减少干预，也给团长留出休息时间。&nbsp;</span><br/><img id=\"aimg_1918\" src=\"/ueditor/admin/image/20190301/1551404542370182.jpg\" class=\"zoom\" width=\"640\"/>&nbsp;<span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\"></span><br/><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团长在小区群推广团购活动</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">3、定期组织线上线下培训，从吸粉、互动、营销等维度提升团长的推广能力；</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">4、群内关注团长反馈，及时解决问题，营造“家”的感觉。</span><br/><img id=\"aimg_1919\" src=\"/ueditor/admin/image/20190301/1551404542265238.jpg\" class=\"zoom\" width=\"640\"/>&nbsp;<span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\"></span><br/><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团长微信群</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">给团长的利润空间不低于5%，越高越好。业绩超过一定数量的团长可以获得额外的奖金。每月可以评选一次优秀团长，提供额外的福利刺激团长投入。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团长日常管理：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">当社区团购达到一定规模，为了保证团购效率和质量，需要对团长进行考核，比如不允许团长在群里推广同类竞品的活动，对长期未开单的团长进行清退等。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">团购选品有依据</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">生鲜是目前社区团购中的核心品类，大众化的日用低价产品也可以尝试销售，小区严选目前以水果+零食+日化/厨房类用品为主。</span><br/><img id=\"aimg_1920\" src=\"/ueditor/admin/image/20190301/1551404542879281.jpg\" class=\"zoom\" width=\"640\"/>&nbsp;<span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\"></span><br/><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">泽惠果蔬则每天选择10款左右的产品，类似蔬菜、水果、肉类、酸奶等高频消费商品，每天都会有；膨化食品、坚果通常出现在周末看剧、朋友聚会的场景，每周上架至少1次；粮油米面等商品，按照1个月1次的周期补货即可，代入目标客群的消费习惯选品，团长推广更容易。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">群团购页面简单，强调快速决策，商品单价不宜超过39元，单个团购活动商品总数量不宜超过10个，同一类目的商品数量不要超过3个。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">为了提高团长的积极性，也保证商家的利润，可以将高利润和低利润的商品混合在一次团购活动中销售。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">社群活跃有方法</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">活跃社群除了发红包以外，也可以通过营销玩法、优质内容提升活跃度。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">会员日：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">每月的double日（1月1日、2月2日以此类推）是小区严选会员日，当天热销产品降价促销，通常会员日的社群活跃度和转化率会有明显提升。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">免单福利：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">前5位免单、随机免单、当日购买金额第一名免单等方式都可以很好提升社群活跃度。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">优质内容：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">商家可以鼓励团长从店铺品类特点出发，从怎么吃、吃什么的角度给邻里提供建议，引发群内讨论互动。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">需要特别注意的是，社区团购是熟人社交下的购买场景，口碑、服务很重要。为了能够更快速地出货，泽惠果蔬始终坚持每日开团、每日结算、及时发货，保证群内都是正向情绪。若部分小区订单量过少，也可以将2天或者3天的订单集中一次发货，节约物流成本，当然要提前同步用户，避免客诉。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">商家心得分享</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">商家信息</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">店铺：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">小区严选</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">简介：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">小区严选从2017年4月开始经营社区团购，覆盖山东滨州、淄博、东营等地，店铺用户以30-40岁女性为主，目前有250多位活跃团长维系着近60000粉丝。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">此前一直以传统的社区团购模式推广产品，开通小程序以来，团购效率得到提升。现在以每日开团的形式，逐步让原始用户熟悉小程序群团购模式。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">店铺：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">奎屯泽惠果蔬配送有限公司</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">简介：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">社区团购是奎屯泽惠果蔬配送有限公司的重要业务板块，公司目前主做新疆奎屯、石河子、乌鲁木齐等城市，已经发展了近200位团长。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">泽惠果蔬于9月28日开始参与群团购内测，进行小范围试点；群团购插件正式上线后开启了每日团购活动，并在所有小区社群中推广。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">商家心得</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">Q1：你怎么看待社区团购？做好社区团购的关键点是什么？</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">小区严选：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">做团购活动，预热、引流、把握节奏都很重要，我们现在就在用每日开团和促销活动给小程序引流，给11月11日会员日大促做预热。最早做社区团购的时候还没有小程序支持，现在推荐大家尽早上线小程序，不要去踩我们踩过的坑。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">泽惠果蔬：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">社区团购建立在人与人之间的感情关系上，也可以称之为社交电商，因此客户体验感很重要。最基础的就是要把控好产品质量，不能让用户觉得便宜是牺牲了质量；其次是价格，必须要优于市场零售价；以及配送，要保证及时送货。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">Q2：「群团购」工具在这里发挥了什么作用？</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">小区严选：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">解决了团长统计订单的问题，提高效率，减少出错率。通过后台实时观察数据，方便我们整理客户资料，分析客户购买习惯和频率，能够及时调整产品排单。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">泽惠果蔬：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">方便团长推广活动，团购结束后团长统一配货很便捷；方便他们统计自己的销售情况，对佣金也有比较清楚的了解。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">Q3：有什么建议可以给到想要做社区团购的商家？</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">小区严选：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">如果仅仅是想通过社区团购节省物流成本、快速出货，建议这类商家把他们的产品供应给社区团购渠道，现在有非常多做社区团购的团队缺好的产品，把做社区团购的成本节约下来，花在找渠道上会有更大的收获。</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">而针对想做社区团购的新商家，可以按照这三个步骤来走：一是组建团队，包含市场、财务、采购、运营、管理、配送六大角色；二是寻找空白市场，招募团长；三是找到合适的货，人货场三者组建新零售。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">泽惠果蔬：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">要沉下心来在合伙人招募上下功夫，并且需要持续做下去，合伙人是具有“流动性”的，可以制定优胜劣汰的考察机制，从中找出最优质的合伙人。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">群团购能帮商家做什么</span><br/><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">对提供同城服务的商家来说，如何增加销售渠道、提高库存周转速度、刺激粉丝复购是逃不开的命题。有赞于今年10月正式上线了“群团购”插件，帮商家解决上述三大难题。</span><br/><img id=\"aimg_1921\" src=\"/ueditor/admin/image/20190301/1551404542813383.jpg\" class=\"zoom\" width=\"640\"/>&nbsp;<span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\"></span><br/><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">提效：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">帮助商家解决了团长在订单统计上的痛点，提高发货效率，减少出错率。利润结算一目了然，团长能够清楚地知道自己的业绩。</span><br/><img id=\"aimg_1922\" src=\"/ueditor/admin/image/20190301/1551404542866614.jpg\" class=\"zoom\" width=\"640\"/>&nbsp;<span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\"></span><br/><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">明确库存，防止超卖：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">限定了每次团购活动的商品库存，防止供应不足的商品出现超卖；商家也可以用群团购工具实现限量促销，将成本控制在自己可以接受的范围。</span><br/><span style=\"word-wrap: break-word; margin: 0px; padding: 0px; font-weight: 700; color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">数据分析：</span><span style=\"color: rgb(54, 54, 54); font-family: &quot;Microsoft Yahei&quot;, Simsun; font-size: 14px; background-color: rgb(255, 255, 255);\">群团购产生的订单都一一记录在后台，可以通过下单量分析产品转化情况来调整产品线、通过实时概况分析用户活跃时段制定传播节奏、通过访客数和新老客户比例分析社群活跃度等等，多种数据维度帮助商家实现精细化运营。</span></p>', '0', '1', '1', '1551404550', null, null);
INSERT INTO `system_news` VALUES ('10', '0', '1', '测试，需要删除', '<p><img src=\"/public/common/ueditor/admin/image/20190305/1551777986428041.png\" title=\"1551777986428041.png\" alt=\"文具.png\"/></p>', '0', '5', '1', '1551775609', '1551777988', null);

-- ----------------------------
-- Table structure for `system_notice`
-- ----------------------------
DROP TABLE IF EXISTS `system_notice`;
CREATE TABLE `system_notice` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `marchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id，0为系统',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '通知类型 1为订单消息 2为系统消息',
  `order_id` varchar(20) DEFAULT NULL COMMENT '订单号,可为空',
  `title` varchar(32) DEFAULT NULL COMMENT '标题',
  `content` varchar(128) DEFAULT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0无效 1有效',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未读 1已读',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知表';

-- ----------------------------
-- Records of system_notice
-- ----------------------------

-- ----------------------------
-- Table structure for `system_notify`
-- ----------------------------
DROP TABLE IF EXISTS `system_notify`;
CREATE TABLE `system_notify` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` mediumint(8) NOT NULL COMMENT '应用id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `content` text NOT NULL COMMENT '内容(富文本)',
  `type` tinyint(1) NOT NULL COMMENT '类型 1动态 2头条',
  `page_view` int(8) NOT NULL DEFAULT '0' COMMENT '访问量',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序权重值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统-应用动态表';

-- ----------------------------
-- Records of system_notify
-- ----------------------------

-- ----------------------------
-- Table structure for `system_official`
-- ----------------------------
DROP TABLE IF EXISTS `system_official`;
CREATE TABLE `system_official` (
  `id` int(8) NOT NULL,
  `app_id` varchar(50) NOT NULL,
  `secret` varchar(50) NOT NULL,
  `token` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of system_official
-- ----------------------------

-- ----------------------------
-- Table structure for `system_operation_record`
-- ----------------------------
DROP TABLE IF EXISTS `system_operation_record`;
CREATE TABLE `system_operation_record` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(10) NOT NULL COMMENT '操作人ID',
  `operation_type` char(20) NOT NULL DEFAULT '' COMMENT '操作类型',
  `operation_id` varchar(24) NOT NULL DEFAULT '' COMMENT '被操作ID',
  `module_name` char(20) NOT NULL DEFAULT '' COMMENT '操作模块名称',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=844 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用操作记录表';


-- ----------------------------
-- Table structure for `system_pay`
-- ----------------------------
DROP TABLE IF EXISTS `system_pay`;
CREATE TABLE `system_pay` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `app_access_id` varchar(32) DEFAULT NULL COMMENT '应用订单号',
  `order_id` varchar(32) DEFAULT NULL COMMENT '订单号',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `remain_price` decimal(8,2) DEFAULT '0.00' COMMENT '支付金额',
  `transaction_id` varchar(32) DEFAULT NULL COMMENT '第三方支付流水号',
  `pay_time` int(11) DEFAULT NULL COMMENT '付款时间',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '支付类型 0=未选定支付方式 1=余额 2=支付宝 3=微信',
  `total_price` decimal(8,2) DEFAULT '0.00' COMMENT '支付总额',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 1=已支付 2=未支付 0支付中 4已退款',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COMMENT='支付表';



-- ----------------------------
-- Table structure for `system_pay_alipay`
-- ----------------------------
DROP TABLE IF EXISTS `system_pay_alipay`;
CREATE TABLE `system_pay_alipay` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `notify_time` int(11) DEFAULT NULL COMMENT '通知时间',
  `notify_type` varchar(50) NOT NULL COMMENT '通知类型',
  `notify_id` char(18) NOT NULL COMMENT '通知校验ID',
  `sign_type` varchar(20) NOT NULL COMMENT '签名方式',
  `app_id` varchar(20) NOT NULL COMMENT '支付宝分配给开发者的应用ID',
  `auth_app_id` varchar(20) NOT NULL COMMENT '授权方的appid注',
  `sign` varchar(255) NOT NULL COMMENT '签名',
  `out_trade_no` varchar(64) NOT NULL COMMENT '商户网站唯一订单号',
  `subject` varchar(128) NOT NULL COMMENT '商品名称',
  `payment_type` varchar(4) NOT NULL COMMENT '支付类型',
  `trade_no` varchar(64) NOT NULL COMMENT '支付宝交易号',
  `trade_status` varchar(255) NOT NULL COMMENT '交易状态',
  `seller_id` varchar(30) NOT NULL COMMENT '卖家支付宝用户号',
  `seller_email` varchar(100) NOT NULL COMMENT '卖家支付宝账号',
  `buyer_id` varchar(30) NOT NULL COMMENT '买家支付宝用户号',
  `buyer_email` varchar(100) NOT NULL COMMENT '买家支付宝账号',
  `total_amount` decimal(10,2) DEFAULT NULL COMMENT '订单总金额',
  `total_fee` decimal(10,2) unsigned DEFAULT NULL COMMENT '交易金额',
  `quantity` int(11) unsigned NOT NULL COMMENT '购买数量',
  `price` decimal(8,2) unsigned NOT NULL COMMENT '商品单价',
  `body` varchar(255) NOT NULL COMMENT '商品描述',
  `gmt_create` int(11) DEFAULT '0' COMMENT '交易创建时间',
  `gmt_payment` int(11) DEFAULT '0' COMMENT '交易付款时间',
  `is_total_fee_adjust` varchar(1) NOT NULL DEFAULT '' COMMENT '是否调整总价',
  `use_coupon` varchar(1) NOT NULL DEFAULT '' COMMENT '是否使用红包买家',
  `discount` decimal(8,2) unsigned NOT NULL COMMENT '折扣',
  `refund_status` varchar(255) NOT NULL DEFAULT '' COMMENT '退款状态',
  `gmt_refund` int(11) DEFAULT NULL COMMENT '退款时间',
  `notify_json` text NOT NULL COMMENT 'JSON数据',
  `TIMESTAMP` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '状态 1支付成功 0支付失败 2支付中',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='支付宝支付表';

-- ----------------------------
-- Records of system_pay_alipay
-- ----------------------------

-- ----------------------------
-- Table structure for `system_pay_weixin`
-- ----------------------------
DROP TABLE IF EXISTS `system_pay_weixin`;
CREATE TABLE `system_pay_weixin` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `return_code` varchar(16) DEFAULT NULL COMMENT '返回状态码',
  `return_msg` varchar(128) DEFAULT NULL COMMENT '返回信息',
  `wx_appid` varchar(32) NOT NULL,
  `appid` varchar(32) NOT NULL COMMENT '微信应用id',
  `mch_id` varchar(32) NOT NULL COMMENT '微信商户号',
  `device_info` varchar(32) NOT NULL COMMENT '设备号',
  `result_code` varchar(16) NOT NULL COMMENT '业务结果',
  `err_code` varchar(32) NOT NULL COMMENT '错误代码',
  `err_code_des` varchar(32) NOT NULL COMMENT '错误代码描述',
  `openid` varchar(128) NOT NULL COMMENT '用户标识',
  `is_subscribe` char(1) NOT NULL COMMENT '是否关注公众账号',
  `trade_type` varchar(16) NOT NULL COMMENT '交易类型',
  `bank_type` varchar(16) NOT NULL COMMENT '付款银行',
  `total_fee` int(10) unsigned NOT NULL COMMENT '总金额',
  `fee_type` varchar(120) NOT NULL COMMENT '标价币种',
  `cash_fee` int(11) NOT NULL COMMENT '现金支付金额',
  `sign` varchar(255) NOT NULL COMMENT '签名',
  `nonce_str` varchar(255) NOT NULL COMMENT '随机字符串',
  `transaction_id` varchar(32) NOT NULL COMMENT '微信支付订单号',
  `out_trade_no` varchar(32) NOT NULL DEFAULT '' COMMENT '商户订单号',
  `attach` varchar(128) NOT NULL DEFAULT '' COMMENT '商家数据包',
  `time_end` varchar(14) NOT NULL DEFAULT '' COMMENT '支付完成时间',
  `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '状态 1支付成功 0支付失败 2支付中',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COMMENT='微信支付表';



-- ----------------------------
-- Table structure for `system_plug_in`
-- ----------------------------
DROP TABLE IF EXISTS `system_plug_in`;
CREATE TABLE `system_plug_in` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '插件名称',
  `english_name` varchar(50) NOT NULL DEFAULT '' COMMENT '英文名',
  `app_id` tinyint(4) NOT NULL COMMENT '应用id',
  `pic_url` varchar(255) NOT NULL COMMENT '插件图片',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `type` tinyint(1) NOT NULL COMMENT '插件类型 1微信 2小程序 3均有',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='插件表';

-- ----------------------------
-- Records of system_plug_in
-- ----------------------------
INSERT INTO `system_plug_in` VALUES ('1', '团购', 'Group_buying', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15597055315cf737bb98d5a.jpeg', null, '3', '1', '1559705531', '1559720976', null);
INSERT INTO `system_plug_in` VALUES ('2', '签到', 'Sign_in', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15597134935cf756d540c6b.jpeg', null, '3', '1', '1559713492', '1559721148', null);
INSERT INTO `system_plug_in` VALUES ('3', '秒杀', 'Spike', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15597211855cf774e1dba05.jpeg', null, '3', '1', '1559721185', '1559721185', null);
INSERT INTO `system_plug_in` VALUES ('4', '会员（积分）', 'Vip_integral', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15597236335cf77e71331fb.png', '', '3', '1', '1559722517', '1564381997', null);
INSERT INTO `system_plug_in` VALUES ('5', '会员（付费）', 'Vip_payment', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15643819865d3e932240ff9.jpeg', '', '3', '1', '1564381985', '1564382882', null);
INSERT INTO `system_plug_in` VALUES ('6', '积分商城', 'Integral_mall', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15643909275d3eb60faf9ce.jpeg', '', '3', '1', '1564390927', '1564558399', null);
INSERT INTO `system_plug_in` VALUES ('7', '购买信息', 'Pay_info', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15646443765d42941879436.jpeg', '', '3', '1', '1564644376', '1564658256', null);
INSERT INTO `system_plug_in` VALUES ('8', '团长等级', 'Leader_level', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15646446165d4295082fd2e.jpeg', '', '3', '1', '1564644616', '1564644616', null);
INSERT INTO `system_plug_in` VALUES ('9', '添加到我的小程序', 'My_miniprogram', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15646576505d42c7f222874.jpeg', '', '3', '1', '1564657650', '1564657917', null);
INSERT INTO `system_plug_in` VALUES ('10', '好物圈', 'Good_phenosphere', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15651414465d4a29c6743eb.jpeg', '', '3', '1', '1565141446', '1565141446', null);
INSERT INTO `system_plug_in` VALUES ('11', '闪送', 'shansong', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15658541655d5509d52d687.jpeg', '', '3', '1', '1565854164', '1565854164', null);
INSERT INTO `system_plug_in` VALUES ('12', '余额支付', 'balance_pay', '2', 'https://imgs.juanpao.com/admin%2Fplugin%2F15658618815d5527f91cf98.png', '', '3', '1', '1565861880', '1565861880', null);

-- ----------------------------
-- Table structure for `system_plug_in_access`
-- ----------------------------
DROP TABLE IF EXISTS `system_plug_in_access`;
CREATE TABLE `system_plug_in_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `plug_in_id` tinyint(4) DEFAULT NULL COMMENT '插件id',
  `is_open` tinyint(1) NOT NULL COMMENT '是否开启 1=开启 0=关闭',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='插件记录表';

-- ----------------------------
-- Records of system_plug_in_access
-- ----------------------------

-- ----------------------------
-- Table structure for `system_sms`
-- ----------------------------
DROP TABLE IF EXISTS `system_sms`;
CREATE TABLE `system_sms` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `appid` varchar(50) NOT NULL,
  `appkey` varchar(50) NOT NULL,
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of system_sms
-- ----------------------------

-- ----------------------------
-- Table structure for `system_sms_access`
-- ----------------------------
DROP TABLE IF EXISTS `system_sms_access`;
CREATE TABLE `system_sms_access` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '模板内容',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id,默认为0(系统)',
  `key` char(6) NOT NULL DEFAULT '0' COMMENT '应用key',
  `template_id` int(11) NOT NULL DEFAULT '0' COMMENT '系统的模板id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户uid',
  `phone` char(11) NOT NULL COMMENT '手机号',
  `prefix` varchar(52) NOT NULL COMMENT '前缀，例:login_user,reg_user',
  `code` char(6) NOT NULL DEFAULT '' COMMENT '验证码,非验证码可为空',
  `content` varchar(512) NOT NULL COMMENT '短信内容,json格式，不能为空',
  `verify_count` tinyint(1) NOT NULL DEFAULT '3' COMMENT '最大验证次数',
  `verify_number` tinyint(1) NOT NULL DEFAULT '0' COMMENT '当前验证次数',
  `status` tinyint(11) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信记录表';

-- ----------------------------
-- Records of system_sms_access
-- ----------------------------

-- ----------------------------
-- Table structure for `system_sms_sign`
-- ----------------------------
DROP TABLE IF EXISTS `system_sms_sign`;
CREATE TABLE `system_sms_sign` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '签名名称',
  `qcloud_sign_id` int(11) NOT NULL DEFAULT '0' COMMENT '腾讯云签名id',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id,默认为0(系统)',
  `key` char(6) NOT NULL DEFAULT '0' COMMENT '应用key',
  `remark` varchar(50) DEFAULT NULL COMMENT '备注',
  `pic_str` mediumtext COMMENT 'base64图片信息',
  `file_name` varchar(255) DEFAULT NULL COMMENT '对象存储key',
  `url` varchar(255) DEFAULT NULL COMMENT '对象存储地址',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='腾讯云短信签名';


-- ----------------------------
-- Table structure for `system_sms_template`
-- ----------------------------
DROP TABLE IF EXISTS `system_sms_template`;
CREATE TABLE `system_sms_template` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '模板内容',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '短信类型，Enum{0：普通短信, 1：营销短信}',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id,默认为0(系统)',
  `key` char(6) NOT NULL DEFAULT '0' COMMENT '应用key',
  `module` varchar(100) DEFAULT NULL COMMENT '模块名称',
  `action` varchar(100) DEFAULT NULL COMMENT '操作名称',
  `sign_id` int(11) DEFAULT NULL COMMENT '签名id',
  `qcloud_template_id` int(11) NOT NULL DEFAULT '0' COMMENT '腾讯云模板id',
  `status` tinyint(11) NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='短信模板表';



-- ----------------------------
-- Table structure for `system_sub_admin`
-- ----------------------------
DROP TABLE IF EXISTS `system_sub_admin`;
CREATE TABLE `system_sub_admin` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `username` varchar(50) NOT NULL COMMENT '管理用户名',
  `real_name` char(12) DEFAULT NULL COMMENT '管理姓名',
  `balance` double(10,2) DEFAULT '0.00' COMMENT '余额',
  `password` char(32) NOT NULL COMMENT '密码',
  `salt` char(32) NOT NULL COMMENT '盐',
  `type` tinyint(1) DEFAULT '0' COMMENT '0=普通员工  1=供货商',
  `phone` char(11) DEFAULT NULL COMMENT '手机号',
  `intro` varchar(200) DEFAULT NULL COMMENT '简单说明',
  `points` double(10,2) DEFAULT '0.00' COMMENT '提现扣点（类型为供货商专属）',
  `self_leader_id` int(11) DEFAULT '0' COMMENT '绑定的自提点',
  `leader` text COMMENT '门店信息',
  `yly_config` text COMMENT '门店易联云配置',
  `status` tinyint(1) NOT NULL COMMENT '状态 1正常 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8 COMMENT='管理员工表';


-- ----------------------------
-- Table structure for `system_sub_admin_balance`
-- ----------------------------
DROP TABLE IF EXISTS `system_sub_admin_balance`;
CREATE TABLE `system_sub_admin_balance` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `sub_admin_id` int(10) NOT NULL COMMENT '员工id',
  `balance_sn` varchar(30) DEFAULT '0' COMMENT '提现单号',
  `order_sn` varchar(18) NOT NULL DEFAULT '' COMMENT '订单编号',
  `fee` decimal(8,2) NOT NULL COMMENT '手续费用',
  `money` decimal(8,2) NOT NULL COMMENT '金额 正数增加 负数消费',
  `remain_money` decimal(8,2) DEFAULT '0.00' COMMENT '到账金额',
  `content` varchar(128) NOT NULL COMMENT '详细',
  `remarks` varchar(128) NOT NULL COMMENT '备注',
  `send_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0=余额 1=微信 2=支付宝 3=银行卡',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 0=默认 1=团长佣金 2=推荐团长佣金 3=自提点佣金 4=推荐佣金 5=团长奖金 6=供货商金额',
  `realname` varchar(10) NOT NULL COMMENT '姓名（收款人姓名）',
  `pay_number` varchar(32) NOT NULL COMMENT '支付账号',
  `is_send` tinyint(1) DEFAULT '0' COMMENT '是否提现订单 1=是 0=否',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=已结算,0=结算中,2=已拒绝',
  `confirm_time` int(11) DEFAULT NULL COMMENT '确认时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='员工-金额表';
-- ----------------------------
-- Table structure for `system_theme`
-- ----------------------------
DROP TABLE IF EXISTS `system_theme`;
CREATE TABLE `system_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(10) NOT NULL,
  `merchant_id` int(11) NOT NULL,
  `type` enum('mini','wechat') NOT NULL COMMENT '类型',
  `theme` varchar(10) NOT NULL COMMENT '主题颜色',
  `theme_text` varchar(10) NOT NULL COMMENT '主题字体颜色',
  `navigation` text NOT NULL COMMENT '自定义菜单地址',
  `bottom_text` varchar(255) NOT NULL COMMENT '底部文字颜色',
  `text_selection` varchar(255) NOT NULL COMMENT '底部文字选中颜色',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0启用1禁用',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_theme
-- ----------------------------
INSERT INTO `system_theme` VALUES ('1', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('2', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('3', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('4', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('5', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('6', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('7', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('8', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('9', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '2020', '1583834652', '0');
INSERT INTO `system_theme` VALUES ('10', 'ccvWPn', '13', 'mini', '#00b800', 'white', '[{\"name\":\"首页\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900994f221.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c7900998996c.png\",\"choice_page_name_view\":\"常用链接--首页\",\"choice_page_type\":\"1\",\"choice_page_name\":\"首页\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/index/index/index\"},{\"name\":\"分类\",\"filePut\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaaa5b7.png\",\"filePutSelection\":\"http://tuan.weikejs.com/api/web/./uploads/system/theme/13/2020/02/29/15829774505e5a51aaad630.png\",\"choice_page_name_view\":\"常用链接--商品总分类\",\"choice_page_type\":\"1\",\"choice_page_name\":\"商品总分类\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/classification/classification/classification\"},{\"name\":\"购物车\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099ba266.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338815c790099dbb37.png\",\"choice_page_name_view\":\"常用链接--购物车\",\"choice_page_type\":\"1\",\"choice_page_name\":\"购物车\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/shopCart/shopCart/shopCart\"},{\"name\":\"我的\",\"filePut\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a0e32c.png\",\"filePutSelection\":\"http://juanpao999-1255754174.cos.cn-south.myqcloud.com/system%2Ftheme%2F13%2F2019%2F03%2F01%2F15514338825c79009a2b873.png\",\"choice_page_name_view\":\"常用链接--我的\",\"choice_page_type\":\"1\",\"choice_page_name\":\"我的\",\"choice_app_id\":\"\",\"choice_page_url\":\"/pages/home/my/my\"}]', '#000', '#ff7070', '1', '1582380722', '1583834652', null);

-- ----------------------------
-- Table structure for `system_unit`
-- ----------------------------
DROP TABLE IF EXISTS `system_unit`;
CREATE TABLE `system_unit` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(6) NOT NULL DEFAULT '' COMMENT '专属标识符',
  `title` varchar(256) DEFAULT NULL COMMENT '插件标题',
  `route` varchar(32) NOT NULL DEFAULT '' COMMENT '插件对应的路由',
  `pic_url` varchar(255) NOT NULL DEFAULT '' COMMENT '插件图片',
  `config` text COMMENT '插件配置',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `expire_time` int(11) NOT NULL COMMENT '到期时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1激活 0未激活',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=539 DEFAULT CHARSET=utf8 COMMENT='插件表';

-- ----------------------------
-- Records of system_unit
-- ----------------------------

INSERT INTO `system_unit` VALUES ('320', 'ccvWPn', '自定义版权', 'copyright', 'https://api.juanpao.com/uploads/copyright.png', '\"https://juanpao999-1255754174.cos.ap-guangzhou.myqcloud.com/ui/%E6%B0%B4%E5%8D%B0.png\"', '13', '1590422400', '1', '1558839283', '1582812671', null);
INSERT INTO `system_unit` VALUES ('373', 'ccvWPn', '签到', 'signIn', '', '{\"status\":\"0\"}', '13', '0', '1', '1563517381', '1564989917', null);
INSERT INTO `system_unit` VALUES ('401', 'ccvWPn', '闪送', 'shansong', '', '{\"md5\":\"hfsi0g69pplp\",\"m_id\":\"SS8850\",\"partnerNo\":\"8850\",\"token\":\"Es5ZpQrw/J4mnmSg2zLNdtApN4jYTJMftYK6CiALJKE=\",\"mobile\":\"18961303123\"}', '13', '0', '1', '1565691325', '1575339897', null);

-- ----------------------------
-- Table structure for `system_user_balance`
-- ----------------------------
DROP TABLE IF EXISTS `system_user_balance`;
CREATE TABLE `system_user_balance` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id,0为系统',
  `money` decimal(8,2) NOT NULL COMMENT '金额',
  `content` varchar(32) NOT NULL COMMENT '详细',
  `type` tinyint(1) NOT NULL COMMENT '类型 1=作者获取,2=商户分佣,3=平台服务费',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=正常,2=禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户金额记录表';

-- ----------------------------
-- Records of system_user_balance
-- ----------------------------

-- ----------------------------
-- Table structure for `system_user_recharge`
-- ----------------------------
DROP TABLE IF EXISTS `system_user_recharge`;
CREATE TABLE `system_user_recharge` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `money` decimal(8,2) NOT NULL COMMENT '金额',
  `content` varchar(32) NOT NULL COMMENT '详细',
  `type` tinyint(1) NOT NULL COMMENT '类型 1支付宝,2=微信',
  `other_order_id` varchar(32) DEFAULT NULL COMMENT '第三方订单号',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=成功,2=充值中 3=失败',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统用户充值表';

-- ----------------------------
-- Records of system_user_recharge
-- ----------------------------

-- ----------------------------
-- Table structure for `system_user_withdraw`
-- ----------------------------
DROP TABLE IF EXISTS `system_user_withdraw`;
CREATE TABLE `system_user_withdraw` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` char(6) NOT NULL COMMENT '唯一标识符',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL COMMENT '类型 1支付宝,2=微信',
  `real_name` varchar(20) DEFAULT NULL COMMENT '姓名',
  `account` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `openid` varchar(32) DEFAULT NULL COMMENT '第三方授权id(微信提现需要)',
  `money` decimal(8,2) DEFAULT NULL COMMENT '金额',
  `fee` decimal(8,2) DEFAULT NULL COMMENT '手续费',
  `connent` varchar(50) DEFAULT NULL COMMENT '详细',
  `other_order_id` varchar(32) DEFAULT NULL COMMENT '第三方订单号',
  `status` tinyint(1) NOT NULL COMMENT '状态 1=成功,2=处理中,3=失败',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统用户提现记录表';

-- ----------------------------
-- Records of system_user_withdraw
-- ----------------------------

-- ----------------------------
-- Table structure for `system_video`
-- ----------------------------
DROP TABLE IF EXISTS `system_video`;
CREATE TABLE `system_video` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `secretId` varchar(50) NOT NULL,
  `secretKey` varchar(50) NOT NULL,
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of system_video
-- ----------------------------

-- ----------------------------
-- Table structure for `system_vip`
-- ----------------------------
DROP TABLE IF EXISTS `system_vip`;
CREATE TABLE `system_vip` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'vip名称',
  `money` decimal(8,2) NOT NULL COMMENT '充值金额',
  `discount` double(3,2) NOT NULL COMMENT '折扣',
  `pic_url` varchar(128) NOT NULL COMMENT 'vip图片',
  `detail_info` varchar(255) DEFAULT NULL COMMENT '详细说明',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1有效 0无效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='系统-vip折扣表';

-- ----------------------------
-- Records of system_vip
-- ----------------------------
INSERT INTO `system_vip` VALUES ('1', '青铜vip', '300.00', '0.95', 'https://imgs.juanpao.com/admin%2Fvip%2F15550329895cafeb9d9b063.png', '青铜vip享受95折优惠', '1', '1555032989', '1555147015', null);
INSERT INTO `system_vip` VALUES ('2', '白银vip', '1000.00', '0.90', 'https://imgs.juanpao.com/admin%2Fvip%2F15550330695cafebedef526.png', '白银vip享受9折优惠', '1', '1555033069', '1555033427', null);
INSERT INTO `system_vip` VALUES ('3', '黄金vip', '2000.00', '0.80', 'https://imgs.juanpao.com/admin%2Fvip%2F15550331305cafec2a2a455.png', '黄金vip享受8折优惠', '1', '1555033129', '1555033908', null);
INSERT INTO `system_vip` VALUES ('4', '钻石vip', '5000.00', '0.70', 'https://imgs.juanpao.com/admin%2Fvip%2F15550337885cafeebcd87d6.png', '钻石vip享受7折优惠', '1', '1555033788', '1555033788', null);
INSERT INTO `system_vip` VALUES ('5', '1', '1.00', '1.00', 'https://imgs.juanpao.com/admin%2Fvip%2F15550339355cafef4f1c7ec.png', '1', '1', '1555033934', '1555033934', '1555033958');

-- ----------------------------
-- Table structure for `system_vip_user`
-- ----------------------------
DROP TABLE IF EXISTS `system_vip_user`;
CREATE TABLE `system_vip_user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `merchant_id` int(10) NOT NULL COMMENT '商户id',
  `province_code` char(6) NOT NULL COMMENT '省编码',
  `city_code` char(6) NOT NULL COMMENT '市编码',
  `area_code` char(6) DEFAULT NULL COMMENT '区编码',
  `addr` varchar(128) NOT NULL COMMENT '地址',
  `company_name` varchar(32) NOT NULL COMMENT '公司名称',
  `telephone` varchar(32) NOT NULL COMMENT '联系电话',
  `qq` varchar(11) NOT NULL COMMENT 'QQ',
  `email` varchar(32) NOT NULL COMMENT '邮箱',
  `status` tinyint(1) NOT NULL COMMENT '状态 1审核通过待支付 0审核中  2=审核失败 3=支付成功并开通vip',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='系统-vip申请表';

-- ----------------------------
-- Records of system_vip_user
-- ----------------------------
INSERT INTO `system_vip_user` VALUES ('2', '13', '320000', '320700', '320706', '凌州东路', '公司', '15366669450', '272074691', '272074691@qq.com', '3', '1555125063', '1555319774', null);

-- ----------------------------
-- Table structure for `system_voucher`
-- ----------------------------
DROP TABLE IF EXISTS `system_voucher`;
CREATE TABLE `system_voucher` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cdkey` varchar(32) NOT NULL DEFAULT '' COMMENT '抵用券码',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '面值',
  `full_price` int(6) NOT NULL DEFAULT '0' COMMENT '到达满减条件的金额，0为无要求',
  `is_exchange` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否兑换',
  `is_used` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否使用',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始生效时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '生效结束时间',
  `type_name` varchar(16) DEFAULT NULL COMMENT '代金券类型名称',
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT '代金券类型id',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态 0无效 1有效',
  `merchant_id` int(11) NOT NULL COMMENT '商户id,不能为0',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='抵用券表';


-- ----------------------------
-- Table structure for `system_voucher_channel`
-- ----------------------------
DROP TABLE IF EXISTS `system_voucher_channel`;
CREATE TABLE `system_voucher_channel` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `act_name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='抵用券活动名称';



-- ----------------------------
-- Table structure for `system_voucher_type`
-- ----------------------------
DROP TABLE IF EXISTS `system_voucher_type`;
CREATE TABLE `system_voucher_type` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(16) NOT NULL COMMENT '抵用券名称',
  `price` decimal(10,2) NOT NULL COMMENT '面值',
  `full_price` int(11) NOT NULL COMMENT '到达满减条件的金额，0为无要求',
  `send_count` int(11) NOT NULL DEFAULT '0' COMMENT '已发放数量',
  `count` int(11) NOT NULL COMMENT '发放总量',
  `days` int(11) NOT NULL COMMENT '领取后有效天数',
  `act_id` int(11) NOT NULL COMMENT '活动id',
  `from_date` int(11) NOT NULL COMMENT '开始时间',
  `to_date` int(11) NOT NULL COMMENT '结束时间',
  `set_online_time` int(11) NOT NULL DEFAULT '0' COMMENT '上一次上线时间',
  `status` tinyint(11) NOT NULL DEFAULT '0' COMMENT '状态 0无效 1有效',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='抵用券类型';


-- ----------------------------
-- Table structure for `system_wx_config`
-- ----------------------------
DROP TABLE IF EXISTS `system_wx_config`;
CREATE TABLE `system_wx_config` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `app_id` varchar(50) NOT NULL COMMENT 'app_id',
  `open_app_id` varchar(50) NOT NULL COMMENT '开放平台id',
  `key` varchar(50) DEFAULT NULL COMMENT '键',
  `wechat_info` text NOT NULL COMMENT '微信信息',
  `wechat` text NOT NULL COMMENT 'json{"type":(0 未配置 1 手工录入 2 授权录入)}',
  `miniprogram_id` varchar(50) NOT NULL,
  `miniprogram` text NOT NULL COMMENT 'json{"type":(0 未配置 1 手工录入 2 授权录入)}',
  `wechat_pay` text NOT NULL,
  `miniprogram_pay` text NOT NULL,
  `saobei` text NOT NULL COMMENT '扫呗支付',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1可用 0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `wx_pay_type` tinyint(1) DEFAULT '1' COMMENT '1微信支付 2 扫呗',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=636 DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- ----------------------------
-- Records of system_wx_config
-- ----------------------------
INSERT INTO `system_wx_config` VALUES ('331', '13', '', '', 'ccvWPn', '', '', '', '{\"app_id\":\"\",\"secret\":\"\"}', '', '{\"key\":\"\",\"app_id\":\"\",\"mch_id\":\"\",\"wx_pay_type\":\"1\",\"cert_path\":\"/uploads/pem/13/apiclient_cert.pem\",\"key_path\":\"/uploads/pem/13/apiclient_key.pem\"}', '{\"app_id\":\"wxb1d07a2d8ae4c0fb\",\"merchant_no\":\"\",\"terminal_id\":\"\",\"saobei_access_token\":\"\"}', '1', '1558575631', '1583805241', null, '1');

-- ----------------------------
-- Table structure for `tencent_instance_log`
-- ----------------------------
DROP TABLE IF EXISTS `tencent_instance_log`;
CREATE TABLE `tencent_instance_log` (
  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `action` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '腾讯云请求方法',
  `orderId` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '腾讯云订单id',
  `openId` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '腾讯云用户openid',
  `productId` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '云市场产品ID',
  `requestId` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '接口请求的ID',
  `is_pay` int(1) NOT NULL DEFAULT '0' COMMENT '是否是购买的0 不是1 是',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `remark` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '备注',
  `info` text CHARACTER SET utf8 NOT NULL COMMENT '接口返回信息详情',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `update_time` int(11) DEFAULT '0' COMMENT '跟新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;



-- ----------------------------
-- Table structure for `testsign`
-- ----------------------------
DROP TABLE IF EXISTS `testsign`;
CREATE TABLE `testsign` (
  `userid` int(5) DEFAULT NULL,
  `username` varchar(20) DEFAULT NULL,
  `signtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` int(1) DEFAULT '0' COMMENT '为0表示签到数据，1表示签到日期字典数据'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wolive_business`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_business`;
CREATE TABLE `wolive_business` (
  `wid` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` varchar(100) NOT NULL COMMENT '商家id',
  `video_state` enum('close','open') NOT NULL DEFAULT 'close' COMMENT '是否开启视频',
  `voice_state` enum('close','open') NOT NULL DEFAULT 'open' COMMENT '是否开启提示音',
  `audio_state` enum('close','open') NOT NULL DEFAULT 'open' COMMENT '是否开启音频',
  `distribution_rule` enum('auto','claim') DEFAULT 'auto' COMMENT 'claim:认领，auto:自动分配',
  `voice_address` varchar(255) NOT NULL DEFAULT '/upload/voice/default.mp3' COMMENT '提示音文件地址',
  `state` enum('close','open') NOT NULL DEFAULT 'open' COMMENT '''open'': 打开该商户 ，‘close’：禁止该商户',
  PRIMARY KEY (`wid`),
  UNIQUE KEY `bussiness` (`business_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=582 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `wolive_chats`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_chats`;
CREATE TABLE `wolive_chats` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `visiter_id` varchar(200) NOT NULL COMMENT '访客id',
  `service_id` int(11) NOT NULL COMMENT '客服id',
  `business_id` varchar(100) NOT NULL COMMENT '商家id',
  `content` mediumtext NOT NULL COMMENT '内容',
  `timestamp` int(11) NOT NULL,
  `state` enum('readed','unread') NOT NULL DEFAULT 'unread' COMMENT 'unread 未读；readed 已读',
  `direction` enum('to_visiter','to_service') DEFAULT NULL,
  PRIMARY KEY (`cid`),
  KEY `visiter` (`visiter_id`) USING BTREE,
  KEY `service` (`service_id`) USING BTREE,
  KEY `time` (`timestamp`) USING BTREE,
  KEY `chat` (`business_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8;



-- ----------------------------
-- Table structure for `wolive_group`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_group`;
CREATE TABLE `wolive_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(255) DEFAULT NULL,
  `business_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_group
-- ----------------------------

-- ----------------------------
-- Table structure for `wolive_message`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_message`;
CREATE TABLE `wolive_message` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '留言内容',
  `name` varchar(255) NOT NULL COMMENT '留言人姓名',
  `moblie` varchar(255) NOT NULL COMMENT '留言人电话',
  `email` varchar(255) NOT NULL COMMENT '留言人邮箱',
  `business_id` varchar(100) DEFAULT NULL COMMENT '商家id',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`mid`),
  KEY `timestamp` (`timestamp`),
  KEY `web` (`business_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_message
-- ----------------------------
INSERT INTO `wolive_message` VALUES ('1', '333', '222', '111', '222', 'iGhRmF', '2019-02-01 06:25:02');
INSERT INTO `wolive_message` VALUES ('2', '55555', '2122', '333', '444', '${sessionStorage.shopkey}', '2019-02-13 06:05:29');

-- ----------------------------
-- Table structure for `wolive_question`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_question`;
CREATE TABLE `wolive_question` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` varchar(225) NOT NULL,
  `question` longtext NOT NULL,
  `answer` longtext NOT NULL,
  `answer_read` longtext NOT NULL,
  PRIMARY KEY (`qid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_question
-- ----------------------------
INSERT INTO `wolive_question` VALUES ('2', 'zsIKVN', '看尽落花能几醉', '<p>看尽落花能几醉看<br>尽落花能几醉', '看尽落花能几醉看\n尽落花能几醉');
INSERT INTO `wolive_question` VALUES ('3', 'Ouupyg', '问题', '<p>回答</p>', '回答');

-- ----------------------------
-- Table structure for `wolive_queue`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_queue`;
CREATE TABLE `wolive_queue` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `visiter_id` varchar(200) NOT NULL COMMENT '访客id',
  `service_id` int(11) NOT NULL COMMENT '客服id',
  `groupid` int(11) DEFAULT '0' COMMENT '客服分类id',
  `business_id` varchar(100) NOT NULL COMMENT '商户id',
  `state` enum('normal','complete','in_black_list') NOT NULL DEFAULT 'normal' COMMENT 'normal：正常接入,‘complete’:已经解决，‘in_black_list’:黑名单',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`qid`),
  KEY `se` (`service_id`) USING BTREE,
  KEY `vi` (`visiter_id`) USING BTREE,
  KEY `business` (`business_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `wolive_reply`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_reply`;
CREATE TABLE `wolive_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_reply
-- ----------------------------
INSERT INTO `wolive_reply` VALUES ('1', '123', '4', '12');
INSERT INTO `wolive_reply` VALUES ('2', '我很好的', '8', '好的');

-- ----------------------------
-- Table structure for `wolive_sentence`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_sentence`;
CREATE TABLE `wolive_sentence` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '内容',
  `service_id` int(11) NOT NULL COMMENT '所属客服id',
  `state` enum('using','unuse') DEFAULT 'unuse' COMMENT 'unuse: 未使用 ，using：使用中',
  PRIMARY KEY (`sid`),
  KEY `se` (`service_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_sentence
-- ----------------------------
INSERT INTO `wolive_sentence` VALUES ('1', '哈哈哈', '8', 'unuse');
INSERT INTO `wolive_sentence` VALUES ('2', '您好，有什么可以帮您？', '20', 'unuse');
INSERT INTO `wolive_sentence` VALUES ('3', '你好，欢迎光临卷泡小程序', '30', 'unuse');
INSERT INTO `wolive_sentence` VALUES ('4', '您好，欢迎光临区小蜜', '49', 'using');

-- ----------------------------
-- Table structure for `wolive_service`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_service`;
CREATE TABLE `wolive_service` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` smallint(8) NOT NULL COMMENT '商户id',
  `key` char(6) NOT NULL DEFAULT '' COMMENT '应用key',
  `user_name` varchar(255) NOT NULL COMMENT '用户名',
  `nick_name` varchar(255) NOT NULL DEFAULT '在线客服' COMMENT '固定 在线客服',
  `real_name` varchar(255) DEFAULT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `salt` varchar(255) DEFAULT NULL COMMENT '盐',
  `groupid` varchar(225) DEFAULT '0' COMMENT '客服分类id',
  `phone` varchar(255) DEFAULT '' COMMENT '手机',
  `email` varchar(255) DEFAULT '' COMMENT '邮箱',
  `business_id` varchar(255) NOT NULL COMMENT '商家id',
  `avatar` varchar(1024) NOT NULL DEFAULT '/assets/images/admin/avatar-admin2.png' COMMENT '头像',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否删除 1=已删 0=未删',
  `level` enum('super_manager','manager','service') NOT NULL DEFAULT 'service' COMMENT 'super_manager: 超级管理员，manager：商家管理员 ，service：普通客服',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属商家管理员id',
  `state` enum('online','offline') NOT NULL DEFAULT 'offline' COMMENT 'online：在线，offline：离线',
  PRIMARY KEY (`service_id`),
  UNIQUE KEY `se` (`service_id`) USING BTREE,
  KEY `pid` (`parent_id`) USING BTREE,
  KEY `web` (`business_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=598 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `wolive_tablist`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_tablist`;
CREATE TABLE `wolive_tablist` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'tab的名称',
  `content_read` text,
  `content` text NOT NULL,
  `business_id` varchar(2555) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_tablist
-- ----------------------------
INSERT INTO `wolive_tablist` VALUES ('4', '123', '123', '<p>123</p>\n', 'wLCSUf');

-- ----------------------------
-- Table structure for `wolive_visiter`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_visiter`;
CREATE TABLE `wolive_visiter` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `visiter_id` varchar(200) NOT NULL COMMENT '访客id',
  `visiter_name` varchar(255) NOT NULL COMMENT '访客名称',
  `channel` varchar(255) NOT NULL COMMENT '用户游客频道',
  `avatar` varchar(1024) NOT NULL COMMENT '头像',
  `connect` text COMMENT '联系方式',
  `comment` text COMMENT '备注',
  `ip` varchar(255) NOT NULL COMMENT '访客ip',
  `from_url` varchar(255) NOT NULL COMMENT '访客浏览地址',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '访问时间',
  `business_id` varchar(100) NOT NULL COMMENT '商户id',
  `state` enum('online','offline') NOT NULL DEFAULT 'offline' COMMENT 'offline：离线，online：在线',
  PRIMARY KEY (`vid`),
  UNIQUE KEY `id` (`visiter_id`,`business_id`) USING BTREE,
  KEY `visiter` (`visiter_id`) USING BTREE,
  KEY `time` (`timestamp`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `wolive_weixin`
-- ----------------------------
DROP TABLE IF EXISTS `wolive_weixin`;
CREATE TABLE `wolive_weixin` (
  `wid` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL COMMENT '客服id',
  `open_id` varchar(255) NOT NULL COMMENT '微信用户id',
  PRIMARY KEY (`wid`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wolive_weixin
-- ----------------------------
