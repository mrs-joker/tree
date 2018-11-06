# tree
tree everything


table
CREATE TABLE `trees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类ID',
  `tree_key` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一健值',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `additional_data` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '附加数据',
  `order_num` int(10) unsigned DEFAULT '999' COMMENT '排序',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trees_tree_key_unique` (`tree_key`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
