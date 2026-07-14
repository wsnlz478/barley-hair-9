-- ============================================
-- Barley 表单数据管理系统 - 数据库初始化脚本
-- ============================================
-- 使用说明：
-- 1. 登录 phpMyAdmin 或 MySQL 命令行
-- 2. 导入此 SQL 文件
-- 3. 会自动创建数据库和表结构
-- 4. 会清空所有旧数据（谨慎使用）
-- ============================================

-- 创建数据库（如果不存在）
CREATE DATABASE IF NOT EXISTS `barley_form` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 使用数据库
USE `barley_form`;

-- 清空旧表（如果存在）
DROP TABLE IF EXISTS `form_submissions`;

-- 创建表单提交记录表
CREATE TABLE `form_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `nickname` VARCHAR(255) NOT NULL COMMENT '用户昵称',
  `project` VARCHAR(255) NOT NULL COMMENT '咨询项目',
  `contact` VARCHAR(255) NOT NULL COMMENT '联系方式',
  `message` TEXT COMMENT '留言内容',
  `site_domain` VARCHAR(255) DEFAULT '' COMMENT '来源域名',
  `site_url` VARCHAR(500) DEFAULT '' COMMENT '来源页面URL',
  `user_ip` VARCHAR(45) DEFAULT '' COMMENT '用户IP地址',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '提交时间',
  PRIMARY KEY (`id`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_project` (`project`),
  INDEX `idx_site_domain` (`site_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='表单提交记录表';

-- 插入测试数据（可选，如需测试可取消注释）
-- INSERT INTO `form_submissions` (`nickname`, `project`, `contact`, `message`, `site_domain`, `site_url`, `user_ip`) VALUES
-- ('张三', '发际线种植', '13800138000', '我想咨询发际线种植', 'barleyhair.com', 'https://barleyhair.com/index.html', '127.0.0.1'),
-- ('李四', '头发加密', '13900139000', '头发稀疏怎么办', 'barleyhair.com', 'https://barleyhair.com/services.html', '127.0.0.1'),
-- ('王五', '秃顶植发', '13700137000', '秃顶可以治疗吗', 'barleyhair.com', 'https://barleyhair.com/contact.html', '127.0.0.1');

-- 显示表结构
DESCRIBE `form_submissions`;

-- 显示创建成功提示
SELECT '✅ 数据库初始化成功！' AS message;
