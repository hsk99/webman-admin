/*
 Navicat Premium Data Transfer

 Source Server         : 81.70.133.191
 Source Server Type    : MySQL
 Source Server Version : 50737
 Source Host           : 81.70.133.191:3306
 Source Schema         : webmanadmin

 Target Server Type    : MySQL
 Target Server Version : 50737
 File Encoding         : 65001

 Date: 18/04/2022 11:38:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_admin
-- ----------------------------
DROP TABLE IF EXISTS `admin_admin`;
CREATE TABLE `admin_admin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名，登陆使用',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户密码',
  `nickname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户昵称',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '用户状态：1正常,2禁用 默认1',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_admin
-- ----------------------------
INSERT INTO `admin_admin` VALUES (1, 'admin', '3af7ff95bfa27f340ff3cd9e932e747e', '超级管理员', 1, NULL, '2022-04-15 17:06:44', NULL);
INSERT INTO `admin_admin` VALUES (2, 'hsk', '2a9172be56de8da535f378a023399b81', 'hsk', 1, '2022-04-15 17:04:08', '2022-04-15 17:06:39', NULL);

-- ----------------------------
-- Table structure for admin_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_admin_log`;
CREATE TABLE `admin_admin_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(11) NULL DEFAULT NULL COMMENT '管理员ID',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作页面',
  `desc` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '日志内容',
  `ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作IP',
  `user_agent` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'User-Agent',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_admin_log
-- ----------------------------

-- ----------------------------
-- Table structure for admin_admin_permission
-- ----------------------------
DROP TABLE IF EXISTS `admin_admin_permission`;
CREATE TABLE `admin_admin_permission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) NULL DEFAULT NULL COMMENT '用户ID',
  `permission_id` int(11) NULL DEFAULT NULL COMMENT '权限ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 106 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理-权限中间表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_admin_permission
-- ----------------------------
INSERT INTO `admin_admin_permission` VALUES (16, 1, 163);
INSERT INTO `admin_admin_permission` VALUES (17, 1, 169);
INSERT INTO `admin_admin_permission` VALUES (73, 2, 163);
INSERT INTO `admin_admin_permission` VALUES (74, 2, 164);
INSERT INTO `admin_admin_permission` VALUES (75, 2, 165);
INSERT INTO `admin_admin_permission` VALUES (76, 2, 166);
INSERT INTO `admin_admin_permission` VALUES (77, 2, 167);
INSERT INTO `admin_admin_permission` VALUES (78, 2, 168);
INSERT INTO `admin_admin_permission` VALUES (79, 2, 169);
INSERT INTO `admin_admin_permission` VALUES (80, 2, 1);
INSERT INTO `admin_admin_permission` VALUES (81, 2, 2);
INSERT INTO `admin_admin_permission` VALUES (82, 2, 6);
INSERT INTO `admin_admin_permission` VALUES (83, 2, 3);
INSERT INTO `admin_admin_permission` VALUES (84, 2, 4);
INSERT INTO `admin_admin_permission` VALUES (85, 2, 5);
INSERT INTO `admin_admin_permission` VALUES (86, 2, 7);
INSERT INTO `admin_admin_permission` VALUES (87, 2, 8);
INSERT INTO `admin_admin_permission` VALUES (88, 2, 9);
INSERT INTO `admin_admin_permission` VALUES (89, 2, 10);
INSERT INTO `admin_admin_permission` VALUES (90, 2, 11);
INSERT INTO `admin_admin_permission` VALUES (91, 2, 12);
INSERT INTO `admin_admin_permission` VALUES (92, 2, 13);
INSERT INTO `admin_admin_permission` VALUES (93, 2, 14);
INSERT INTO `admin_admin_permission` VALUES (94, 2, 15);
INSERT INTO `admin_admin_permission` VALUES (95, 2, 16);
INSERT INTO `admin_admin_permission` VALUES (96, 2, 107);
INSERT INTO `admin_admin_permission` VALUES (97, 2, 17);
INSERT INTO `admin_admin_permission` VALUES (98, 2, 18);
INSERT INTO `admin_admin_permission` VALUES (99, 2, 19);
INSERT INTO `admin_admin_permission` VALUES (100, 2, 20);
INSERT INTO `admin_admin_permission` VALUES (101, 2, 21);
INSERT INTO `admin_admin_permission` VALUES (102, 2, 110);
INSERT INTO `admin_admin_permission` VALUES (103, 2, 22);
INSERT INTO `admin_admin_permission` VALUES (104, 2, 94);
INSERT INTO `admin_admin_permission` VALUES (105, 2, 95);

-- ----------------------------
-- Table structure for admin_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `admin_admin_role`;
CREATE TABLE `admin_admin_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(11) NULL DEFAULT NULL COMMENT '用户ID',
  `role_id` int(11) NULL DEFAULT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理-角色中间表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_admin_role
-- ----------------------------
INSERT INTO `admin_admin_role` VALUES (6, 1, 1);
INSERT INTO `admin_admin_role` VALUES (7, 1, 2);
INSERT INTO `admin_admin_role` VALUES (11, 2, 1);

-- ----------------------------
-- Table structure for admin_file
-- ----------------------------
DROP TABLE IF EXISTS `admin_file`;
CREATE TABLE `admin_file`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文件名称',
  `href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文件路径',
  `mime` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'mime类型',
  `size` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '大小',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1--本地  2--阿里云  3--七牛云',
  `ext` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '文件后缀',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文件表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_file
-- ----------------------------

-- ----------------------------
-- Table structure for admin_permission
-- ----------------------------
DROP TABLE IF EXISTS `admin_permission`;
CREATE TABLE `admin_permission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '父级ID',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '名称',
  `href` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '地址',
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '图标',
  `sort` tinyint(4) NOT NULL DEFAULT 1 COMMENT '排序',
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '菜单',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 111 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_permission
-- ----------------------------
INSERT INTO `admin_permission` VALUES (1, 0, '后台权限', '', 'layui-icon layui-icon-username', 98, 0, 1);
INSERT INTO `admin_permission` VALUES (2, 1, '管理员', '/admin/admin/index', '', 1, 1, 1);
INSERT INTO `admin_permission` VALUES (3, 2, '新增管理员', '/admin/admin/add', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (4, 2, '编辑管理员', '/admin/admin/edit', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (5, 2, '修改管理员状态', '/admin/admin/status', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (6, 2, '删除管理员', '/admin/admin/remove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (7, 2, '批量删除管理员', '/admin/admin/batchRemove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (8, 2, '管理员分配角色', '/admin/admin/role', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (9, 2, '管理员分配直接权限', '/admin/admin/permission', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (10, 2, '管理员回收站', '/admin/admin/recycle', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (11, 1, '角色管理', '/admin/role/index', '', 2, 1, 1);
INSERT INTO `admin_permission` VALUES (12, 11, '新增角色', '/admin/role/add', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (13, 11, '编辑角色', '/admin/role/edit', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (14, 11, '删除角色', '/admin/role/remove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (15, 11, '角色分配权限', '/admin/role/permission', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (16, 11, '角色回收站', '/admin/role/recycle', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (17, 1, '菜单权限', '/admin/permission/index', '', 3, 1, 1);
INSERT INTO `admin_permission` VALUES (18, 17, '新增菜单', '/admin/permission/add', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (19, 17, '编辑菜单', '/admin/permission/edit', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (20, 17, '修改菜单状态', '/admin/permission/status', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (21, 17, '删除菜单', '/admin/permission/remove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (22, 0, '系统管理', '', 'layui-icon layui-icon-set', 99, 0, 1);
INSERT INTO `admin_permission` VALUES (23, 22, '后台日志', '/admin/admin/log', '', 2, 1, 1);
INSERT INTO `admin_permission` VALUES (24, 23, '清空管理员日志', '/admin/admin/removelog', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (25, 22, '系统设置', '/config/index', '', 4, 1, 1);
INSERT INTO `admin_permission` VALUES (94, 22, '文件管理', '/admin/file/index', '', 1, 1, 1);
INSERT INTO `admin_permission` VALUES (95, 94, '新增文件', '/admin/file/add', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (96, 94, '修改文件', '/admin/file/edit', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (97, 94, '删除文件', '/admin/file/remove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (98, 94, '批量删除文件', '/admin/file/batchremove', 'layui-icon layui-icon layui-icon-face-smile', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (99, 94, '回收站文件', '/admin/file/recycle', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (107, 11, '批量删除', '/admin/admin/batchremove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (108, 94, '添加多文件', '/admin/file/adds', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (109, 94, '批量删除', '/admin/file/batchremove', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (110, 17, '生成菜单', '/admin/permission/generate', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (111, 25, '清除应用监控数据', '/config/transferclear', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (112, 22, '应用监控', '/transferstatistics/index', '', 3, 1, 1);
INSERT INTO `admin_permission` VALUES (113, 112, '调用记录', '/transferstatistics/tracinglist', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (114, 112, '调用入口', '/transferstatistics/transfer', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (115, 112, '入口调用记录', '/transferstatistics/transfertracinglist', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (116, 112, '调用IP', '/transferstatistics/ip', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (117, 112, 'IP调用记录', '/transferstatistics/iptracinglist', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (118, 112, '状态码', '/transferstatistics/code', '', 1, 1, 2);
INSERT INTO `admin_permission` VALUES (119, 112, '状态码调用记录', '/transferstatistics/codetracinglist', '', 1, 1, 2);



-- ----------------------------
-- Table structure for admin_role
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `desc` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_role
-- ----------------------------
INSERT INTO `admin_role` VALUES (1, '超级管理员', '拥有所有管理权限', '2020-09-01 11:01:34', '2022-04-18 11:17:32', NULL);
INSERT INTO `admin_role` VALUES (2, '管理员', '', '2022-04-15 15:27:57', '2022-04-17 02:17:58', NULL);

-- ----------------------------
-- Table structure for admin_role_permission
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_permission`;
CREATE TABLE `admin_role_permission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` int(11) NULL DEFAULT NULL COMMENT '角色ID',
  `permission_id` int(11) NULL DEFAULT NULL COMMENT '权限ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 642 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色-权限中间表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_role_permission
-- ----------------------------
INSERT INTO `admin_role_permission` VALUES (565, 2, 163);
INSERT INTO `admin_role_permission` VALUES (566, 2, 164);
INSERT INTO `admin_role_permission` VALUES (567, 2, 165);
INSERT INTO `admin_role_permission` VALUES (568, 2, 166);
INSERT INTO `admin_role_permission` VALUES (569, 2, 167);
INSERT INTO `admin_role_permission` VALUES (570, 2, 168);
INSERT INTO `admin_role_permission` VALUES (571, 2, 169);
INSERT INTO `admin_role_permission` VALUES (607, 1, 1);
INSERT INTO `admin_role_permission` VALUES (608, 1, 2);
INSERT INTO `admin_role_permission` VALUES (609, 1, 3);
INSERT INTO `admin_role_permission` VALUES (610, 1, 4);
INSERT INTO `admin_role_permission` VALUES (611, 1, 5);
INSERT INTO `admin_role_permission` VALUES (612, 1, 6);
INSERT INTO `admin_role_permission` VALUES (613, 1, 7);
INSERT INTO `admin_role_permission` VALUES (614, 1, 8);
INSERT INTO `admin_role_permission` VALUES (615, 1, 9);
INSERT INTO `admin_role_permission` VALUES (616, 1, 10);
INSERT INTO `admin_role_permission` VALUES (617, 1, 11);
INSERT INTO `admin_role_permission` VALUES (618, 1, 12);
INSERT INTO `admin_role_permission` VALUES (619, 1, 13);
INSERT INTO `admin_role_permission` VALUES (620, 1, 14);
INSERT INTO `admin_role_permission` VALUES (621, 1, 15);
INSERT INTO `admin_role_permission` VALUES (622, 1, 16);
INSERT INTO `admin_role_permission` VALUES (623, 1, 107);
INSERT INTO `admin_role_permission` VALUES (624, 1, 17);
INSERT INTO `admin_role_permission` VALUES (625, 1, 18);
INSERT INTO `admin_role_permission` VALUES (626, 1, 110);
INSERT INTO `admin_role_permission` VALUES (627, 1, 19);
INSERT INTO `admin_role_permission` VALUES (628, 1, 20);
INSERT INTO `admin_role_permission` VALUES (629, 1, 21);
INSERT INTO `admin_role_permission` VALUES (630, 1, 22);
INSERT INTO `admin_role_permission` VALUES (631, 1, 94);
INSERT INTO `admin_role_permission` VALUES (632, 1, 108);
INSERT INTO `admin_role_permission` VALUES (633, 1, 109);
INSERT INTO `admin_role_permission` VALUES (634, 1, 95);
INSERT INTO `admin_role_permission` VALUES (635, 1, 96);
INSERT INTO `admin_role_permission` VALUES (636, 1, 97);
INSERT INTO `admin_role_permission` VALUES (637, 1, 98);
INSERT INTO `admin_role_permission` VALUES (638, 1, 99);
INSERT INTO `admin_role_permission` VALUES (639, 1, 23);
INSERT INTO `admin_role_permission` VALUES (640, 1, 24);
INSERT INTO `admin_role_permission` VALUES (641, 1, 25);

-- ----------------------------
-- Table structure for casbin_rule
-- ----------------------------
DROP TABLE IF EXISTS `casbin_rule`;
CREATE TABLE `casbin_rule`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `ptype` char(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '规则类型',
  `v0` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `v1` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `v2` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `v3` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `v4` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `v5` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 31 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'casbin_rule表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of casbin_rule
-- ----------------------------
INSERT INTO `casbin_rule` VALUES (1, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'index', '', '', '');
INSERT INTO `casbin_rule` VALUES (2, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'add', '', '', '');
INSERT INTO `casbin_rule` VALUES (3, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'edit', '', '', '');
INSERT INTO `casbin_rule` VALUES (4, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'status', '', '', '');
INSERT INTO `casbin_rule` VALUES (5, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'remove', '', '', '');
INSERT INTO `casbin_rule` VALUES (6, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'batchRemove', '', '', '');
INSERT INTO `casbin_rule` VALUES (7, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'role', '', '', '');
INSERT INTO `casbin_rule` VALUES (8, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'permission', '', '', '');
INSERT INTO `casbin_rule` VALUES (9, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'recycle', '', '', '');
INSERT INTO `casbin_rule` VALUES (10, 'p', 'admin_role_1', '\\controller\\admin\\Role', 'index', '', '', '');
INSERT INTO `casbin_rule` VALUES (11, 'p', 'admin_role_1', '\\controller\\admin\\Role', 'add', '', '', '');
INSERT INTO `casbin_rule` VALUES (12, 'p', 'admin_role_1', '\\controller\\admin\\Role', 'edit', '', '', '');
INSERT INTO `casbin_rule` VALUES (13, 'p', 'admin_role_1', '\\controller\\admin\\Role', 'remove', '', '', '');
INSERT INTO `casbin_rule` VALUES (14, 'p', 'admin_role_1', '\\controller\\admin\\Role', 'permission', '', '', '');
INSERT INTO `casbin_rule` VALUES (15, 'p', 'admin_role_1', '\\controller\\admin\\Role', 'recycle', '', '', '');
INSERT INTO `casbin_rule` VALUES (16, 'p', 'admin_role_1', '\\controller\\admin\\Permission', 'index', '', '', '');
INSERT INTO `casbin_rule` VALUES (17, 'p', 'admin_role_1', '\\controller\\admin\\Permission', 'add', '', '', '');
INSERT INTO `casbin_rule` VALUES (18, 'p', 'admin_role_1', '\\controller\\admin\\Permission', 'edit', '', '', '');
INSERT INTO `casbin_rule` VALUES (19, 'p', 'admin_role_1', '\\controller\\admin\\Permission', 'status', '', '', '');
INSERT INTO `casbin_rule` VALUES (20, 'p', 'admin_role_1', '\\controller\\admin\\Permission', 'remove', '', '', '');
INSERT INTO `casbin_rule` VALUES (21, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'log', '', '', '');
INSERT INTO `casbin_rule` VALUES (22, 'p', 'admin_role_1', '\\controller\\admin\\Admin', 'removeLog', '', '', '');
INSERT INTO `casbin_rule` VALUES (23, 'p', 'admin_role_1', '\\controller\\Config', 'index', '', '', '');
INSERT INTO `casbin_rule` VALUES (24, 'p', 'admin_role_1', '\\controller\\admin\\File', 'index', '', '', '');
INSERT INTO `casbin_rule` VALUES (25, 'p', 'admin_role_1', '\\controller\\admin\\File', 'add', '', '', '');
INSERT INTO `casbin_rule` VALUES (26, 'p', 'admin_role_1', '\\controller\\admin\\File', 'remove', '', '', '');
INSERT INTO `casbin_rule` VALUES (27, 'p', 'admin_role_1', '\\controller\\admin\\File', 'batchRemove', '', '', '');
INSERT INTO `casbin_rule` VALUES (28, 'p', 'admin_role_1', '\\controller\\admin\\File', 'adds', '', '', '');
INSERT INTO `casbin_rule` VALUES (29, 'p', 'admin_role_1', '\\controller\\admin\\Permission', 'generate', '', '', '');
INSERT INTO `casbin_rule` VALUES (30, 'g', 'admin_admin_2', 'admin_role_1', '', '', '', '');

SET FOREIGN_KEY_CHECKS = 1;
