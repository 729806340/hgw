DROP TABLE IF EXISTS `shopwwi_open_app`;
CREATE TABLE `shopwwi_open_app` (
    `store_id`      int NOT NULL COMMENT '店铺id',
    `secret`        varchar(255) NOT NULL COMMENT '应用密钥',
    `status`        tinyint NOT NULL DEFAULT 0 COMMENT '状态：0.禁用 1.正常',
    `token`         varchar(255) NOT NULL DEFAULT '' COMMENT '通信令牌',
    `expires_in`    int NOT NULL DEFAULT 0 COMMENT '过期时间',
    PRIMARY KEY (`store_id`),
    UNIQUE INDEX `token` (`token`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
