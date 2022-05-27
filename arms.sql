/*
 Navicat Premium Data Transfer

 Source Server         : 192.168.0.13
 Source Server Type    : MariaDB
 Source Server Version : 100331
 Source Host           : 192.168.0.13:3306
 Source Schema         : arms

 Target Server Type    : MariaDB
 Target Server Version : 100331
 File Encoding         : 65001

 Date: 27/05/2022 14:31:40
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for access
-- ----------------------------
DROP TABLE IF EXISTS `access`;
CREATE TABLE `access`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `modul` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `groupId` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `groupId`(`groupId`) USING BTREE,
  CONSTRAINT `access_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of access
-- ----------------------------
INSERT INTO `access` VALUES (2, 'cfg', 1);
INSERT INTO `access` VALUES (3, 'vcm', 1);
INSERT INTO `access` VALUES (5, 'rbl', 1);
INSERT INTO `access` VALUES (6, 'pln', 1);
INSERT INTO `access` VALUES (7, 'main', 1);
INSERT INTO `access` VALUES (8, 'spc', 1);
INSERT INTO `access` VALUES (14, 'spc', 8);

-- ----------------------------
-- Table structure for classes
-- ----------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `yearId` int(10) UNSIGNED NOT NULL,
  `parallel` tinyint(3) UNSIGNED NOT NULL,
  `teacherId` int(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1738 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of classes
-- ----------------------------

-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of groups
-- ----------------------------
INSERT INTO `groups` VALUES (1, 0, 'Работники');
INSERT INTO `groups` VALUES (2, 0, 'Ученики');
INSERT INTO `groups` VALUES (3, 1, 'Учителя');
INSERT INTO `groups` VALUES (4, 1, 'Медицинские работники');
INSERT INTO `groups` VALUES (5, 1, 'Завучи');
INSERT INTO `groups` VALUES (6, 3, 'Классный руководитель');
INSERT INTO `groups` VALUES (7, 1, 'Психолог');
INSERT INTO `groups` VALUES (8, 1, 'Логопед');
INSERT INTO `groups` VALUES (10, 1, 'Социальный педагог');

-- ----------------------------
-- Table structure for ink_commission_classes_data
-- ----------------------------
DROP TABLE IF EXISTS `ink_commission_classes_data`;
CREATE TABLE `ink_commission_classes_data`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `classId` int(10) UNSIGNED NOT NULL,
  `commissionId` int(10) UNSIGNED NOT NULL,
  `commissionNum` tinyint(3) UNSIGNED NOT NULL,
  `val` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 39 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_commission_classes_data
-- ----------------------------

-- ----------------------------
-- Table structure for ink_commission_data
-- ----------------------------
DROP TABLE IF EXISTS `ink_commission_data`;
CREATE TABLE `ink_commission_data`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` int(10) UNSIGNED NOT NULL,
  `commissionId` int(10) UNSIGNED NOT NULL,
  `commissionNum` tinyint(3) UNSIGNED NOT NULL,
  `parameterId` int(10) UNSIGNED NOT NULL,
  `val` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `specialistId` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10035 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_commission_data
-- ----------------------------

-- ----------------------------
-- Table structure for ink_commission_group_access
-- ----------------------------
DROP TABLE IF EXISTS `ink_commission_group_access`;
CREATE TABLE `ink_commission_group_access`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `commissionGroupId` int(10) UNSIGNED NOT NULL,
  `groupId` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Доступ на редактирование параметров комиссии' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_commission_group_access
-- ----------------------------
INSERT INTO `ink_commission_group_access` VALUES (1, 1, 5);
INSERT INTO `ink_commission_group_access` VALUES (2, 2, 5);
INSERT INTO `ink_commission_group_access` VALUES (3, 3, 5);
INSERT INTO `ink_commission_group_access` VALUES (4, 4, 5);
INSERT INTO `ink_commission_group_access` VALUES (5, 5, 5);
INSERT INTO `ink_commission_group_access` VALUES (6, 6, 5);
INSERT INTO `ink_commission_group_access` VALUES (7, 7, 5);
INSERT INTO `ink_commission_group_access` VALUES (8, 8, 5);
INSERT INTO `ink_commission_group_access` VALUES (9, 4, 6);
INSERT INTO `ink_commission_group_access` VALUES (10, 9, 5);

-- ----------------------------
-- Table structure for ink_commission_groups
-- ----------------------------
DROP TABLE IF EXISTS `ink_commission_groups`;
CREATE TABLE `ink_commission_groups`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `access` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - доступ выбранным группам, 1 - доступ всем кроме выбранных групп',
  `orderNum` int(10) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_commission_groups
-- ----------------------------
INSERT INTO `ink_commission_groups` VALUES (1, 'Учитель-логопед', 0, 3);
INSERT INTO `ink_commission_groups` VALUES (2, 'Педагог-психолог', 0, 2);
INSERT INTO `ink_commission_groups` VALUES (3, 'Социальный педагог', 0, 7);
INSERT INTO `ink_commission_groups` VALUES (4, 'Классный руководитель ${teacher}', 0, 1);
INSERT INTO `ink_commission_groups` VALUES (5, 'Дефектолог', 0, 4);
INSERT INTO `ink_commission_groups` VALUES (6, 'Общие рекомендации', 0, 9);
INSERT INTO `ink_commission_groups` VALUES (7, 'Врач-ортопед', 0, 6);
INSERT INTO `ink_commission_groups` VALUES (8, 'Тьютор', 0, 5);
INSERT INTO `ink_commission_groups` VALUES (9, 'Нейропсихолог', 0, 8);

-- ----------------------------
-- Table structure for ink_commission_parameters
-- ----------------------------
DROP TABLE IF EXISTS `ink_commission_parameters`;
CREATE TABLE `ink_commission_parameters`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `orderNum` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `isFirstCommissionAccess` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `isSecondCommissionAccess` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `isThirdCommissionAccess` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_commission_parameters
-- ----------------------------
INSERT INTO `ink_commission_parameters` VALUES (1, 1, 1, 'из решения ПМПК ${ps_help}', 1, 0, 0);
INSERT INTO `ink_commission_parameters` VALUES (2, 1, 2, 'первичная диагностика', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (3, 1, 3, 'сведения о проделанной работе', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (4, 1, 4, 'результат', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (9, 2, 1, 'из решения ПМПК ${ps_help}', 1, 0, 0);
INSERT INTO `ink_commission_parameters` VALUES (10, 2, 2, 'первичная диагностика', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (11, 2, 3, 'сведения о проделанной работе', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (12, 2, 4, 'результат', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (13, 3, 1, 'из решения ПМПК ${ps_help}', 1, 0, 0);
INSERT INTO `ink_commission_parameters` VALUES (14, 3, 2, 'первичная диагностика', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (15, 3, 3, 'сведения о проделанной работе', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (16, 3, 4, 'результат', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (17, 4, 2, 'первичная диагностика', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (18, 4, 3, 'сведения о проделанной работе', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (19, 4, 4, 'результат', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (20, 5, 1, 'из решения ПМПК ${ps_help}', 1, 0, 0);
INSERT INTO `ink_commission_parameters` VALUES (21, 5, 2, 'первичная диагностика', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (22, 5, 3, 'сведения о проделанной работе', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (23, 5, 4, 'результат', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (24, 6, 1, 'Общие рекомендации', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (25, 7, 1, 'из решения ПМПК ${ps_help}', 1, 0, 0);
INSERT INTO `ink_commission_parameters` VALUES (26, 7, 2, 'первичная диагностика', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (27, 7, 3, 'сведения о проделанной работе', 1, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (28, 8, 1, 'из решения ПМПК ${ps_help}', 1, 0, 0);
INSERT INTO `ink_commission_parameters` VALUES (29, 8, 2, 'сведения о проделанной работе', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (30, 8, 3, 'результат', 0, 1, 1);
INSERT INTO `ink_commission_parameters` VALUES (31, 9, 1, 'проделанная работа', 1, 1, 1);

-- ----------------------------
-- Table structure for ink_commissions
-- ----------------------------
DROP TABLE IF EXISTS `ink_commissions`;
CREATE TABLE `ink_commissions`  (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'id - совпадает с id года',
  `isFirstLock` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `firstDate` date NULL DEFAULT NULL,
  `isSecondLock` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `secondDate` date NULL DEFAULT NULL,
  `isThirdLock` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `thirdDate` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_commissions
-- ----------------------------

-- ----------------------------
-- Table structure for ink_variants
-- ----------------------------
DROP TABLE IF EXISTS `ink_variants`;
CREATE TABLE `ink_variants`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parameterId` int(10) UNSIGNED NOT NULL,
  `val` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 79 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ink_variants
-- ----------------------------

-- ----------------------------
-- Table structure for lgp_increports
-- ----------------------------
DROP TABLE IF EXISTS `lgp_increports`;
CREATE TABLE `lgp_increports`  (
  `id` int(10) UNSIGNED NOT NULL,
  `docType` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - без нарушений; 1 - нарушение речи; 2 - нарушение письма; 3 - нарушение речи и письма',
  PRIMARY KEY (`id`) USING BTREE,
  CONSTRAINT `fk_lgp_increports_spc_increports` FOREIGN KEY (`id`) REFERENCES `spc_increports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of lgp_increports
-- ----------------------------

-- ----------------------------
-- Table structure for pln_plan_classes
-- ----------------------------
DROP TABLE IF EXISTS `pln_plan_classes`;
CREATE TABLE `pln_plan_classes`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `yearId` int(10) UNSIGNED NOT NULL,
  `classId` int(10) UNSIGNED NOT NULL,
  `dateFrom` date NOT NULL,
  `dateTo` date NOT NULL,
  `dayWeek` tinyint(1) UNSIGNED NOT NULL,
  `timeFrom` time(0) NOT NULL,
  `timeTo` time(0) NOT NULL,
  `activityType` tinyint(1) UNSIGNED NOT NULL,
  `activitySpecialist` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activityComment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `yearId`(`yearId`) USING BTREE,
  INDEX `classId`(`classId`) USING BTREE,
  CONSTRAINT `pln_plan_classes_ibfk_1` FOREIGN KEY (`yearId`) REFERENCES `years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pln_plan_classes_ibfk_2` FOREIGN KEY (`classId`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pln_plan_classes
-- ----------------------------

-- ----------------------------
-- Table structure for pln_plan_users
-- ----------------------------
DROP TABLE IF EXISTS `pln_plan_users`;
CREATE TABLE `pln_plan_users`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `yearId` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `dateFrom` date NOT NULL,
  `dateTo` date NOT NULL,
  `dayWeek` tinyint(1) UNSIGNED NOT NULL,
  `timeFrom` time(0) NOT NULL,
  `timeTo` time(0) NOT NULL,
  `activityType` tinyint(1) UNSIGNED NOT NULL,
  `activitySpecialist` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activityComment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  INDEX `yearId`(`yearId`) USING BTREE,
  CONSTRAINT `pln_plan_users_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users_base` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pln_plan_users_ibfk_2` FOREIGN KEY (`yearId`) REFERENCES `years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 50 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pln_plan_users
-- ----------------------------

-- ----------------------------
-- Table structure for rbl_list
-- ----------------------------
DROP TABLE IF EXISTS `rbl_list`;
CREATE TABLE `rbl_list`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `yearId` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL COMMENT 'id ребёнка',
  `specialistId` int(10) UNSIGNED NOT NULL COMMENT 'id должности',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - не в работе; 1 - в работе; 2 - приостановлена работа; 3 - работа завершена',
  `specialistUserId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id человека взявшего на себя работу',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  INDEX `yearId`(`yearId`) USING BTREE,
  CONSTRAINT `rbl_list_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users_base` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rbl_list_ibfk_2` FOREIGN KEY (`yearId`) REFERENCES `years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 84 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of rbl_list
-- ----------------------------

-- ----------------------------
-- Table structure for spc_increports
-- ----------------------------
DROP TABLE IF EXISTS `spc_increports`;
CREATE TABLE `spc_increports`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `yearId` int(10) UNSIGNED NOT NULL,
  `specialistId` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `isNeed` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `docDate` date NOT NULL,
  `val` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `examUserId` int(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `yearId`(`yearId`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  CONSTRAINT `spc_increports_ibfk_1` FOREIGN KEY (`yearId`) REFERENCES `years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `spc_increports_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users_base` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of spc_increports
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users_base` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Список пользователей с доступом и их пароли' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '$2y$10$wi.uded/hOvDUUGYTIb0F.3Lgnh1UWyXZmXX18vwijQIXfLB9ZK/W', 1);

-- ----------------------------
-- Table structure for users_base
-- ----------------------------
DROP TABLE IF EXISTS `users_base`;
CREATE TABLE `users_base`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `surname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `patronymic` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `gender` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - не определено, 1 - муж, 2 - жен',
  `birthday` date NULL DEFAULT NULL,
  `tag` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Поле для синхронизации с существующей БД',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4993 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Основаня информация о пользователях' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_base
-- ----------------------------
INSERT INTO `users_base` VALUES (1, 'Админ', '', '', 1, '2022-05-27', '');

-- ----------------------------
-- Table structure for users_classes
-- ----------------------------
DROP TABLE IF EXISTS `users_classes`;
CREATE TABLE `users_classes`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` int(10) UNSIGNED NOT NULL,
  `classId` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `classId`(`classId`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  CONSTRAINT `users_classes_ibfk_1` FOREIGN KEY (`classId`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_classes_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users_base` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 18272 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_classes
-- ----------------------------

-- ----------------------------
-- Table structure for users_extend
-- ----------------------------
DROP TABLE IF EXISTS `users_extend`;
CREATE TABLE `users_extend`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userBaseId` int(10) UNSIGNED NOT NULL,
  `addrCityName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrCityType` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrStreetName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrStreetType` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrHouse` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrFlat` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrIndex` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addrDistrict` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2464 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_extend
-- ----------------------------

-- ----------------------------
-- Table structure for users_groups
-- ----------------------------
DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE `users_groups`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` int(10) UNSIGNED NOT NULL,
  `groupId` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  INDEX `groupId`(`groupId`) USING BTREE,
  CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users_base` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2583 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_groups
-- ----------------------------
INSERT INTO `users_groups` VALUES (1, 1, 1);

-- ----------------------------
-- Table structure for vcm_extreports
-- ----------------------------
DROP TABLE IF EXISTS `vcm_extreports`;
CREATE TABLE `vcm_extreports`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` int(10) UNSIGNED NOT NULL,
  `docNumber` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `docDate` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vcm_extreports
-- ----------------------------
INSERT INTO `vcm_extreports` VALUES (6, 4987, '78', '2022-03-10');
INSERT INTO `vcm_extreports` VALUES (8, 4989, '33', '2022-03-11');
INSERT INTO `vcm_extreports` VALUES (9, 4988, '1', '2022-03-23');

-- ----------------------------
-- Table structure for vcm_extreports_items
-- ----------------------------
DROP TABLE IF EXISTS `vcm_extreports_items`;
CREATE TABLE `vcm_extreports_items`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reportId` int(10) UNSIGNED NOT NULL,
  `isNeed` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `specialistId` int(10) UNSIGNED NOT NULL,
  `recom` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vcm_extreports_items
-- ----------------------------
INSERT INTO `vcm_extreports_items` VALUES (8, 7, 1, 8, 'звукопроизношение шипящих');
INSERT INTO `vcm_extreports_items` VALUES (9, 6, 1, 7, 'внимание и память\nтревожность');
INSERT INTO `vcm_extreports_items` VALUES (10, 6, 1, 8, 'дисграфия');
INSERT INTO `vcm_extreports_items` VALUES (11, 8, 1, 8, 'звуковка');
INSERT INTO `vcm_extreports_items` VALUES (12, 9, 1, 8, 'нарушение звукопроизношения');
INSERT INTO `vcm_extreports_items` VALUES (13, 9, 1, 7, 'Развитие внимаия');

-- ----------------------------
-- Table structure for vcm_specialists
-- ----------------------------
DROP TABLE IF EXISTS `vcm_specialists`;
CREATE TABLE `vcm_specialists`  (
  `id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  CONSTRAINT `vcm_specialists_ibfk_1` FOREIGN KEY (`id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vcm_specialists
-- ----------------------------
INSERT INTO `vcm_specialists` VALUES (6);
INSERT INTO `vcm_specialists` VALUES (7);
INSERT INTO `vcm_specialists` VALUES (8);
INSERT INTO `vcm_specialists` VALUES (10);

-- ----------------------------
-- Table structure for years
-- ----------------------------
DROP TABLE IF EXISTS `years`;
CREATE TABLE `years`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `begindate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of years
-- ----------------------------
INSERT INTO `years` VALUES (20, '2021-2022', '2021-09-01');

SET FOREIGN_KEY_CHECKS = 1;
