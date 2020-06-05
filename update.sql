ALTER TABLE `shop_assemble` COMMENT '商品拼团信息表';
ALTER TABLE `shop_assemble_access` COMMENT '拼团订单记录表';
ALTER TABLE `shop_user` ADD `level_id` int(8) DEFAULT 0 COMMENT '分销小等级ID';
ALTER TABLE `shop_user` ADD `up_level_id` int(8) DEFAULT 0 COMMENT '手动审核的分销小等级ID';
ALTER TABLE `shop_stock` ADD `assemble_price` decimal(10, 2) NOT NULL COMMENT '拼团价';
ALTER TABLE `shop_order_group` ADD `commissions_pool` decimal(11, 2) NOT NULL DEFAULT 0.00 COMMENT '未分配完的分销佣金';
ALTER TABLE `shop_distribution_access` MODIFY COLUMN `type` int(11) NOT NULL DEFAULT '1' COMMENT '佣金来源 1=下线提佣 2=股权分佣 3=自购提佣 4=退款减佣';

